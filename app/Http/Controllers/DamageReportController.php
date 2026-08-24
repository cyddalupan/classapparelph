<?php

namespace App\Http\Controllers;

use App\Models\DamageReport;
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

    /** The shop this user manages, if any (sales_departments.manager_id). */
    private function managedShop(): ?SalesDepartment
    {
        return SalesDepartment::where('manager_id', auth()->id())->first();
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

        return view('damage.index', compact('reports', 'shops', 'managedShop'));
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
            $evidencePath = '/storage/' . $file->storeAs('uploads/damage_evidence', $filename, 'public');
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

        return view('damage.show', compact(
            'report', 'managedShop', 'canEdit', 'isAccountable', 'myAccountability', 'shops', 'users'
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
            $evidencePath = '/storage/' . $file->storeAs('uploads/damage_evidence', $filename, 'public');
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

        // Anti-duplicate: dapat may tagged sale number muna bago i-issue
        if (!$report->sale_id) {
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
            $points = DamageReport::SEVERITY_POINTS[$request->severity] ?? 0;

            $report->update([
                'reviewer_id' => auth()->id(),
                'category' => $request->category,
                'severity' => $request->severity,
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
            'comment' => 'Report issued — accountable user(s) set, damage amount recorded, ' . $report->points . ' pt(s).',
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
