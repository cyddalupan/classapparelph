<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\LayoutJob;
use App\Models\LayoutJobPayout;

/**
 * Layout Job System — standalone layout jobs (bayad / libre) for Full Sublimation.
 *
 * Flow:
 *   User (admin/sales/qa/staff) → create Layout Job (bayad: payment+ref+image; libre: tag GA lang)
 *   → tagged GA sees job → does layout → mark done
 *   → Approver (admin/coo/cpo/cmo) reviews all jobs in Layout Job List:
 *        • bayad → verify payment
 *        • libre → pwedeng lagyan ng amount (reflect sa GA)
 *   → GA payout request (total layout) → sa Layout Job List ng approver → approver
 *     uploads payment proof → verified → bawas credit ng GA
 *   → bayad na layout lang ang nalilink sa sale (libre = HINDI)
 *
 * Access:
 *   canCreate  : admin, staff, coo, sales_agent, sales_representative, prod_manager, qa
 *   canReview  : admin (approver), coo, cpo, cmo
 *   GA actions : ga role (own jobs) — done + payout request
 */
class LayoutJobController extends Controller
{
    /* ------------------------------------------------------------------
     * Permission helpers
     * ---------------------------------------------------------------- */

    private function canCreate(): bool
    {
        $u = auth()->user();
        if (!$u) return false;
        return in_array($u->role, [
            'admin', 'staff', 'coo', 'sales_agent', 'sales_representative',
            'prod_manager', 'qa',
        ]);
    }

    private function canReview(): bool
    {
        $u = auth()->user();
        return $u && ($u->isAdmin() || $u->isCoo() || $u->isCpo() || $u->isCmo());
    }

    private function isGaUser(): bool
    {
        $u = auth()->user();
        return $u && $u->isGa();
    }

    /* ------------------------------------------------------------------
     * Views
     * ---------------------------------------------------------------- */

