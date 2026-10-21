<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\LayoutJob;
use App\Models\LayoutJobPayout;
use App\Models\PaymentAccount;
use App\Services\ImageOptimizer;

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
    /** Ilang araw na lock bago pwedeng i-done ulit pagkatapos ng retag/undo (Andrew 2026-10-21) */
    private const RETAG_LOCK_DAYS = 7;

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

        // Set Amount history (Andrew 2026-09-17) — sino ang nag-set ng amount at kailan.
        //  • Libre: via "Set Amount" ni approver (amount_set_by / amount_set_at)
        //  • Bayad: amount na naisama nung ginawa ang job (created_by / created_at)
        // Sinusundan din ang parehong filters ng main list (type/status/ga/agent/q).
        $historyQ = LayoutJob::with(['gaUser', 'amountSetter', 'creator', 'customer', 'sale'])
            ->where(function ($q) {
                $q->whereNotNull('amount_set_by')
                  ->orWhere(function ($q2) {
                      $q2->where('type', 'paid')->whereNotNull('amount');
                  });
            });
        $this->applyFilters($historyQ, $request, $u);
        $amountSetHistory = $historyQ
            ->orderByDesc(DB::raw('COALESCE(amount_set_at, created_at)'))
            ->limit(45)
            ->get();

        // Galing kay (Agent / Sales Agent) — mga user na gumawa ng layout jobs
        $agentUsers = DB::table('users')
            ->whereIn('id', function ($sub) {
                $sub->select('created_by')->from('layout_jobs')->whereNotNull('created_by');
            })
            ->orderBy('name')->get(['id', 'name']);

        return view('sales.layout_jobs.index', compact('jobs', 'gaUsers', 'credit', 'paymentAccounts', 'mode', 'payoutRequests', 'payoutMap', 'amountSetHistory', 'agentUsers'));
    }

    /**
     * Payout Requests — sariling page na (Andrew 2026-10-21).
     * Dati naka-collapse/dropdown lang sa Layout Job List (All).
     */
    public function payouts(Request $request)
    {
        if (!$this->canReview()) {
            abort(403, 'Unauthorized.');
        }

        $pending = LayoutJobPayout::with('gaUser')
            ->whereIn('status', ['requested', 'paid'])
            ->orderByDesc('id')->get();

        $history = LayoutJobPayout::with(['gaUser', 'receivedBy'])
            ->whereIn('status', ['verified', 'rejected'])
            ->orderByDesc('id')->limit(100)->get();

        $payoutMap = $this->buildPayoutMap(collect(), $pending);
        $paymentAccounts = $this->paymentAccounts();

        return view('sales.layout_jobs.payouts', compact('pending', 'history', 'payoutMap', 'paymentAccounts'));
    }

    /**
     * Layout Dashboard (Andrew 2026-09-17) — per layout-doer money view:
     *   Earned (nakamagkano na) · Nabigay na (given) · Pending request · Available (pwede pa makuha).
     * Approvers (admin/coo/cpo/cmo) lang ang may access.
     * Filter: GA + period (this/last month, this year, custom range).
     */
    public function dashboard(Request $request)
    {
        if (!$this->canReview()) {
            abort(403, 'Unauthorized.');
        }

        $gaFilter = $request->get('ga', '');
        $period   = $request->get('period', 'all'); // all|this_month|last_month|this_year|custom
        $fromIn   = $request->get('from', '');
        $toIn     = $request->get('to', '');

        // Resolve period → [from, to] (Y-m-d) o null para sa "all".
        $dFrom = $dTo = null;
        $now = now();
        if ($period === 'this_month') {
            $dFrom = $now->copy()->startOfMonth()->toDateString();
            $dTo   = $now->copy()->endOfMonth()->toDateString();
        } elseif ($period === 'last_month') {
            $prev = $now->copy()->subMonthNoOverflow();
            $dFrom = $prev->copy()->startOfMonth()->toDateString();
            $dTo   = $prev->copy()->endOfMonth()->toDateString();
        } elseif ($period === 'this_year') {
            $dFrom = $now->copy()->startOfYear()->toDateString();
            $dTo   = $now->copy()->endOfYear()->toDateString();
        } elseif ($period === 'custom') {
            $dFrom = $fromIn ?: null;
            $dTo   = $toIn ?: null;
        }

        // --- Earned (kita): done + (may sale O naka-mark na "Hindi Tumuloy") + (paid verified | free na may amount) ---
        // Parehong rule sa gaCredit() para tugma ang dashboard at ang credit ng doer (Andrew 2026-10-21).
        $earnedQ = DB::table('layout_jobs')
            ->where('status', 'done')
            ->where(function ($q) {
                $q->whereNotNull('sale_id')->orWhereNotNull('cancelled_at');
            })
            ->where(function ($q) {
                $q->where(function ($s) {
                    $s->where('type', 'paid')->where(function ($p) {
                        $p->where('payment_status', 'verified')->orWhereNotNull('amount_set_by');
                    });
                })
                  ->orWhere(function ($s) { $s->where('type', 'free')->whereNotNull('amount'); });
            })
            ->when($gaFilter, fn($q) => $q->where('ga_user_id', $gaFilter))
            ->when($dFrom, fn($q) => $q->whereRaw('DATE(COALESCE(done_at, created_at)) >= ?', [$dFrom]))
            ->when($dTo, fn($q) => $q->whereRaw('DATE(COALESCE(done_at, created_at)) <= ?', [$dTo]));
        $earnedByGa = $earnedQ->groupBy('ga_user_id')
            ->select('ga_user_id', DB::raw('SUM(amount) as earned'), DB::raw('COUNT(*) as jobs'))
            ->get()->keyBy('ga_user_id');

        // --- Need i-verify (Andrew 2026-10-09): done + bayad + payment_status = pending.
        // Ito ang gaps na HINDI pa kasama sa Earned (kita) — kapag na-verify na, doon pa lang
        // mabibilang sa kita/credit. Pantay na period filter sa Earned (COALESCE(done_at, created_at)).
        $verifyQ = DB::table('layout_jobs')
            ->where('status', 'done')
            ->where('type', 'paid')
            ->where('payment_status', 'pending')
            ->when($gaFilter, fn($q) => $q->where('ga_user_id', $gaFilter))
            ->when($dFrom, fn($q) => $q->whereRaw('DATE(COALESCE(done_at, created_at)) >= ?', [$dFrom]))
            ->when($dTo, fn($q) => $q->whereRaw('DATE(COALESCE(done_at, created_at)) <= ?', [$dTo]));
        $verifyByGa = $verifyQ->groupBy('ga_user_id')
            ->select('ga_user_id', DB::raw('SUM(amount) as verify_amt'), DB::raw('COUNT(*) as verify_jobs'))
            ->get()->keyBy('ga_user_id');

        // --- Rejected (Andrew 2026-10-09): done + bayad + payment_status = rejected.
        // Hindi kasama sa kita; ipinapakita para malaman kung ilan ang na-reject na bayad.
        // Pantay na period filter sa Earned/Verify (COALESCE(done_at, created_at)).
        $rejectQ = DB::table('layout_jobs')
            ->where('status', 'done')
            ->where('type', 'paid')
            ->where('payment_status', 'rejected')
            ->when($gaFilter, fn($q) => $q->where('ga_user_id', $gaFilter))
            ->when($dFrom, fn($q) => $q->whereRaw('DATE(COALESCE(done_at, created_at)) >= ?', [$dFrom]))
            ->when($dTo, fn($q) => $q->whereRaw('DATE(COALESCE(done_at, created_at)) <= ?', [$dTo]));
        $rejectByGa = $rejectQ->groupBy('ga_user_id')
            ->select('ga_user_id', DB::raw('SUM(amount) as reject_amt'), DB::raw('COUNT(*) as reject_jobs'))
            ->get()->keyBy('ga_user_id');

        // --- Payouts: nabigay na / pending request (period-filtered) ---
        $payQ = DB::table('layout_job_payouts')
            ->whereIn('status', ['requested', 'paid', 'verified'])
            ->when($gaFilter, fn($q) => $q->where('ga_user_id', $gaFilter))
            ->when($dFrom, fn($q) => $q->whereRaw('DATE(COALESCE(paid_at, requested_at, created_at)) >= ?', [$dFrom]))
            ->when($dTo, fn($q) => $q->whereRaw('DATE(COALESCE(paid_at, requested_at, created_at)) <= ?', [$dTo]));
        $payAgg = $payQ->groupBy('ga_user_id')
            ->select('ga_user_id',
                DB::raw("SUM(CASE WHEN status IN ('paid','verified') THEN amount ELSE 0 END) as given"),
                DB::raw("SUM(CASE WHEN status = 'requested' THEN amount ELSE 0 END) as pending"))
            ->get()->keyBy('ga_user_id');

        // --- Available (kasalukuyang balanse, all-time): earned_all - reserved(requested/paid/verified) ---
        $earnedAllAgg = DB::table('layout_jobs')
            ->where('status', 'done')
            ->where(function ($q) {
                $q->whereNotNull('sale_id')->orWhereNotNull('cancelled_at');
            })
            ->where(function ($q) {
                $q->where(function ($s) {
                    $s->where('type', 'paid')->where(function ($p) {
                        $p->where('payment_status', 'verified')->orWhereNotNull('amount_set_by');
                    });
                })
                  ->orWhere(function ($s) { $s->where('type', 'free')->whereNotNull('amount'); });
            })
            ->when($gaFilter, fn($q) => $q->where('ga_user_id', $gaFilter))
            ->groupBy('ga_user_id')
            ->select('ga_user_id', DB::raw('SUM(amount) as earned_all'))
            ->get()->keyBy('ga_user_id');
        $reservedAgg = DB::table('layout_job_payouts')
            ->whereIn('status', ['requested', 'paid', 'verified'])
            ->when($gaFilter, fn($q) => $q->where('ga_user_id', $gaFilter))
            ->groupBy('ga_user_id')
            ->select('ga_user_id', DB::raw('SUM(amount) as reserved'))
            ->get()->keyBy('ga_user_id');

        // --- Buuin ang per-doer rows ---
        $doers = $this->layoutDoerUsers()->when($gaFilter, fn($c) => $c->where('id', (int) $gaFilter));
        $rows = [];
        $totals = ['jobs' => 0, 'earned' => 0.0, 'verify_jobs' => 0, 'verify_amt' => 0.0, 'reject_jobs' => 0, 'reject_amt' => 0.0, 'given' => 0.0, 'pending' => 0.0, 'available' => 0.0];
        foreach ($doers as $d) {
            $earned = (float) optional($earnedByGa->get($d->id))->earned;
            $jobs   = (int) optional($earnedByGa->get($d->id))->jobs;
            $verifyAmt  = (float) optional($verifyByGa->get($d->id))->verify_amt;
            $verifyJobs = (int) optional($verifyByGa->get($d->id))->verify_jobs;
            $rejectAmt  = (float) optional($rejectByGa->get($d->id))->reject_amt;
            $rejectJobs = (int) optional($rejectByGa->get($d->id))->reject_jobs;
            $given  = (float) optional($payAgg->get($d->id))->given;
            $pending = (float) optional($payAgg->get($d->id))->pending;
            $earnedAll = (float) optional($earnedAllAgg->get($d->id))->earned_all;
            $reserved  = (float) optional($reservedAgg->get($d->id))->reserved;
            // Raw difference (hindi naka-clamp) — para makita kung sobra ang nabigay na payout.
            $available = round($earnedAll - $reserved, 2);

            $rows[] = (object) [
                'id' => $d->id, 'name' => $d->name, 'position' => $d->position,
                'jobs' => $jobs, 'earned' => $earned, 'given' => $given,
                'pending' => $pending, 'available' => $available,
                'verify_jobs' => $verifyJobs, 'verify_amt' => $verifyAmt,
                'reject_jobs' => $rejectJobs, 'reject_amt' => $rejectAmt,
            ];
            $totals['jobs'] += $jobs;
            $totals['earned'] += $earned;
            $totals['verify_jobs'] += $verifyJobs;
            $totals['verify_amt'] += $verifyAmt;
            $totals['reject_jobs'] += $rejectJobs;
            $totals['reject_amt'] += $rejectAmt;
            $totals['given'] += $given;
            $totals['pending'] += $pending;
            $totals['available'] += $available;
        }

        $gaUsers = $this->layoutDoerUsers();

        return view('sales.layout_jobs.dashboard', compact('rows', 'totals', 'gaUsers', 'gaFilter', 'period', 'fromIn', 'toIn', 'dFrom', 'dTo'));
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
            } elseif ($status === 'cancelled') {
                $query->whereNotNull('cancelled_at'); // hindi tumuloy ang client
            }
        }
        if ($ga = $request->get('ga')) {
            $query->where('ga_user_id', $ga);
        }
        if ($agent = $request->get('agent')) {
            $query->where('created_by', $agent);
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

    /**
     * Earned (kita) ng isang layout doer — iisang rule lang, ginagamit ng credit at dashboard.
     *   done  +  (may sale O naka-tag na "Hindi Tumuloy")  +
     *   bayad: verified ang payment O may amount na nilagay ng CEO/COO (rejected)
     *   libre: may amount na nilagay ng approver
     */
    private function gaEarnedQuery(int $gaUserId)
    {
        return LayoutJob::where('ga_user_id', $gaUserId)
            ->where('status', 'done')
            ->where(function ($q) {
                $q->whereNotNull('sale_id')->orWhereNotNull('cancelled_at');
            })
            ->where(function ($q) {
                $q->where(function ($paid) {
                    $paid->where('type', 'paid')->where(function ($p) {
                        $p->where('payment_status', 'verified')->orWhereNotNull('amount_set_by');
                    });
                })->orWhere(function ($free) {
                    $free->where('type', 'free')->whereNotNull('amount');
                });
            });
    }

    /** Reserved (requested/paid/verified) — bawas agad sa credit para hindi mag-double request. */
    private function gaReserved(int $gaUserId): float
    {
        return (float) LayoutJobPayout::where('ga_user_id', $gaUserId)
            ->whereIn('status', ['requested', 'paid', 'verified'])
            ->sum('amount');
    }

    /** GA credit computation — clamped sa 0 (para walang negative na mahihingi). */
    public function gaCredit(int $gaUserId): float
    {
        $earned = (float) $this->gaEarnedQuery($gaUserId)->sum('amount');
        $reserved = $this->gaReserved($gaUserId);

        return max(0, round($earned - $reserved, 2));
    }

    /**
     * Hiiram na kita ng job kung sakaling mabago ang amount/doer (Andrew 2026-10-21).
     * Ginagamit para hindi hayaang maging negative ang balanse ng doer pagkatapos
     * may nakuha nang payout.
     */
    private function jobEarnedImpact(LayoutJob $job, float $newAmount, ?int $newGaId = null): float
    {
        $counts = $job->status === 'done'
            && ($job->sale_id || $job->cancelled_at)
            && (
                ($job->type === 'paid' && ($job->payment_status === 'verified' || $job->amount_set_by))
                || ($job->type === 'free' && $newAmount > 0)
            );
        if (!$counts) {
            return 0.0;
        }
        $gaId = $newGaId ?: (int) $job->ga_user_id;
        if ($gaId !== (int) $job->ga_user_id) {
            return 0.0; // lumipat sa ibang doer — ibang balanse ang apektado
        }

        return round($newAmount - (float) $job->amount, 2);
    }

    /** Balanse ng doer pagkatapos ng hypothetical na pagbabago (para sa guard). */
    private function projectedCredit(int $gaUserId, float $earnedDelta = 0.0): float
    {
        $earned = (float) $this->gaEarnedQuery($gaUserId)->sum('amount');

        return round($earned + $earnedDelta - $this->gaReserved($gaUserId), 2);
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
            'payment_date'  => 'nullable|date|required_if:type,paid',
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
            if (empty($data['payment_date'])) {
                return response()->json(['error' => 'Bayad na layout — kailangan ng petsa ng payment (Date ng Payment).'], 422);
            }
        }

        $jobNo = LayoutJob::nextJobNo();

        // Upload reference image
        $refPath = null;
        if ($request->hasFile('reference_image')) {
            $refPath = ImageOptimizer::store($request->file('reference_image'), 'layout-jobs/reference', 'public');
        }

        // Upload payment screenshot (bayad)
        $payPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $payPath = ImageOptimizer::store($request->file('payment_screenshot'), 'layout-jobs/payment', 'public');
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
            'payment_date' => $data['type'] === 'paid' ? ($data['payment_date'] ?? null) : null,
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
     * Permission (Andrew 2026-10-21): ANG MAY-ARI lang ng payment account.
     * Admin = fallback lang kapag company account (walang naka-assign na owner).
     */
    public function verifyPayment(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::with('paymentAccount')->findOrFail($id);
        if ($job->type !== 'paid') {
            return response()->json(['error' => 'Hindi bayad na layout ito.'], 422);
        }

        // Account owner check: may-ari lang; company accounts (walang user_id) → admin lang.
        $accountOwnerId = $job->paymentAccount?->user_id;
        $canVerify = $accountOwnerId
            ? ($u && (int) $u->id === (int) $accountOwnerId)
            : ($u && $u->isAdmin());
        if (!$canVerify) {
            $ownerName = $job->paymentAccount?->user?->name ?? 'Company';
            return response()->json([
                'error' => "Ikaw ay hindi ang verifier ng payment na ito. Ang payment ay nasa " . ($job->paymentAccount?->name ?? 'account') . " — si " . $ownerName . " lang ang pwedeng mag-verify."
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
     * Re-upload ng payment pagkatapos ma-reject (Andrew 2026-10-21).
     *
     * Kapag rejected na ang payment, hindi na dedead-end ang job: ang gumawa
     * (created_by) o approver ay pwedeng mag-upload ulit ng bagong proof/ref.
     * Nire-reset ang payment_status pabalik sa 'pending' → balik sa verifier.
     * Ini-archive ang huling reject reason (last_reject_*) para may audit trail.
     */
    public function resubmitPayment(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::with('paymentAccount')->findOrFail($id);

        if (!$job->isPaid()) {
            return response()->json(['error' => 'Libre na layout ito — walang payment na ire-resubmit.'], 422);
        }
        if ($job->isCancelled()) {
            return response()->json(['error' => 'Naka-marka na itong "Hindi Tumuloy" — hindi na pwedeng mag-upload ng payment.'], 422);
        }
        if ($job->payout_id) {
            return response()->json(['error' => 'May payout request na ito — sarado na ang payment nito.'], 422);
        }
        // Re-upload lang kapag rejected. Pending/verified = sarado.
        if ($job->payment_status !== 'rejected') {
            return response()->json(['error' => 'Hindi pa na-reject ang payment na ito — hintayin ang verifier.'], 422);
        }
        // Ang gumawa lang (o approver) ang pwedeng mag-upload ulit.
        if ((int) $job->created_by !== (int) $u->id && !$this->canReview()) {
            return response()->json(['error' => 'Ikaw lang ang gumawa ng job na ito (o approver) ang pwedeng mag-upload ulit ng payment.'], 403);
        }

        $data = $request->validate([
            'payment_account_id' => 'nullable|integer|exists:payment_accounts,id',
            'payment_reference'  => 'nullable|string|max:255',
            'payment_date'       => 'nullable|date|before_or_equal:today',
            'payment_screenshot' => 'nullable|image|max:5120',
        ]);

        if (empty($data['payment_reference']) && !$request->hasFile('payment_screenshot')) {
            return response()->json(['error' => 'Maglagay ng bagong reference number O bagong payment screenshot.'], 422);
        }

        $accountName = $job->payment_method;
        if (!empty($data['payment_account_id'])) {
            $account = PaymentAccount::where('is_active', true)->find($data['payment_account_id']);
            if (!$account) {
                return response()->json(['error' => 'Hindi valid ang payment account na pinili.'], 422);
            }
            $accountName = $account->name;
        }

        $payPath = $job->payment_screenshot_path;
        if ($request->hasFile('payment_screenshot')) {
            $payPath = ImageOptimizer::store($request->file('payment_screenshot'), 'layout-jobs/payment', 'public');
        }

        $job->update([
            'payment_account_id'      => $data['payment_account_id'] ?? $job->payment_account_id,
            'payment_method'          => $accountName,
            'payment_reference'       => $data['payment_reference'] ?? $job->payment_reference,
            'payment_date'            => $data['payment_date'] ?? now()->toDateString(),
            'payment_screenshot_path' => $payPath,
            // Archive muna ang huling reject bago i-reset → balik sa verifier
            'payment_last_reject_reason' => $job->payment_reject_reason,
            'payment_last_reject_at'     => now(),
            'payment_reject_reason'   => null,
            'payment_status'          => 'pending',
            'payment_verified_by'     => null,
            'payment_verified_at'     => null,
            'payment_resubmit_count'  => (int) $job->payment_resubmit_count + 1,
        ]);

        return response()->json([
            'ok' => true,
            'payment_status' => 'pending',
            'resubmit_count' => (int) $job->payment_resubmit_count,
        ]);
    }

    /**
     * Libre → Bayad: nagbayad pala ang client (Andrew 2026-10-21).
     *
     * May mga client na una libre ang layout tapos biglang magbabayad. Kapag
     * nag-upload ng payment ang gumawa (o approver), magiging BAYAD na ang job
     * at lalabas na ang payment details/proof sa Payment column.
     */
    public function uploadFreePayment(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::findOrFail($id);

        if (!$job->isFree()) {
            return response()->json(['error' => 'Bayad na ang layout na ito — may payment na.'], 422);
        }
        if ($job->isCancelled()) {
            return response()->json(['error' => 'Naka-marka na itong "Hindi Tumuloy" — hindi na pwedeng lagyan ng payment.'], 422);
        }
        if ($job->payout_id) {
            return response()->json(['error' => 'May payout request na ito — sarado na.'], 422);
        }
        // Creator lang ang pwedeng mag-upload ng payment sa libreng job (Andrew 2026-10-21).
        if ((int) $job->created_by !== (int) $u->id) {
            return response()->json(['error' => 'Ang gumawa lang ng layout job na ito ang pwedeng mag-upload ng payment.'], 403);
        }

        $data = $request->validate([
            'amount'             => 'required|numeric|min:0.01',
            'payment_account_id' => 'required|integer|exists:payment_accounts,id',
            'payment_reference'  => 'nullable|string|max:255',
            'payment_date'       => 'nullable|date|before_or_equal:today',
            'payment_screenshot' => 'nullable|image|max:5120',
        ]);

        $account = PaymentAccount::where('is_active', true)->find($data['payment_account_id']);
        if (!$account) {
            return response()->json(['error' => 'Hindi valid ang payment account na pinili.'], 422);
        }
        if (empty($data['payment_reference']) && !$request->hasFile('payment_screenshot')) {
            return response()->json(['error' => 'Maglagay ng reference number O payment screenshot.'], 422);
        }

        $payPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $payPath = ImageOptimizer::store($request->file('payment_screenshot'), 'layout-jobs/payment', 'public');
        }

        $job->update([
            'type'               => 'paid',
            'amount'             => round((float) $data['amount'], 2),
            'amount_set_by'      => null,
            'amount_set_at'      => null,
            'payment_method'     => $account->name,
            'payment_account_id' => (int) $data['payment_account_id'],
            'payment_reference'  => $data['payment_reference'] ?? null,
            'payment_date'       => $data['payment_date'] ?? now()->toDateString(),
            'payment_screenshot_path' => $payPath,
            'payment_status'     => 'pending',
            'payment_verified_by' => null,
            'payment_verified_at' => null,
            'payment_reject_reason' => null,
        ]);

        return response()->json(['ok' => true, 'type' => 'paid', 'payment_status' => 'pending']);
    }

    /**
     * Retag ng Layout Doer habang OPEN pa ang job (Andrew 2026-10-21).
     *
     * May mga user na nagkakamali ng tag. Hangga't hindi pa done ang job
     * (status = open), pwede itong i-retag sa ibang layout doer — walang
     * credit impact dahil done pa lang ang pumapasok sa credit.
     */
    public function retag(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::findOrFail($id);

        $isCurrentGa = $job->gaUser && (int) $job->ga_user_id === (int) $u->id;

        if ($job->isCancelled()) {
            return response()->json(['error' => 'Naka-marka na itong "Hindi Tumuloy" — hindi na pwedeng i-retag.'], 422);
        }
        if ($job->payout_id) {
            return response()->json(['error' => 'May payout request na ito — sarado na.'], 422);
        }
        if (!in_array($job->status, ['open', 'done'], true)) {
            return response()->json(['error' => 'Hindi pwedeng i-retag sa status na ito.'], 422);
        }
        // Done na + hindi ikaw ang naka-tag + hindi ka creator/approver → bawal.
        if ((int) $job->created_by !== (int) $u->id && !$this->canReview() && !$isCurrentGa) {
            return response()->json(['error' => 'Ang gumawa, ang naka-tag na layout doer, o approver lang ang pwedeng mag-retag/undo.'], 403);
        }

        $data = $request->validate([
            'ga_user_id' => 'required|integer|exists:users,id',
        ]);

        $allowedIds = $this->layoutDoerUsers()->pluck('id')->map(fn ($v) => (int) $v)->all();
        if (!in_array((int) $data['ga_user_id'], $allowedIds, true)) {
            return response()->json(['error' => 'Hindi layout doer ang napiling user.'], 422);
        }
        if ((int) $data['ga_user_id'] === (int) $job->ga_user_id) {
            return response()->json(['error' => 'Siya na ang naka-tag na layout doer.'], 422);
        }

        $newGa = \App\Models\User::find($data['ga_user_id']);
        $oldGaId = $job->ga_user_id;

        // Guard (Andrew 2026-10-21): kung nakuha na ng dating doer ang bayad para sa job na ito,
        // bababa ang earned niya — i-block kung mag-negative ang balanse niya.
        $jobEarned = (float) $job->amount;
        $countsForEarned = $job->status === 'done'
            && ($job->sale_id || $job->cancelled_at)
            && (
                ($job->type === 'paid' && ($job->payment_status === 'verified' || $job->amount_set_by))
                || ($job->type === 'free' && $jobEarned > 0)
            );
        if ($countsForEarned && $oldGaId && $jobEarned > 0) {
            $projected = $this->projectedCredit((int) $oldGaId, -$jobEarned);
            if ($projected < -0.001) {
                $oldName = $job->gaUser?->name ?? 'layout doer';

                return response()->json([
                    'error' => 'Hindi pwedeng ilipat — sobra na ang nakuha ni ' . $oldName . ' (kulang ₱'
                        . number_format(abs($projected), 2) . '). I-adjust o i-reject muna ang payout niya bago ilipat ang job.',
                ], 422);
            }
        }

        $wasDone = $job->status === 'done';
        $update = [
            'ga_user_id'  => (int) $data['ga_user_id'],
            'assigned_by' => $u->id,
            'assigned_at' => now(),
            'retagged_at' => now(),
            'retagged_by' => $u->id,
            'retag_count' => (int) $job->retag_count + 1,
        ];

        // Undo mula done → balik sa open, may 7-day lock bago pwedeng i-done ulit.
        $lockUntil = null;
        if ($wasDone) {
            $lockUntil = now()->addDays(self::RETAG_LOCK_DAYS);
            $update['status'] = 'open';
            $update['done_by'] = null;
            $update['done_at'] = null;
            $update['retag_locked_until'] = $lockUntil;
        }

        $job->update($update);

        return response()->json([
            'ok' => true,
            'ga_user_id' => (int) $data['ga_user_id'],
            'ga' => $newGa?->name,
            'previous_ga_user_id' => $oldGaId,
            'undone' => $wasDone,
            'locked_until' => $lockUntil?->toIso8601String(),
            'lock_days' => self::RETAG_LOCK_DAYS,
        ]);
    }

    /**
     * Approver: maglagay / magpalit ng amount → reflect sa GA credit.
     *
     * Libre  : approver (admin/coo/cpo/cmo) — kailangan may sale O naka-mark na "Hindi Tumuloy".
     * Bayad  : kapag REJECTED ang payment, admin/COO lang ang pwedeng maglagay ng amount
     *          (parang libre) — Andrew 2026-10-21.
     */
    public function setAmount(Request $request, int $id)
    {
        $u = auth()->user();
        if (!$this->canReview()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $job = LayoutJob::findOrFail($id);

        if ($job->isPaid()) {
            if ($job->payment_status !== 'rejected') {
                return response()->json(['error' => 'Bayad na layout — hindi rejected ang payment. Ito ay para lang sa rejected payment.'], 422);
            }
            if (!$u->isAdmin() && !$u->isCoo()) {
                return response()->json(['error' => 'CEO (admin) o COO lang ang pwedeng maglagay ng amount sa rejected payment.'], 403);
            }
        }

        // Kailangan may sale O naka-mark na Hindi Tumuloy bago lagyan ng amount.
        if (!$job->sale_id && !$job->isCancelled()) {
            return response()->json(['error' => 'I-link muna sa sale (o i-mark na "Hindi Tumuloy") bago mag-set ng amount.'], 422);
        }

        $amount = (float) $request->validate(['amount' => 'required|numeric|min:0'])['amount'];

        // Guard (Andrew 2026-10-21): baka nakuha na ng doer ang payout tapos babaan ang amount
        // → mag-negative ang balanse. I-block at ipakita ang sobra.
        if ($job->ga_user_id) {
            $delta = $this->jobEarnedImpact($job, $amount);
            if (abs($delta) > 0.001) {
                $projected = $this->projectedCredit((int) $job->ga_user_id, $delta);
                if ($projected < -0.001) {
                    $gaName = $job->gaUser?->name ?? 'layout doer';

                    return response()->json([
                        'error' => 'Hindi pwedeng gawing ₱' . number_format($amount, 2) . ' — sobra na ang nakuha ni ' . $gaName
                            . ' (kulang ₱' . number_format(abs($projected), 2) . '). I-adjust o i-reject muna ang payout bago baguhin ang amount.',
                    ], 422);
                }
            }
        }

        $job->update([
            'amount' => $amount,
            'amount_set_by' => $u->id,
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
        // Anti-abuse: pagka-retag/undo, may 7-day lock bago pwedeng i-done ulit.
        if ($job->retag_locked_until && now()->lt($job->retag_locked_until)) {
            $daysLeft = (int) ceil(now()->diffInSeconds($job->retag_locked_until, false) / 86400);
            return response()->json([
                'error' => 'Naka-lock pa ang job na ito hanggang ' . $job->retag_locked_until->timezone('Asia/Manila')->format('M d, Y g:i A')
                    . ' (' . max(1, $daysLeft) . ' araw pa) dahil na-retag/undo — hindi pa pwedeng i-done.',
            ], 422);
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
            $proofPath = ImageOptimizer::store($request->file('account_proof'), 'layout-jobs/payout-proofs', 'public');
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
            $proofPath = ImageOptimizer::store($request->file('payment_proof'), 'layout-jobs/payouts', 'public');
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
     * Layout doer confirms na pumasok/natanggap na niya ang payout (Andrew 2026-10-21).
     *
     * Kapag na-mark na 'paid' ng approver (may proof ng bayad), pwede nang
     * i-confirm ng GA na natanggap niya → status 'verified' + received_by/at.
     * Sariling payout lang ang pwedeng i-confirm.
     */
    public function confirmReceipt(Request $request, int $payoutId)
    {
        $u = auth()->user();
        $payout = LayoutJobPayout::findOrFail($payoutId);

        if ((int) $payout->ga_user_id !== (int) $u->id) {
            return response()->json(['error' => 'Sariling payout request mo lang ang pwedeng i-confirm.'], 403);
        }
        if ($payout->status === 'verified') {
            return response()->json(['error' => 'Verified na ang payout na ito.'], 422);
        }
        if ($payout->status === 'rejected') {
            return response()->json(['error' => 'Rejected ang payout na ito — hindi pwedeng i-confirm.'], 422);
        }
        if ($payout->status !== 'paid') {
            return response()->json(['error' => 'Hintayin munang mabayaran ng approver bago ka mag-confirm na natanggap mo na.'], 422);
        }

        $payout->update([
            'status'       => 'verified',
            'received_by'  => $u->id,
            'received_at'  => now(),
            'verified_by'  => $u->id,
            'verified_at'  => now(),
        ]);

        return response()->json(['ok' => true, 'status' => 'verified']);
    }

    /**
     * Approver/creator: ilink ang bayad na layout job sa sale.
     * LIBRE = HINDI pwedeng i-link (spec ni Andrew).
     */
    public function linkSale(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::findOrFail($id);
        // Andrew 2026-09-29: pwede nang mag-link ng sale kahit HINDI PA verified ang payment
        // (dati: paid = verified muna). Ang credit rule (gaCredit) ay verified pa rin ang basehan,
        // kaya safe — ang link ay para lang maitala na may kaugnay na sale.
        if ($job->sale_id) {
            return response()->json(['error' => 'Naka-link na ang job na ito sa isang sale.'], 422);
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

    /**
     * USER: mark ang layout job na "Hindi Tumuloy" ang client (walang sale na na-create).
     * Additive lang — hindi ginagalaw ang existing status/credit logic. Ang cancelled job
     * ay hindi kailangan ng sale link (at hindi kasama sa credit dahil wala itong sale_id).
     */
    public function cancel(Request $request, int $id)
    {
        $u = auth()->user();
        $job = LayoutJob::findOrFail($id);

        if ($job->isCancelled()) {
            return response()->json(['error' => 'Naka-marka na itong "Hindi Tumuloy".'], 422);
        }
        // Bayad: dapat verified O rejected na ang payment bago pwedeng i-mark na hindi tumuloy
        // (Andrew 2026-10-21) — para hindi mawala sa ere ang pending payment.
        if ($job->isPaid() && !in_array($job->payment_status, ['verified', 'rejected'], true)) {
            return response()->json(['error' => 'Hintayin munang ma-verify o ma-reject ang payment bago i-mark na "Hindi Tumuloy".'], 422);
        }
        if ($job->sale_id) {
            return response()->json(['error' => 'Naka-link na ito sa sale — hindi na pwedeng i-mark na hindi tumuloy.'], 422);
        }
        if ($job->payout_id) {
            return response()->json(['error' => 'May payout request na ito — hindi na pwedeng i-cancel.'], 422);
        }
        // Ikaw (ang gumawa) o ang approver lang ang pwedeng mag-mark.
        if ($job->created_by !== $u->id && !$this->canReview()) {
            return response()->json(['error' => 'Ikaw lang ang gumawa ng job na ito (o approver) ang pwedeng mag-mark na hindi tumuloy.'], 403);
        }

        $reason = trim((string) $request->input('reason', ''));
        $job->update([
            'cancelled_at' => now(),
            'cancelled_by' => $u->id,
            'cancel_reason' => $reason !== '' ? mb_substr($reason, 0, 1000) : null,
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
