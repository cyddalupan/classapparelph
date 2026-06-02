<?php

namespace App\Http\Controllers;

use App\Models\ProcurementOrder;
use App\Models\ProcurementOrderItem;
use App\Models\ProcurementRemark;
use App\Models\ProcurementNotification;
use App\Models\MasterItem;
use App\Models\SalesDepartment;
use App\Models\Supplier;
use App\Models\User;
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
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $departmentId = request('department_id', $departments->first()->id ?? null);
        
        // Get items from department_master_items (per-department inventory)
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

        // Group items by category
        $groupedItems = $items->groupBy('category');
        
        // Full catalog for manual modal quick-pick
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

        // Reorder recommendations
        $recommendations = $this->getReorderRecommendations(departmentId: $departmentId);
        $recommendationMap = [];
        foreach ($recommendations as $rec) {
            $recommendationMap[$rec['master_item_id']] = $rec;
        }

        return view('procurement.orders.create', compact(
            'departments', 'suppliers', 'items', 'groupedItems',
            'recommendations', 'recommendationMap', 'departmentId',
            'manualCategories', 'manualBrands', 'catalogJson'
        ));
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
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $soldItems = $query->groupBy('master_item_id', 'item_name', 'sku', 'department_id', 'department_name')->get();
        $recommendations = [];
        
        foreach ($soldItems as $sold) {
            $masterItem = MasterItem::find($sold->master_item_id);
            if (!$masterItem) continue;
            
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
            $reorderQty = 0;
            $priority = 'low';
            
            if ($currentStock <= 0) {
                $reorderQty = max($totalSold, $minStock);
                $priority = 'critical';
            } elseif ($currentStock <= $minStock) {
                $reorderQty = $totalSold + ($minStock - $currentStock);
                $priority = 'high';
            } elseif ($currentStock <= $totalSold * 0.5) {
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
            'supplier_id' => 'nullable|exists:suppliers,id',
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
                'supplier_id' => $validated['supplier_id'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['master_item_id'])) {
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
     * List all orders with per-supplier grouping and manager filtering.
     * Features: (1) brand/color/size summary, (2) supplier filter, (3) totals,
     * (4) status tags, (6) manager filtering, (7) notifications
     */
    /**
     * Analytics dashboard with charts and cost analysis.
     */
    public function dashboard(Request $request)
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin() && !auth()->user()->isProcurement()) {
            abort(403, 'Unauthorized access.');
        }

        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $isProcurement = $user->isProcurement();

        // Date range defaults: last 7 days to today
        $dateFrom = $request->input('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Base query with date filter
        $query = ProcurementOrder::with(['items', 'department', 'supplier'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        // Manager filtering
        if (!$isAdmin && !$isProcurement) {
            $managedDeptIds = SalesDepartment::where('manager_id', $user->id)->pluck('id');
            $query->whereIn('department_id', $managedDeptIds);
        }

        // Total aggregates
        $totalOrders = (clone $query)->count();
        $totalItemsOrdered = (clone $query)->with('items')->get()->sum(fn($o) => $o->items->sum('quantity_ordered'));
        $totalCost = (clone $query)->with('items')->get()->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered));

        // Orders by status
        $ordersByStatus = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('total', 'desc')
            ->get();

        // Orders by department (count + cost)
        $ordersByDept = (clone $query)->with('items')->get()->groupBy('department_id')->map(function ($orders, $deptId) {
            $dept = SalesDepartment::find($deptId);
            return [
                'department_name' => $dept ? $dept->name : 'Unknown',
                'count' => $orders->count(),
                'cost' => $orders->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered)),
            ];
        })->sortByDesc('cost');

        // Orders by supplier (count + cost)
        $ordersBySupplier = (clone $query)->with('items')->get()->groupBy('supplier_id')->map(function ($orders, $supplierId) {
            $supplier = Supplier::find($supplierId);
            return [
                'supplier_name' => $supplier ? $supplier->name : 'Unknown',
                'count' => $orders->count(),
                'cost' => $orders->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered)),
            ];
        })->sortByDesc('cost');

        // Daily order trends (for line chart)
        $dailyTrends = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as order_count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Add cost per day
        $dailyCosts = (clone $query)->with('items')->get()->groupBy(fn($o) => $o->created_at->format('Y-m-d'));
        $dailyTrendData = $dailyTrends->map(function ($day) use ($dailyCosts) {
            $date = $day->date;
            $cost = isset($dailyCosts[$date]) ? $dailyCosts[$date]->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered)) : 0;
            return [
                'date' => $date,
                'order_count' => $day->order_count,
                'cost' => $cost,
            ];
        })->values();

        // Top purchased items by quantity
        $topItemsByQty = (clone $query)->with('items')->get()->flatMap(fn($o) => $o->items)
            ->groupBy('master_item_id')
            ->map(function ($items, $masterItemId) {
                $mi = MasterItem::find($masterItemId);
                return [
                    'item_name' => $mi ? $mi->name : ($items->first()->item_name ?? 'Unknown'),
                    'total_qty' => $items->sum('quantity_ordered'),
                ];
            })->sortByDesc('total_qty')->take(10);

        // Top purchased items by cost
        $topItemsByCost = (clone $query)->with('items')->get()->flatMap(fn($o) => $o->items)
            ->groupBy('master_item_id')
            ->map(function ($items, $masterItemId) {
                $mi = MasterItem::find($masterItemId);
                return [
                    'item_name' => $mi ? $mi->name : ($items->first()->item_name ?? 'Unknown'),
                    'total_cost' => $items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered),
                ];
            })->sortByDesc('total_cost')->take(10);

        // Cost analysis: completed orders in the date range
        $completedQuery = ProcurementOrder::with(['items', 'department', 'supplier'])
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $completedOrders = (clone $completedQuery)->get();
        $totalOrderedCost = 0;
        $totalActualCost = 0;
        $overpaymentTotal = 0;
        $underDeliveryCount = 0;
        $costVarianceItems = [];

        foreach ($completedOrders as $co) {
            foreach ($co->items as $item) {
                $orderedCost = ($item->quantity_ordered ?? 0) * ($item->unit_price ?? 0);
                $actualCost = ($item->qty_verified ?? 0) * ($item->unit_price ?? 0);
                $totalOrderedCost += $orderedCost;
                $totalActualCost += $actualCost;
                $diff = $orderedCost - $actualCost;

                if (abs($diff) > 0) {
                    if ($diff > 0) $overpaymentTotal += $diff;
                    if ($item->qty_verified < $item->quantity_ordered) $underDeliveryCount++;
                    $costVarianceItems[] = [
                        'order_number' => $co->order_number,
                        'department_name' => $co->department?->name ?? 'Unknown',
                        'supplier_name' => $co->supplier?->name ?? 'Unknown',
                        'item_name' => $item->item_name,
                        'ordered_qty' => $item->quantity_ordered,
                        'verified_qty' => $item->qty_verified ?? 0,
                        'unit_price' => $item->unit_price ?? 0,
                        'ordered_cost' => $orderedCost,
                        'actual_cost' => $actualCost,
                        'difference' => $diff,
                    ];
                }
            }
        }

        // Procurement activity (orders grouped by day with totals)
        $activityByDay = (clone $query)->with('items')->get()->groupBy(fn($o) => $o->created_at->format('Y-m-d'))
            ->map(function ($orders, $date) {
                return [
                    'date' => $date,
                    'order_count' => $orders->count(),
                    'item_count' => $orders->sum(fn($o) => $o->items->sum('quantity_ordered')),
                    'total_cost' => $orders->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered)),
                ];
            })->sortKeys();

        // Dropdown data
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $departments = SalesDepartment::where('is_active', true)->orderBy('name')->get();
        $statuses = ['draft', 'for_approval', 'for_procurement', 'ordered', 'ongoing', 'preparing', 'for_delivery', 'partial', 'delivered', 'for_verification', 'completed', 'cancelled'];

        // Unread notifications for current user
        $unreadNotifications = ProcurementNotification::with(['order', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->get();

        $managedDeptIds = SalesDepartment::where('manager_id', $user->id)->pluck('id');
        $pendingVerificationsCount = ProcurementOrder::where('status', 'for_verification')
            ->whereIn('department_id', $managedDeptIds)
            ->count();

        return view('procurement.orders.dashboard', compact(
            'dateFrom', 'dateTo',
            'totalOrders', 'totalItemsOrdered', 'totalCost',
            'ordersByStatus', 'ordersByDept', 'ordersBySupplier',
            'dailyTrendData', 'topItemsByQty', 'topItemsByCost',
            'totalOrderedCost', 'totalActualCost', 'overpaymentTotal', 'underDeliveryCount',
            'costVarianceItems', 'activityByDay',
            'suppliers', 'departments', 'statuses',
            'isAdmin', 'isProcurement', 'unreadNotifications',
            'pendingVerificationsCount'
        ));
    }

    public function index(Request $request)
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin() && !auth()->user()->isProcurement()) {
            abort(403, 'Unauthorized access.');
        }

        $user = auth()->user();
        $isProcurement = $user->isProcurement();
        $isAdmin = $user->isAdmin();

        $query = ProcurementOrder::with([
            'items.masterItem',
            'department', 'creator', 'supplier', 'procurementUser',
            'remarks.user',
        ]);

        // Manager filtering: managers only see orders from their departments
        if (!$isAdmin && !$isProcurement) {
            $managedDeptIds = SalesDepartment::where('manager_id', $user->id)->pluck('id');
            $query->whereIn('department_id', $managedDeptIds);
        }

        // Filters
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        // Group by supplier
        $ordersGroupedBySupplier = $orders->groupBy(function ($order) {
            return $order->supplier ? $order->supplier->name : 'No Supplier';
        });

        // Supplier totals
        $supplierTotals = [];
        foreach ($ordersGroupedBySupplier as $supplierName => $supplierOrders) {
            $totalQty = 0;
            $totalCost = 0;
            foreach ($supplierOrders as $o) {
                foreach ($o->items as $item) {
                    $totalQty += $item->quantity_ordered;
                    $totalCost += ($item->unit_price ?? 0) * $item->quantity_ordered;
                }
            }
            $supplierTotals[$supplierName] = [
                'count' => $supplierOrders->count(),
                'total_qty' => $totalQty,
                'total_cost' => $totalCost,
            ];
        }

        // Grand totals
        $grandTotalQty = collect($supplierTotals)->sum('total_qty');
        $grandTotalCost = collect($supplierTotals)->sum('total_cost');
        $totalOrders = $orders->total();

        // Completed orders summary (across all orders, not just paginated)
        $completedOrders = ProcurementOrder::with('items')
            ->where('status', 'completed')
            ->get();
        $completedCount = $completedOrders->count();
        $completedTotalCost = 0;
        $discrepantOrders = [];
        foreach ($completedOrders as $co) {
            $orderedCost = 0;
            $actualCost = 0;
            foreach ($co->items as $item) {
                $orderedCost += ($item->quantity_ordered ?? 0) * ($item->unit_price ?? 0);
                $actualCost += ($item->qty_verified ?? 0) * ($item->unit_price ?? 0);
            }
            $completedTotalCost += $orderedCost;
            $diff = $orderedCost - $actualCost;
            if (abs($diff) > 0) {
                $discrepantOrders[] = [
                    'order' => $co,
                    'ordered_cost' => $orderedCost,
                    'actual_cost' => $actualCost,
                    'difference' => $diff,
                ];
            }
        }

        // Dropdown data
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $departments = SalesDepartment::where('is_active', true)->orderBy('name')->get();
        $statuses = ['draft', 'for_approval', 'for_procurement', 'ordered', 'ongoing', 'preparing', 'for_delivery', 'partial', 'delivered', 'for_verification', 'completed', 'cancelled'];

        // Unread notifications for current user
        $unreadNotifications = ProcurementNotification::with(['order', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->get();

        // Pending verifications for managers
        $managedDeptIds = SalesDepartment::where('manager_id', $user->id)->pluck('id');
        $pendingVerificationsCount = ProcurementOrder::where('status', 'for_verification')
            ->whereIn('department_id', $managedDeptIds)
            ->count();

        return view('procurement.orders.index', compact(
            'orders', 'ordersGroupedBySupplier', 'supplierTotals',
            'grandTotalQty', 'grandTotalCost', 'totalOrders',
            'suppliers', 'departments', 'statuses',
            'isProcurement', 'isAdmin', 'unreadNotifications',
            'pendingVerificationsCount',
            'completedCount', 'completedTotalCost', 'discrepantOrders'
        ));
    }

    /**
     * Show a specific order with full details, grouped items, remarks, and notifications.
     */
    public function show($id)
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin() && !auth()->user()->isProcurement()) {
            abort(403, 'Unauthorized access.');
        }

        $order = ProcurementOrder::with([
            'items.masterItem',
            'department', 'creator', 'supplier', 'procurementUser',
            'receiver', 'verifier',
            'remarks' => function ($q) { $q->latest(); },
            'remarks.user',
            'notifications' => function ($q) { $q->latest(); },
            'notifications.fromUser',
            'notifications.toUser',
        ])->findOrFail($id);

        // Group items by brand/color/size for the supplier summary
        $groupedItems = $order->items->groupBy(function ($item) {
            $masterItem = $item->masterItem;
            $brand = $masterItem?->brand ?? '';
            $color = $masterItem?->color ?? $masterItem?->other_color ?? '';
            $size = $masterItem?->size ?? '';
            return trim("{$brand}|{$color}|{$size}");
        });

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        // Catalog items for substitution modal (same department)
        $catalogItems = \App\Models\MasterItem::whereNull('master_items.deleted_at')
            ->join('department_master_items', function ($j) use ($order) {
                $j->on('department_master_items.master_item_id', '=', 'master_items.id')
                  ->where('department_master_items.department_id', $order->department_id);
            })
            ->orderBy('master_items.category')
            ->orderBy('master_items.brand')
            ->orderBy('master_items.name')
            ->selectRaw('master_items.id, master_items.name, master_items.sku, master_items.unit_price, master_items.brand, master_items.category, master_items.color, master_items.size, COALESCE(department_master_items.current_stock, 0) as current_stock')
            ->get();
        $catalogJson = $catalogItems->toJson();

        return view('procurement.orders.show', compact('order', 'groupedItems', 'suppliers', 'catalogItems', 'catalogJson'));
    }

    /**
     * Update order status, supplier, and procurement notes.
     */
    public function updateStatus(Request $request, $id)
    {
        if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin() && !auth()->user()->isProcurement()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validStatuses = ['draft', 'for_approval', 'for_procurement', 'ordered', 'ongoing', 'preparing', 'for_delivery', 'partial', 'for_verification', 'delivered', 'completed', 'cancelled'];

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses),
            'procurement_notes' => 'nullable|string|max:1000',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $order = ProcurementOrder::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // Only procurement and admin can change status — NOT managers
        if (!auth()->user()->isAdmin() && !auth()->user()->isProcurement()) {
            return redirect()->back()->with('error', 'Only procurement staff can change order status.');
        }

        // Marking as completed requires verification first
        if ($newStatus === 'completed') {
            if ($order->status !== 'for_verification') {
                return redirect()->back()->with('error', 'Order must be in For Verification status first before completing.');
            }
        }

        try {
            DB::beginTransaction();

            $order->update([
                'status' => $newStatus,
                'procurement_notes' => $validated['procurement_notes'] ?? $order->procurement_notes,
                'supplier_id' => $validated['supplier_id'] ?? $order->supplier_id,
            ]);

            // Auto-set timestamps
            if ($newStatus === 'for_approval' && !$order->fresh()->submitted_at) {
                $order->update(['submitted_at' => now()]);
            }
            if ($newStatus === 'ordered' && !$order->fresh()->ordered_at) {
                $order->update(['ordered_at' => now()]);
            }
            if ($newStatus === 'for_delivery' && !$order->fresh()->expected_delivery_at) {
                $order->update(['expected_delivery_at' => now()->addDays(3)]);
            }
            if ($newStatus === 'for_verification') {
                // Delivery physically arrived — log it
                $order->update([
                    'received_at' => $order->fresh()->received_at ?? now(),
                    'received_by' => $order->fresh()->received_by ?? auth()->id(),
                    'delivered_at' => $order->fresh()->delivered_at ?? now(),
                ]);
            }

            // When completed — update inventory with qty_verified (or qty_received as fallback)
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $order->update(['completed_at' => now()]);
                $items = ProcurementOrderItem::where('procurement_order_id', $order->id)->get();
                foreach ($items as $item) {
                    $qtyForStock = $item->qty_verified ?? 0;
                    if ($qtyForStock > 0 && $item->master_item_id) {
                        DB::table('department_master_items')
                            ->where('master_item_id', $item->master_item_id)
                            ->where('department_id', $order->department_id)
                            ->increment('current_stock', $qtyForStock);
                    }
                }
            }

            // Log status change remark
            ProcurementRemark::create([
                'procurement_order_id' => $order->id,
                'user_id' => auth()->id(),
                'remark' => "Status changed: {$oldStatus} → {$newStatus}",
                'type' => 'general',
            ]);

            DB::commit();

            $message = "Order {$order->order_number} moved to " . ucfirst(str_replace('_', ' ', $newStatus)) . "!";

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'status' => $newStatus]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'Failed to update status: ' . $e->getMessage();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }
            return redirect()->back()->with('error', $errorMsg);
        }
    }

    /**
     * Update supplier availability — Procurement checks with supplier and logs:
     *   - How many the supplier actually has (qty_from_supplier)
     *   - Any substitution / brand change notes (supplier_notes)
     * No status change — just info. Status is managed via dropdown.
     */
    public function updateSupplierAvailability(Request $request, $id)
    {
        $order = ProcurementOrder::findOrFail($id);

        if (!auth()->user()->isProcurement() && !auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Only procurement staff can update supplier availability.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:procurement_order_items,id',
            'items.*.qty_from_supplier' => 'nullable|integer|min:0',
            'items.*.supplier_notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $hasChanges = false;

            foreach ($validated['items'] as $itemData) {
                $item = ProcurementOrderItem::findOrFail($itemData['id']);
                $qtyFromSupplier = $itemData['qty_from_supplier'] ?? $item->quantity_ordered;
                $notes = $itemData['supplier_notes'] ?? null;

                $item->update([
                    'qty_from_supplier' => $qtyFromSupplier,
                    'supplier_notes' => $notes,
                ]);

                if ((int)$qtyFromSupplier !== (int)$item->quantity_ordered || !empty($notes)) {
                    $hasChanges = true;
                }
            }

            $remarkText = "Supplier availability updated.";
            if ($hasChanges) {
                $remarkText .= " Some items may have limited supply or substitutions — check notes.";
            }

            ProcurementRemark::create([
                'procurement_order_id' => $order->id,
                'user_id' => auth()->id(),
                'remark' => $remarkText,
                'type' => 'general',
                'is_internal' => false,
            ]);

            DB::commit();

            return redirect()->back()->with('success', '✅ Supplier availability updated! Manager will see expected quantities.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * Substitute an item with a different brand/master_item.
     * Updates item_name, sku, unit_price from the new master_item.
     */
    public function substituteItem(Request $request, $id, $itemId)
    {
        if (!auth()->user()->isProcurement() && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'master_item_id' => 'required|exists:master_items,id',
        ]);

        $order = \App\Models\ProcurementOrder::findOrFail($id);
        $item = \App\Models\ProcurementOrderItem::where('procurement_order_id', $order->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $newMasterItem = \App\Models\MasterItem::findOrFail($request->master_item_id);

        // Update the PO item with new master item data
        $item->update([
            'master_item_id' => $newMasterItem->id,
            'item_name' => $newMasterItem->name,
            'sku' => $newMasterItem->sku,
            'unit_price' => $newMasterItem->unit_price,
            'supplier_notes' => trim(($item->supplier_notes ? $item->supplier_notes . ' | ' : '') . 'Substituted: was ' . $item->item_name),
        ]);

        // Log the substitution
        \App\Models\ProcurementRemark::create([
            'procurement_order_id' => $order->id,
            'user_id' => auth()->id(),
            'remark' => "Item substituted: {$item->item_name} → {$newMasterItem->name} (brand: {$newMasterItem->brand}) — price updated to ₱{$newMasterItem->unit_price}",
            'type' => 'general',
        ]);

        return redirect()->back()->with('success', "Item substituted to {$newMasterItem->name} with updated price ₱{$newMasterItem->unit_price}!");
    }

    /**
     * Manager verifies the delivery — only qty_verified goes to inventory.
     */
    public function verifyDelivery(Request $request, $id)
    {
        $order = ProcurementOrder::findOrFail($id);

        // Only the assigned manager can verify
        $managedDeptIds = SalesDepartment::where('manager_id', auth()->id())->pluck('id')->toArray();
        if (!in_array($order->department_id, $managedDeptIds) && !auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'You are not the manager of this shop.');
        }

        if ($order->status !== 'for_verification') {
            return redirect()->back()->with('error', 'Order is not pending verification.');
        }

        if ($order->verified_at) {
            return redirect()->back()->with('error', 'This order has already been verified.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:procurement_order_items,id',
            'items.*.qty_verified' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $hasDiscrepancy = false;
            $discrepancyDetails = [];

            foreach ($validated['items'] as $itemData) {
                $item = ProcurementOrderItem::findOrFail($itemData['id']);
                $item->update([
                    'qty_verified' => $itemData['qty_verified'],
                ]);

                // Check for discrepancy between what supplier had vs what manager counted
                $supplierQty = $item->qty_from_supplier ?? $item->quantity_ordered;
                $diff = $supplierQty - $itemData['qty_verified'];
                if ($diff != 0) {
                    $hasDiscrepancy = true;
                    $discrepancyDetails[] = "{$item->item_name}: supplier had {$supplierQty}, counted {$itemData['qty_verified']} (diff: {$diff})";
                }
            }

            // Mark as verified only — NO status change, NO inventory update
            $order->update([
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Remark
            $remarkText = "✅ Order verified by manager.";
            if ($hasDiscrepancy) {
                $remarkText .= " Discrepancies found:\n" . implode("\n", $discrepancyDetails);
            }
            ProcurementRemark::create([
                'procurement_order_id' => $order->id,
                'user_id' => auth()->id(),
                'remark' => $remarkText,
                'type' => $hasDiscrepancy ? 'issue' : 'general',
                'is_internal' => false,
            ]);

            // Auto-notify procurement that verification is done
            if ($order->procurement_user_id) {
                ProcurementNotification::create([
                    'procurement_order_id' => $order->id,
                    'from_user_id' => auth()->id(),
                    'to_user_id' => $order->procurement_user_id,
                    'type' => 'status_update',
                    'title' => 'Order verified by manager',
                    'message' => "Order {$order->order_number} has been verified. You can now mark it as completed to update inventory.",
                ]);
            }

            DB::commit();

            $msg = '✅ Delivery verified! Status remains For Verification.';
            if ($hasDiscrepancy) {
                $msg .= ' May discrepancy — check remarks. Procurement can now adjust or mark as completed.';
            } else {
                $msg .= ' Procurement can now mark as Completed to update inventory.';
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to verify delivery: ' . $e->getMessage());
        }
    }

    /**
     * Add a remark to an order (Feature 5).
     */
    public function addRemark(Request $request, $id)
    {
        $order = ProcurementOrder::findOrFail($id);

        $validated = $request->validate([
            'remark' => 'required|string|max:2000',
            'type' => 'required|in:general,issue,shortage,damage',
            'is_internal' => 'boolean',
        ]);

        ProcurementRemark::create([
            'procurement_order_id' => $order->id,
            'user_id' => auth()->id(),
            'remark' => $validated['remark'],
            'type' => $validated['type'],
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        return redirect()->back()->with('success', 'Remark added!');
    }

    /**
     * Send a notification from Procurement to the Manager (Feature 7).
     */
    public function notifyManager(Request $request, $id)
    {
        $order = ProcurementOrder::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:info,urgent,status_update,reminder',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        // Find the manager assigned to this order's department
        $managerId = null;
        if ($order->department && $order->department->manager_id) {
            $managerId = $order->department->manager_id;
        }

        if (!$managerId) {
            return redirect()->back()->with('error', 'No manager assigned to this department.');
        }

        ProcurementNotification::create([
            'procurement_order_id' => $order->id,
            'from_user_id' => auth()->id(),
            'to_user_id' => $managerId,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'message' => $validated['message'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Manager notified! ✅');
    }

    /**
     * Mark a notification as read.
     */
    public function markNotificationRead($id)
    {
        $notification = ProcurementNotification::findOrFail($id);

        if ($notification->to_user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Analytics dashboard with charts and analysis.
     */
    public function analytics(Request $request)
    {
        $dateFrom = $request->date_from ?? today()->subDays(7)->toDateString();
        $dateTo = $request->date_to ?? today()->toDateString();

        $ordersQuery = ProcurementOrder::with('items', 'department', 'supplier', 'creator')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $orders = $ordersQuery->get();
        $totalOrders = $orders->count();
        $totalItems = $orders->sum(fn($o) => $o->items->sum('quantity_ordered'));
        $totalCost = $orders->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered));

        // Orders by status
        $statusCounts = $orders->groupBy('status')->map->count();

        // Orders by department
        $deptCosts = [];
        foreach ($orders->groupBy('department_id') as $deptId => $deptOrders) {
            $deptName = $deptOrders->first()->department?->name ?? 'Unknown';
            $deptCosts[] = [
                'name' => $deptName,
                'count' => $deptOrders->count(),
                'cost' => $deptOrders->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered)),
            ];
        }

        // Daily trends
        $dateRange = new \DatePeriod(
            \Carbon\Carbon::parse($dateFrom),
            \Carbon\CarbonInterval::day(),
            \Carbon\Carbon::parse($dateTo)->addDay()
        );
        $dailyTrend = [];
        foreach ($dateRange as $date) {
            $dayOrders = $orders->filter(fn($o) => \Carbon\Carbon::parse($o->created_at)->toDateString() === \Carbon\Carbon::parse($date)->toDateString());
            $dailyTrend[] = [
                'date' => $date->format('M d'),
                'count' => $dayOrders->count(),
                'cost' => $dayOrders->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered)),
            ];
        }

        // Top items by quantity
        $allItems = $orders->flatMap->items->groupBy('item_name')->map(fn($g) => [
            'name' => $g->first()->item_name,
            'qty' => $g->sum('quantity_ordered'),
            'cost' => $g->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered),
        ])->sortByDesc('qty')->take(10)->values();

        // Cost variance for completed orders
        $completedOrders = $orders->where('status', 'completed');
        $costVariance = [];
        foreach ($completedOrders as $co) {
            $orderedCost = $co->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered);
            $actualCost = $co->items->sum(fn($i) => ($i->unit_price ?? 0) * ($i->qty_verified ?? 0));
            $diff = $orderedCost - $actualCost;
            if ($diff != 0) {
                $costVariance[] = [
                    'order' => $co,
                    'ordered_cost' => $orderedCost,
                    'actual_cost' => $actualCost,
                    'difference' => $diff,
                ];
            }
        }

        // Supplier breakdown
        $supplierBreakdown = [];
        $isProcurement = auth()->user()->isProcurement() || auth()->user()->isAdmin();
        foreach ($orders->groupBy('supplier_id') as $supId => $supOrders) {
            if (!$supId) {
                $supOrders->count();
                continue;
            }
            $supplier = $supOrders->first()->supplier;
            $supplierBreakdown[] = [
                'name' => $isProcurement ? ($supplier?->name ?? 'Unknown') : 'Supplier',
                'count' => $supOrders->count(),
                'cost' => $supOrders->sum(fn($o) => $o->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered)),
            ];
        }

        // Status flow stats
        $statusFlow = [
            'draft' => $statusCounts->get('draft', 0),
            'for_approval' => $statusCounts->get('for_approval', 0),
            'for_procurement' => $statusCounts->get('for_procurement', 0),
            'ordered' => $statusCounts->get('ordered', 0),
            'ongoing' => $statusCounts->get('ongoing', 0),
            'preparing' => $statusCounts->get('preparing', 0),
            'for_delivery' => $statusCounts->get('for_delivery', 0),
            'for_verification' => $statusCounts->get('for_verification', 0),
            'completed' => $statusCounts->get('completed', 0),
            'cancelled' => $statusCounts->get('cancelled', 0),
            'partial' => $statusCounts->get('partial', 0),
            'delivered' => $statusCounts->get('delivered', 0),
        ];

        return view('procurement.orders.analytics', compact(
            'dateFrom', 'dateTo',
            'totalOrders', 'totalItems', 'totalCost',
            'statusCounts', 'deptCosts',
            'dailyTrend', 'allItems',
            'costVariance', 'supplierBreakdown',
            'statusFlow', 'orders',
            'isProcurement'
        ));
    }
}
