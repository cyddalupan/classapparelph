<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Freebie Slip System — customer appreciation freebies (tshirt, banner, etc.).
 *
 * Flow:
 *   sales/staff → Add Freebies request (multi items w/ qty, purpose, ref image)
 *   → Manager / CEO (admin) / COO approve or reject (reason)
 *   → approval creates a FREEBIE SLIP (open) → GA / QA / Manager mark done
 *   → red (open) / green (done) indicators + backjob-list integration
 *
 * Roles:
 *   request   : admin, manager, coo, staff, sales_agent, sales_representative, prod_manager (class-scoped)
 *   approve   : isManager() (admin|manager|prod_manager) || isCoo()
 *   reject    : isManager() || isCoo()
 *   done      : isGa() || isQa() || isManager()
 *
 * Class-scoped users (prod_manager/QA) only see/act on Class department (4) sales.
 */
class FreebieSlipController extends Controller
{
    /* ------------------------------------------------------------------
     * Permission helpers
     * ---------------------------------------------------------------- */

    private function canRequest(): bool
    {
        $u = auth()->user();
        if (!$u) return false;
        return in_array($u->role, [
            'admin', 'manager', 'coo', 'staff',
            'sales_agent', 'sales_representative', 'prod_manager',
            'qa',
        ]);
    }

    private function canApprove(): bool
    {
        $u = auth()->user();
        return $u && ($u->isManager() || $u->isCoo());
    }

    private function canDone(): bool
    {
        $u = auth()->user();
        return $u && ($u->isGa() || $u->isQa() || $u->isManager());
    }

    /** Class-scoped users (prod_manager/QA) are limited to Class (dept 4). */
    private function classScopeAbortIfBlocked($sale): void
    {
        $u = auth()->user();
        if ($u && $u->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
        }
    }

    /* ------------------------------------------------------------------
     * Read endpoints
     * ---------------------------------------------------------------- */

    /**
     * Per-sale freebie data (requests + slips with items) — for sale page & kanban.
     */
    public function pending(int $saleId)
    {
        $sale = DB::table('prototype_sales')->find($saleId);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        $this->classScopeAbortIfBlocked($sale);

        $requests = DB::table('freebie_requests')
            ->leftJoin('users', 'freebie_requests.requested_by', '=', 'users.id')
            ->where('freebie_requests.sale_id', $saleId)
            ->select('freebie_requests.*', 'users.name as requested_by_name')
            ->orderBy('freebie_requests.created_at', 'desc')
            ->get();

        $requestIds = $requests->pluck('id');
        $items = collect();
        if ($requestIds->isNotEmpty()) {
            $items = DB::table('freebie_request_items')
                ->whereIn('freebie_request_id', $requestIds)
                ->orderBy('id')
                ->get()
                ->groupBy('freebie_request_id');
        }

        $slips = DB::table('freebie_slips')
            ->where('sale_id', $saleId)
            ->get()
            ->keyBy('freebie_request_id');

        // Decorate each request: items, slip status, images (public url)
        $requests->each(function ($r) use ($items, $slips) {
            $r->items = $items->get($r->id, collect())->map(function ($it) {
                $it->reference_image_url = $it->reference_image
                    ? asset('storage/' . $it->reference_image)
                    : null;
                return $it;
            })->values();
            $slip = $slips->get($r->id);
            $r->slip_status = $slip->status ?? null;
            $r->slip_done_by = $slip->done_by ?? null;
            $r->slip_done_at = $slip->done_at ?? null;
            $r->age_hours = round((now()->timestamp - strtotime($r->created_at)) / 3600, 1);
            $r->approved_by_name = $this->userName($r->approved_by);
            $r->rejected_by_name = $this->userName($r->rejected_by);
            $r->done_by_name = $this->userName($r->slip_done_by);
        });

        return response()->json([
            'requests' => $requests,
            'can_request' => $this->canRequest(),
            'can_approve' => $this->canApprove(),
            'can_done' => $this->canDone(),
        ]);
    }

