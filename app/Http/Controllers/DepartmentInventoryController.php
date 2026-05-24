<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterItem;

class DepartmentInventoryController extends Controller
{
    /**
     * Get departments list (for filters/modals)
     */
    public function departments()
    {
        $departments = DB::table('sales_departments')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($departments);
    }

    /**
     * Get master items for a specific department (via department_master_items pivot).
     * If department_id is null, returns ALL master items.
     */
    public function departmentItems(Request $request)
    {
        $departmentId = $request->department_id;
        $category = $request->category;

        $query = DB::table('master_items')
            ->leftJoin('department_master_items', 'master_items.id', '=', 'department_master_items.master_item_id')
            ->leftJoin('sales_departments', 'department_master_items.department_id', '=', 'sales_departments.id')
            ->whereNull('master_items.deleted_at');

        // Filter by department
        if ($departmentId && $departmentId !== 'unassigned' && $departmentId !== 'all') {
            $query->where('department_master_items.department_id', $departmentId);
        } elseif ($departmentId === 'unassigned') {
            $query->whereNull('department_master_items.master_item_id');
        }

        // Filter by category
        if ($category) {
            $query->where('master_items.category', $category);
        }

        // For 'all' view: SUM stocks across all departments; for specific dept: per-dept stock
        if (!$departmentId || $departmentId === 'all') {
            $query->select(
                'master_items.id',
                'master_items.name',
                'master_items.sku',
                'master_items.brand',
                'master_items.category',
                'master_items.unit_price',
                'master_items.description',
                DB::raw('COALESCE(SUM(department_master_items.current_stock), master_items.current_stock) as current_stock'),
                DB::raw('COALESCE(SUM(department_master_items.minimum_stock), master_items.minimum_stock) as minimum_stock'),
                'master_items.created_at',
                DB::raw('GROUP_CONCAT(DISTINCT sales_departments.name ORDER BY sales_departments.name SEPARATOR ", ") as department_names'),
                DB::raw('GROUP_CONCAT(DISTINCT sales_departments.id ORDER BY sales_departments.id SEPARATOR ",") as department_ids')
            );
        } else {
            $query->select(
                'master_items.id',
                'master_items.name',
                'master_items.sku',
                'master_items.brand',
                'master_items.category',
                'master_items.unit_price',
                'master_items.description',
                DB::raw('COALESCE(department_master_items.current_stock, master_items.current_stock) as current_stock'),
                DB::raw('COALESCE(department_master_items.minimum_stock, master_items.minimum_stock) as minimum_stock'),
                'master_items.created_at',
                DB::raw('GROUP_CONCAT(DISTINCT sales_departments.name ORDER BY sales_departments.name SEPARATOR ", ") as department_names'),
                DB::raw('GROUP_CONCAT(DISTINCT sales_departments.id ORDER BY sales_departments.id SEPARATOR ",") as department_ids')
            );
        }

        $items = $query->groupBy('master_items.id')
            ->orderBy('master_items.name')
            ->get();

        // Role-based pricing: non-admin users see sales_team_price instead of unit_price
        $user = $request->user();
        $isNonAdmin = $user && !$user->isAdmin();
        
        if ($isNonAdmin) {
            $items->transform(function ($item) {
                // Get master item to access sales_team_price
                $masterItem = \App\Models\MasterItem::find($item->id);
                if ($masterItem && $masterItem->sales_team_price) {
                    $item->unit_price = $masterItem->sales_team_price;
                }
                return $item;
            });
        }

        return response()->json($items);
    }

    /**
     * Assign master items to a department.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:sales_departments,id',
            'inventory_ids' => 'required|array',
            'inventory_ids.*' => 'integer|exists:master_items,id',
        ]);

        $userId = auth()->id();
        $inserted = [];
        $now = now();

        foreach ($request->inventory_ids as $masterItemId) {
            try {
                // Only insert if not yet assigned to this department
                $existing = DB::table('department_master_items')
                    ->where('department_id', $request->department_id)
                    ->where('master_item_id', $masterItemId)
                    ->first();

                if (!$existing) {
                    // Get global stock to copy as initial department stock
                    $masterItem = MasterItem::find($masterItemId);
                    $initialStock = $masterItem ? $masterItem->current_stock : 0;

                    DB::table('department_master_items')->insert([
                        'department_id' => $request->department_id,
                        'master_item_id' => $masterItemId,
                        'assigned_by' => $userId,
                        'current_stock' => $initialStock,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]);
                }
                $inserted[] = $masterItemId;
            } catch (\Exception $e) {
                // Skip duplicates
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($inserted) . ' item(s) assigned to department.',
            'assigned' => $inserted,
        ]);
    }

    /**
     * Remove master items from a department.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:sales_departments,id',
            'inventory_ids' => 'required|array',
            'inventory_ids.*' => 'integer|exists:master_items,id',
        ]);

        $deleted = DB::table('department_master_items')
            ->where('department_id', $request->department_id)
            ->whereIn('master_item_id', $request->inventory_ids)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' item(s) removed from department.',
            'removed' => $deleted,
        ]);
    }
}
