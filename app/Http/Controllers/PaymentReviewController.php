<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Payment Review Requests (balance close-out).
 *
 * Flow (Andrew 2026-09-09):
 *   Sales Agent → "For Review Payment" (tabi ng Pay Balance): reason bakit hindi
 *     nag-zero (EWT/taxes/bawas), amount = FULL remaining balance (server-side),
 *     proof image (REQUIRED), reference # (optional). Status: requested.
 *   Accountant queue → i-verify ang proof.
 *     ACCEPT → settled amount ibinabawas sa balance (via review_settled_amount)
 *              → zero → ma-u-unlock ang DONE. Status: accepted → CEO/COO queue.
 *     REJECT → required ang reason. Status: rejected. (payment history + audit)
 *   CEO/COO queue → i-review ang in-accept ng Accountant → mark reviewed
 *     (timestamp + sino). HABANG accepted pa (hindi pa reviewed) → bawal i-archive.
 *   History sa baba: lahat ng hakbang + audit logs.
 *
 * Roles:
 *   request  : sales_agent, sales_representative, admin (same as agentPaymentStore)
 *   accountant queue/decision : accountant, admin (CEO oversight)
 *   exec review (CEO/COO)     : admin, coo
 *   Class-scoped (prod_manager/QA): Class department (4) only — same discipline.
 */
class PaymentReviewController extends Controller
{
    /* ------------------------------------------------------------------
     * Permission helpers
     * ---------------------------------------------------------------- */

    protected function canRequest(): bool
    {
        $u = auth()->user();
        return $u && ($u->isSalesAgent() || $u->isSalesRepresentative() || $u->isAdmin());
    }

    protected function canAccountant(): bool
    {
        $u = auth()->user();
        return $u && ($u->isAccountant() || $u->isAdmin());
    }

    protected function canExecReview(): bool
    {
        $u = auth()->user();
        return $u && ($u->isAdmin() || $u->isCoo());
    }

    protected function classScopeAbortIfBlocked(object $sale): void
    {
        $u = auth()->user();
        if ($u && $u->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
        }
    }

    /* ------------------------------------------------------------------
     * Sales Agent: submit a payment review request (full remaining balance)
     * ---------------------------------------------------------------- */

    public function store(Request $request, string $saleId)
    {
        $user = auth()->user();
        if (!$user || !$this->canRequest()) {
            return response()->json(['success' => false, 'message' => 'Only sales agents / sales representatives can request a payment review.'], 403);
        }

        $sale = DB::table('prototype_sales')->find($saleId);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }
        $this->classScopeAbortIfBlocked($sale);