    /**
     * All pending freebie requests (approval center — manager/CEO/COO).
     * Non-managers see only requests on their own sales; class-scoped = Class dept only.
     */
    public function allPending()
    {
        $user = auth()->user();
        if (!$user || !($this->canApprove() || $this->canRequest())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = DB::table('freebie_requests')
            ->join('prototype_sales', 'freebie_requests.sale_id', '=', 'prototype_sales.id')
            ->where('freebie_requests.status', 'pending');

        $isManager = $this->canApprove();
        if (!$isManager) {
            $query->where('prototype_sales.sales_agent_id', $user->id);
        }
        if ($user->isClassScoped()) {
            $query->where('prototype_sales.department_id', 4);
        }

        $requests = $query->select(
                'freebie_requests.*',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.kanban_status',
                'prototype_sales.department_id'
            )
            ->orderBy('freebie_requests.created_at', 'desc')
            ->get();

        $requestIds = $requests->pluck('id');
        $items = collect();
        if ($requestIds->isNotEmpty()) {
            $items = DB::table('freebie_request_items')
                ->whereIn('freebie_request_id', $requestIds)
                ->orderBy('id')
                ->get()
                ->groupBy('freebie_request_id');
        }

        $requests->each(function ($r) use ($items) {
            $r->source = 'freebie';
            $r->items = $items->get($r->id, collect())->values();
            $r->age_hours = round((now()->timestamp - strtotime($r->created_at)) / 3600, 1);
            $r->requested_by_name = $this->userName($r->requested_by);
        });

        return response()->json($requests);
    }

    /**
     * Lightweight pending count (badge polling) — same scoping as allPending.
     */
    public function pendingCount()
    {
        $user = auth()->user();
        if (!$user || !($this->canApprove() || $this->canRequest())) {
            return response()->json(['count' => 0]);
        }

        $query = DB::table('freebie_requests')
            ->join('prototype_sales', 'freebie_requests.sale_id', '=', 'prototype_sales.id')
            ->where('freebie_requests.status', 'pending');

        if (!$this->canApprove()) {
            $query->where('prototype_sales.sales_agent_id', $user->id);
        }
        if ($user->isClassScoped()) {
            $query->where('prototype_sales.department_id', 4);
        }

        return response()->json(['count' => $query->count()]);
    }

    /**
     * FREEBIE LIST — full review page (all freebie requests across sales).
     * Manager/CEO/COO only. Server-rendered: filters + stats (top requesters,
     * most-given freebies) + approve/reject/done review actions.
     */
    public function reviewList()
    {
        $user = auth()->user();
        if (!$user || !$this->canApprove()) {
            abort(403, 'Only managers/CEO/COO can view the freebie list.');
        }

        $request = request();
        $status = trim((string) $request->get('status', ''));
        $audit = trim((string) $request->get('audit', ''));
        $q = trim((string) $request->get('q', ''));
        $from = trim((string) $request->get('from', ''));
        $to = trim((string) $request->get('to', ''));
        $requesterId = (int) $request->get('requester', 0);

        // Class-scoped (prod_manager) → Class dept (4) only
        $scope = function ($qb) use ($user) {
            if ($user->isClassScoped()) {
                $qb->where('prototype_sales.department_id', 4);
            }
            return $qb;
        };

        // ---- Main list (filterable) ----
        $query = DB::table('freebie_requests')
            ->join('prototype_sales', 'freebie_requests.sale_id', '=', 'prototype_sales.id')
            ->leftJoin('users', 'freebie_requests.requested_by', '=', 'users.id')
            ->select(
                'freebie_requests.*',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.department_id',
                'prototype_sales.total_amount',
                'prototype_sales.overall_total_amount',
                'users.name as requested_by_name'
            );
        $scope($query);

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('freebie_requests.status', $status);
        }
        if ($audit === 'awaiting') {
            $query->where('freebie_requests.status', 'approved')->whereNull('freebie_requests.audited_at');
        }
        if ($audit === 'audited') {
            $query->where('freebie_requests.status', 'approved')->whereNotNull('freebie_requests.audited_at');
        }
        if ($requesterId > 0) {
            $query->where('freebie_requests.requested_by', $requesterId);
        }
        if ($from !== '') {
            $query->whereDate('freebie_requests.created_at', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('freebie_requests.created_at', '<=', $to);
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('prototype_sales.sales_number', 'like', $like)
                    ->orWhere('prototype_sales.customer_name', 'like', $like)
                    ->orWhere('users.name', 'like', $like)
                    ->orWhereExists(function ($ex) use ($q) {
                        $ex->select(DB::raw(1))
                            ->from('freebie_request_items')
                            ->whereColumn('freebie_request_items.freebie_request_id', 'freebie_requests.id')
                            ->where('freebie_request_items.description', 'like', '%' . $q . '%')
                            ->orWhere('freebie_request_items.purpose', 'like', '%' . $q . '%');
                    });
            });
        }

