<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\LayoutJob;
use App\Models\LayoutJobPayout;
use App\Models\PaymentAccount;

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
 *   canCreate  : admin, staff, coo, cpo, cmo, sales_agent, sales_representative, prod_manager, qa
 *   canReview  : admin (approver), coo, cpo, cmo
 *   Layout-doer (credit + payout request) : admin, coo, cpo, cmo, ga
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
            'admin', 'staff', 'coo', 'cpo', 'cmo', 'sales_agent', 'sales_representative',
            'prod_manager', 'qa', 'hr_accountant_agent',
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

    /** Layout-doer: tumatanggap ng bayad sa layout (admin/coo/cpo/cmo/ga) */
    private function isLayoutDoer(): bool
    {
        $u = auth()->user();
        return $u && in_array($u->role, ['admin', 'coo', 'cpo', 'cmo', 'ga']);
    }

    /** Aktibong payment accounts para sa payment method dropdown */
    private function paymentAccounts()
    {
        return PaymentAccount::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'provider', 'account_number', 'user_id']);
    }

    /* ------------------------------------------------------------------
     * Views
     * ---------------------------------------------------------------- */

    /**
     * Personal Layout Job — jobs na assigned sa akin (ga_user_id) O ginawa ko (created_by).
     * Para sa lahat ng layout-doers (admin/coo/cpo/cmo/ga) at mga creators.
     */
    public function index(Request $request)
    {
        $u = auth()->user();
        if (!$this->canCreate() && !$this->canReview() && !$this->isGaUser()) {
            abort(403, 'Unauthorized.');
        }

        $query = LayoutJob::with(['gaUser', 'creator', 'customer', 'sale', 'payout', 'paymentAccount'])
            ->orderByDesc('id');

        // Personal scope: assigned sa akin O ako ang gumawa
        $query->where(function ($q) use ($u) {
            $q->where('ga_user_id', $u->id)
              ->orWhere('created_by', $u->id);
        });

        $this->applyFilters($query, $request, $u);

        $jobs = $query->paginate(25)->withQueryString();

        // Layout credit: para sa LAHAT ng layout-doers (admin/coo/cpo/cmo/ga) — credit box + payout request
        $credit = null;
        if ($this->isLayoutDoer()) {
            $credit = $this->gaCredit($u->id);
        }

        $gaUsers = $this->layoutDoerUsers();
        $paymentAccounts = $this->paymentAccounts();
        $mode = 'personal';

        // Sariling payout requests (reservation-based statuses) para sa personal list
        $payoutRequests = collect();
        if ($this->isLayoutDoer()) {
            $payoutRequests = LayoutJobPayout::with('gaUser')
                ->where('ga_user_id', $u->id)
                ->orderByDesc('id')->limit(10)->get();
        }
        $payoutMap = $this->buildPayoutMap($jobs, $payoutRequests);

        return view('sales.layout_jobs.index', compact('jobs', 'gaUsers', 'credit', 'paymentAccounts', 'mode', 'payoutRequests', 'payoutMap'));
    }

    /**
     * Global Layout Job List — lahat ng jobs, para sa approvers (admin/coo/cpo/cmo).
     */
    public function all(Request $request)
    {
        $u = auth()->user();
        if (!$this->canReview()) {
            abort(403, 'Unauthorized.');
        }

        $query = LayoutJob::with(['gaUser', 'creator', 'customer', 'sale', 'payout', 'paymentAccount'])
            ->orderByDesc('id');

        $this->applyFilters($query, $request, $u);

        $jobs = $query->paginate(25)->withQueryString();

        $gaUsers = $this->layoutDoerUsers();
        $paymentAccounts = $this->paymentAccounts();
        $credit = null;
        $mode = 'global';

        // Pending payout requests (requested/paid) — para sa review panel ng approver
        $payoutRequests = LayoutJobPayout::with('gaUser')
            ->whereIn('status', ['requested', 'paid'])
            ->orderByDesc('id')->get();
        $payoutMap = $this->buildPayoutMap($jobs, $payoutRequests);

        return view('sales.layout_jobs.index', compact('jobs', 'gaUsers', 'credit', 'paymentAccounts', 'mode', 'payoutRequests', 'payoutMap'));
    }

    /**
     * Payout info map (id => details) para sa Pay modal — galing sa payout list
     * at sa mga payout na naka-link sa kasalukuyang jobs (legacy links).
     */
    private function buildPayoutMap($jobs, $payoutRequests)
    {
        $map = [];
        $collect = function ($p) use (&$map) {
            if (!$p) return;
            $map[$p->id] = [
                'id'      => $p->id,
                'amount'  => (float) $p->amount,
                'ga'      => $p->gaUser?->name ?? '',
                'account' => trim(($p->account_name ?? '') . ' / ' . ($p->account_number ?? '')),
                'notes'   => $p->request_notes ?? '',
                'proof'   => $p->account_proof_path ? asset('storage/' . $p->account_proof_path) : '',
                'status'  => $p->status,
            ];
        };
        foreach ($payoutRequests as $p) $collect($p);
        foreach ($jobs as $j) $collect($j->payout ?? null);
        return $map;
    }

    /** Shared filter logic para sa index() at all() */
    private function applyFilters($query, Request $request, $u)
    {
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
    }

    /** Layout-doer users: admin/coo/cpo/cmo + ga (para sa assignee/GA filter dropdown) */
    private function layoutDoerUsers()
    {
        return DB::table('users')
            ->whereIn('role', ['admin', 'coo', 'cpo', 'cmo', 'ga'])
            ->orderBy('name')
            ->get(['id', 'name', 'position']);
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

        // Reservation-based: lahat ng hindi pa rejected (requested/paid/verified)
        // ay bawas agad sa available credit para hindi mag-double request.
        $reserved = (float) LayoutJobPayout::where('ga_user_id', $gaUserId)
            ->whereIn('status', ['requested', 'paid', 'verified'])
            ->sum('amount');

        return max(0, round($earned - $reserved, 2));
    }

    /**
     * Create page/modal data — returns view (form) for now; store handles POST.
     */
    public function create()
    {
        if (!$this->canCreate()) {
            abort(403, 'Unauthorized.');
        }
        $gaUsers = $this->layoutDoerUsers();
        $paymentAccounts = $this->paymentAccounts();
        return view('sales.layout_jobs.create', compact('gaUsers', 'paymentAccounts'));
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
            'payment_account_id' => 'nullable|integer|exists:payment_accounts,id',
            'payment_reference' => 'nullable|string|max:255',
            'ga_user_id'    => 'required|integer|exists:users,id',
            'reference_image' => 'nullable|image|max:5120',
            'payment_screenshot' => 'nullable|image|max:5120',
        ]);

        // Bayad: kailangan ng payment info (actual payment account, hindi generic)
        $accountName = null;
        if ($data['type'] === 'paid') {
            if (empty($data['amount'])) {
                return response()->json(['error' => 'Bayad na layout — kailangan ng amount.'], 422);
            }
            if (empty($data['payment_account_id'])) {
                return response()->json(['error' => 'Bayad na layout — piliin kung saang account binayad (hal. Drew Gcash, Jemel Gcash).'], 422);
            }
            $account = PaymentAccount::where('is_active', true)->find($data['payment_account_id']);
            if (!$account) {
                return response()->json(['error' => 'Hindi valid ang payment account na pinili.'], 422);
            }
            $accountName = $account->name;
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
            'payment_method' => $data['type'] === 'paid' ? $accountName : null,
            'payment_account_id' => $data['type'] === 'paid' ? ($data['payment_account_id'] ?? null) : null,
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
     * Verify/reject bayad na layout job payment.
     * Permission: admin O ang may-ari ng payment account (tulad ng sales/prototype system).
     */
    public function verifyPayment(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::with('paymentAccount')->findOrFail($id);
        if ($job->type !== 'paid') {
            return response()->json(['error' => 'Hindi bayad na layout ito.'], 422);
        }

        // Account owner check: company accounts (walang user_id) → admin lang.
        $accountOwnerId = $job->paymentAccount?->user_id;
        $isAdmin = $u && $u->isAdmin();
        $isOwner = $accountOwnerId && $u && $u->id === $accountOwnerId;
        if (!$isAdmin && !$isOwner) {
            $ownerName = $job->paymentAccount?->user?->name ?? 'Company';
            return response()->json([
                'error' => "Ikaw ay hindi ang verifier ng payment na ito. Ang payment ay nasa " . ($job->paymentAccount?->name ?? 'account') . " — si " . $ownerName . " (o admin) lang ang pwedeng mag-verify."
            ], 403);
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
     * Layout-doer: mag-request ng payout (buo o partial) ng available layout credit.
     * Reservation-based: ang amount na nirequest (status requested/paid/verified)
     * ay bawas agad sa available credit para hindi mag-double request.
     * Approvers ay pwedeng mag-request on behalf (ga_user_id param) kung hindi sarili.
     */
    public function requestPayout(Request $request)
    {
        $u = auth()->user();
        if (!$this->isLayoutDoer()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Default: sarili. Approver pwede mag-request para sa ibang layout-doer.
        $gaUserId = (int) ($request->get('ga_user_id') ?: $u->id);
        if (!$gaUserId) {
            return response()->json(['error' => 'Kailangan ng layout-doer.'], 422);
        }
        if (!$this->canReview() && $gaUserId !== $u->id) {
            return response()->json(['error' => 'Hindi ka pwedeng mag-request para sa iba.'], 403);
        }

        $data = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'account_name'   => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'notes'          => 'nullable|string|max:1000',
            'account_proof'  => 'nullable|image|max:5120',
        ]);

        $amount = round((float) $data['amount'], 2);
        $available = $this->gaCredit($gaUserId);
        if ($amount > $available + 0.001) {
            return response()->json([
                'error' => 'Halagang ₱' . number_format($amount, 2) . ' ay lampas sa available credit mo (₱' . number_format($available, 2) . ').',
            ], 422);
        }

        $proofPath = null;
        if ($request->hasFile('account_proof')) {
            $proofPath = $request->file('account_proof')->store('layout-jobs/payout-proofs', 'public');
        }

        $payout = LayoutJobPayout::create([
            'ga_user_id'         => $gaUserId,
            'amount'             => $amount,
            'status'             => 'requested',
            'requested_by'       => $u->id,
            'requested_at'       => now(),
            'request_notes'      => $data['notes'] ?? null,
            'account_name'       => $data['account_name'],
            'account_number'     => $data['account_number'],
            'account_proof_path' => $proofPath,
        ]);

        return response()->json([
            'ok' => true, 'payout_id' => $payout->id, 'amount' => $amount,
            'available' => round($available - $amount, 2),
        ]);
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
        // Spec (2026-09-09): pagkatapos ma-verify, ang nag-create ng job (sales agent/user) lang ang
        // pwedeng mag-link ng sale — sila ang nakakaalam ng sales number.
        if ($job->created_by !== $u->id) {
            return response()->json(['error' => 'Ikaw lang ang gumawa ng job na ito ang pwedeng mag-link ng sale.'], 403);
        }

        // Tanggapin ang numeric prototype_sales.id O ang sales_number (e.g. SALE-2026-...)
        $input = trim((string) $request->input('sale_id', ''));
        if ($input === '') {
            return response()->json(['error' => 'Ilagay ang Sale ID o Sales #.'], 422);
        }

        $sale = \App\Models\PrototypeSale::where('id', $input)
            ->orWhere('sales_number', $input)
            ->first();

        if (!$sale) {
            return response()->json(['error' => 'Hindi nahanap ang sale — i-check ang Sales # o ID na nilagay.'], 422);
        }

        $job->update([
            'sale_id' => $sale->id,
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