        $data = $request->validate([
            'reason'           => 'required|string|max:1000',
            'reference_number' => 'nullable|string|max:255',
            'proof_image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        // Full remaining balance ONLY (no partial) — computed server-side.
        $saleModel = \App\Models\PrototypeSale::find($saleId);
        $remaining = (float) $saleModel->balance_due_computed;
        if ($remaining <= 0) {
            return response()->json(['success' => false, 'message' => 'Wala nang balance due — hindi na kailangan ng payment review.'], 422);
        }

        // One open request at a time per sale (requested = pending accountant, accepted = pending exec review)
        $open = DB::table('payment_review_requests')
            ->where('prototype_sale_id', $saleId)
            ->whereIn('status', ['requested', 'accepted'])
            ->exists();
        if ($open) {
            return response()->json(['success' => false, 'message' => 'May nakabinbing payment review na para sa sale na ito. Hintayin munang ma-process.'], 422);
        }

        $file = $request->file('proof_image');
        $filename = 'prr_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('uploads/payment-reviews', $filename, 'public');

        $reviewId = DB::table('payment_review_requests')->insertGetId([
            'prototype_sale_id' => $saleId,
            'requested_by'      => $user->id,
            'reason'            => trim($data['reason']),
            'amount'            => $remaining,
            'reference_number'  => trim((string) ($data['reference_number'] ?? '')),
            'proof_image'       => '/storage/' . $filePath,
            'status'            => 'requested',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('prototype_sale_audit_logs')->insert([
            'sale_id'     => $saleId,
            'user_id'     => $user->id,
            'action'      => 'payment_review_requested',
            'description' => 'Payment review requested (close-out ₱' . number_format($remaining, 2) . '): ' . trim($data['reason']),
            'details'     => json_encode([
                'review_id' => $reviewId,
                'amount'    => $remaining,
                'status'    => 'requested',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Payment review request submitted — naghihintay sa verification ng Accountant.',
            'review_id' => $reviewId,
        ]);
    }

    /* ------------------------------------------------------------------
     * Accountant queue
     * ---------------------------------------------------------------- */

    public function accountantQueue(Request $request)
    {
        if (!$this->canAccountant()) {
            abort(403, 'Only the Accountant (and Admin) can view the payment review queue.');
        }

        $user = auth()->user();
        $status = trim((string) $request->get('status', 'requested'));
        $q = trim((string) $request->get('q', ''));

        $query = DB::table('payment_review_requests')
            ->join('prototype_sales', 'payment_review_requests.prototype_sale_id', '=', 'prototype_sales.id')
            ->join('users as requester', 'payment_review_requests.requested_by', '=', 'requester.id')
            ->leftJoin('users as accountant', 'payment_review_requests.accountant_id', '=', 'accountant.id')
            ->select(
                'payment_review_requests.*',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.department_id',
                'prototype_sales.total_amount',
                'requester.name as requested_by_name',
                'accountant.name as accountant_name'
            );

        if ($user->isClassScoped()) {
            $query->where('prototype_sales.department_id', 4);
        }
        if (in_array($status, ['requested', 'accepted', 'rejected', 'reviewed', 'all'])) {
            if ($status !== 'all') {
                $query->where('payment_review_requests.status', $status);
            }
        } else {
            $query->where('payment_review_requests.status', 'requested');
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('prototype_sales.sales_number', 'like', $like)
                    ->orWhere('prototype_sales.customer_name', 'like', $like);
            });
        }

        $reviews = $query->orderByDesc('payment_review_requests.created_at')->paginate(100)->withQueryString();

        $counts = [
            'requested' => DB::table('payment_review_requests')->where('status', 'requested')->count(),
            'accepted'  => DB::table('payment_review_requests')->where('status', 'accepted')->count(),
            'rejected'  => DB::table('payment_review_requests')->where('status', 'rejected')->count(),
            'reviewed'  => DB::table('payment_review_requests')->where('status', 'reviewed')->count(),
        ];

        return view('sales.prototype.payment_review_accountant', compact('reviews', 'counts', 'status', 'q'));
    }

    /* ------------------------------------------------------------------
     * Accountant: accept / reject (reason required on reject)
     * ---------------------------------------------------------------- */

    public function accountantDecision(Request $request, string $reviewId)
    {
        $user = auth()->user();
        if (!$user || !$this->canAccountant()) {
            return response()->json(['success' => false, 'message' => 'Accountant access only.'], 403);
        }

        $review = DB::table('payment_review_requests')->find($reviewId);
        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review request not found.'], 404);
        }
        if ($review->status !== 'requested') {
            return response()->json(['success' => false, 'message' => 'Request already processed (' . $review->status . ').'], 422);
        }

        $sale = DB::table('prototype_sales')->find($review->prototype_sale_id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }
        $this->classScopeAbortIfBlocked($sale);

        $action = trim((string) $request->get('action', ''));
        $note = trim((string) $request->get('accountant_note', ''));

        if ($action === 'reject') {
            if ($note === '') {
                return response()->json(['success' => false, 'message' => 'Kailangan ng dahilan para i-reject ang request.'], 422);
            }
            if (mb_strlen($note) < 10) {
                return response()->json(['success' => false, 'message' => 'Masyadong maikli ang dahilan — ilagay kung bakit hindi na-approve (min 10 characters).'], 422);
            }

            DB::transaction(function () use ($reviewId, $review, $user, $note) {
                DB::table('payment_review_requests')->where('id', $reviewId)->update([
                    'status'              => 'rejected',
                    'accountant_id'       => $user->id,
                    'accountant_note'     => $note,
                    'accountant_action_at'=> now(),
                    'updated_at'          => now(),
                ]);

                DB::table('prototype_sale_audit_logs')->insert([
                    'sale_id'     => $review->prototype_sale_id,
                    'user_id'     => $user->id,
                    'action'      => 'payment_review_rejected',
                    'description' => 'Payment review #' . $review->id . ' REJECTED: ' . $note,
                    'details'     => json_encode(['review_id' => $review->id, 'amount' => $review->amount, 'reason' => $note]),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Request rejected — naitala na sa payment history at audit trail.']);
        }

        if ($action === 'accept') {
            // Full amount only: hindi pa-approve kapag may image proof man lang ang kulang
            // o hindi nito kayang i-cover ang BUONG natitirang balance.
            $saleModel = \App\Models\PrototypeSale::find($review->prototype_sale_id);
            $remainingNow = (float) $saleModel->balance_due_computed;

            if ((float) $review->amount < $remainingNow - 0.005) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hindi ito FULL close-out — ang request ay ₱' . number_format($review->amount, 2)
                        . ' pero ang natitirang balance ay ₱' . number_format($remainingNow, 2)
                        . '. I-reject na lang ito at mag-request ulit ng buong balance.',
                ], 422);
            }

            // Para iwas over-settle, ang ibababa ay ang CURRENT remaining (kung may partial
            // payment na na-verify habang nakabinbin, mas maliit na ito kaysa sa in-request).
            $settleAmount = min((float) $review->amount, $remainingNow);

            DB::transaction(function () use ($reviewId, $review, $user, $note, $settleAmount) {
                $saleRow = DB::table('prototype_sales')->where('id', $review->prototype_sale_id)->first();

                DB::table('prototype_sales')->where('id', $review->prototype_sale_id)->update([
                    'review_settled_amount' => ((float) $saleRow->review_settled_amount) + $settleAmount,
                    'balance_due'           => max((float) $saleRow->balance_due - $settleAmount, 0),
                    'updated_at'            => now(),
                ]);

                DB::table('payment_review_requests')->where('id', $reviewId)->update([
                    'status'              => 'accepted',
                    'accountant_id'       => $user->id,
                    'accountant_note'     => $note !== '' ? $note : null,
                    'accountant_action_at'=> now(),
                    'updated_at'          => now(),
                ]);

                DB::table('prototype_sale_audit_logs')->insert([
                    'sale_id'     => $review->prototype_sale_id,
                    'user_id'     => $user->id,
                    'action'      => 'payment_review_accepted',
                    'description' => 'Payment review #' . $review->id . ' ACCEPTED — close-out ₱' . number_format($settleAmount, 2)
                        . ' ibinawas sa balance (DONE unlocked).' . ($note !== '' ? ' Note: ' . $note : ''),
                    'details'     => json_encode(['review_id' => $review->id, 'requested' => $review->amount, 'settled' => $settleAmount]),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Request accepted — balance closed out at na-unlock ang DONE. Nakapila na ito sa close-out review.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 422);
    }

    /* ------------------------------------------------------------------
     * CEO/COO review queue (accepted by Accountant → mark reviewed)
     * ---------------------------------------------------------------- */

    public function executiveQueue(Request $request)
    {
        if (!$this->canExecReview()) {
            abort(403, 'Only the Admin can review accepted payment reviews.');
        }

        $user = auth()->user();
        $filter = trim((string) $request->get('filter', 'pending')); // pending | reviewed | all
        $q = trim((string) $request->get('q', ''));

        $query = DB::table('payment_review_requests')
            ->join('prototype_sales', 'payment_review_requests.prototype_sale_id', '=', 'prototype_sales.id')
            ->join('users as requester', 'payment_review_requests.requested_by', '=', 'requester.id')
            ->leftJoin('users as accountant', 'payment_review_requests.accountant_id', '=', 'accountant.id')
            ->leftJoin('users as reviewer', 'payment_review_requests.reviewed_by', '=', 'reviewer.id')
            ->select(
                'payment_review_requests.*',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.department_id',
                'prototype_sales.total_amount',
                'requester.name as requested_by_name',
                'accountant.name as accountant_name',
                'reviewer.name as reviewed_by_name'
            );

        if ($user->isClassScoped()) {
            $query->where('prototype_sales.department_id', 4);
        }
        if ($filter === 'pending') {
            $query->where('payment_review_requests.status', 'accepted');
        } elseif ($filter === 'reviewed') {
            $query->where('payment_review_requests.status', 'reviewed');
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('prototype_sales.sales_number', 'like', $like)
                    ->orWhere('prototype_sales.customer_name', 'like', $like);
            });
        }

        $reviews = $query->orderByDesc('payment_review_requests.updated_at')->paginate(100)->withQueryString();

        return view('sales.prototype.payment_review_executive', compact('reviews', 'filter', 'q'));
    }

    public function markReviewed(Request $request, string $reviewId)
    {
        $user = auth()->user();
        if (!$user || !$this->canExecReview()) {
            return response()->json(['success' => false, 'message' => 'Admin access only for close-out review.'], 403);
        }

        $review = DB::table('payment_review_requests')->find($reviewId);
        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review request not found.'], 404);
        }
        if ($review->status !== 'accepted') {
            return response()->json(['success' => false, 'message' => 'Only accepted requests (pending review) can be marked as reviewed.'], 422);
        }

        $sale = DB::table('prototype_sales')->find($review->prototype_sale_id);
        $this->classScopeAbortIfBlocked($sale);

        DB::transaction(function () use ($reviewId, $review, $user) {
            DB::table('payment_review_requests')->where('id', $reviewId)->update([
                'status'      => 'reviewed',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'updated_at'  => now(),
            ]);

            DB::table('prototype_sale_audit_logs')->insert([
                'sale_id'     => $review->prototype_sale_id,
                'user_id'     => $user->id,
                'action'      => 'payment_review_reviewed',
                'description' => 'Payment review #' . $review->id . ' marked as REVIEWED. Puwede nang i-archive ang sale.',
                'details'     => json_encode(['review_id' => $review->id]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Marked as reviewed — puwede nang i-archive ang sale.']);
    }
}