    /**
     * Layout Job List — pangkalahatan (approver review) o own-scoped (GA / creator).
     */
    public function index(Request $request)
    {
        $u = auth()->user();
        if (!$this->canCreate() && !$this->canReview() && !$this->isGaUser()) {
            abort(403, 'Unauthorized.');
        }

        $query = LayoutJob::with(['gaUser', 'creator', 'customer', 'sale', 'payout'])
            ->orderByDesc('id');

        // GA: sariling jobs lang
        if ($this->isGaUser() && !$this->canReview()) {
            $query->where('ga_user_id', $u->id);
        }

        // Filters
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->get('status')) {
            if ($status === 'done') {
                $query->where('status', 'done');
            } elseif ($status === 'open') {
                $query->where('status', 'open');
            } elseif ($status === 'payment_pending') {
                $query->where('type', 'paid')->where('payment_status', 'pending');
            } elseif ($status === 'no_amount') {
                $query->whereNull('amount'); // libre na wala pang amount
            }
        }
        if ($ga = $request->get('ga')) {
            $query->where('ga_user_id', $ga);
        }
        if ($q = trim($request->get('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('job_no', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $jobs = $query->paginate(25)->withQueryString();

        // GA credit: SUM(earning jobs) − SUM(verified payouts)
        $credit = null;
        if ($this->isGaUser()) {
            $credit = $this->gaCredit($u->id);
        }

        $gaUsers = DB::table('users')
            ->where('role', 'ga')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('sales.layout_jobs.index', compact('jobs', 'gaUsers', 'credit'));
    }

    /** GA credit computation */
    public function gaCredit(int $gaUserId): float
    {
        $earned = (float) LayoutJob::where('ga_user_id', $gaUserId)
            ->where('status', 'done')
            ->where(function ($q) {
                $q->where(function ($sub) { // bayad na verified
                    $sub->where('type', 'paid')->where('payment_status', 'verified');
                })->orWhere(function ($sub) { // libre na may amount
                    $sub->where('type', 'free')->whereNotNull('amount');
                });
            })
            ->sum('amount');

        $paidOut = (float) LayoutJobPayout::where('ga_user_id', $gaUserId)
            ->whereIn('status', ['verified'])
            ->sum('amount');

        return max(0, $earned - $paidOut);
    }

    /**
     * Create page/modal data — returns view (form) for now; store handles POST.
     */
    public function create()
    {
        if (!$this->canCreate()) {
            abort(403, 'Unauthorized.');
        }
        $gaUsers = DB::table('users')->where('role', 'ga')->orderBy('name')->get(['id', 'name']);
        return view('sales.layout_jobs.create', compact('gaUsers'));
    }

    /* ------------------------------------------------------------------
     * Mutations
     * ---------------------------------------------------------------- */

    /**
     * Store new layout job. Bayad: require amount + payment fields.
     */
    public function store(Request $request)
    {
        if (!$this->canCreate()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'customer_id'   => 'nullable|integer',
            'customer_name' => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|in:paid,free',
            'amount'        => 'nullable|numeric|min:0',
            'payment_method'=> 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:255',
            'ga_user_id'    => 'required|integer|exists:users,id',
            'reference_image' => 'nullable|image|max:5120',
            'payment_screenshot' => 'nullable|image|max:5120',
        ]);

        // Bayad: kailangan ng payment info
        if ($data['type'] === 'paid') {
            if (empty($data['amount'])) {
                return response()->json(['error' => 'Bayad na layout — kailangan ng amount.'], 422);
            }
            if (empty($data['payment_method'])) {
                return response()->json(['error' => 'Bayad na layout — piliin ang payment method.'], 422);
            }
            if (empty($data['payment_reference']) && !$request->hasFile('payment_screenshot')) {
                return response()->json(['error' => 'Bayad na layout — maglagay ng reference number O payment screenshot.'], 422);
            }
        }

        $jobNo = LayoutJob::nextJobNo();

        // Upload reference image
        $refPath = null;
        if ($request->hasFile('reference_image')) {
            $refPath = $request->file('reference_image')->store('layout-jobs/reference', 'public');
        }

        // Upload payment screenshot (bayad)
        $payPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $payPath = $request->file('payment_screenshot')->store('layout-jobs/payment', 'public');
        }

        $job = LayoutJob::create([
            'job_no' => $jobNo,
            'customer_id' => $data['customer_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'description' => $data['description'] ?? null,
            'reference_image_path' => $refPath,
            'type' => $data['type'],
            'amount' => $data['type'] === 'paid' ? $data['amount'] : null,
            'payment_method' => $data['type'] === 'paid' ? ($data['payment_method'] ?? null) : null,
            'payment_reference' => $data['type'] === 'paid' ? ($data['payment_reference'] ?? null) : null,
            'payment_screenshot_path' => $payPath,
            'payment_status' => $data['type'] === 'paid' ? 'pending' : null,
            'ga_user_id' => $data['ga_user_id'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        return response()->json(['ok' => true, 'job_no' => $jobNo, 'id' => $job->id]);
    }

    /**
     * Approver: verify bayad na layout job payment (pending → verified/rejected).
     */
    public function verifyPayment(Request $request, int $id)
    {
        if (!$this->canReview()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $job = LayoutJob::findOrFail($id);
        if ($job->type !== 'paid') {
            return response()->json(['error' => 'Hindi bayad na layout ito.'], 422);
        }

        $action = $request->get('action', 'verify');
        if ($action === 'verify') {
            $job->update([
                'payment_status' => 'verified',
                'payment_verified_by' => auth()->id(),
                'payment_verified_at' => now(),
                'payment_reject_reason' => null,
            ]);
        } else {
            $job->update([
                'payment_status' => 'rejected',
                'payment_reject_reason' => $request->get('reason') ?: 'No reason provided',
            ]);
        }

        return response()->json(['ok' => true, 'payment_status' => $job->payment_status]);
    }

    /**
     * Approver: maglagay ng amount sa libreng layout job → reflect sa GA + history.
     */
    public function setAmount(Request $request, int $id)
    {
        if (!$this->canReview()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $job = LayoutJob::findOrFail($id);
        $amount = (float) $request->validate(['amount' => 'required|numeric|min:0'])['amount'];

        $job->update([
            'amount' => $amount,
            'amount_set_by' => auth()->id(),
            'amount_set_at' => now(),
        ]);

        return response()->json(['ok' => true, 'amount' => $job->amount]);
    }

    /**
     * GA: mark layout job as done.
     */
    public function done(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::findOrFail($id);

        $isOwnGa = $this->isGaUser() && $job->ga_user_id === $u->id;
        if (!$isOwnGa && !$this->canReview()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        if ($job->status === 'done') {
            return response()->json(['error' => 'Done na ang job na ito.'], 422);
        }

        $job->update([
            'status' => 'done',
            'done_by' => $u->id,
            'done_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * GA: mag-request ng payout ng kanyang total layout credit.
     * Gumagawa ng payout record; lahat ng earning (done) jobs na wala pang payout
     * ay ililink sa payout request na ito.
     */
    public function requestPayout(Request $request)
    {
        $u = auth()->user();
        if (!$this->isGaUser() && !$this->canReview()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $gaUserId = $this->isGaUser() ? $u->id : (int) $request->get('ga_user_id');
        if (!$gaUserId) {
            return response()->json(['error' => 'Kailangan ng GA.'], 422);
        }

        // Earning + done + belum linked sa payout
        $eligible = LayoutJob::where('ga_user_id', $gaUserId)
            ->where('status', 'done')
            ->whereNull('payout_id')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('type', 'paid')->where('payment_status', 'verified');
                })->orWhere(function ($sub) {
                    $sub->where('type', 'free')->whereNotNull('amount');
                });
            })
            ->get();

        if ($eligible->isEmpty()) {
            return response()->json(['error' => 'Wala pang eligible na layout jobs (done + verified/may amount).'], 422);
        }

        $total = (float) $eligible->sum('amount');

        DB::transaction(function () use ($eligible, $total, $u, $gaUserId, $request) {
            $payout = LayoutJobPayout::create([
                'ga_user_id' => $gaUserId,
                'amount' => $total,
                'status' => 'requested',
                'requested_by' => $u->id,
                'requested_at' => now(),
                'request_notes' => $request->get('notes'),
            ]);
            $eligible->each(function ($job) use ($payout) {
                $job->update([
                    'payout_id' => $payout->id,
                    'payout_requested_at' => now(),
                ]);
            });
        });

        return response()->json(['ok' => true, 'amount' => $total]);
    }

    /**
     * Approver: sagutin ang payout request — mag-upload ng payment proof.
     * status: requested → paid (may proof) → verified (credit bawas) | rejected.
     */
    public function payPayout(Request $request, int $payoutId)
    {
        if (!$this->canReview()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $payout = LayoutJobPayout::findOrFail($payoutId);
        $action = $request->get('action', 'pay');

        if ($action === 'reject') {
            $payout->update([
                'status' => 'rejected',
                'reject_reason' => $request->get('reason') ?: 'No reason provided',
            ]);
            // I-unlink ang jobs para makapag-request ulit ang GA
            LayoutJob::where('payout_id', $payout->id)->update(['payout_id' => null, 'payout_requested_at' => null]);
            return response()->json(['ok' => true, 'status' => 'rejected']);
        }

        $data = $request->validate([
            'payment_method'    => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:255',
            'payment_proof'     => 'nullable|image|max:5120',
        ]);

        $proofPath = $payout->payment_proof_path;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('layout-jobs/payouts', 'public');
        }

        if ($action === 'verify') {
            // Diretso verify — credit mababawas
            $payout->update([
                'status' => 'verified',
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_proof_path' => $proofPath,
                'paid_by' => auth()->id(),
                'paid_at' => now(),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'reject_reason' => null,
            ]);
        } else {
            // pay muna (may proof), verify later
            $payout->update([
                'status' => 'paid',
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_proof_path' => $proofPath,
                'paid_by' => auth()->id(),
                'paid_at' => now(),
                'reject_reason' => null,
            ]);
        }

        return response()->json(['ok' => true, 'status' => $payout->status]);
    }

    /**
     * Approver/creator: ilink ang bayad na layout job sa sale.
     * LIBRE = HINDI pwedeng i-link (spec ni Andrew).
     */
    public function linkSale(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::findOrFail($id);
        if ($job->type !== 'paid') {
            return response()->json(['error' => 'Libreng layout — hindi ito idinadagdag sa sales.'], 422);
        }
        if ($job->payment_status !== 'verified') {
            return response()->json(['error' => 'I-verify muna ang payment bago i-link sa sale.'], 422);
        }
        if (!$this->canReview() && $job->created_by !== $u->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $saleId = (int) $request->validate(['sale_id' => 'required|integer'])['sale_id'];

        $job->update([
            'sale_id' => $saleId,
            'linked_to_sale_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /* ------------------------------------------------------------------
     * API: pending counts para sa nav badges (optional)
     * ---------------------------------------------------------------- */

    public function pendingCounts()
    {
        $u = auth()->user();
        if (!$u) return response()->json([]);

        $counts = ['payment_pending' => 0, 'payout_requests' => 0];

        if ($this->canReview()) {
            $counts['payment_pending'] = LayoutJob::where('type', 'paid')
                ->where('payment_status', 'pending')->count();
            $counts['payout_requests'] = LayoutJobPayout::where('status', 'requested')->count();
        }

        if ($this->isGaUser()) {
            $counts['my_open'] = LayoutJob::where('ga_user_id', $u->id)
                ->where('status', 'open')->count();
        }

        return response()->json($counts);
    }
}