        $requests = $query->orderByDesc('freebie_requests.created_at')->paginate(100)->withQueryString();

        // Attach items + slip info + reviewer names
        $ids = $requests->pluck('id');
        $items = collect();
        $slips = collect();
        if ($ids->isNotEmpty()) {
            $items = DB::table('freebie_request_items')
                ->whereIn('freebie_request_id', $ids)
                ->orderBy('id')
                ->get()
                ->groupBy('freebie_request_id');
            $slips = DB::table('freebie_slips')->whereIn('freebie_request_id', $ids)->get()->keyBy('freebie_request_id');
        }
        $requests->getCollection()->transform(function ($r) use ($items, $slips) {
            $r->items = $items->get($r->id, collect())->map(function ($it) {
                $it->reference_image_url = $it->reference_image ? asset('storage/' . $it->reference_image) : null;
                return $it;
            })->values();
            $slip = $slips->get($r->id);
            $r->slip_status = $slip->status ?? null;
            $r->slip_done_by_name = $this->userName($slip->done_by ?? null);
            $r->slip_done_at = $slip->done_at ?? null;
            $r->approved_by_name = $this->userName($r->approved_by);
            $r->rejected_by_name = $this->userName($r->rejected_by);
            $r->audited_by_name = $this->userName($r->audited_by);
            $r->age_hours = round((now()->timestamp - strtotime($r->created_at)) / 3600, 1);
            // Project total sales: use group-wide total for multi-dept sales, else the sale's own total.
            $r->project_total = $r->overall_total_amount !== null ? (float) $r->overall_total_amount : (float) ($r->total_amount ?? 0);
            return $r;
        });

        // ---- Stats (all-time; respect dept scope) ----
        $statBase = function () use ($scope) {
            return $scope(DB::table('freebie_requests')
                ->join('prototype_sales', 'freebie_requests.sale_id', '=', 'prototype_sales.id'));
        };
        $totalRequests = $statBase()->count();
        $pendingCount = $statBase()->where('freebie_requests.status', 'pending')->count();
        $approvedCount = $statBase()->where('freebie_requests.status', 'approved')->count();
        $awaitingAuditCount = $statBase()->where('freebie_requests.status', 'approved')->whereNull('freebie_requests.audited_at')->count();
        $rejectedCount = $statBase()->where('freebie_requests.status', 'rejected')->count();
        $givenQty = $statBase()
            ->join('freebie_request_items', 'freebie_request_items.freebie_request_id', '=', 'freebie_requests.id')
            ->where('freebie_requests.status', 'approved')
            ->sum('freebie_request_items.quantity');

