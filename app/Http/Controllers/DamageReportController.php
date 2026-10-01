<?php

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Services\ImageOptimizer;
use App\Models\DamageReportComment;
use App\Models\DamageReportUser;
use App\Models\SalesDepartment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DamageReportController extends Controller
{
    /**
     * Roles allowed to REVIEW (issue) damage reports.
     * For now: CEO/Admin only (Andrew's account). Expand here later.
     */
    private const REVIEWER_ROLES = ['admin', 'coo', 'cpo', 'cmo'];

    private function isReviewer(): bool
    {
        return auth()->user() && in_array(auth()->user()->role, self::REVIEWER_ROLES);
    }

    /**
     * Pinakamataas na authority (CEO / admin). Bypass ang "kailangan muna ng
     * sales number" gate — ang CEO ay pwedeng mag-issue kahit wala pang
     * naka-tag na sales number galing sa edit report. Andrew 2026-10-01.
     */
    private function isTopAuthority(): bool
    {
        $user = auth()->user();

        return $user && ($user->role === 'admin' || (method_exists($user, 'isAdmin') && $user->isAdmin()));
    }

    /** The shop this user manages, if any (sales_departments.manager_id). */
    private function managedShop(): ?SalesDepartment
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $shop = SalesDepartment::where('manager_id', $user->id)->first();
        if ($shop) {
            return $shop;
        }

        // Class Production Manager (prod_manager) manages the "Class" shop even though
        // sales_departments.manager_id is not set for it. Without this fallback they only
        // see reports they personally filed/tagged in — hindi LAHAT ng damage reports na
        // galing sa Class (dept 4). Andrew 2026-10-01.
        if ($user->isProdManager()) {
            return SalesDepartment::find(4); // Class (department_id = 4)
        }

        return null;
    }

    /**
     * List damage reports.
     * - Shop manager: sees reports for their shop (+ reports they filed/tagged in).
     * - Reviewer: sees everything.
     * - Everyone else: sees reports they filed, are tagged in, or that concern their shop.
     */
    public function index(Request $request)
    {
        $query = DamageReport::with(['shop', 'sale', 'reporter', 'reviewer', 'accountableUsers.user'])
            ->orderByDesc('created_at');

        $managedShop = $this->managedShop();

        if (!$this->isReviewer()) {
            if ($managedShop) {
                $query->where(function ($q) use ($managedShop) {
                    $q->where('shop_id', $managedShop->id)
                      ->orWhere('reporter_id', auth()->id())
                      ->orWhereHas('accountableUsers', function ($u) {
                          $u->where('user_id', auth()->id());
                      });
                });
            } else {
                $query->where(function ($q) {
                    $q->where('reporter_id', auth()->id())
                      ->orWhereHas('accountableUsers', function ($u) {
                          $u->where('user_id', auth()->id());
                      });
                });
            }
        }

        // Filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }
        if ($request->filled('sale_id')) {
            $query->where('sale_id', $request->integer('sale_id'));
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('report_no', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        $reports = $query->paginate(15)->withQueryString();
        $shops = SalesDepartment::where('is_active', true)->get();

        // Damage Dashboard — naka-embed na tab sa loob ng /damage. Access: CEO (admin) + COO lang.
        $user = auth()->user();
        $canSeeDashboard = $user && ($user->isAdmin() || $user->isCoo());
        $dashboard = $canSeeDashboard ? $this->dashboardData() : null;

        return view('damage.index', compact('reports', 'shops', 'managedShop', 'canSeeDashboard', 'dashboard'));
    }

    /**
     * Scoped query builder shared by index() and dashboard().
     * Reviewer = everything; manager = shop + own/tagged; else = own/tagged.
     */
    private function scopedDamageQuery()
    {
        $query = DamageReport::query();

        if ($this->isReviewer()) {
            return $query;
        }

        $managedShop = $this->managedShop();
        if ($managedShop) {
            $query->where(function ($q) use ($managedShop) {
                $q->where('shop_id', $managedShop->id)
                  ->orWhere('reporter_id', auth()->id())
                  ->orWhereHas('accountableUsers', function ($u) {
                      $u->where('user_id', auth()->id());
                  });
            });
        } else {
            $query->where(function ($q) {
                $q->where('reporter_id', auth()->id())
                  ->orWhereHas('accountableUsers', function ($u) {
                      $u->where('user_id', auth()->id());
                  });
            });
        }

        return $query;
    }

    /**
     * Aggregated stats para sa Damage Dashboard (embed na tab sa /damage).
     * Access: CEO (admin) + COO lang — buong-buo ang nakikita (hindi shop-scoped).
     * Andrew 2026-10-01.
     */
    private function dashboardData(): array
    {
        $managedShop = $this->managedShop();
        $scoped = fn () => $this->scopedDamageQuery();

        $total = $scoped()->count();

        $byStatus = $scoped()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')->pluck('c', 'status');

        $bySeverity = $scoped()
            ->selectRaw('severity, count(*) as c')
            ->groupBy('severity')->pluck('c', 'severity');

        $byShop = $scoped()
            ->selectRaw('shop_id, count(*) as c')
            ->groupBy('shop_id')->pluck('c', 'shop_id');
        $shopNames = SalesDepartment::pluck('name', 'id');

        $openCount      = $scoped()->whereIn('status', DamageReport::OPEN_STATUSES)->count();
        $reviewedCount  = $scoped()->whereNotNull('reviewer_id')->count();
        $pendingReview  = $scoped()->where('status', 'submitted')->count();
        $resolvedCount  = $scoped()->where('status', 'resolved')->count();
        $dismissedCount = $scoped()->where('status', 'dismissed')->count();

        $amountTotal = (float) $scoped()->sum('damage_amount');
        $withAmount  = $scoped()->where('damage_amount', '>', 0)->count();

        // Top accountable users (bilang ng reports + kabuuang amount share) sa loob ng scope
        $ids = $scoped()->pluck('id');
        $topUsers = DamageReportUser::query()
            ->whereIn('damage_report_id', $ids)
            ->selectRaw('user_id, count(distinct damage_report_id) as reports, sum(amount_share) as amount')
            ->groupBy('user_id')
            ->orderByDesc('amount')
            ->orderByDesc('reports')
            ->take(8)
            ->get();
        $userNames = User::whereIn('id', $topUsers->pluck('user_id'))->get()->pluck('display_label', 'id');

        // Recent reports
        $recent = $scoped()->with(['shop', 'reporter', 'sale'])
            ->orderByDesc('created_at')->take(8)->get();

        // 6-month trend
        $trendRows = $scoped()
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, count(*) as c")
            ->groupBy('ym')->pluck('c', 'ym');
        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $trend->push(['label' => $m->format('M'), 'count' => (int) ($trendRows[$m->format('Y-m')] ?? 0)]);
        }
        $trendMax = max(1, (int) $trend->max('count'));

        $severityLabels = DamageReport::SEVERITIES;
        $statusLabels   = DamageReport::STATUSES;

        return compact(
            'managedShop', 'total', 'byStatus', 'bySeverity', 'byShop', 'shopNames',
            'openCount', 'reviewedCount', 'pendingReview', 'resolvedCount', 'dismissedCount',
            'amountTotal', 'withAmount', 'topUsers', 'userNames', 'recent', 'trend', 'trendMax',
            'severityLabels', 'statusLabels'
        );
    }

    public function create(Request $request)
    {
        $shops = SalesDepartment::where('is_active', true)->get();
        $managedShop = $this->managedShop();
        $presetSaleId = $request->integer('sale_id') ?: null;

        // Existing open reports for the pre-tagged sale (duplicate warning)
        $existingOpen = $presetSaleId
            ? DamageReport::with(['shop', 'reporter'])
                ->where('sale_id', $presetSaleId)
                ->whereIn('status', DamageReport::OPEN_STATUSES)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('damage.create', compact('shops', 'managedShop', 'presetSaleId', 'existingOpen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:sales_departments,id',
            'description' => 'required|string|max:5000',
            'severity' => 'required|in:minor,major,critical',
            'category' => 'required|string|max:50',
            'sale_id' => 'nullable|exists:prototype_sales,id',
            'evidence' => 'nullable|image|max:5120',
            'quantity' => 'nullable|integer|min:1',
            'involved_position' => 'nullable|string|max:100',
            'involved_name' => 'nullable|string|max:255',
        ]);

        // Duplicate guard: same shop + same sale with an open report -> point to existing
        if ($request->sale_id) {
            $dup = DamageReport::where('shop_id', $request->shop_id)
                ->where('sale_id', $request->sale_id)
                ->whereIn('status', DamageReport::OPEN_STATUSES)
                ->orderByDesc('created_at')
                ->first();
            if ($dup) {
                return redirect()->route('damage.show', $dup->id)
                    ->with('error', 'May existing open report na para sa sale na ito: <b>' . e($dup->report_no) . '</b>. I-comment na lang doon imbes na mag-file ng bago.');
            }
        }

        $reportNo = 'DMG-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = 'damage_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $evidencePath = '/storage/' . ImageOptimizer::storeAs($file, 'uploads/damage_evidence', $filename, 'public');
        }

        $report = DamageReport::create([
            'report_no' => $reportNo,
            'shop_id' => $request->shop_id,
            'sale_id' => $request->sale_id ?: null,
            'reporter_id' => auth()->id(),
            'category' => $request->category,
            'severity' => $request->severity,
            'status' => 'submitted',
            'description' => $request->description,
            'quantity' => $request->filled('quantity') ? $request->integer('quantity') : null,
            'involved_position' => $request->involved_position ?: null,
            'involved_name' => $request->involved_name ?: null,
            'evidence_path' => $evidencePath,
        ]);

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => 'Report filed (' . $report->report_no . ') — pending shop manager review.',
        ]);

        return redirect()->route('damage.show', $report->id)
            ->with('success', 'Damage report ' . $report->report_no . ' filed successfully.');
    }

    public function show(DamageReport $report)
    {
        $report->load(['shop', 'sale', 'reporter', 'reviewer', 'accountableUsers.user', 'comments.user']);

        $managedShop = $this->managedShop();
        $canEdit = $this->isReviewer() || ($managedShop && $managedShop->id === $report->shop_id)
            || $report->reporter_id === auth()->id();
        $isAccountable = $report->accountableUsers->contains('user_id', auth()->id());
        $myAccountability = $report->accountableUsers->firstWhere('user_id', auth()->id());
        $shops = SalesDepartment::where('is_active', true)->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $isTopAuthority = $this->isTopAuthority();

        return view('damage.show', compact(
            'report', 'managedShop', 'canEdit', 'isAccountable', 'myAccountability', 'shops', 'users', 'isTopAuthority'
        ));
    }

    /**
     * Shop manager / reporter edits the report and/or tags a sale number.
     * Submitting an edit moves status submitted -> under_review (ready for reviewer).
     */
    public function update(Request $request, DamageReport $report)
    {
        $request->validate([
            'description' => 'required|string|max:5000',
            'severity' => 'required|in:minor,major,critical',
            'category' => 'required|string|max:50',
            'sale_id' => 'required|exists:prototype_sales,id',
            'evidence' => 'nullable|image|max:5120',
            'quantity' => 'nullable|integer|min:1',
            'involved_position' => 'nullable|string|max:100',
            'involved_name' => 'nullable|string|max:255',
        ], [
            'sale_id.required' => 'Kailangang i-tag ang sales number bago ma-send sa review — para ma-trace ang damage at maiwasan ang duplicate reports.',
        ]);

        $managedShop = $this->managedShop();
        $allowed = $this->isReviewer() || ($managedShop && $managedShop->id === $report->shop_id)
            || $report->reporter_id === auth()->id();
        abort_unless($allowed, 403);

        $evidencePath = $report->evidence_path;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = 'damage_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $evidencePath = '/storage/' . ImageOptimizer::storeAs($file, 'uploads/damage_evidence', $filename, 'public');
        }

        $report->update([
            'description' => $request->description,
            'severity' => $request->severity,
            'category' => $request->category,
            'sale_id' => $request->sale_id ?: null,
            'quantity' => $request->filled('quantity') ? $request->integer('quantity') : null,
            'involved_position' => $request->involved_position ?: null,
            'involved_name' => $request->involved_name ?: null,
            'evidence_path' => $evidencePath,
            'status' => $report->status === 'submitted' ? 'under_review' : $report->status,
        ]);

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => 'Report updated' . ($request->sale_id ? ' and tagged to sale #' . $request->sale_id : '') . '.',
        ]);

        return redirect()->route('damage.show', $report->id)
            ->with('success', 'Report updated.');
    }

    /**
     * Reviewer (CEO/Admin) issues the report: sets accountable user(s),
     * damage amount, points (from severity), and review notes.
     */
    public function review(Request $request, DamageReport $report)
    {
        abort_unless($this->isReviewer(), 403);

        // Anti-duplicate: dapat may tagged sale number muna bago i-issue —
        // MALIBAN sa pinakamataas na authority (CEO/admin), na pwedeng mag-issue
        // kahit wala pang sales number (mas mataas ang authority sa lahat).
        if (!$report->sale_id && !$this->isTopAuthority()) {
            return redirect()->route('damage.show', $report->id)
                ->with('error', 'Hindi pa pwedeng i-review: kailangan munang i-tag ng shop manager ang sales number para ma-trace ang damage.');
        }

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'amounts' => 'nullable|array',
            'amounts.*' => 'nullable|numeric|min:0',
            'damage_amount' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'review_notes' => 'nullable|string|max:3000',
            'severity' => 'required|in:minor,major,critical',
            'category' => 'required|string|max:50',
        ]);

        DB::transaction(function () use ($request, $report) {
            // Severity: kapag may amount, i-derive base sa SEVERITY_BANDS (Minor ₱1–1,000 ·
            // Major ₱1,001–10,000 · Critical ₱10,001+) — NAKA-LOCK, hindi na manual.
            // Kung walang amount, gamitin ang manual na pinili. Andrew 2026-10-01.
            $amount = $request->filled('damage_amount')
                ? (float) $request->damage_amount
                : (float) collect($request->amounts ?? [])->sum();
            $severity = DamageReport::severityForAmount($amount) ?? $request->severity;
            $points = DamageReport::SEVERITY_POINTS[$severity] ?? 0;

            $report->update([
                'reviewer_id' => auth()->id(),
                'category' => $request->category,
                'severity' => $severity,
                'points' => $points,
                'damage_amount' => $request->filled('damage_amount') ? $request->damage_amount : null,
                'quantity' => $request->filled('quantity') ? $request->integer('quantity') : $report->quantity,
                'review_notes' => $request->review_notes,
                'status' => 'issued',
            ]);

            // Replace accountable users
            DamageReportUser::where('damage_report_id', $report->id)->delete();
            foreach ($request->user_ids as $idx => $userId) {
                DamageReportUser::create([
                    'damage_report_id' => $report->id,
                    'user_id' => $userId,
                    'amount_share' => $request->amounts[$idx] ?? 0,
                    'acknowledge_status' => 'pending',
                ]);
            }
        });

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => 'Report issued — accountable user(s) set, damage amount recorded, ' . $report->points . ' pt(s).'
                . ($report->hasAmount() ? ' Severity: ' . ucfirst($report->severity) . ' (naka-lock base sa ₱' . number_format($report->damage_amount, 2) . ').' : ''),
        ]);

        return redirect()->route('damage.show', $report->id)
            ->with('success', 'Report issued. Accountable users have been notified.');
    }

    public function acknowledge(Request $request, DamageReport $report)
    {
        $entry = DamageReportUser::where('damage_report_id', $report->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $entry->update([
            'acknowledge_status' => 'acknowledged',
            'reply' => $request->reply ?: 'Acknowledged',
            'acknowledged_at' => now(),
        ]);

        // If all accountable users acknowledged -> report acknowledged
        $pending = $report->accountableUsers()->where('acknowledge_status', '!=', 'acknowledged')->count();
        if ($pending === 0) {
            $report->update(['status' => 'acknowledged', 'acknowledged_at' => now()]);
        }

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => 'Acknowledged by ' . auth()->user()->name . ($request->reply ? ': ' . $request->reply : '') . '.',
        ]);

        return redirect()->route('damage.show', $report->id)
            ->with('success', 'Acknowledged. Thank you.');
    }

    public function contest(Request $request, DamageReport $report)
    {
        $request->validate(['reply' => 'required|string|max:3000']);

        $entry = DamageReportUser::where('damage_report_id', $report->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $entry->update([
            'acknowledge_status' => 'contested',
            'reply' => $request->reply,
            'acknowledged_at' => now(),
        ]);

        $report->update(['status' => 'contested']);

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => 'Contested by ' . auth()->user()->name . ': ' . $request->reply,
        ]);

        return redirect()->route('damage.show', $report->id)
            ->with('success', 'Contest submitted. The reviewer will evaluate your reply.');
    }

    public function resolve(Request $request, DamageReport $report)
    {
        abort_unless($this->isReviewer(), 403);

        $report->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'review_notes' => $request->review_notes ?: $report->review_notes,
        ]);

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => 'Report resolved.' . ($request->review_notes ? ' ' . $request->review_notes : ''),
        ]);

        return redirect()->route('damage.show', $report->id)->with('success', 'Report resolved.');
    }

    public function dismiss(Request $request, DamageReport $report)
    {
        abort_unless($this->isReviewer(), 403);

        $report->update([
            'status' => 'dismissed',
            'resolved_at' => now(),
            'review_notes' => $request->review_notes ?: $report->review_notes,
        ]);

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => 'Report dismissed.' . ($request->review_notes ? ' ' . $request->review_notes : ''),
        ]);

        return redirect()->route('damage.show', $report->id)->with('success', 'Report dismissed.');
    }

    /**
     * Reviewer (CEO/admin) na-adjust ang penalty / bayad pagkatapos ma-issue —
     * halimbawa kung napag-usapan na at napagkasunduang babaan. Pwede ring
     * baguhin ang hatian kada accountable user.
     */
    public function adjust(Request $request, DamageReport $report)
    {
        abort_unless($this->isReviewer(), 403);

        if (!in_array($report->status, ['issued', 'acknowledged', 'contested'])) {
            return redirect()->route('damage.show', $report->id)
                ->with('error', 'Ang penalty ay pwedeng i-adjust lang kapag naka-issue na ang report.');
        }

        $request->validate([
            'damage_amount' => 'nullable|numeric|min:0',
            'amounts' => 'nullable|array',
            'amounts.*' => 'nullable|numeric|min:0',
            'adjust_reason' => 'nullable|string|max:1000',
        ]);

        $oldAmount = $report->damage_amount;

        // Bagong total: kung may damage_amount, iyon; kung hindi, suma ng shares.
        $newAmount = $request->filled('damage_amount')
            ? (float) $request->damage_amount
            : (float) collect($request->amounts ?? [])->sum();

        // Severity ay diniderive pa rin sa amount (naka-lock sa bands).
        $severity = DamageReport::severityForAmount($newAmount) ?? $report->severity;
        $points = DamageReport::SEVERITY_POINTS[$severity] ?? $report->points;

        DB::transaction(function () use ($request, $report, $newAmount, $severity, $points) {
            $report->update([
                'damage_amount' => $request->filled('damage_amount') ? $newAmount : $report->damage_amount,
                'severity' => $severity,
                'points' => $points,
            ]);

            // I-update ang per-user shares kung may binigay.
            if ($request->filled('amounts')) {
                foreach ($request->amounts as $idx => $amount) {
                    if (!isset($request->adjust_user_ids[$idx])) {
                        continue;
                    }
                    DamageReportUser::where('damage_report_id', $report->id)
                        ->where('user_id', $request->adjust_user_ids[$idx])
                        ->update(['amount_share' => $amount ?? 0]);
                }
            }
        });

        $fmt = fn ($v) => '₱' . number_format((float) $v, 2);
        $note = 'Penalty adjusted mula ' . $fmt($oldAmount) . ' → ' . $fmt($newAmount)
            . ' (Severity: ' . ucfirst($severity) . ').'
            . ($request->adjust_reason ? ' Dahilan: ' . $request->adjust_reason : '');

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => $note,
        ]);

        return redirect()->route('damage.show', $report->id)
            ->with('success', 'Na-adjust na ang penalty — ' . $fmt($oldAmount) . ' → ' . $fmt($newAmount) . '.');
    }

    public function comment(Request $request, DamageReport $report)
    {
        $request->validate(['comment' => 'required|string|max:3000']);

        DamageReportComment::create([
            'damage_report_id' => $report->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return redirect()->route('damage.show', $report->id)->with('success', 'Comment added.');
    }
}
