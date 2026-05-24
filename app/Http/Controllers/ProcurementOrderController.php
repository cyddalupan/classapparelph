<?php

namespace App\Http\Controllers;

use App\Models\ProcurementOrder;
use App\Models\ProcurementOrderItem;
use App\Models\MasterItem;
use App\Models\SalesDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProcurementOrderController extends Controller
{
    /**
     * Show the create order form.
     */
    public function create()
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $departments = SalesDepartment::where('is_active', true)->orderBy('name')->get();
        $departmentId = request('department_id', $departments->first()->id ?? null);
        
        // Get items from department_master_items (per-department inventory)
        // Only show items assigned to departments — no generic MasterItems
        $query = MasterItem::whereNull('master_items.deleted_at')
            ->whereExists(function ($q) {
                $q->select(\DB::raw(1))
                  ->from('department_master_items')
                  ->whereColumn('department_master_items.master_item_id', 'master_items.id');
            });

        if ($departmentId) {
            $query->whereExists(function ($q) use ($departmentId) {
                $q->select(\DB::raw(1))
                  ->from('department_master_items')
                  ->whereColumn('department_master_items.master_item_id', 'master_items.id')
                  ->where('department_master_items.department_id', $departmentId);
            });
        }

        $items = $query->orderBy('master_items.category')
            ->orderBy('master_items.brand')
            ->orderBy('master_items.name')
            ->selectRaw("master_items.id, master_items.name, master_items.sku, master_items.category,
                         (SELECT current_stock FROM department_master_items 
                          WHERE master_item_id = master_items.id AND department_id = ?) as current_stock,
                         master_items.unit_price, master_items.brand, master_items.color,
                         master_items.size, master_items.shirt_type, master_items.material", [$departmentId])
            ->get();

        // Group items by category for the view
        $groupedItems = $items->groupBy('category');
        
        // Full catalog for manual modal quick-pick — with stock from selected department
        $catalogItems = MasterItem::whereNull('master_items.deleted_at')
            ->join('department_master_items', function ($j) use ($departmentId) {
                $j->on('department_master_items.master_item_id', '=', 'master_items.id')
                  ->where('department_master_items.department_id', $departmentId);
            })
            ->orderBy('master_items.category')
            ->orderBy('master_items.brand')
            ->orderBy('master_items.name')
            ->selectRaw('master_items.id, master_items.name, master_items.sku, master_items.unit_price, master_items.brand, master_items.category, COALESCE(department_master_items.current_stock, 0) as current_stock')
            ->get();
        $manualCategories = $catalogItems->pluck('category')->unique()->values()->toArray();
        $manualBrands = $catalogItems->pluck('brand')->unique()->filter()->values()->toArray();
        $catalogJson = $catalogItems->toJson();

        // 🆕 Get reorder recommendations based on recent sales
        $recommendations = $this->getReorderRecommendations(departmentId: $departmentId);

        // Build recommendation lookup map keyed by master_item_id
        $recommendationMap = [];
        foreach ($recommendations as $rec) {
            $recommendationMap[$rec['master_item_id']] = $rec;
        }

        return view('procurement.orders.create', compact('departments', 'items', 'groupedItems', 'recommendations', 'recommendationMap', 'departmentId', 'manualCategories', 'manualBrands', 'catalogJson'));
    }

    /**
     * Calculate reorder recommendations based on recent sales and current stock.
     */
    private function getReorderRecommendations($daysBack = 7, $departmentId = null)
    {
        $since = now()->subDays($daysBack);
        
        $query = \DB::table('sale_tracked_items')
            ->select(
                'master_item_id',
                'item_name',
                'sku',
                \DB::raw('SUM(quantity) as total_sold'),
                \DB::raw('COUNT(DISTINCT sale_id) as sale_count'),
                'department_id',
                'department_name'
            )
            ->where('created_at', '>=', $since)
            ->whereNotNull('master_item_id');
        
        // Filter by department if one is selected
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $soldItems = $query->groupBy('master_item_id', 'item_name', 'sku', 'department_id', 'department_name')->get();
        
        $recommendations = [];
        
        foreach ($soldItems as $sold) {
            $masterItem = MasterItem::find($sold->master_item_id);
            if (!$masterItem) continue;
            
            // Use department-level stock and minimum if department is selected
            if ($departmentId) {
                $deptInventory = \DB::table('department_master_items')
                    ->where('master_item_id', $masterItem->id)
                    ->where('department_id', $departmentId)
                    ->first(['current_stock', 'minimum_stock']);
                $currentStock = (float)(($deptInventory->current_stock ?? $masterItem->current_stock) ?: 0);
                $minStock = (int)(($deptInventory->minimum_stock ?? $masterItem->minimum_stock) ?: 10);
            } else {
                $currentStock = (float)($masterItem->current_stock ?? 0);
                $minStock = (int)($masterItem->minimum_stock ?? 10);
            }
            
            $totalSold = (int)$sold->total_sold;
            
            // Calculate reorder recommendation
            // If stock is low relative to sales, recommend reorder
            $reorderQty = 0;
            $priority = 'low';
            
            if ($currentStock <= 0) {
                // Out of stock - urgently needs reorder
                $reorderQty = max($totalSold, $minStock);
                $priority = 'critical';
            } elseif ($currentStock <= $minStock) {
                // Below minimum stock
                $reorderQty = $totalSold + ($minStock - $currentStock);
                $priority = 'high';
            } elseif ($currentStock <= $totalSold * 0.5) {
                // Stock is less than 50% of what was recently sold
                $reorderQty = $totalSold - $currentStock;
                $priority = 'medium';
            }
            
            if ($reorderQty > 0) {
                $recommendations[] = [
                    'master_item_id' => $masterItem->id,
                    'item_name' => $masterItem->name,
                    'sku' => $masterItem->sku,
                    'category' => $masterItem->category,
                    'brand' => $masterItem->brand,
                    'color' => $masterItem->color,
                    'size' => $masterItem->size,
                    'shirt_type' => $masterItem->shirt_type,
                    'material' => $masterItem->material,
                    'current_stock' => $currentStock,
                    'total_sold' => $totalSold,
                    'sale_count' => $sold->sale_count,
                    'recommended_qty' => $reorderQty,
                    'priority' => $priority,
                    'department_id' => $sold->department_id,
                    'department_name' => $sold->department_name,
                ];
            }
        }
        
        // Sort by priority (critical first, then high, then medium)
        $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2];
        usort($recommendations, function($a, $b) use ($priorityOrder) {
            return ($priorityOrder[$a['priority']] ?? 99) - ($priorityOrder[$b['priority']] ?? 99);
        });
        
        return $recommendations;
    }

    /**
     * Store a new procurement order.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'department_id' => 'nullable|exists:sales_departments,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.master_item_id' => 'nullable|exists:master_items,id',
            'items.*.item_name' => 'required_without:items.*.master_item_id|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $order = ProcurementOrder::create([
                'order_number' => ProcurementOrder::generateOrderNumber(),
                'department_id' => $validated['department_id'],
                'created_by' => auth()->id(),
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['master_item_id'])) {
                    // Existing item from master list
                    $item = MasterItem::findOrFail($itemData['master_item_id']);
                    
                    ProcurementOrderItem::create([
                        'procurement_order_id' => $order->id,
                        'master_item_id' => $item->id,
                        'item_name' => $item->name,
                        'sku' => $item->sku,
                        'quantity_ordered' => $itemData['quantity'],
                        'unit_price' => $item->unit_price,
                        'notes' => $itemData['notes'] ?? null,
                    ]);

                    // Log to inventory activity
                    try {
                        \App\Models\InventoryActivityLog::create([
                            'master_item_id' => $item->id,
                            'department_id' => $validated['department_id'],
                            'action_type' => 'item_ordered',
                            'item_name' => $item->name,
                            'sku' => $item->sku,
                            'category' => $item->category,
                            'quantity' => $itemData['quantity'],
                            'user_id' => auth()->id(),
                            'notes' => "Order #{$order->order_number}: Qty {$itemData['quantity']}",
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to log order activity: ' . $e->getMessage());
                    }
                } else {
                    // Manual item (not in master list)
                    ProcurementOrderItem::create([
                        'procurement_order_id' => $order->id,
                        'master_item_id' => null,
                        'item_name' => $itemData['item_name'],
                        'sku' => $itemData['sku'] ?? null,
                        'quantity_ordered' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'] ?? null,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Order {$order->order_number} created successfully!",
                'order' => $order->load('items'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all orders for the current user/department.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $departments = SalesDepartment::where('is_active', true)->orderBy('name')->get();

        $query = ProcurementOrder::with(['items', 'department', 'creator']);

        // Filter by department if requested
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Stats
        $stats = [
            'total' => ProcurementOrder::count(),
            'pending' => ProcurementOrder::whereIn('status', ['draft', 'for_approval'])->count(),
            'in_progress' => ProcurementOrder::whereIn('status', ['for_procurement', 'ordered', 'for_delivery', 'partial'])->count(),
            'completed' => ProcurementOrder::where('status', 'completed')->count(),
        ];

        // Compiled items across all orders (respecting filters)
        $compiledItems = [];
        $allItems = ProcurementOrderItem::whereHas('order', function ($q) use ($request) {
            if ($request->has('department_id')) {
                $q->where('department_id', $request->department_id);
            }
            if ($request->has('status')) {
                $q->where('status', $request->status);
            }
        })->with('order.department')->get();
        
        foreach ($allItems as $item) {
            $mi = MasterItem::find($item->master_item_id);
            $cat = $mi->category ?? 'Uncategorized';
            $name = $item->item_name;
            $brand = $mi->brand ?? '';
            $size = $mi->size ?? '';
            $color = $mi->color ?? '';
            $key = $cat . '|' . $name . '|' . $brand . '|' . $size . '|' . $color;
            
            if (!isset($compiledItems[$cat])) {
                $compiledItems[$cat] = ['total_qty' => 0, 'items' => []];
            }
            
            if (!isset($compiledItems[$cat]['items'][$key])) {
                $compiledItems[$cat]['items'][$key] = [
                    'name' => $name,
                    'brand' => $brand,
                    'size' => $size,
                    'color' => $color,
                    'total_qty' => 0,
                    'departments' => [],
                ];
            }
            
            $qty = (int)($item->quantity_ordered ?? $item->quantity ?? 1);
            $compiledItems[$cat]['items'][$key]['total_qty'] += $qty;
            $compiledItems[$cat]['total_qty'] += $qty;
            
            $deptName = $item->order->department->name ?? 'General';
            $deptFound = false;
            foreach ($compiledItems[$cat]['items'][$key]['departments'] as &$d) {
                if ($d['name'] === $deptName) {
                    $d['qty'] += $qty;
                    $deptFound = true;
                    break;
                }
            }
            if (!$deptFound) {
                $compiledItems[$cat]['items'][$key]['departments'][] = [
                    'name' => $deptName,
                    'qty' => $qty,
                ];
            }
        }
        
        if ($request->wantsJson()) {
            return response()->json($orders);
        }

        return view('procurement.orders.index', compact('orders', 'departments', 'stats', 'compiledItems'));
    }

    /**
     * Show a specific order.
     */
    public function show($id)
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $order = ProcurementOrder::with(['items', 'department', 'creator'])->findOrFail($id);
        return response()->json(['success' => true, 'order' => $order]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validStatuses = ['draft', 'for_approval', 'for_procurement', 'ordered', 'for_delivery', 'partial', 'completed', 'cancelled'];
        
        $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses),
        ]);

        $order = ProcurementOrder::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        try {
            DB::beginTransaction();

            $order->update(['status' => $newStatus]);

            // Auto-set timestamps based on status
            if ($newStatus === 'for_approval' && !$order->submitted_at) {
                $order->update(['submitted_at' => now()]);
            }
            if ($newStatus === 'ordered' && !$order->ordered_at) {
                $order->update(['ordered_at' => now()]);
            }
            if ($newStatus === 'for_delivery' && !$order->expected_delivery_at) {
                $order->update(['expected_delivery_at' => now()->addDays(3)]);
            }
            if ($newStatus === 'delivered') {
                $order->update(['delivered_at' => now()]);
            }

            // If completed, auto-update stocks in department_master_items
            if ($newStatus === 'completed') {
                foreach ($order->items as $item) {
                    if ($item->master_item_id) {
                        $qtyReceived = (int)($item->quantity_received ?? $item->quantity_ordered ?? $item->quantity ?? 0);
                        if ($qtyReceived > 0) {
                            DB::table('department_master_items')
                                ->where('master_item_id', $item->master_item_id)
                                ->where('department_id', $order->department_id)
                                ->increment('current_stock', $qtyReceived);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Order {$order->order_number} moved to " . $order->statusLabel() . "!",
                'order' => $order->fresh()->load('items'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