        // Top requesters (sino madalas mag-request)
        $topRequesters = $scope(DB::table('freebie_requests as fr')
            ->join('prototype_sales', 'fr.sale_id', '=', 'prototype_sales.id')
            ->join('users as u', 'fr.requested_by', '=', 'u.id')
            ->leftJoin('freebie_request_items as it', 'it.freebie_request_id', '=', 'fr.id'))
            ->select('u.id as user_id', 'u.name as user_name',
                DB::raw('COUNT(DISTINCT fr.id) as req_count'),
                DB::raw('COALESCE(SUM(CASE WHEN fr.status = "approved" THEN it.quantity ELSE 0 END), 0) as given_qty'))
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('req_count')
            ->orderByDesc('given_qty')
            ->limit(6)
            ->get();

        // Most-given freebie items (anong freebie madalas ibigay + ilan)
        $topItems = $scope(DB::table('freebie_request_items as it')
            ->join('freebie_requests as fr', 'it.freebie_request_id', '=', 'fr.id')
            ->join('prototype_sales', 'fr.sale_id', '=', 'prototype_sales.id')
            ->where('fr.status', 'approved'))
            ->select('it.description',
                DB::raw('SUM(it.quantity) as total_qty'),
                DB::raw('COUNT(DISTINCT fr.id) as times'))
            ->groupBy('it.description')
            ->orderByDesc('total_qty')
            ->orderByDesc('times')
            ->limit(8)
            ->get();

        // Requester dropdown for the filter
        $requesters = $scope(DB::table('freebie_requests as fr')
            ->join('prototype_sales', 'fr.sale_id', '=', 'prototype_sales.id')
            ->join('users as u', 'fr.requested_by', '=', 'u.id'))
            ->select('u.id as user_id', 'u.name as user_name', DB::raw('COUNT(*) as c'))
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('c')
            ->limit(100)
            ->get();

        $departmentLabels = [1 => 'iPrint', 2 => 'Consol', 3 => 'Cinco', 4 => 'Class', 5 => 'MTO', 6 => 'Other'];

