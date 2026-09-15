<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PricingCalculator;
use Illuminate\Support\Facades\DB;

class SaleAddonController extends Controller
{
    /**
     * Show pending addon requests for a sale (for kanban modal)
     */
    public function pending(int $saleId)
    {
        $requests = DB::table('sale_addon_requests')
            ->where('sale_id', $saleId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    /**
     * Get all pending requests (for manager view)
     * Includes both add-on requests AND change requests (Add Product from sales page)
     */
    public function allPending()
    {
        $user = auth()->user();
        $isManager = $user && $user->isManager();

        // ── Add-on requests ──
        $query = DB::table('sale_addon_requests')
            ->join('prototype_sales', 'sale_addon_requests.sale_id', '=', 'prototype_sales.id')
            ->where('sale_addon_requests.status', 'pending');

        // Non-managers only see pending add-ons on their own sales
        if (!$isManager) {
            $query->where('prototype_sales.sales_agent_id', $user ? $user->id : -1);
        }

        // Prod manager is Class-only
        if ($user && $user->isProdManager()) {
            $query->where('prototype_sales.department_id', 4);
        }

        $requests = $query->select(
                'sale_addon_requests.*',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.kanban_status'
            )
            ->orderBy('sale_addon_requests.created_at', 'desc')
            ->get();

        // Decode requested_items for each + compute age in hours
        $requests->each(function ($r) {
            $r->source = 'addon';
            $r->items = json_decode($r->requested_items, true);
            $r->age_hours = round((now()->timestamp - strtotime($r->created_at)) / 3600, 1);
        });

        // ── Change requests (Add Product from sales page) ──
        $changeQuery = DB::table('prototype_sale_changes')
            ->join('prototype_sales', 'prototype_sale_changes.sale_id', '=', 'prototype_sales.id')
            ->leftJoin('users', 'prototype_sale_changes.submitted_by', '=', 'users.id')
            ->where('prototype_sale_changes.status', 'pending');

        if (!$isManager) {
            $changeQuery->where('prototype_sales.sales_agent_id', $user ? $user->id : -1);
        }

        // Prod manager is Class-only
        if ($user && $user->isProdManager()) {
            $changeQuery->where('prototype_sales.department_id', 4);
        }

        $changes = $changeQuery->select(
                'prototype_sale_changes.id as change_id',
                'prototype_sale_changes.sale_id',
                'prototype_sale_changes.change_summary',
                'prototype_sale_changes.total_before',
                'prototype_sale_changes.total_after',
                'prototype_sale_changes.created_at',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.kanban_status',
                'users.name as submitted_by_name',
                
                'users.position as submitted_by_position'
            )
            ->orderBy('prototype_sale_changes.created_at', 'desc')
            ->get();

        $changeRequests = $changes->map(function ($c) {
            return (object) [
                'source' => 'change',
                'id' => $c->change_id,
                'sale_id' => $c->sale_id,
                'sales_number' => $c->sales_number,
                'customer_name' => $c->customer_name,
                'kanban_status' => $c->kanban_status,
                'change_summary' => $c->change_summary,
                'total_before' => (float) $c->total_before,
                'total_after' => (float) $c->total_after,
                'requested_by' => ($fn = trim(explode(' ', ($c->submitted_by_name ?? ''))[0])) ? $fn . ($c->submitted_by_position ? ' - ' . $c->submitted_by_position : '') : ($c->submitted_by_name ?? 'Agent'),
                'created_at' => $c->created_at,
                'age_hours' => round((now()->timestamp - strtotime($c->created_at)) / 3600, 1),
            ];
        });

        // Merge both lists, newest first
        $merged = collect($changeRequests)->concat($requests)->sortByDesc('created_at')->values();

        return response()->json($merged);
    }

    /**
     * Lightweight pending count (for button badge polling)
     * Includes both add-on requests AND change requests
     */
    public function pendingCount()
    {
        $user = auth()->user();
        $isManager = $user && $user->isManager();

        $query = DB::table('sale_addon_requests')
            ->join('prototype_sales', 'sale_addon_requests.sale_id', '=', 'prototype_sales.id')
            ->where('sale_addon_requests.status', 'pending');

        if (!$isManager) {
            $query->where('prototype_sales.sales_agent_id', $user ? $user->id : -1);
        }

        if ($user && $user->isProdManager()) {
            $query->where('prototype_sales.department_id', 4);
        }

        $addonCount = $query->count();

        // Change requests (Add Product from sales page)
        $changeQuery = DB::table('prototype_sale_changes')
            ->join('prototype_sales', 'prototype_sale_changes.sale_id', '=', 'prototype_sales.id')
            ->where('prototype_sale_changes.status', 'pending');

        if (!$isManager) {
            $changeQuery->where('prototype_sales.sales_agent_id', $user ? $user->id : -1);
        }

        if ($user && $user->isProdManager()) {
            $changeQuery->where('prototype_sales.department_id', 4);
        }

        return response()->json(['count' => $addonCount + $changeQuery->count()]);
    }

    /**
     * Submit an addon request
     */
    public function request(Request $request, int $saleId)
    {
        $validated = $request->validate([
            'requested_items' => 'required|json',
            'reason' => 'nullable|string|max:500',
            'requested_by' => 'nullable|string|max:255',
        ]);

        $sale = DB::table('prototype_sales')->find($saleId);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        $id = DB::table('sale_addon_requests')->insertGetId([
            'sale_id' => $saleId,
            'requested_items' => $validated['requested_items'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
            'requested_by' => $validated['requested_by'] ?? 'Agent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'id' => $id,
            'message' => 'Add-on request submitted for approval.',
        ]);
    }

    /**
     * Approve an addon request — merge items and recalculate
     */
    public function approve(Request $request, int $requestId)
    {
        $user = auth()->user();
        if (!$user || !$user->isManager()) {
            return response()->json(['error' => 'Unauthorized: admin/manager only'], 403);
        }

        $addon = DB::table('sale_addon_requests')->find($requestId);
        if (!$addon || $addon->status !== 'pending') {
            return response()->json(['error' => 'Request not found or already processed'], 404);
        }

        $sale = DB::table('prototype_sales')->find($addon->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        // Prod manager is Class-only
        if ($user && $user->isProdManager() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
        }

        // Get existing services
        $existingServices = json_decode($sale->services ?? '[]', true);
        if (!is_array($existingServices)) {
            $existingServices = [];
        }

        // Get addon items
        $addonItems = json_decode($addon->requested_items, true);
        if (!is_array($addonItems)) {
            return response()->json(['error' => 'Invalid addon items'], 400);
        }

        // Get pricing data from database
        $pricingData = self::getPricingData();

        // Merge and recalculate
        $mergedItems = PricingCalculator::mergeAndRecalculate($existingServices, $addonItems, $pricingData);
        $newSubtotal = PricingCalculator::calculateGrandTotal($mergedItems);

        // Calculate tax (12% of subtotal)
        $taxRate = 0.12;
        $newTax = $newSubtotal * $taxRate;
        $newTotal = $newSubtotal + $newTax;

        // Old values
        $oldSubtotal = (float)($sale->subtotal ?? 0);
        $oldTotal = (float)($sale->total_amount ?? 0);
        $oldDepositPaid = (float)($sale->deposit_paid ?? 0);
        $oldBalanceDue = (float)($sale->balance_due ?? 0);

        // New balance due = old deposit already paid, so new balance = new total - old deposit
        $newBalanceDue = $newTotal - $oldDepositPaid;

        // Update the sale
        DB::table('prototype_sales')
            ->where('id', $sale->id)
            ->update([
                'services' => json_encode($mergedItems),
                'subtotal' => $newSubtotal,
                'tax' => $newTax,
                'total_amount' => $newTotal,
                'balance_due' => $newBalanceDue,
                'updated_at' => now(),
            ]);

        // Mark request as approved
        DB::table('sale_addon_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'approved',
                'approved_by' => $request->input('approved_by', 'Manager'),
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

        // Also update kanban item description
        $itemCount = count($mergedItems);
        DB::table('sales_kanban_items')
            ->where('sale_id', $sale->id)
            ->update([
                'description' => 'Services: ' . $itemCount . ' items | Total: ₱' . number_format($newTotal, 2),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Add-on approved and pricing recalculated.',
            'old_subtotal' => $oldSubtotal,
            'new_subtotal' => $newSubtotal,
            'old_total' => $oldTotal,
            'new_total' => $newTotal,
            'balance_due' => $newBalanceDue,
            'adjustment' => $newTotal - $oldTotal,
            'items' => $mergedItems,
        ]);
    }

    /**
     * Reject an addon request
     */
    public function reject(Request $request, int $requestId)
    {
        $user = auth()->user();
        if (!$user || !$user->isManager()) {
            return response()->json(['error' => 'Unauthorized: admin/manager only'], 403);
        }

        $addon = DB::table('sale_addon_requests')->find($requestId);
        if (!$addon || $addon->status !== 'pending') {
            return response()->json(['error' => 'Request not found or already processed'], 404);
        }

        $sale = DB::table('prototype_sales')->find($addon->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        // Prod manager is Class-only
        if ($user && $user->isProdManager() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
        }

        DB::table('sale_addon_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'rejected',
                'approved_by' => $request->input('approved_by', 'Manager'),
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Add-on request rejected.',
        ]);
    }

    /**
     * Restore a rejected add-on request → 'pending' (balik sa approval queue).
     * Hindi dinudublika ang merge/pricing logic ng approve() — iyon pa rin ang gagamitin.
     */
    public function restore(Request $request, int $requestId)
    {
        $user = auth()->user();
        if (!$user || !$user->isManager()) {
            return response()->json(['error' => 'Unauthorized: admin/manager only'], 403);
        }

        $addon = DB::table('sale_addon_requests')->find($requestId);
        if (!$addon) {
            return response()->json(['error' => 'Request not found'], 404);
        }
        if ($addon->status !== 'rejected') {
            return response()->json(['error' => 'This add-on request is not rejected (status: ' . $addon->status . ').'], 409);
        }

        $sale = DB::table('prototype_sales')->find($addon->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        // Prod manager is Class-only
        if ($user->isProdManager() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
        }
        if ($sale->status === 'cancelled') {
            return response()->json(['error' => 'Hindi ma-restore: cancelled ang sale. I-restore muna ang sale.'], 409);
        }

        DB::table('sale_addon_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'updated_at' => now(),
            ]);

        // Audit log (add-on has no audit FK; log against the sale)
        DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $addon->sale_id,
            'user_id' => $user->id,
            'action' => 'addon_restored',
            'description' => 'Rejected add-on request restored to pending approval.',
            'details' => json_encode(['addon_id' => $requestId]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notify the sale's agent (add-on has no submitted_by user id)
        $agentId = $sale->sales_agent_id ?? null;
        if ($agentId && (int) $agentId !== (int) $user->id) {
            DB::table('sale_notifications')->insert([
                'sale_id' => $addon->sale_id,
                'from_user_id' => $user->id,
                'to_user_id' => $agentId,
                'type' => 'approval',
                'title' => 'Rejected Add-on Restored ✅',
                'message' => 'Na-restore sa pending approval ang na-reject na add-on sa sale '
                    . ($sale->sales_number ?? ('#' . $sale->id)) . '.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Na-restore ang add-on — balik sa pending approval queue.',
        ]);
    }

    /**
     * Get pricing data from database (matching the front-end JS)
     */
    private static function getPricingData(): array
    {
        $prices = DB::table('product_print_prices')->get()->map(fn($p) => [
            'id' => (int)$p->id,
            'name' => $p->name,
            'price' => (float)$p->price,
        ])->toArray();

        $combos = DB::table('product_print_combos')->get()->map(fn($c) => [
            'size1_id' => (int)$c->size1_id,
            'size2_id' => (int)$c->size2_id,
            'discount' => (float)$c->discount,
            'label' => $c->label ?? '',
        ])->toArray();

        $bulkTiers = DB::table('product_print_bulk_tiers')->get()->map(fn($t) => [
            'min' => (int)$t->min_qty,
            'max' => (int)$t->max_qty,
            'type' => $t->discount_type,
            'percent' => (float)$t->discount_value,
            'amount' => (float)$t->discount_value,
            'label' => $t->label ?? '',
        ])->toArray();

        return [
            'prices' => $prices,
            'combos' => $combos,
            'bulk_tiers' => $bulkTiers,
        ];
    }
}
