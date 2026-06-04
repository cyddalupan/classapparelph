<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrototypeSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('sales.prototype.index');
    }

    /**
     * Show cart-based order creation form.
     */
    public function cartCreate()
    {
        return view('sales.prototype.cart-create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = \DB::table('sales_departments')->where('is_active', true)->get();
        $marketplaceOptions = \App\Models\Customer::getMarketplaceOptions();
        $paymentAccounts = \App\Models\PaymentAccount::where('is_active', true)->get();
        return view('sales.prototype.create', compact('departments', 'marketplaceOptions', 'paymentAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
            public function store(Request $request)
    {
        // Validate customer data
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'marketplace' => 'nullable|string',
        ]);
        
        // Use existing customer_id if provided, otherwise find/create
        if ($request->customer_id) {
            $customer = \App\Models\Customer::find($request->customer_id);
            if (!$customer) {
                return back()->with('error', 'Customer not found. Please save customer first.');
            }
        } else {
            // Find or create customer
            $customer = \App\Models\Customer::firstOrCreate(
                ['phone' => $request->customer_phone],
                [
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'marketplace' => $request->marketplace,
                    'location' => $request->customer_address,
                    'company' => $request->customer_company,
                    'created_by' => auth()->id(),
                ]
            );
        }
        
        // If customer already exists, update their info if provided
        if ($customer->wasRecentlyCreated === false) {
            $updates = [];
            if ($request->customer_email && !$customer->email) {
                $updates['email'] = $request->customer_email;
            }
            if ($request->marketplace && !$customer->marketplace) {
                $updates['marketplace'] = $request->marketplace;
            }
            if ($request->customer_address && !$customer->location) {
                $updates['location'] = $request->customer_address;
            }
            if ($request->customer_company && !$customer->company) {
                $updates['company'] = $request->customer_company;
            }
            if (!empty($updates)) {
                $customer->update($updates);
            }
        }
        
        // Handle payment screenshot upload
        $paymentScreenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $file = $request->file('payment_screenshot');
            $filename = 'payment_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads/payments', $filename, 'public');
            $paymentScreenshotPath = '/storage/' . $filePath;
        }
        
        // Parse items and group by department
        $itemsJson = $request->items_json;
        $allItems = [];
        if ($itemsJson) {
            $allItems = json_decode($itemsJson, true) ?: [];
        }
        
        // Group items by department
        $deptGroups = [];
        foreach ($allItems as $item) {
            $dept = $item['department'] ?? 'iprint';
            if (!isset($deptGroups[$dept])) {
                $deptGroups[$dept] = [];
            }
            $deptGroups[$dept][] = $item;
        }
        
        // Ensure at least one department group
        if (empty($deptGroups)) {
            $deptGroups['iprint'] = [];
        }
        
        // Calculate overall totals
        $overallSubtotal = $request->subtotal ?: 0;
        $overallTax = $request->tax ?: 0;
        $overallTotal = $request->total_amount ?: 0;
        $overallDeposit = $request->deposit_paid ?: 0;
        
        // Generate base sales number
        $baseUid = strtoupper(uniqid());
        $isMultiDept = count($deptGroups) > 1;
        
        // Generate a group_id to link multi-department sales
        $group_id = $isMultiDept ? \Illuminate\Support\Str::uuid()->toString() : null;
        $firstSaleId = null;
        $saleIds = [];
        
        // Department code to id mapping
        $deptCache = [];
        $deptIndex = 0;
        $deptCount = count($deptGroups);
        $accumulatedDeposit = 0;
        $accumulatedTax = 0;
        
        foreach ($deptGroups as $deptCode => $items) {
            $deptIndex++;
            
            // Get department record
            if (!isset($deptCache[$deptCode])) {
                $deptCache[$deptCode] = \DB::table('sales_departments')->where('code', $deptCode)->first();
            }
            $department = $deptCache[$deptCode];
            if (!$department) {
                $department = \DB::table('sales_departments')->where('code', 'iprint')->first();
            }
            
            // Calculate this department's subtotal from its items
            $deptItemTotal = 0;
            foreach ($items as $item) {
                $itemBase = $item['totalPrice'] ?? $item['unitPrice'] ?? $item['price'] ?? 0;
                $printSub = $item['printing']['printSubtotal'] ?? 0;
                $deptItemTotal += $itemBase + $printSub;
            }
            
            // Calculate proportion of overall totals
            $isOnlyDept = !$isMultiDept;
            if ($isOnlyDept) {
                // Single department: use submitted totals directly
                $deptSubtotal = $overallSubtotal;
                $deptTax = $overallTax;
                $deptTotal = $overallTotal;
                $deptDeposit = $overallDeposit;
            } else {
                // Multiple departments: use actual item prices as ground truth
                // Calculate total of ALL items across all departments (including print costs)
                $totalItemSum = 0;
                foreach ($deptGroups as $dg) {
                    foreach ($dg as $dItem) {
                        $dItemBase = $dItem['totalPrice'] ?? $dItem['unitPrice'] ?? $dItem['price'] ?? 0;
                        $dPrintSub = $dItem['printing']['printSubtotal'] ?? 0;
                        $totalItemSum += $dItemBase + $dPrintSub;
                    }
                }
                $deptSubtotal = $deptItemTotal;
                $deptTotal = $deptItemTotal;
                // Proportionally split deposit and tax based on item share
                $proportion = $totalItemSum > 0 ? ($deptItemTotal / $totalItemSum) : (1 / $deptCount);
                if ($deptIndex == $deptCount) {
                    // Last department: take remainder to ensure exact match
                    $deptDeposit = round($overallDeposit - $accumulatedDeposit, 2);
                    $deptTax = round($overallTax - $accumulatedTax, 2);
                } else {
                    $deptDeposit = round($overallDeposit * $proportion, 2);
                    $deptTax = round($overallTax * $proportion, 2);
                }
                // Cap deposit to department total to prevent negative balance
                if ($deptDeposit > $deptTotal) {
                    $deptDeposit = $deptTotal;
                }
            }
            $accumulatedDeposit += $deptDeposit;
            $accumulatedTax += $deptTax;
            
            $balanceDue = $deptTotal - $deptDeposit;
            
            // Generate sales number: base for single, base-1/base-2 for multi
            if ($isOnlyDept) {
                $salesNumber = 'SALE-' . date('Ymd') . '-' . $baseUid;
            } else {
                $salesNumber = 'SALE-' . date('Ymd') . '-' . $baseUid . '-' . $deptIndex;
            }
            
            // Get the earliest date_needed from this department's items, fall back to form date
            $deptDateNeeded = $request->estimated_completion_date;
            foreach ($items as $item) {
                if (!empty($item['date_needed'])) {
                    if (empty($deptDateNeeded) || $item['date_needed'] < $deptDateNeeded) {
                        $deptDateNeeded = $item['date_needed'];
                    }
                }
            }

            // Build services JSON (only this department's items)
            $deptServicesJson = json_encode($items);
            
            // Store overall totals for multi-department sales
            // Use actual item sum (not form values) for subtotal/total to ensure math checks out
            $totalItemSumAll = $totalItemSum ?? $deptItemTotal;
            $deptOverallSubtotal = $isMultiDept ? $totalItemSumAll : null;
            $deptOverallTotal = $isMultiDept ? $totalItemSumAll : null;
            $deptOverallDeposit = $isMultiDept ? $overallDeposit : null;
            $deptOverallTax = $isMultiDept ? $overallTax : null;
            
            // Create sale record
            $saleId = \DB::table('prototype_sales')->insertGetId([
                'sales_number' => $salesNumber,
                'customer_id' => $customer->id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'sales_agent_id' => auth()->id(),
                'sales_agent_name' => auth()->user()->name,
                'department_id' => $department->id,
                'department_name' => $department->name,
                'services' => $deptServicesJson,
                'subtotal' => $deptSubtotal,
                'tax' => $deptTax,
                'total_amount' => $deptTotal,
                'deposit_paid' => $deptDeposit,
                'balance_due' => $balanceDue,
                'payment_method' => $request->payment_method ?: 'cash',
                'payment_owner' => $request->payment_owner ?: ($request->payment_account_id ? \App\Models\PaymentAccount::find($request->payment_account_id)?->name : 'company'),
                'payment_account_id' => $request->payment_account_id ?: null,
                'payment_date' => $request->payment_date ?: null,
                'reference_number' => $request->reference_number ?: null,
                'payment_status' => 'pending',
                'payment_screenshot_path' => $paymentScreenshotPath,
                'customer_notes' => $request->customer_notes,
                'internal_notes' => $request->internal_notes,
                'estimated_completion_date' => $deptDateNeeded,
                'kanban_status' => 'new',
                'status' => 'pending',
                'group_id' => $group_id,
                'overall_subtotal' => $deptOverallSubtotal,
                'overall_total_amount' => $deptOverallTotal,
                'overall_deposit_paid' => $deptOverallDeposit,
                'overall_tax' => $deptOverallTax,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            if ($firstSaleId === null) $firstSaleId = $saleId;
            $saleIds[] = $saleId;
            
            // Track sold items for this department
            try {
                $trackedItems = [];
                foreach ($items as $soldItem) {
                    $masterItemId = null;
                    $sku = null;
                    $itemName = $soldItem['name'] ?? 'Unknown';
                    
                    if (isset($soldItem['productId'])) {
                        $priceRecord = \DB::table('printing_prices')->find($soldItem['productId']);
                        if ($priceRecord && $priceRecord->master_item_id) {
                            $masterItemId = $priceRecord->master_item_id;
                            $masterItem = \DB::table('master_items')->find($masterItemId);
                            if ($masterItem) {
                                $sku = $masterItem->sku;
                            }
                        }
                    }
                    
                    if (!$masterItemId) {
                        $matched = \App\Models\MasterItem::where('name', 'LIKE', '%' . substr($itemName, 0, 30) . '%')
                            ->whereNull('deleted_at')
                            ->first();
                        if ($matched) {
                            $masterItemId = $matched->id;
                            $sku = $matched->sku;
                        }
                    }
                    
                    $trackedItems[] = [
                        'sale_id' => $saleId,
                        'master_item_id' => $masterItemId,
                        'item_name' => $itemName,
                        'sku' => $sku,
                        'quantity' => $soldItem['quantity'] ?? 1,
                        'unit_price' => $soldItem['unitPrice'] ?? $soldItem['totalPrice'] ?? 0,
                        'department_id' => $department->id,
                        'department_name' => $department->name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($trackedItems)) {
                    \DB::table('sale_tracked_items')->insert($trackedItems);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to track sale items: ' . $e->getMessage());
            }
            
            // Create KANBAN item
            $itemsCount = count($items);
            \DB::table('sales_kanban_items')->insert([
                'sale_id' => $saleId,
                'department_id' => $department->id,
                'title' => 'New Sale: ' . $request->customer_name,
                'description' => 'Services: ' . $itemsCount . ' items | Total: ₱' . number_format($deptTotal, 2),
                'status' => 'todo',
                'assigned_to' => null,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Save mockup image from sublimation form
            foreach ($items as $item) {
                if (isset($item['sublimationForm']['mockup']) && !empty($item['sublimationForm']['mockup'])) {
                    $mockupImages = [[
                        'name' => ($item['sublimationForm']['projectName'] ?? 'mockup') . '-mockup.png',
                        'url' => $item['sublimationForm']['mockup'],
                        'type' => 'sublimation'
                    ]];
                    \DB::table('prototype_sales')->where('id', $saleId)->update(['mockup_images' => json_encode($mockupImages)]);
                    break;
                }
            }
        }
        
        // Update customer LTV stats (once per transaction)
        $customer->total_orders += 1;
        $customer->total_spent += $overallSubtotal;
        $customer->notes = $request->customer_notes ?: $customer->notes;
        $customer->average_order_value = $customer->total_spent / $customer->total_orders;
        
        if (!$customer->first_order_date) {
            $customer->first_order_date = now();
        }
        $customer->last_order_date = now();
        
        $customer->updateTier();
        $customer->save();
        
        // Build success message
        $deptNames = [];
        foreach ($deptGroups as $dc => $di) {
            $deptNames[] = ucfirst($dc);
        }
        
        if ($isMultiDept) {
            $successMsg = count($deptGroups) . ' sales created (' . implode(', ', $deptNames) . ') — each added to their respective department Kanban board.';
        } else {
            $successMsg = 'Sale saved! It has been added to the Kanban board.';
        }

        return redirect()->route('sales.prototype.create')
            ->with('success', $successMsg);
    }
public function details(Request $request, string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        
        $services = json_decode($sale->services, true) ?: [];
        
        // Build HTML for modal
        $html = '';
        
        // --- Customer Info ---
        $html .= '<div class="sale-detail-section">';
        $html .= '<h6><i class="fas fa-user me-2"></i>Customer Information</h6>';
        $html .= '<div class="row g-2 small">';
        $html .= '<div class="col-6"><span class="text-muted">Name:</span> <strong>' . e($sale->customer_name) . '</strong></div>';
        $html .= '<div class="col-6"><span class="text-muted">Sales #:</span> <strong>' . e($sale->sales_number) . '</strong></div>';
        if ($sale->customer_phone) $html .= '<div class="col-6"><span class="text-muted">Phone:</span> ' . e($sale->customer_phone) . '</div>';
        $html .= '<div class="col-6"><span class="text-muted">Agent:</span> ' . e($sale->sales_agent_name ?? 'N/A') . '</div>';
        $html .= '<div class="col-6"><span class="text-muted">Dept:</span> ' . e($sale->department_name ?? 'N/A') . '</div>';
        $html .= '</div></div>';
        
        // --- Notes ---
        if ($sale->customer_notes || $sale->internal_notes) {
            $html .= '<div class="sale-detail-section">';
            $html .= '<h6><i class="fas fa-sticky-note me-2"></i>Notes</h6>';
            if ($sale->customer_notes) {
                $html .= '<div class="mb-1"><span class="text-muted small">Customer Notes:</span><br>' . nl2br(e($sale->customer_notes)) . '</div>';
            }
            if ($sale->internal_notes) {
                $html .= '<div><span class="text-muted small">Internal Notes:</span><br>' . nl2br(e($sale->internal_notes)) . '</div>';
            }
            $html .= '</div>';
        }
        
        // --- Items (from services JSON) ---
        if (!empty($services)) {
            $html .= '<div class="sale-detail-section">';
            $html .= '<h6><i class="fas fa-box me-2"></i>Order Items (' . count($services) . ')</h6>';
            
            foreach ($services as $idx => $item) {
                $itemTotal = $item['totalPrice'] ?? $item['total_price'] ?? $item['price'] ?? 0;
                $itemName = $item['name'] ?? $item['product_name'] ?? 'Item #' . ($idx + 1);
                $itemNotes = $item['notes'] ?? '';
                $subItems = $item['subItems'] ?? [];
                $printing = $item['printing'] ?? null;
                $refImages = $item['referenceImages'] ?? [];
                
                $html .= '<div class="item-card">';
                $html .= '<div class="d-flex justify-content-between align-items-start mb-2">';
                $html .= '<div><strong>' . e($itemName) . '</strong>';
                if ($item['department'] ?? null) {
                    $html .= ' <span class="badge bg-secondary">' . e($item['department']) . '</span>';
                }
                $html .= '</div>';
                $html .= '<div class="fw-bold text-nowrap">₱' . number_format($itemTotal, 2) . '</div>';
                $html .= '</div>';
                
                // Sub-items: brand, size, color, qty
                if (!empty($subItems)) {
                    $html .= '<div class="mb-2">';
                    foreach ($subItems as $si) {
                        $brand = $si['brand'] ?? $si['product_brand'] ?? '';
                        $size = $si['size'] ?? $si['type'] ?? $si['product_size'] ?? '';
                        $color = $si['color'] ?? $si['product_color'] ?? '';
                        $qty = $si['qty'] ?? $si['quantity'] ?? 1;
                        $unitPrice = $si['price'] ?? $si['unit_price'] ?? 0;
                        
                        $html .= '<span class="subitem-row">';
                        $parts = [];
                        if ($brand) $parts[] = e($brand);
                        if ($size) $parts[] = e($size);
                        if ($color) $parts[] = e($color);
                        $parts[] = '×' . $qty;
                        if ($unitPrice > 0) $parts[] = '₱' . number_format($unitPrice, 2);
                        $html .= implode(' • ', $parts);
                        $html .= '</span>';
                    }
                    $html .= '</div>';
                }
                
                // Print details
                if ($printing) {
                    $html .= '<div class="print-detail">';
                    $html .= '<div class="fw-semibold small mb-1">🖨️ Print Details</div>';
                    if ($printing['printType'] ?? null) {
                        $html .= '<div><span class="text-muted">Type:</span> ' . e($printing['printType']) . '</div>';
                    }
                    if (!empty($printing['printSizes'] ?? [])) {
                        $sizes = is_array($printing['printSizes']) ? implode(', ', $printing['printSizes']) : $printing['printSizes'];
                        $html .= '<div><span class="text-muted">Sizes:</span> ' . e($sizes) . '</div>';
                    }
                    $html .= '<div><span class="text-muted">Qty:</span> ' . ($printing['printQty'] ?? 'N/A') . '</div>';
                    if (($printing['printSubtotal'] ?? 0) > 0) {
                        $html .= '<div><span class="text-muted">Print Subtotal:</span> ₱' . number_format($printing['printSubtotal'], 2) . '</div>';
                    }
                    if ($printing['isSpecialPrice'] ?? false) {
                        $html .= '<div class="text-warning">⭐ Special Price: ' . e($printing['specialReason'] ?? '') . '</div>';
                    }
                    $html .= '</div>';
                }
                
                // Item notes
                if ($itemNotes) {
                    $html .= '<div class="mt-2 small"><span class="text-muted">📝 Notes:</span> ' . nl2br(e($itemNotes)) . '</div>';
                }
                
                // Reference images
                if (!empty($refImages)) {
                    $html .= '<div class="mt-2">';
                    $html .= '<div class="small text-muted mb-1">🖼️ Reference Images (' . count($refImages) . ')</div>';
                    $html .= '<div class="d-flex flex-wrap">';
                    foreach ($refImages as $rimg) {
                        $src = $rimg['dataUrl'] ?? $rimg['url'] ?? $rimg['src'] ?? '';
                        if ($src) {
                            $html .= '<img src="' . e($src) . '" class="ref-image" style="cursor:pointer;" alt="' . e($rimg['name'] ?? 'Image') . '">';
                        }
                    }
                    $html .= '</div></div>';
                }
                
                $html .= '</div>'; // item-card
            }
            
            $html .= '</div>'; // section
        }
        
        // --- Payment Info ---
        $html .= '<div class="sale-detail-section">';
        $html .= '<h6><i class="fas fa-credit-card me-2"></i>Payment & Totals</h6>';
        $html .= '<div class="total-row"><span>Total Amount</span><span>₱' . number_format($sale->total_amount ?? 0, 2) . '</span></div>';
        if (($sale->deposit_paid ?? 0) > 0) {
            $html .= '<div class="subtotal-row text-success"><span>Deposit Paid</span><span>-₱' . number_format($sale->deposit_paid, 2) . '</span></div>';
        }
        $balance = ($sale->total_amount ?? 0) - ($sale->deposit_paid ?? 0);
        if ($balance > 0) {
            $html .= '<div class="subtotal-row text-danger fw-bold"><span>Balance Due</span><span>₱' . number_format($balance, 2) . '</span></div>';
        }

        $html .= '<div class="mt-2 small">';
        $html .= '<span class="text-muted">Payment Method:</span> ' . e(ucfirst($sale->payment_method ?? 'N/A')) . ' &nbsp;|&nbsp; ';
        $html .= '<span class="text-muted">Paid by:</span> ' . e(ucfirst($sale->payment_owner ?? 'N/A')) . '<br>';
        if ($sale->payment_account_id) {
            $account = \App\Models\PaymentAccount::find($sale->payment_account_id);
            if ($account) {
                $html .= '<span class="text-muted">Account:</span> <strong>' . e($account->name) . '</strong>';
                if ($account->user) {
                    $html .= ' <span class="text-muted">(' . e($account->user->name) . ')</span>';
                }
                $html .= '<br>';
            }
        }
        $html .= '<span class="text-muted">Payment Status:</span> ';
        if ($sale->payment_status === 'verified') {
            $html .= '<span class="badge bg-success">✅ Verified</span>';
            if ($sale->verified_at) $html .= ' <small class="text-muted">' . \Carbon\Carbon::parse($sale->verified_at)->format('M d, g:i A') . '</small>';
        } elseif ($sale->payment_status === 'rejected') {
            $html .= '<span class="badge bg-danger">❌ Rejected</span>';
        } elseif ($sale->payment_status === 'pending' && $sale->payment_account_id) {
            $html .= '<span class="badge bg-warning text-dark">⏳ Pending</span>';
        } else {
            $html .= '<span class="badge bg-secondary">—</span>';
        }
        if ($sale->reference_number) {
            $html .= '<br><span class="text-muted">Reference:</span> ' . e($sale->reference_number);
        }
        if ($sale->payment_date) {
            $html .= ' &nbsp;|&nbsp; <span class="text-muted">Date:</span> ' . \Carbon\Carbon::parse($sale->payment_date)->format('M d, Y');
        }
        if ($sale->verify_requested_at) {
            $html .= '<br><span class="badge bg-info"><i class="fas fa-exclamation-circle"></i> Verify Requested</span>';
            $html .= ' <small class="text-muted">' . \Carbon\Carbon::parse($sale->verify_requested_at)->format('M d, g:i A') . '</small>';
        }
        $html .= '</div>';
        // Audit log link
        $auditCount = \App\Models\PaymentAuditLog::where('prototype_sale_id', $id)->count();
        if ($auditCount > 0) {
            $html .= '<div class="mt-1 small">';
            $html .= '<button class="btn btn-sm btn-outline-info" onclick="showAuditLogs(' . $id . ')"><i class="fas fa-history"></i> View Audit Log (' . $auditCount . ')</button>';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        // --- Payment Screenshot ---
        if ($sale->payment_screenshot_path) {
            $html .= '<div class="sale-detail-section">';
            $html .= '<h6><i class="fas fa-camera me-2"></i>Payment Screenshot</h6>';
            $html .= '<img src="' . e($sale->payment_screenshot_path) . '" class="payment-img" style="cursor:pointer;">';
            $html .= '<div class="small text-muted mt-1">Click to enlarge</div>';
            $html .= '</div>';
        }
        
        return response()->json([
            'html' => $html,
            'title' => 'Sale: ' . $sale->customer_name . ' (#' . $sale->sales_number . ')',
            'can_addon' => !in_array($sale->kanban_status, ['delivered', 'completed'])
        ]);
    }

                public function show(string $id)
    {
        $sale = \DB::table('prototype_sales')->leftJoin('sales_departments', 'prototype_sales.department_id', '=', 'sales_departments.id')
            ->select('prototype_sales.*', 'sales_departments.name as department_name', 'sales_departments.code as department_code')
            ->where('prototype_sales.id', $id)
            ->first();
        if (!$sale) {
            abort(404);
        }
        
        $services = json_decode($sale->services, true);
        $kanbanItem = \DB::table('sales_kanban_items')->where('sale_id', $id)->first();
        
        // Check if this sale is part of a group (multi-department transaction)
        $relatedSales = collect();
        $overallGroupTotal = null;
        $overallGroupSubtotal = null;
        $overallGroupDeposit = null;
        $overallGroupBalance = null;
        
        if ($sale->group_id) {
            $relatedSales = \DB::table('prototype_sales')
                ->leftJoin('sales_departments', 'prototype_sales.department_id', '=', 'sales_departments.id')
                ->select('prototype_sales.*', 'sales_departments.name as department_name', 'sales_departments.code as department_code')
                ->where('prototype_sales.group_id', $sale->group_id)
                ->where('prototype_sales.id', '!=', $id)
                ->get();
            
            // Calculate overall group totals from sale's stored values
            $overallGroupSubtotal = $sale->overall_subtotal;
            $overallGroupTotal = $sale->overall_total_amount;
            $overallGroupDeposit = $sale->overall_deposit_paid;
            
            // Fallback: calculate from group if stored values are null
            if (is_null($overallGroupTotal)) {
                $allInGroup = \DB::table('prototype_sales')
                    ->where('group_id', $sale->group_id)
                    ->get();
                $overallGroupSubtotal = $allInGroup->sum('subtotal');
                $overallGroupTotal = $allInGroup->sum('total_amount');
                $overallGroupDeposit = $allInGroup->sum('deposit_paid');
            }
            
            $overallGroupBalance = $overallGroupTotal - $overallGroupDeposit;
        }
        
        return view('sales.prototype.show', compact(
            'sale', 'services', 'kanbanItem', 'relatedSales',
            'overallGroupSubtotal', 'overallGroupTotal', 'overallGroupDeposit', 'overallGroupBalance'
        ));
    }
public function printSlip(string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }
        
        $services = json_decode($sale->services, true);
        if (!is_array($services)) {
            $services = [];
        }
        
        return view('sales.prototype.print-slip', compact('sale', 'services'));
    }

    /**
     * Get or auto-generate production checklist for a sale.
     */
    public function getProductionChecklist(string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        $services = json_decode($sale->services, true) ?: [];
        $sublimationForm = null;
        foreach ($services as $item) {
            if (isset($item['sublimationForm'])) {
                $sublimationForm = $item['sublimationForm'];
                break;
            }
        }

        // Build spec parts map (same as print-slip.blade.php)
        $specPartsMap = [
            'neckRibbingColor' => 'Neck Ribbing', 'neckTape' => 'Neck Tape', 'cuffs' => 'Cuffs',
            'slit' => 'Slit', 'pocket' => 'Pocket', 'collar' => 'Collar', 'neckShape' => 'Neck Shape',
            'cutType' => 'Cut Type', 'inner' => 'Inner', 'buttonColor' => 'Button',
            'zipperColor' => 'Zipper', 'innerStr' => 'Inner String', 'jersey' => 'Jersey',
            'defaultDesign' => 'Design', 'armsleeve' => 'Arm Sleeve', 'shoulder' => 'Shoulder',
            'sizeLabel' => 'Size Label'
        ];

        $main = $sublimationForm;
        $specs = $main['specifications'] ?? [];
        $partRows = [];
        $garmentName = $main['garment']['name'] ?? '';
        $partsAdded = $main['parts'] ?? [];

        if ($garmentName) {
            $partRows[] = ['part' => 'Garment', 'detail' => $garmentName];
        }
        foreach ($specPartsMap as $key => $label) {
            $val = $specs[$key] ?? '';
            if ($val) {
                $partRows[] = ['part' => $label, 'detail' => $val];
            }
        }
        if (!empty($partsAdded)) {
            $partDetails = implode(', ', array_map(function($p) { return $p['name'] ?? ''; }, $partsAdded));
            if ($partDetails) {
                $partRows[] = ['part' => 'Parts Added', 'detail' => $partDetails];
            }
        }

        // Roster data
        $allRosters = [];
        foreach ($services as $item) {
            if (isset($item['sublimationForm']['roster'])) {
                foreach ($item['sublimationForm']['roster'] as $r) {
                    $allRosters[] = $r;
                }
            }
        }

        // Sizes (from sublimation or fallback)
        $sizes = $main['sizes'] ?? [];

        // Total QTY
        $totalQty = 0;
        if ($main) {
            foreach ($main['sizes'] ?? [] as $s) {
                $totalQty += intval($s['quantity'] ?? 0);
            }
            foreach ($main['roster'] ?? [] as $r) {
                $totalQty += intval($r['number'] ?? 1);
            }
        }

        // Customer info
        $customerName = $sale->customer_name ?? '';
        $salesNumber = $sale->sales_number ?? '';
        $salesAgent = $sale->sales_agent_name ?? '';
        $notes = $services[0]['notes'] ?? '';

        // Build checklist items for status tracking
        $checklist = \App\Models\ProductionChecklist::where('sale_id', $id)->first();

        if (!$checklist) {
            $items = [];
            // Part items
            foreach ($partRows as $pi) {
                $items[] = [
                    'type' => 'part',
                    'label' => $pi['part'] . ': ' . $pi['detail'],
                    'value' => '',
                    'status' => 'pending',
                ];
            }
            // Roster items
            foreach ($allRosters as $r) {
                $items[] = [
                    'type' => 'roster',
                    'label' => $r['name'] ?? 'Unknown',
                    'value' => ($r['size'] ?? '') . ' ×' . ($r['number'] ?? 1),
                    'status' => 'pending',
                ];
            }
            // Size items
            foreach ($sizes as $s) {
                $items[] = [
                    'type' => 'size',
                    'label' => ($s['size'] ?? 'Size') . ' ×' . ($s['quantity'] ?? 0),
                    'value' => '',
                    'status' => 'pending',
                ];
            }

            $checklist = \App\Models\ProductionChecklist::create([
                'sale_id' => $id,
                'items' => $items,
            ]);
        }
        // Build mockupImages with fallback
        $mockupImages_final = [];
        $mockupsRaw_svc = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
        if (!empty($mockupsRaw_svc)) {
            $mockupImages_final = $mockupsRaw_svc;
        } else {
            foreach ((array)$services as $svcItem) {
                if (!empty($svcItem['sublimationForm']['mockup'])) {
                    $mockupImages_final = [[
                        'name' => ($svcItem['sublimationForm']['projectName'] ?? 'mockup') . '-mockup.png',
                        'url' => $svcItem['sublimationForm']['mockup'],
                        'type' => 'sublimation'
                    ]];
                    break;
                }
            }
        }

        return response()->json([
            'checklist' => [
                'sale_id' => $checklist->sale_id,
                'id' => $checklist->id,
                'items' => $checklist->items ?? [],
                'ga_done' => $checklist->ga_done,
                'ga_done_at' => $checklist->ga_done_at ? $checklist->ga_done_at->toISOString() : null,
                'ga_notes' => $checklist->ga_notes,
                'qa1_done' => $checklist->qa1_done,
                'qa1_done_at' => $checklist->qa1_done_at ? $checklist->qa1_done_at->toISOString() : null,
                'qa1_notes' => $checklist->qa1_notes,
                'press_done' => $checklist->press_done,
                'press_done_at' => $checklist->press_done_at ? $checklist->press_done_at->toISOString() : null,
                'qa2_done' => $checklist->qa2_done,
                'qa2_done_at' => $checklist->qa2_done_at ? $checklist->qa2_done_at->toISOString() : null,
                'qa2_notes' => $checklist->qa2_notes,
            ],
            'slip' => [
                'projectName' => $main['projectName'] ?? '',
                'description' => $main['description'] ?? '',
                'fabric' => $main['fabric']['name'] ?? '',
                'designer' => $main['designer'] ?? '',
                'totalQty' => $totalQty,
                'dateNeeded' => $main['dateNeeded'] ?? '',
                'salesNumber' => $salesNumber,
                'agent' => $salesAgent,
                'customer' => $customerName,
                'partRows' => $partRows,
                'allRosters' => $allRosters,
                'sizes' => $sizes,
                'notes' => $notes,
                'hasRoster' => !empty($allRosters),
                'mockupImages' => $mockupImages_final,
            ],
        ]);
    }

    /**
     * Save production checklist status updates.
     */
    public function saveProductionChecklist(string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        $checklist = \App\Models\ProductionChecklist::where('sale_id', $id)->first();
        if (!$checklist) {
            return response()->json(['error' => 'Checklist not found. Create it first.'], 404);
        }

        $input = request()->all();

        // Update individual item statuses
        if (isset($input['items'])) {
            $incomingItems = $input['items'];
            // Check if this is partial update (array of {index, status}) or full replacement
            if (is_array($incomingItems) && isset($incomingItems[0]['index'])) {
                // Partial update: apply status changes by index
                $currentItems = $checklist->items ?? [];
                foreach ($incomingItems as $update) {
                    $idx = $update['index'] ?? -1;
                    if ($idx >= 0 && $idx < count($currentItems)) {
                        if (isset($update['status'])) {
                            $currentItems[$idx]['status'] = $update['status'];
                        }
                        if (isset($update['ga_done'])) {
                            $currentItems[$idx]['ga_done'] = filter_var($update['ga_done'], FILTER_VALIDATE_BOOLEAN);
                        }
                        if (isset($update['qa1_done'])) {
                            $currentItems[$idx]['qa1_done'] = filter_var($update['qa1_done'], FILTER_VALIDATE_BOOLEAN);
                        }
                        if (isset($update['qa2_done'])) {
                            $currentItems[$idx]['qa2_done'] = filter_var($update['qa2_done'], FILTER_VALIDATE_BOOLEAN);
                        }
                    }
                }
                $checklist->items = $currentItems;
            } else {
                // Full replacement
                $checklist->items = $incomingItems;
            }
        }

        // Update stage flags
        if (isset($input['ga_done'])) {
            $checklist->ga_done = filter_var($input['ga_done'], FILTER_VALIDATE_BOOLEAN);
            if ($checklist->ga_done && !$checklist->ga_done_at) {
                $checklist->ga_done_at = now();
            } elseif (!$checklist->ga_done) {
                $checklist->ga_done_at = null;
            }
        }
        if (array_key_exists('ga_notes', $input)) {
            $checklist->ga_notes = $input['ga_notes'];
        }

        if (isset($input['qa1_done'])) {
            $checklist->qa1_done = filter_var($input['qa1_done'], FILTER_VALIDATE_BOOLEAN);
            if ($checklist->qa1_done && !$checklist->qa1_done_at) {
                $checklist->qa1_done_at = now();
            } elseif (!$checklist->qa1_done) {
                $checklist->qa1_done_at = null;
            }
        }
        if (array_key_exists('qa1_notes', $input)) {
            $checklist->qa1_notes = $input['qa1_notes'];
        }

        if (isset($input['press_done'])) {
            $checklist->press_done = filter_var($input['press_done'], FILTER_VALIDATE_BOOLEAN);
            if ($checklist->press_done && !$checklist->press_done_at) {
                $checklist->press_done_at = now();
            } elseif (!$checklist->press_done) {
                $checklist->press_done_at = null;
            }
        }

        if (isset($input['qa2_done'])) {
            $checklist->qa2_done = filter_var($input['qa2_done'], FILTER_VALIDATE_BOOLEAN);
            if ($checklist->qa2_done && !$checklist->qa2_done_at) {
                $checklist->qa2_done_at = now();
            } elseif (!$checklist->qa2_done) {
                $checklist->qa2_done_at = null;
            }
        }
        if (array_key_exists('qa2_notes', $input)) {
            $checklist->qa2_notes = $input['qa2_notes'];
        }

        $checklist->save();

        return response()->json([
            'success' => true,
            'message' => 'Checklist saved',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }
        
        // Handle double-encoded JSON (some records store services as a string inside a JSON string)
        $raw = $sale->services;
        $services = json_decode($raw, true);
        if (is_string($services)) {
            $services = json_decode($services, true);
        }
        if (!is_array($services)) {
            $services = [['name' => $raw, 'qty' => 1, 'price' => 0]];
        }
        
        // Normalize: convert flat string arrays to associative format
        $normalized = [];
        foreach ($services as $i => $item) {
            if (is_string($item)) {
                $normalized[] = ['name' => $item, 'qty' => 1, 'price' => 0];
            } else {
                $normalized[] = $item;
            }
        }
        $services = $normalized;
        
        $deptColors = [
            1 => '#0d6efd',
            2 => '#198754',
            3 => '#dc3545',
            4 => '#6f42c1',
            5 => '#fd7e14',
            6 => '#6c757d',
        ];
        $deptLabels = [
            1 => 'iPrint',
            2 => 'Consol',
            3 => 'Cinco',
            4 => 'Class',
            5 => 'MTO',
            6 => 'Other',
        ];
        
        return view('sales.prototype.edit', compact('sale', 'services', 'deptColors', 'deptLabels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }
        
        $request->validate([
            'department_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        
        $items = $request->items;
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['qty'] ?? 1) * ($item['price'] ?? 0);
        }
        
        $totalAmount = $subtotal;
        
        \DB::table('prototype_sales')->where('id', $id)->update([
            'services' => json_encode($items),
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'balance_due' => $totalAmount - ($sale->deposit_paid ?? 0),
            'updated_at' => now(),
        ]);
        
        // Save mockup image from sublimation form
        $items = json_decode($request->items_json, true) ?: [];
        foreach ($items as $item) {
            if (isset($item['sublimationForm']['mockup']) && !empty($item['sublimationForm']['mockup'])) {
                $mockupImages = [[
                    'name' => ($item['sublimationForm']['projectName'] ?? 'mockup') . '-mockup.png',
                    'url' => $item['sublimationForm']['mockup'],
                    'type' => 'sublimation'
                ]];
                \DB::table('prototype_sales')->where('id', $id)->update(['mockup_images' => json_encode($mockupImages)]);
                break;
            }
        }
        
        return redirect()->route('sales.prototype.edit', $id)
            ->with('success', 'Order updated successfully! New total: ₱' . number_format($totalAmount, 2));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display KANBAN board for sales.
     */
    public function kanban($department = null)
    {
        // Department codes from dropdown
        $deptCodeMap = [
            'iprint' => 1,
            'consol' => 2,
            'cinco'  => 3,
            'class'  => 4,
            'mto'    => 5,
            'other'  => 6,
        ];
        $allowedDepts = array_keys($deptCodeMap);
        $activeDept = $department;
        
        // Default: show ALL departments (no filter) — for admin view
        $showAll = false;
        
        if (!$activeDept || !in_array($activeDept, $allowedDepts)) {
            $showAll = true;
            $activeDept = 'all';
        }
        
        // Kanban columns matching the database ENUM
        $kanbanOrder = ['new', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $kanbanLabels = [
            'new'                => 'New',
            'design'            => 'Design',
            'production'        => 'Production',
            'quality_check'      => 'Quality Check',
            'ready_for_delivery' => 'Ready for Delivery',
            'delivered'         => 'Delivered',
            'completed'         => 'Completed',
        ];
        $departmentLabels = [
            1 => 'iPrint',
            2 => 'Consol',
            3 => 'Cinco',
            4 => 'Class',
            5 => 'MTO',
            6 => 'Other',
        ];
        $departmentColors = [
            1 => '#0d6efd',
            2 => '#198754',
            3 => '#dc3545',
            4 => '#6f42c1',
            5 => '#fd7e14',
            6 => '#6c757d',
        ];
        
        // Get sales — filter by department if specific, or get ALL
        $query = \App\Models\PrototypeSale::with([])
            ->whereIn('status', ['confirmed', 'in_production', 'pending', 'completed']);
        
        if (!$showAll) {
            $deptId = $deptCodeMap[$activeDept];
            $query->where('department_id', $deptId);
        }
        
        // Non-admin users only see their own sales
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $query->where('sales_agent_id', $user->id);
        }
        
        $sales = $query->orderBy('created_at', 'desc')->paginate(100);
        
        // Initialize columns with proper order
        $columns = [];
        foreach ($kanbanOrder as $k) {
            $columns[$k] = [];
        }
        
        foreach ($sales as $sale) {
            $status = $sale->kanban_status ?: 'new';
            if (isset($columns[$status])) {
                $columns[$status][] = $sale;
            }
        }
        
        return view('sales.prototype.kanban', compact(
            'columns', 'activeDept', 'allowedDepts', 'kanbanLabels', 'kanbanOrder',
            'showAll', 'departmentLabels', 'departmentColors'
        ));
    }

    /**
     * Display Manager List page with pipeline progress bar.
     */
    public function list()
    {
        $deptCodeMap = [
            "iprint" => 1,
            "consol" => 2,
            "cinco"  => 3,
            "class"  => 4,
            "mto"    => 5,
            "other"  => 6,
        ];
        $kanbanStatuses = ["new", "design", "production", "quality_check", "ready_for_delivery", "delivered", "completed"];
        $kanbanLabels = [
            "new"                => "New",
            "design"            => "Design",
            "production"        => "Production",
            "quality_check"      => "QC",
            "ready_for_delivery" => "Ready",
            "delivered"         => "Delivered",
            "completed"         => "Completed",
        ];
        $departmentLabels = [
            1 => "iPrint",
            2 => "Consol",
            3 => "Cinco",
            4 => "Class",
            5 => "MTO",
            6 => "Other",
        ];
        $departmentColors = [
            1 => "#0d6efd",
            2 => "#198754",
            3 => "#dc3545",
            4 => "#6f42c1",
            5 => "#fd7e14",
            6 => "#6c757d",
        ];

        $query = \App\Models\PrototypeSale::whereIn("status", ["confirmed", "in_production", "pending", "completed"]);
        
        // Non-admin users only see their own sales
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $query->where('sales_agent_id', $user->id);
        }
        
        $sales = $query->orderBy("created_at", "desc")
            ->paginate(50);
        
        // Determine if current user is an agent-type user
        $isAgent = !$user->isAdmin() && ($user->isSalesAgent() || $user->isSalesRepresentative());
        
        return view("sales.prototype.list", compact(
            "sales", "kanbanStatuses", "kanbanLabels",
            "departmentLabels", "departmentColors", "isAgent"
        ));
    }

    public function updateKanbanStatus(Request $request, $id)
    {
        $request->validate([
            'kanban_status' => 'required|in:new,design,production,quality_check,ready_for_delivery,delivered,completed'
        ]);
        
        $sale = \App\Models\PrototypeSale::findOrFail($id);
        $sale->kanban_status = $request->kanban_status;
        $sale->save();
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Status updated!');
    }

    /**
     * Update sale status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'kanban_status' => 'required|string'
        ]);

        $sale = \App\Models\PrototypeSale::findOrFail($id);
        $sale->kanban_status = $request->kanban_status;
        $sale->save();

        return response()->json(['success' => true, 'status' => $sale->kanban_status]);
    }

    /**
     * Verify payment for a sale.
     */
    public function calendar()
    {
        $departments = \DB::table('sales_departments')->where('is_active', true)->get();
        return view('sales.prototype.calendar', compact('departments'));
    }

    public function calendarData(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $department = $request->department;

        $query = \App\Models\PrototypeSale::whereIn('status', ['pending', 'confirmed', 'in_production', 'completed']);
        
        // Filter by date range (use created_at, estimated_completion_date, or date_needed)
        $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
              ->orWhereBetween('estimated_completion_date', [$startDate, $endDate]);
        });
        
        // Filter by department
        if ($department && $department !== 'all') {
            $query->where('department_name', $department);
        }
        
        $projects = $query->orderBy('created_at', 'desc')->get();
        
        // Also get all sales with date_needed if they have it stored differently
        // We return all data needed by the frontend
        $projectsFormatted = $projects->map(function($p) {
            $items = [];
            $raw = $p->services;
            $services = [];
            if ($raw) {
                // Handle cases: string JSON, already decoded array, or just a string
                if (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $services = $decoded;
                    } elseif ($raw !== '[]' && $raw !== '') {
                        $services = [$raw];
                    }
                } elseif (is_array($raw)) {
                    $services = $raw;
                }
                // Parse items into structured format
                foreach ($services as $s) {
                    if (is_string($s)) {
                        $items[] = ['name' => $s, 'qty' => 1];
                    } elseif (is_array($s)) {
                        $items[] = $s;
                    }
                }
            }
            return [
                'id' => $p->id,
                'sales_number' => $p->sales_number,
                'customer_name' => $p->customer_name,
                'customer_phone' => $p->customer_phone,
                'department_id' => $p->department_id,
                'department_name' => $p->department_name,
                'total_amount' => $p->total_amount,
                'subtotal' => $p->subtotal,
                'deposit_paid' => $p->deposit_paid,
                'balance_due' => $p->balance_due,
                'kanban_status' => $p->kanban_status,
                'services' => $items,
                'services_raw' => $services,
                'date_needed' => $p->estimated_completion_date,
                'estimated_completion_date' => $p->estimated_completion_date,
                'created_at' => $p->created_at,
                'status' => $p->status,
            ];
        });
        
        return response()->json(['projects' => $projectsFormatted]);
    }

    public function verifyPayment(Request $request, $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        $action = $request->action; // 'verify', 'reject', 're_tag', 'edit_ref'
        $remark = $request->remark;
        $oldAccountId = $sale->payment_account_id;
        $oldRef = $sale->reference_number;
        $oldDate = $sale->payment_date;

        if ($action === 'verify') {
            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $sale->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'verified',
                'remarks' => $remark,
            ]);

            $msg = 'Payment verified successfully!';

        } elseif ($action === 'reject') {
            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $sale->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'rejected',
                'remarks' => $remark,
            ]);

            $msg = 'Payment rejected.';

        } elseif ($action === 're_tag') {
            $newAccountId = $request->new_account_id;
            if (!$newAccountId) {
                return response()->json(['error' => 'Please select a new account'], 400);
            }

            $oldAccount = \App\Models\PaymentAccount::find($oldAccountId);
            $newAccount = \App\Models\PaymentAccount::find($newAccountId);

            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_account_id' => $newAccountId,
                'payment_owner' => $newAccount?->name ?? 're-tagged',
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $newAccountId,
                'user_id' => auth()->id(),
                'action' => 're_tagged',
                'old_value' => $oldAccount?->name,
                'new_value' => $newAccount?->name,
                'remarks' => $remark,
            ]);

            $msg = 'Payment re-tagged from ' . ($oldAccount?->name ?? 'Unknown') . ' to ' . ($newAccount?->name ?? 'Unknown') . '.';

        } elseif ($action === 'edit_ref') {
            $newRef = $request->new_reference_number;
            $newDate = $request->new_payment_date;

            $changes = [];
            if ($newRef && $newRef !== $oldRef) {
                $changes['reference_number'] = $newRef;
            }
            if ($newDate && $newDate !== $oldDate) {
                $changes['payment_date'] = $newDate;
            }

            if (!empty($changes)) {
                $changes['updated_at'] = now();
                \DB::table('prototype_sales')->where('id', $id)->update($changes);

                \App\Models\PaymentAuditLog::create([
                    'prototype_sale_id' => $id,
                    'payment_account_id' => $sale->payment_account_id,
                    'user_id' => auth()->id(),
                    'action' => 'edited_ref',
                    'old_value' => $oldRef ?: $oldDate,
                    'new_value' => $newRef ?: $newDate,
                    'remarks' => $remark,
                ]);
            }

            $msg = 'Payment details updated.';

        } elseif ($action === 'request_verify') {
            \DB::table('prototype_sales')->where('id', $id)->update([
                'verify_requested_at' => now(),
                'verify_requested_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $sale->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'requested_verify',
                'remarks' => $remark ?: 'Manager requested verification',
            ]);

            $msg = 'Verification request sent!';

        } else {
            return response()->json(['error' => 'Invalid action'], 400);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'sale_id' => $id,
                'payment_status' => \DB::table('prototype_sales')->where('id', $id)->value('payment_status'),
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Payment verification dashboard - shows all pending and recent payments.
     */
    public function paymentVerification()
    {
        $pendingPayments = \DB::table('prototype_sales')
            ->leftJoin('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as verifier', 'prototype_sales.verified_by', '=', 'verifier.id')
            ->leftJoin('users as requester', 'prototype_sales.verify_requested_by', '=', 'requester.id')
            ->select([
                'prototype_sales.*',
                'payment_accounts.name as account_name',
                'payment_accounts.user_id as account_user_id',
                'verifier.name as verified_by_name',
                'requester.name as requested_by_name',
            ])
            ->whereIn('prototype_sales.payment_status', ['pending', 'rejected'])
            ->orderBy('prototype_sales.created_at', 'desc')
            ->get();

        $verifiedPayments = \DB::table('prototype_sales')
            ->leftJoin('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as verifier', 'prototype_sales.verified_by', '=', 'verifier.id')
            ->select([
                'prototype_sales.*',
                'payment_accounts.name as account_name',
                'verifier.name as verified_by_name',
            ])
            ->where('prototype_sales.payment_status', 'verified')
            ->orderBy('prototype_sales.verified_at', 'desc')
            ->limit(50)
            ->get();

        $accounts = \App\Models\PaymentAccount::with('user')->where('is_active', true)->get();

        return view('sales.prototype.verification', compact('pendingPayments', 'verifiedPayments', 'accounts'));
    }

    /**
     * Cash flow view - per account breakdown.
     */
    public function cashFlow(Request $request)
    {
        $accountId = $request->account_id;

        $accounts = \App\Models\PaymentAccount::where('is_active', true)->get();

        $query = \DB::table('prototype_sales')
            ->leftJoin('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as verifier', 'prototype_sales.verified_by', '=', 'verifier.id')
            ->select([
                'prototype_sales.*',
                'payment_accounts.name as account_name',
                'verifier.name as verified_by_name',
            ])
            ->where('prototype_sales.payment_status', 'verified');

        if ($accountId) {
            $query->where('prototype_sales.payment_account_id', $accountId);
        }

        $payments = $query->orderBy('prototype_sales.verified_at', 'desc')->get();

        // Calculate totals per account for the summary
        $accountTotals = \DB::table('prototype_sales')
            ->select([
                'payment_account_id',
                \DB::raw('COUNT(*) as total_count'),
                \DB::raw('COALESCE(SUM(total_amount), 0) as total_amount'),
                \DB::raw('COALESCE(SUM(deposit_paid), 0) as total_deposit'),
            ])
            ->where('payment_status', 'verified')
            ->groupBy('payment_account_id')
            ->get()
            ->keyBy('payment_account_id');

        // Recent audit trail
        $auditLogs = \App\Models\PaymentAuditLog::with(['user', 'prototypeSale', 'paymentAccount'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('sales.prototype.cashflow', compact('accounts', 'payments', 'accountTotals', 'auditLogs', 'accountId'));
    }

    /**
     * Get audit logs for a specific sale (AJAX).
     */
    public function getAuditLogs($saleId)
    {
        $logs = \App\Models\PaymentAuditLog::with(['user', 'paymentAccount'])
            ->where('prototype_sale_id', $saleId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }

    /**
     * Get audit logs for a specific payment account (AJAX).
     */
    public function getAccountHistory($accountId)
    {
        $logs = \App\Models\PaymentAuditLog::with(['user', 'prototypeSale', 'paymentAccount'])
            ->where('payment_account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }

    // ================================================================
    // AGENT METHODS — Simplified Sales for Sales Agents & Reps
    // ================================================================

    /**
     * Show simplified "Add Sale" form for agents.
     */
    /**
     * Show dedicated Sales Team dashboard — "My Sales" for agents/reps.
     */
    public function agentDashboard(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $query = \DB::table('prototype_sales')
            ->where('sales_agent_id', $user->id);

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Kanban/order status filter
        if ($request->filled('kanban_status')) {
            $query->where('kanban_status', $request->kanban_status);
        }

        // Department/shop filter
        if ($request->filled('department')) {
            $query->where('department_name', $request->department);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        $statuses = ['new', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $statusLabels = [
            'new'                => 'New',
            'design'            => 'Design',
            'production'        => 'Production',
            'quality_check'      => 'Quality Check',
            'ready_for_delivery' => 'Ready for Delivery',
            'delivered'         => 'Delivered',
            'completed'         => 'Completed',
        ];

        // Get unique departments for filter dropdown
        $departments = \DB::table('prototype_sales')
            ->select('department_name')
            ->distinct()
            ->where('sales_agent_id', $user->id)
            ->whereNotNull('department_name')
            ->orderBy('department_name')
            ->pluck('department_name')
            ->toArray();

        // Preserve filter state for the view
        $filters = $request->only(['date_from', 'date_to', 'payment_status', 'kanban_status', 'department']);

        return view('sales.prototype.agent-dashboard', compact('sales', 'statuses', 'statusLabels', 'departments', 'filters'));
    }

    public function agentCreate()
    {
        $user = auth()->user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
        
        $departments = \DB::table('sales_departments')->where('is_active', true)->get()->toArray();
        return view('sales.prototype.agent-create', compact('departments'));
    }

    /**
     * Store a simplified sale created by an agent.
     */
    public function agentStore(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'services' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
            'deposit_paid' => 'nullable|numeric|min:0',
            'department_id' => 'required|exists:sales_departments,id',
            'payment_method' => 'nullable|string',
            'payment_screenshot' => 'nullable|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        // Get department
        $department = \DB::table('sales_departments')->find($request->department_id);
        if (!$department) {
            return back()->withErrors(['department_id' => 'Invalid department'])->withInput();
        }

        // Generate sales number
        $salesNumber = 'SALE-' . date('Ymd') . '-' . strtoupper(uniqid());

        // Handle payment screenshot upload
        $paymentScreenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $file = $request->file('payment_screenshot');
            $filename = 'payment_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads/payments', $filename, 'public');
            $paymentScreenshotPath = '/storage/' . $filePath;
        }

        $depositPaid = $request->deposit_paid ?? 0;

        \DB::table('prototype_sales')->insert([
            'sales_number' => $salesNumber,
            'customer_id' => null,
            'customer_name' => $request->customer_name,
            'customer_email' => null,
            'customer_phone' => $request->customer_phone,
            'customer_address' => null,
            'sales_agent_id' => $user->id,
            'sales_agent_name' => $user->name,
            'department_id' => $department->id,
            'department_name' => $department->name,
            'services' => json_encode([['name' => $request->services, 'qty' => 1]]),
            'subtotal' => $request->total_amount,
            'tax' => 0,
            'total_amount' => $request->total_amount,
            'deposit_paid' => $depositPaid,
            'balance_due' => $request->total_amount - $depositPaid,
            'payment_method' => $request->payment_method ?? 'cash',
            'payment_owner' => 'company',
            'payment_account_id' => null,
            'payment_date' => $depositPaid > 0 ? now() : null,
            'reference_number' => null,
            'payment_status' => $depositPaid > 0 ? 'pending' : 'unpaid',
            'payment_screenshot_path' => $paymentScreenshotPath,
            'customer_notes' => $request->notes,
            'internal_notes' => null,
            'estimated_completion_date' => null,
            'kanban_status' => 'new',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('sales.prototype.list')
            ->with('success', 'Sale submitted successfully!');
    }

    /**
     * Show "Add Payment" form for a sale (agent-facing).
     */
    public function agentAddPayment($id)
    {
        $user = auth()->user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404, 'Sale not found.');
        }

        // Non-admin can only add payments to their own sales
        if (!$user->isAdmin() && $sale->sales_agent_id != $user->id) {
            abort(403, 'You can only add payments to your own sales.');
        }

        return view('sales.prototype.agent-add-payment', compact('sale'));
    }

    /**
     * Process adding a payment to an existing sale (agent-facing).
     */
    public function agentPaymentStore(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404, 'Sale not found.');
        }

        // Non-admin can only add payments to their own sales
        if (!$user->isAdmin() && $sale->sales_agent_id != $user->id) {
            abort(403, 'You can only add payments to your own sales.');
        }

        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'payment_account_id' => 'nullable|integer|exists:payment_accounts,id',
            'reference_number' => 'nullable|string|max:255',
            'payment_screenshot' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        // Handle payment screenshot
        $paymentScreenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $file = $request->file('payment_screenshot');
            $filename = 'payment_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads/payments', $filename, 'public');
            $paymentScreenshotPath = '/storage/' . $filePath;
        }

        $newDepositPaid = ($sale->deposit_paid ?? 0) + $request->payment_amount;
        $newBalanceDue = $sale->total_amount - $newDepositPaid;

        // Update the sale with additional deposit
        \DB::table('prototype_sales')->where('id', $id)->update([
            'deposit_paid' => $newDepositPaid,
            'balance_due' => max($newBalanceDue, 0),
            'payment_method' => $request->payment_method,
            'payment_account_id' => $request->payment_account_id ?? $sale->payment_account_id,
            'payment_date' => $request->payment_date,
            'payment_owner' => $user->name,
            'reference_number' => $request->reference_number,
            'payment_screenshot_path' => $paymentScreenshotPath ?: $sale->payment_screenshot_path,
            'payment_status' => $request->payment_method === 'cash' ? 'verified' : 'pending',
            'updated_at' => now(),
        ]);

        // Log payment addition
        try {
            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $request->payment_account_id,
                'user_id' => $user->id,
                'action' => 'additional_payment',
                'remarks' => 'Additional payment of ₱' . number_format($request->payment_amount, 2) . ' via ' . $request->payment_method . ($request->notes ? ' — ' . $request->notes : ''),
            ]);
        } catch (\Exception $e) {
            // Non-critical — don't break the flow
        }

        return redirect()->route('sales.prototype.show', $id)
            ->with('success', 'Payment added successfully!');
    }
}