        return view('sales.prototype.freebie-list', compact(
            'requests', 'status', 'audit', 'q', 'from', 'to', 'requesterId',
            'totalRequests', 'pendingCount', 'approvedCount', 'awaitingAuditCount', 'rejectedCount', 'givenQty',
            'topRequesters', 'topItems', 'requesters', 'departmentLabels'
        ));
    }

    /* ------------------------------------------------------------------
     * Mutations
     * ---------------------------------------------------------------- */

    /**
     * Submit a freebie request (multiple items; each may carry a reference image).
     */
    public function request(Request $request, int $saleId)
    {
        $user = auth()->user();
        if (!$user || !$this->canRequest()) {
            return response()->json(['error' => 'Unauthorized: sales staff can submit freebie requests.'], 403);
        }

        $sale = DB::table('prototype_sales')->find($saleId);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        $this->classScopeAbortIfBlocked($sale);

        $itemsRaw = $request->input('items');
        if (!is_array($itemsRaw) || empty($itemsRaw)) {
            return response()->json(['error' => 'At least one freebie item is required.'], 422);
        }

        // Normalize + validate item rows
        $items = [];
        foreach (array_values($itemsRaw) as $i => $row) {
            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') continue;
            $qty = max(1, (int) ($row['quantity'] ?? 1));
            $purpose = trim((string) ($row['purpose'] ?? ''));
            $imagePath = null;

            // reference image file named image_{index} (FormData per row)
            $file = $request->file('image_' . $i);
            if ($file && $file->isValid()) {
                $imagePath = $file->store('freebie-refs', 'public');
            }
            $items[] = [
                'description' => mb_substr($desc, 0, 255),
                'quantity' => $qty,
                'purpose' => $purpose === '' ? null : mb_substr($purpose, 0, 500),
                'reference_image' => $imagePath,
            ];
        }

        if (empty($items)) {
            return response()->json(['error' => 'At least one freebie item with a description is required.'], 422);
        }

        $notes = trim((string) $request->input('notes', ''));

        $requestId = DB::table('freebie_requests')->insertGetId([
            'sale_id' => $saleId,
            'requested_by' => $user->id,
            'status' => 'pending',
            'notes' => $notes === '' ? null : mb_substr($notes, 0, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($items as $it) {
            DB::table('freebie_request_items')->insert([
                'freebie_request_id' => $requestId,
                'description' => $it['description'],
                'quantity' => $it['quantity'],
                'purpose' => $it['purpose'],
                'reference_image' => $it['reference_image'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Audit
        $summary = collect($items)->map(fn($it) =>
            $it['quantity'] . '× ' . $it['description'] . ($it['purpose'] ? ' (' . $it['purpose'] . ')' : '')
        )->join(', ');

        DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $saleId,
            'user_id' => $user->id,
            'action' => 'freebie_requested',
            'description' => 'Freebie request #' . $requestId . ': ' . $summary,
            'details' => json_encode(['freebie_request_id' => $requestId, 'items' => $items]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'id' => $requestId,
            'message' => 'Freebie request submitted — awaiting Manager/CEO/COO approval.',
        ]);
    }

    /**
     * Approve a freebie request → creates the FREEBIE SLIP (open).
     */
    public function approve(Request $request, int $requestId)
    {
        $user = auth()->user();
        if (!$user || !$this->canApprove()) {
            return response()->json(['error' => 'Unauthorized: Manager/CEO/COO only.'], 403);
        }

        $fb = DB::table('freebie_requests')->find($requestId);
        if (!$fb || $fb->status !== 'pending') {
            return response()->json(['error' => 'Request not found or already processed.'], 404);
        }
        $sale = DB::table('prototype_sales')->find($fb->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        $this->classScopeAbortIfBlocked($sale);

        DB::transaction(function () use ($fb, $sale, $user) {
            DB::table('freebie_requests')->where('id', $fb->id)->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

            // Freebie slip exists only after approval (1:1 per approved request)
            DB::table('freebie_slips')->insert([
                'sale_id' => $sale->id,
                'freebie_request_id' => $fb->id,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $items = DB::table('freebie_request_items')
                ->where('freebie_request_id', $fb->id)
                ->get();
            $summary = $items->map(fn($it) =>
                $it->quantity . '× ' . $it->description . ($it->purpose ? ' (' . $it->purpose . ')' : '')
            )->join(', ');

            DB::table('prototype_sale_audit_logs')->insert([
                'sale_id' => $sale->id,
                'user_id' => $user->id,
                'action' => 'freebie_approved',
                'description' => 'Freebie request #' . $fb->id . ' approved → Freebie Slip created: ' . $summary,
                'details' => json_encode(['freebie_request_id' => $fb->id, 'approved_by' => $user->id]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Freebie approved — Freebie Slip created.']);
    }

    /**
     * AUDIT (double-check) an approved freebie request.
     * Only a DIFFERENT approver-level user (not the one who approved) may
     * audit/verify the approval — prevents approve-without-review.
     */
    public function audit(Request $request, int $requestId)
    {
        $user = auth()->user();
        if (!$user || !$this->canApprove()) {
            return response()->json(['error' => 'Unauthorized: Manager/CEO/COO only.'], 403);
        }

        $fb = DB::table('freebie_requests')->find($requestId);
        if (!$fb || $fb->status !== 'approved') {
            return response()->json(['error' => 'Only approved freebie requests can be audited.'], 404);
        }
        if ($fb->audited_at) {
            return response()->json(['error' => 'This freebie request is already audited.'], 422);
        }
        // CEO/Admin exemption (Andrew 2026-09-22): pwedeng i-audit ng CEO ang
        // sarili niyang approval. Nananatili pa rin ang double-check rule para sa
        // manager/COO (kailangan pa rin ng IBANG approver-level user).
        $isCeo = $user->isAdmin();
        if (!$isCeo && (int) $fb->approved_by === (int) $user->id) {
            return response()->json(['error' => 'Double-check rule: hindi mo maaaring i-audit ang sarili mong approval — kailangan ng ibang manager/CEO/COO.'], 422);
        }

        $sale = DB::table('prototype_sales')->find($fb->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        $this->classScopeAbortIfBlocked($sale);

        DB::table('freebie_requests')->where('id', $fb->id)->update([
            'audited_by' => $user->id,
            'audited_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $sale->id,
            'user_id' => $user->id,
            'action' => 'freebie_audited',
            'description' => 'Freebie request #' . $fb->id . ' audited (double-check) — approved by user #' . $fb->approved_by . ', audited by user #' . $user->id,
            'details' => json_encode(['freebie_request_id' => $fb->id, 'approved_by' => $fb->approved_by, 'audited_by' => $user->id]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Freebie request audited — double-check complete.']);
    }

    /**
     * Reject a freebie request (reason required) — no slip is created.
     */
    public function reject(Request $request, int $requestId)
    {
        $user = auth()->user();
        if (!$user || !$this->canApprove()) {
            return response()->json(['error' => 'Unauthorized: Manager/CEO/COO only.'], 403);
        }

        $fb = DB::table('freebie_requests')->find($requestId);
        if (!$fb || $fb->status !== 'pending') {
            return response()->json(['error' => 'Request not found or already processed.'], 404);
        }
        $sale = DB::table('prototype_sales')->find($fb->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        $this->classScopeAbortIfBlocked($sale);

        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            return response()->json(['error' => 'Rejection reason is required.'], 422);
        }

        DB::table('freebie_requests')->where('id', $fb->id)->update([
            'status' => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => mb_substr($reason, 0, 500),
            'updated_at' => now(),
        ]);

        DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $sale->id,
            'user_id' => $user->id,
            'action' => 'freebie_rejected',
            'description' => 'Freebie request #' . $fb->id . ' rejected. Reason: ' . $reason,
            'details' => json_encode(['freebie_request_id' => $fb->id, 'rejected_by' => $user->id, 'reason' => $reason]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Freebie request rejected.']);
    }

    /**
     * Mark a freebie slip done (GA / QA / Manager / admin).
     */
    public function done(Request $request, int $requestId)
    {
        $user = auth()->user();
        if (!$user || !$this->canDone()) {
            return response()->json(['error' => 'Unauthorized: GA/QA/Manager only.'], 403);
        }

        $fb = DB::table('freebie_requests')->find($requestId);
        if (!$fb || $fb->status !== 'approved') {
            return response()->json(['error' => 'No approved freebie request found for this slip.'], 404);
        }
        $sale = DB::table('prototype_sales')->find($fb->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        $this->classScopeAbortIfBlocked($sale);

        $slip = DB::table('freebie_slips')->where('freebie_request_id', $fb->id)->first();
        if (!$slip) {
            return response()->json(['error' => 'Freebie slip not found.'], 404);
        }
        if ($slip->status === 'done') {
            return response()->json(['error' => 'Freebie slip is already done.'], 422);
        }

        DB::table('freebie_slips')->where('id', $slip->id)->update([
            'status' => 'done',
            'done_by' => $user->id,
            'done_at' => now(),
            'updated_at' => now(),
        ]);

        $items = DB::table('freebie_request_items')->where('freebie_request_id', $fb->id)->get();
        $summary = $items->map(fn($it) => $it->quantity . '× ' . $it->description)->join(', ');

        DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $sale->id,
            'user_id' => $user->id,
            'action' => 'freebie_done',
            'description' => 'Freebie Slip done — ' . $summary,
            'details' => json_encode(['freebie_request_id' => $fb->id, 'freebie_slip_id' => $slip->id, 'done_by' => $user->id]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Freebie slip marked done.']);
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ---------------------------------------------------------------- */

    private function userName(?int $id): ?string
    {
        if (!$id) return null;
        static $cache = [];
        if (!array_key_exists($id, $cache)) {
            $cache[$id] = DB::table('users')->where('id', $id)->value('name');
        }
        return $cache[$id];
    }
}
