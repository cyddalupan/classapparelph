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

            // Create a prototype_payments record for the initial deposit
            // so every payment (initial + additional/full) has its own record
            if ((float) $deptDeposit > 0) {
                try {
                    \App\Models\PrototypePayment::create([
                        'prototype_sale_id' => $saleId,
                        'payment_type' => ((float) $deptDeposit >= (float) $deptTotal) ? 'full_payment' : 'down_payment',
                        'amount' => $deptDeposit,
                        'payment_method' => $request->payment_method ?: 'cash',
                        'payment_account_id' => $request->payment_account_id ?: null,
                        'reference_number' => $request->reference_number ?: null,
                        'screenshot_path' => $paymentScreenshotPath,
                        'payment_status' => 'pending',
                        'payment_date' => $request->payment_date ?: null,
                        'notes' => 'Initial deposit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create initial deposit payment record: ' . $e->getMessage());
                }
            }

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
        
        // Build HTML for modal (production-focused: items, mockups, refs, print details, notes, comments)
        $html = '';
        
        // --- Design References: Mockup, File Photo, Approved Color (front and center for production) ---
        $mockups = json_decode($sale->mockup_images, true) ?: [];
        $designImgs = is_string($sale->design_images) ? (json_decode($sale->design_images, true) ?: []) : ($sale->design_images ?: []);
        
        $imgSections = [];
        $mockList = [];
        foreach ($mockups as $m) {
            $url = is_string($m) ? $m : ($m['url'] ?? '');
            if ($url) $mockList[] = ['url' => $url, 'label' => '🎨 Mockup'];
        }
        if ($mockList) $imgSections[] = $mockList;
        
        $fileList = [];
        foreach ($designImgs as $d) {
            if (($d['type'] ?? '') === 'file_screenshot') $fileList[] = ['url' => $d['url'] ?? '', 'label' => '📄 File Photo'];
        }
        if ($fileList) $imgSections[] = $fileList;
        
        $colorList = [];
        foreach ($designImgs as $d) {
            if (($d['type'] ?? '') === 'sample_color') $colorList[] = ['url' => $d['url'] ?? '', 'label' => '🎯 Approved Color'];
        }
        if ($colorList) $imgSections[] = $colorList;
        
        if ($imgSections) {
            $html .= '<div class="sale-detail-section">';
            $html .= '<h6><i class="fas fa-images me-2"></i>Design References</h6>';
            $html .= '<div class="d-flex flex-wrap">';
            foreach ($imgSections as $sec) {
                foreach ($sec as $img) {
                    $html .= '<div class="me-3 mb-2 text-center" style="width:110px;">';
                    $html .= '<img src="' . e($img['url']) . '" style="width:110px;height:110px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid #dee2e6;" onclick="window.openLightbox(\'' . e($img['url']) . '\')" onerror="this.style.display=\'none\';">';
                    $html .= '<div class="small text-muted mt-1">' . e($img['label']) . '</div>';
                    $html .= '</div>';
                }
            }
            $html .= '</div></div>';
        }
        
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
                $itemName = $item['name'] ?? $item['product_name'] ?? 'Item #' . ($idx + 1);
                $itemSpec = \App\Models\PrototypeSale::itemSpecSummary($item);
                $itemQty = $item['quantity'] ?? $item['qty'] ?? 0;
                $itemNotes = $item['notes'] ?? '';
                $subItems = $item['subItems'] ?? [];
                $printing = $item['printing'] ?? null;
                $refImages = $item['referenceImages'] ?? [];
                
                $html .= '<div class="item-card">';
                $html .= '<div class="d-flex justify-content-between align-items-start mb-2">';
                $html .= '<div><strong>' . e($itemSpec) . '</strong>';
                if ($item['department'] ?? null) {
                    $html .= ' <span class="badge bg-secondary">' . e($item['department']) . '</span>';
                }
                if ($itemQty > 0) {
                    $html .= ' <span class="badge bg-primary">×' . $itemQty . '</span>';
                }
                if ($itemSpec !== $itemName && $itemName) {
                    $html .= '<div class="small text-muted">' . e($itemName) . '</div>';
                }
                $html .= '</div>';
                $html .= '</div>';
                
                // Sub-items: brand, size, color, qty
                if (!empty($subItems)) {
                    $html .= '<div class="mb-2">';
                    foreach ($subItems as $si) {
                        $brand = $si['brand'] ?? $si['product_brand'] ?? '';
                        $size = $si['size'] ?? $si['type'] ?? $si['product_size'] ?? '';
                        $color = $si['color'] ?? $si['product_color'] ?? '';
                        $qty = $si['qty'] ?? $si['quantity'] ?? 1;
                        
                        $html .= '<span class="subitem-row">';
                        $parts = [];
                        if ($brand) $parts[] = e($brand);
                        if ($size) $parts[] = e($size);
                        if ($color) $parts[] = e($color);
                        $parts[] = '×' . $qty;
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
        
        // --- Comments (production-relevant) ---
        $comments = \DB::table('prototype_sale_comments')
            ->leftJoin('users', 'prototype_sale_comments.user_id', '=', 'users.id')
            ->select('prototype_sale_comments.*', 'users.name as user_name')
            ->where('prototype_sale_comments.sale_id', $id)
            ->orderBy('prototype_sale_comments.created_at', 'desc')
            ->get();
        if ($comments->isNotEmpty()) {
            $html .= '<div class="sale-detail-section">';
            $html .= '<h6><i class="fas fa-comments me-2"></i>Comments (' . $comments->count() . ')</h6>';
            foreach ($comments as $c) {
                $html .= '<div class="item-card" style="padding:8px 12px;margin-bottom:6px;">';
                $html .= '<div class="d-flex justify-content-between">';
                $html .= '<strong class="small">' . e($c->user_name ?? 'User #' . $c->user_id) . '</strong>';
                $html .= '<small class="text-muted">' . \Carbon\Carbon::parse($c->created_at)->format('M d, g:i A') . '</small>';
                $html .= '</div>';
                $html .= '<div class="small mt-1">' . nl2br(e($c->comment)) . '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        // Extract first service name for addon modals
        $firstServiceName = '';
        if (is_array($services) && count($services) > 0) {
            $first = $services[0];
            $firstServiceName = $first['name'] ?? $first['projectName'] ?? $first['project_name'] ?? '';
        }
        
        return response()->json([
            'html' => $html,
            'title' => 'Sale: ' . $sale->customer_name . ' (#' . $sale->sales_number . ')',
            'can_addon' => !in_array($sale->kanban_status, ['delivered', 'completed']),
            'firstServiceName' => $firstServiceName
        ]);
    }

                public function show(string $id)
    {
        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale) {
            abort(404);
        }
        // Attach department fields (view expects department_name/department_code)
        $department = \App\Models\SalesDepartment::find($sale->department_id);
        $sale->department_name = $department->name ?? null;
        $sale->department_code = $department->code ?? null;
        
        $services = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
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
        
        // Compute progress percentage from kanban_status
        $kanbanProgressMap = [
            'new' => 0,
            'sample_approval' => 8,
            'design' => 15,
            'production' => 50,
            'quality_check' => 70,
            'ready_for_delivery' => 85,
            'delivered' => 95,
            'completed' => 100,
        ];
        $progressPercent = $kanbanProgressMap[$sale->kanban_status] ?? 0;
        
        // Fetch pending changes, audit logs, and comments for this sale
        $pendingChanges = \DB::table('prototype_sale_changes')
            ->where('sale_id', $id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        $currentUser = auth()->user();
        $isManager = $currentUser && in_array($currentUser->role, ['admin', 'manager']);
        
        // Determine if editing is allowed (not delivered/completed)
        $canEdit = !in_array($sale->kanban_status, ['delivered', 'completed', 'cancelled']);
        
        // Fetch refund data for this sale
        $refunds = \DB::table('prototype_refunds')
            ->where('prototype_sale_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        $activeRefund = $refunds->whereIn('refund_status', ['pending', 'accepted'])->first();
        
        // Completed refunds for display (to show total refunded + proof)
        $completedRefunds = $refunds->where('refund_status', 'completed');
        $totalRefunded = $completedRefunds->sum('refund_amount');
        
        // Fetch refund audit logs for this sale
        $refundLogs = \DB::table('prototype_sale_audit_logs')
            ->where('sale_id', $id)
            ->where('action', 'like', 'refund_%')
            ->join('users', 'prototype_sale_audit_logs.user_id', '=', 'users.id')
            ->select('prototype_sale_audit_logs.*', 'users.name as user_name')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Fetch individual payments for this sale
        $payments = \App\Models\PrototypePayment::where('prototype_sale_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        $sale->setRelation('payments', $payments);

        // Single source of truth: computed from model accessors (payments minus completed refunds)
        $totalPaid = $sale->total_paid;
        $totalRefunded = $sale->total_refunded;
        $netPaid = $sale->net_paid;
        $balanceDue = $sale->balance_due_computed;

        return view('sales.prototype.show', compact(
            'sale', 'services', 'kanbanItem', 'relatedSales',
            'overallGroupSubtotal', 'overallGroupTotal', 'overallGroupDeposit', 'overallGroupBalance',
            'progressPercent', 'pendingChanges', 'isManager', 'canEdit',
            'refunds', 'activeRefund', 'refundLogs', 'completedRefunds', 'totalRefunded',
            'payments', 'totalPaid', 'netPaid', 'balanceDue'
        ));
    }

    /**
     * Show the edit items page for adding/removing/changing items.
     */
    public function editItems(string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }
        
        // Only allow editing if not delivered/completed/cancelled
        if (in_array($sale->kanban_status, ['delivered', 'completed', 'cancelled'])) {
            return redirect()->route('sales.prototype.show', $id)
                ->with('error', 'Cannot edit completed or cancelled orders.');
        }
        
        // Check if there's already a pending change
        $hasPending = \DB::table('prototype_sale_changes')
            ->where('sale_id', $id)
            ->where('status', 'pending')
            ->exists();
        if ($hasPending) {
            return redirect()->route('sales.prototype.show', $id)
                ->with('error', 'There is already a pending change request awaiting approval.');
        }
        
        // Decode services
        $raw = $sale->services;
        $services = json_decode($raw, true);
        if (is_string($services)) {
            $services = json_decode($services, true);
        }
        if (!is_array($services)) {
            $services = [];
        }
        
        $products = \DB::table('products')->orderBy('name')->get();
        
        return view('sales.prototype.edit-items', compact('sale', 'services', 'products'));
    }

    /**
     * Submit a change request (pending manager approval).
     */
    public function submitChange(Request $request, string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }
        
        if (in_array($sale->kanban_status, ['delivered', 'completed', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Cannot modify a completed or cancelled order.']);
        }
        
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.']);
        }
        
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unitPrice' => 'required|numeric|min:0',
        ]);
        
        // Decode current services as baseline
        $servicesBefore = json_decode($sale->services, true);
        if (is_string($servicesBefore)) {
            $servicesBefore = json_decode($servicesBefore, true);
        }
        if (!is_array($servicesBefore)) {
            $servicesBefore = [];
        }
        
        // Build the new services array from request
        $servicesAfter = [];
        foreach ($request->items as $item) {
            $servicesAfter[] = [
                'id' => $item['id'] ?? (round(microtime(true) * 1000)),
                'name' => $item['name'],
                'quantity' => (int) $item['quantity'],
                'unitPrice' => (float) $item['unitPrice'],
                'totalPrice' => (int) $item['quantity'] * (float) $item['unitPrice'],
                'department' => $item['department'] ?? $sale->department_name,
                'notes' => $item['notes'] ?? '',
                'productType' => $item['productType'] ?? 'cutting',
            ];
        }
        
        // Calculate totals
        $totalBefore = $sale->total_amount;
        $totalAfter = array_sum(array_column($servicesAfter, 'totalPrice'));
        
        // Generate summary of changes
        $summary = $this->generateChangeSummary($servicesBefore, $servicesAfter);
        
        // Save the pending change
        $changeId = \DB::table('prototype_sale_changes')->insertGetId([
            'sale_id' => $id,
            'services_before' => json_encode($servicesBefore),
            'services_after' => json_encode($servicesAfter),
            'total_before' => $totalBefore,
            'total_after' => $totalAfter,
            'deposit_before' => $sale->deposit_paid,
            'deposit_after' => $sale->deposit_paid, // deposit doesn't change until refund/additional payment
            'change_summary' => $summary,
            'status' => 'pending',
            'submitted_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Log the audit trail
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $id,
            'user_id' => $user->id,
            'action' => 'change_submitted',
            'description' => $summary,
            'details' => json_encode([
                'change_id' => $changeId,
                'total_before' => $totalBefore,
                'total_after' => $totalAfter,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Change request submitted for approval.',
            'change_id' => $changeId,
        ]);
    }

    /**
     * Approve a pending change request.
     */
    public function approveChange(Request $request, string $changeId)
    {
        $change = \DB::table('prototype_sale_changes')->find($changeId);
        if (!$change) {
            return response()->json(['success' => false, 'message' => 'Change request not found.'], 404);
        }
        
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Only managers can approve changes.']);
        }
        
        if ($change->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This change request has already been ' . $change->status . '.']);
        }
        
        $servicesAfter = json_decode($change->services_after, true);
        
        // Calculate new totals
        $subtotal = $servicesAfter ? array_sum(array_column($servicesAfter, 'totalPrice')) : 0;
        $totalAmount = $subtotal; // no 12% tax per Andrew's rule
        
        // Compute NET paid: verified payments minus completed refunds.
        // (Previously used raw deposit_after, which ignored refunds and
        //  caused phantom overpayments after a refund was completed.)
        $totalPaid = \App\Models\PrototypePayment::where('prototype_sale_id', $change->sale_id)
            ->whereNotIn('payment_status', ['rejected', 'reject_pending'])
            ->sum('amount');
        if ($totalPaid <= 0) {
            $totalPaid = (float) ($change->deposit_after ?? 0);
        }
        $totalRefunded = \App\Models\PrototypeRefund::where('prototype_sale_id', $change->sale_id)
            ->where('refund_status', 'completed')
            ->sum('refund_amount');
        $netPaid = max($totalPaid - $totalRefunded, 0);
        
        // Detect overpayment for reprocess
        $isReprocess = ($change->type ?? 'addition') === 'reprocess';
        $rawBalance = $totalAmount - $netPaid;
        $hasOverpayment = $rawBalance < 0;
        $balanceDue = max($rawBalance, 0); // Don't show negative balance
        $overpaymentAmount = $hasOverpayment ? abs($rawBalance) : 0;
        
        // Update the sale's services and recalculate prices
        $updateData = [
            'services' => json_encode($servicesAfter),
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'balance_due' => $balanceDue,
            'overpayment' => $overpaymentAmount, // always set: 0 when no overpayment
            'updated_at' => now(),
        ];
        if ($hasOverpayment) {
            $updateData['balance_due'] = 0; // zero out balance, overpayment tracked separately
        }
        \DB::table('prototype_sales')
            ->where('id', $change->sale_id)
            ->update($updateData);
        
        // Update mockup_images when reprocess is approved
        if ($isReprocess && !empty($servicesAfter)) {
            $firstItem = $servicesAfter[0];
            if (isset($firstItem['sublimationForm']['mockup']) && !empty($firstItem['sublimationForm']['mockup'])) {
                $mockupImages = [[
                    'name' => ($firstItem['sublimationForm']['projectName'] ?? 'mockup') . '-mockup.png',
                    'url' => $firstItem['sublimationForm']['mockup'],
                    'type' => 'sublimation'
                ]];
                \DB::table('prototype_sales')
                    ->where('id', $change->sale_id)
                    ->update(['mockup_images' => json_encode($mockupImages)]);
            }
        }

        // Mark change as approved
        \DB::table('prototype_sale_changes')
            ->where('id', $changeId)
            ->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);
        
        // Build audit description
        $desc = 'Change request approved. New total: ₱' . number_format($totalAmount, 2);
        if ($isReprocess) {
            $desc = 'Reprocess approved. New total: ₱' . number_format($totalAmount, 2);
            if ($hasOverpayment) {
                $desc .= ' — Overpayment of ₱' . number_format($overpaymentAmount, 2) . ' detected. Manager may request refund.';
            }
        }
        
        // Audit log
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $change->sale_id,
            'user_id' => $user->id,
            'action' => $isReprocess ? 'reprocess_approved' : 'change_approved',
            'description' => $desc,
            'details' => json_encode([
                'change_id' => $changeId,
                'type' => $change->type ?? 'addition',
                'total_before' => $change->total_before,
                'total_after' => $totalAmount,
                'overpayment' => $hasOverpayment ? $overpaymentAmount : 0,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json(['success' => true, 'message' => $desc]);
    }

    /**
     * Reject a pending change request.
     */
    public function rejectChange(Request $request, string $changeId)
    {
        $change = \DB::table('prototype_sale_changes')->find($changeId);
        if (!$change) {
            return response()->json(['success' => false, 'message' => 'Change request not found.'], 404);
        }
        
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Only managers can reject changes.']);
        }
        
        if ($change->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This change request has already been ' . $change->status . '.']);
        }
        
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);
        
        \DB::table('prototype_sale_changes')
            ->where('id', $changeId)
            ->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'rejected_at' => now(),
                'rejection_reason' => $request->reason,
                'updated_at' => now(),
            ]);
        
        // Audit log
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $change->sale_id,
            'user_id' => $user->id,
            'action' => 'change_rejected',
            'description' => 'Change rejected. Reason: ' . $request->reason,
            'details' => json_encode([
                'change_id' => $changeId,
                'reason' => $request->reason,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json(['success' => true, 'message' => 'Change request rejected.']);
    }

    /**
     * Add a manager comment to a sale.
     */
    public function addComment(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Only managers can add comments.']);
        }
        
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        
        $commentId = \DB::table('prototype_sale_comments')->insertGetId([
            'sale_id' => $id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Audit log
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $id,
            'user_id' => $user->id,
            'action' => 'comment_added',
            'description' => 'Manager added a comment: ' . substr($request->comment, 0, 100) . (strlen($request->comment) > 100 ? '...' : ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'comment_id' => $commentId]);
        }
        
        return redirect()->back()->with('success', 'Comment added.');
    }

    /**
     * Get audit history for a sale (AJAX).
     */
    public function auditHistory(string $id)
    {
        $logs = \DB::table('prototype_sale_audit_logs')
            ->where('sale_id', $id)
            ->join('users', 'prototype_sale_audit_logs.user_id', '=', 'users.id')
            ->select(
                'prototype_sale_audit_logs.*',
                'users.name as user_name'
            )
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json(['logs' => $logs]);
    }

    /**
     * Generate a human-readable summary of changes between two service arrays.
     */
    private function generateChangeSummary(array $before, array $after): string
    {
        $parts = [];
        
        // Find items that were removed
        $beforeIds = array_column($before, 'id');
        $afterIds = array_column($after, 'id');
        
        foreach ($before as $bItem) {
            if (!in_array($bItem['id'] ?? null, $afterIds)) {
                $parts[] = 'Removed: ' . ($bItem['name'] ?? 'Unknown item');
            }
        }
        
        foreach ($after as $aItem) {
            $bid = $aItem['id'] ?? null;
            if ($bid && in_array($bid, $beforeIds)) {
                // Find the before item
                $bItem = null;
                foreach ($before as $bi) {
                    if (($bi['id'] ?? null) === $bid) {
                        $bItem = $bi;
                        break;
                    }
                }
                if ($bItem) {
                    $changes = [];
                    if (($bItem['quantity'] ?? 0) !== ($aItem['quantity'] ?? 0)) {
                        $changes[] = 'qty ' . ($bItem['quantity'] ?? 0) . '→' . ($aItem['quantity'] ?? 0);
                    }
                    if (($bItem['unitPrice'] ?? 0) !== ($aItem['unitPrice'] ?? 0)) {
                        $changes[] = 'price ₱' . number_format($bItem['unitPrice'] ?? 0, 2) . '→₱' . number_format($aItem['unitPrice'] ?? 0, 2);
                    }
                    if (!empty($changes)) {
                        $parts[] = 'Modified ' . ($aItem['name'] ?? 'Unknown') . ': ' . implode(', ', $changes);
                    }
                }
            } else {
                // New item
                $parts[] = 'Added: ' . ($aItem['name'] ?? 'Unknown item') . ' x' . ($aItem['quantity'] ?? 1) . ' (₱' . number_format($aItem['totalPrice'] ?? 0, 2) . ')';
            }
        }
        
        return empty($parts) ? 'No changes detected' : implode('; ', $parts);
    }

    public function addProduct(Request $request, string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        // Check if already in completed/delivered
        if (in_array($sale->kanban_status, ['delivered', 'completed', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Cannot add product to a completed or cancelled order.']);
        }

        // Support both old format (product_name/sizes/unit_price) and new fullsublimation format
        $productName = $request->input('product_name', $request->input('name', ''));
        $rawSizes = $request->input('sizes', []);
        if (empty($rawSizes) && $request->has('sublimationForm.sizes')) {
            $rawSizes = $request->input('sublimationForm.sizes', []);
        }
        $unitPrice = $request->input('unit_price', $request->input('unitPrice', 0));
        $sublimationForm = $request->input('sublimationForm', null);
        $productType = $request->input('productType', 'cutting');

        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'You must be logged in to add products.'], 401);
        }

        if (empty($productName)) {
            return response()->json(['success' => false, 'message' => 'Product name is required.'], 400);
        }

        // Build size details from sizes array (supports {size, qty} and {name, size, qty})
        $sizeDetails = [];
        foreach ($rawSizes as $sd) {
            $qty = intval($sd['qty'] ?? 1);
            if ($qty <= 0) continue;
            $entry = [
                'size' => $sd['size'] ?? 'M',
                'qty' => $qty,
            ];
            if (!empty($sd['name'])) {
                $entry['name'] = $sd['name'];
            }
            $sizeDetails[] = $entry;
        }

        if (empty($sizeDetails)) {
            return response()->json(['success' => false, 'message' => 'At least one size/quantity is required.'], 400);
        }

        $totalQty = array_sum(array_column($sizeDetails, 'qty'));
        $unitPrice = floatval($unitPrice);
        $itemTotal = $totalQty * $unitPrice;

        // Parse current services
        $servicesBefore = json_decode($sale->services ?? '[]', true);
        if (!is_array($servicesBefore)) $servicesBefore = [];

        // Generate a unique item ID
        $maxId = 0;
        foreach ($servicesBefore as $s) {
            if (isset($s['id']) && is_numeric($s['id']) && $s['id'] > $maxId) $maxId = $s['id'];
        }
        $newId = $maxId + 1;

        // Build size display string
        $sizeLines = [];
        foreach ($sizeDetails as $sd) {
            $label = !empty($sd['name']) ? $sd['name'] . ' (' . $sd['size'] . ')' : $sd['size'] . ': ' . $sd['qty'];
            $sizeLines[] = $label;
        }

        // Build item with full sublimation data if provided
        $item = [
            'id' => $newId,
            'name' => $productName,
            'productType' => $productType === 'fullsublimation' ? 'fullsublimation' : 'cutting',
            'quantity' => $totalQty,
            'unitPrice' => $unitPrice,
            'totalPrice' => $itemTotal,
            'department' => $sale->department_code ?? 'class',
            'sizeDetails' => $sizeDetails,
        ];

        if ($sublimationForm && is_array($sublimationForm)) {
            // NORMALIZE: Convert JS key names to view-expected key names
            $normalized = $sublimationForm;

            // garmentType + garmentId → garment: {name, id}
            if (!isset($normalized['garment']) && !empty($normalized['garmentType'])) {
                $normalized['garment'] = [
                    'name' => $normalized['garmentType'],
                    'id' => $normalized['garmentId'] ?? '',
                ];
            }
            unset($normalized['garmentType'], $normalized['garmentId']);

            // specs → specifications
            if (!isset($normalized['specifications']) && isset($normalized['specs'])) {
                $normalized['specifications'] = $normalized['specs'];
            }
            unset($normalized['specs']);

            // fabric (string) + fabricId → fabric: {name, id}
            if (!isset($normalized['fabric']) || is_string($normalized['fabric'])) {
                $fabricStr = (isset($normalized['fabric']) && is_string($normalized['fabric'])) ? $normalized['fabric'] : '';
                $normalized['fabric'] = [
                    'name' => $fabricStr,
                    'id' => $normalized['fabricId'] ?? '',
                ];
            }
            unset($normalized['fabricId']);

            // Convert roster-mode sizes into dedicated roster array
            $hasNamedSizes = false;
            if (!empty($normalized['sizes'])) {
                foreach ($normalized['sizes'] as $s) {
                    if (!empty($s['name'])) { $hasNamedSizes = true; break; }
                }
            }
            if ($hasNamedSizes && empty($normalized['roster'])) {
                $normalized['roster'] = [];
                foreach ($normalized['sizes'] as $s) {
                    $entry = [
                        'name' => $s['name'] ?? '',
                        'size' => $s['size'] ?? '',
                        'number' => $s['number'] ?? 1,
                        'qty' => $s['qty'] ?? 1,
                    ];
                    // Preserve Excel columns for print slip / name list rendering
                    if (!empty($s['columns'])) {
                        $entry['columns'] = $s['columns'];
                    }
                    $normalized['roster'][] = $entry;
                }
                // Keep sizes but also set roster for view
            }

            // Ensure mockupUrl is also accessible as 'mockup'
            if (!empty($normalized['mockupUrl'])) {
                $normalized['mockup'] = $normalized['mockupUrl'];
            }

            // Strip helper/extra keys that are not part of the expected format
            unset($normalized['unitPrice'], $normalized['totalQty'], $normalized['totalPrice']);
            unset($normalized['rosterMode'], $normalized['mockupData'], $normalized['mockupDataStripped']);

            // Store the normalized sublimation form data
            $item['sublimationForm'] = $normalized;
            // Set sizes display string
            if (empty($item['sublimationForm']['sizes'])) {
                $item['sublimationForm']['sizes'] = implode(', ', $sizeLines);
            }
            // Handle special price
            if (!empty($sublimationForm['specialPrice'])) {
                $item['sublimationForm']['hasSpecialPrice'] = true;
            }
        } else {
            // Fallback: minimal sublimation form for backward compat
            $item['sublimationForm'] = [
                'sizes' => implode(', ', $sizeLines),
            ];
        }

        // Handle mockup image: convert base64 data URL to file and store URL
        // (base64 can be 40MB+ which breaks JSON storage)
        if (!empty($item['sublimationForm']['mockupData'])) {
            $mockupData = $item['sublimationForm']['mockupData'];
            // Check if it's a base64 data URL
            if (is_string($mockupData) && preg_match('/^data:image\/(\w+);base64,/', $mockupData, $matches)) {
                $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                $base64 = substr($mockupData, strpos($mockupData, ',') + 1);
                $decoded = base64_decode($base64);
                if ($decoded !== false) {
                    $filename = 'mockup_' . $id . '_' . $newId . '_' . time() . '.' . $ext;
                    $subdir = 'uploads/mockups';
                    $dir = public_path($subdir);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                    $filepath = $dir . '/' . $filename;
                    file_put_contents($filepath, $decoded);
                    // Store URL instead of base64 data
                    $item['sublimationForm']['mockupUrl'] = asset($subdir . '/' . $filename);
                    $item['sublimationForm']['mockupDataStripped'] = true;
                    unset($item['sublimationForm']['mockupData']);
                } else {
                    // Could not decode; strip to avoid large JSON
                    $item['sublimationForm']['mockupDataStripped'] = true;
                    unset($item['sublimationForm']['mockupData']);
                }
            } else {
                // Not base64 (already a URL or other format) — keep as-is
                $item['sublimationForm']['mockupUrl'] = $mockupData;
                unset($item['sublimationForm']['mockupData']);
            }
        }

        // Build services_after: current services + the new item
        $servicesAfter = $servicesBefore;
        $servicesAfter[] = $item;

        // Calculate totals
        $totalBefore = $sale->total_amount;
        $totalAfter = array_sum(array_map(fn($svc) => floatval($svc['totalPrice'] ?? 0), $servicesAfter));
        $totalBefore = max($totalBefore, array_sum(array_map(fn($svc) => floatval($svc['totalPrice'] ?? 0), $servicesBefore)));

        // Generate summary
        $summary = 'Added: ' . $productName . ' x' . $totalQty . ' (₱' . number_format($itemTotal, 2) . ')';

        // Save as pending change request (like submitChange)
        $user = auth()->user();
        $changeId = \DB::table('prototype_sale_changes')->insertGetId([
            'sale_id' => $id,
            'services_before' => json_encode($servicesBefore),
            'services_after' => json_encode($servicesAfter),
            'total_before' => $totalBefore,
            'total_after' => $totalAfter,
            'deposit_before' => $sale->deposit_paid ?? 0,
            'deposit_after' => $sale->deposit_paid ?? 0,
            'change_summary' => $summary,
            'status' => 'pending',
            'submitted_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Audit log
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $id,
            'user_id' => $user->id,
            'action' => 'add_product_pending',
            'description' => $summary . ' — awaiting manager approval',
            'details' => json_encode([
                'change_id' => $changeId,
                'total_before' => $totalBefore,
                'total_after' => $totalAfter,
            ]),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added! Waiting for Manager approval.',
            'change_id' => $changeId,
        ]);
    }

    /**
     * Reprocess Order: Replace all services with new item(s).
     * Creates a pending change request (type=reprocess) for manager approval.
     */
    public function reprocessOrder(Request $request, string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        if (in_array($sale->kanban_status, ['delivered', 'completed', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Cannot reprocess a completed or cancelled order.']);
        }

        $pendingReprocess = \DB::table('prototype_sale_changes')
            ->where('sale_id', $id)
            ->where('type', 'reprocess')
            ->where('status', 'pending')
            ->count();
        if ($pendingReprocess > 0) {
            return response()->json(['success' => false, 'message' => 'There is already a pending reprocess request. Please wait for the current one to be resolved.']);
        }

        $servicesBefore = json_decode($sale->services ?? '[]', true);
        if (!is_array($servicesBefore)) $servicesBefore = [];

        $productName = $request->input('product_name', $request->input('name', ''));
        $rawSizes = $request->input('sizes', []);
        if (empty($rawSizes) && $request->has('sublimationForm.sizes')) {
            $rawSizes = $request->input('sublimationForm.sizes', []);
        }
        $unitPrice = $request->input('unit_price', $request->input('unitPrice', 0));
        $sublimationForm = $request->input('sublimationForm', null);
        $productType = $request->input('productType', 'cutting');

        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'You must be logged in to reprocess orders.'], 401);
        }

        if (empty($productName)) {
            return response()->json(['success' => false, 'message' => 'Product name is required.'], 400);
        }

        $sizeDetails = [];
        foreach ($rawSizes as $sd) {
            $qty = intval($sd['qty'] ?? 1);
            if ($qty <= 0) continue;
            $entry = ['size' => $sd['size'] ?? 'M', 'qty' => $qty];
            if (!empty($sd['name'])) $entry['name'] = $sd['name'];
            $sizeDetails[] = $entry;
        }

        if (empty($sizeDetails)) {
            return response()->json(['success' => false, 'message' => 'At least one size/quantity is required.'], 400);
        }

        $totalQty = array_sum(array_column($sizeDetails, 'qty'));
        $unitPrice = floatval($unitPrice);
        $itemTotal = $totalQty * $unitPrice;

        $maxId = 0;
        foreach ($servicesBefore as $s) {
            if (isset($s['id']) && is_numeric($s['id']) && $s['id'] > $maxId) $maxId = $s['id'];
        }
        $newId = $maxId + 1;

        $sizeLines = [];
        foreach ($sizeDetails as $sd) {
            $label = !empty($sd['name']) ? $sd['name'] . ' (' . $sd['size'] . ')' : $sd['size'] . ': ' . $sd['qty'];
            $sizeLines[] = $label;
        }

        $item = [
            'id' => $newId,
            'name' => $productName,
            'productType' => $productType === 'fullsublimation' ? 'fullsublimation' : 'cutting',
            'quantity' => $totalQty,
            'unitPrice' => $unitPrice,
            'totalPrice' => $itemTotal,
            'department' => $sale->department_code ?? 'class',
            'sizeDetails' => $sizeDetails,
        ];

        if ($sublimationForm && is_array($sublimationForm)) {
            $normalized = $sublimationForm;
            if (!isset($normalized['garment']) && !empty($normalized['garmentType'])) {
                $normalized['garment'] = ['name' => $normalized['garmentType'], 'id' => $normalized['garmentId'] ?? ''];
            }
            unset($normalized['garmentType'], $normalized['garmentId']);
            if (!isset($normalized['specifications']) && isset($normalized['specs'])) $normalized['specifications'] = $normalized['specs'];
            unset($normalized['specs']);
            if (!isset($normalized['fabric']) || is_string($normalized['fabric'])) {
                $fabricStr = (isset($normalized['fabric']) && is_string($normalized['fabric'])) ? $normalized['fabric'] : '';
                $normalized['fabric'] = ['name' => $fabricStr, 'id' => $normalized['fabricId'] ?? ''];
            }
            unset($normalized['fabricId']);

            $hasNamedSizes = false;
            if (!empty($normalized['sizes'])) {
                foreach ($normalized['sizes'] as $s) { if (!empty($s['name'])) { $hasNamedSizes = true; break; } }
            }
            if ($hasNamedSizes && empty($normalized['roster'])) {
                $normalized['roster'] = [];
                foreach ($normalized['sizes'] as $s) {
                    $entry = ['name' => $s['name'] ?? '', 'size' => $s['size'] ?? '', 'number' => $s['number'] ?? 1, 'qty' => $s['qty'] ?? 1];
                    if (!empty($s['columns'])) $entry['columns'] = $s['columns'];
                    $normalized['roster'][] = $entry;
                }
            }
            if (!empty($normalized['mockupUrl'])) $normalized['mockup'] = $normalized['mockupUrl'];
            unset($normalized['unitPrice'], $normalized['totalQty'], $normalized['totalPrice']);
            unset($normalized['rosterMode'], $normalized['mockupData'], $normalized['mockupDataStripped']);

            $item['sublimationForm'] = $normalized;
            if (empty($item['sublimationForm']['sizes'])) $item['sublimationForm']['sizes'] = implode(', ', $sizeLines);
            if (!empty($sublimationForm['specialPrice'])) $item['sublimationForm']['hasSpecialPrice'] = true;

            // Handle mockup image
            if (!empty($item['sublimationForm']['mockupData'])) {
                $mockupData = $item['sublimationForm']['mockupData'];
                if (is_string($mockupData) && preg_match('/^data:image\/(\w+);base64,/', $mockupData, $matches)) {
                    $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                    $base64 = substr($mockupData, strpos($mockupData, ',') + 1);
                    $decoded = base64_decode($base64);
                    if ($decoded !== false) {
                        $filename = 'mockup_' . $id . '_' . $newId . '_' . time() . '.' . $ext;
                        $subdir = 'uploads/mockups';
                        $dir = public_path($subdir);
                        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
                        file_put_contents($dir . '/' . $filename, $decoded);
                        $item['sublimationForm']['mockupUrl'] = asset($subdir . '/' . $filename);
                        $item['sublimationForm']['mockupDataStripped'] = true;
                        unset($item['sublimationForm']['mockupData']);
                    } else {
                        $item['sublimationForm']['mockupDataStripped'] = true;
                        unset($item['sublimationForm']['mockupData']);
                    }
                } else {
                    $item['sublimationForm']['mockupUrl'] = $mockupData;
                    unset($item['sublimationForm']['mockupData']);
                }
            }
        } else {
            $item['sublimationForm'] = ['sizes' => implode(', ', $sizeLines)];
        }

        // services_after = just the new item (replaces old services completely)
        $servicesAfter = [$item];

        $totalBefore = $sale->total_amount;
        $totalAfter = array_sum(array_map(fn($svc) => floatval($svc['totalPrice'] ?? 0), $servicesAfter));

        $summary = 'Reprocess: ' . $productName . ' x' . $totalQty . ' (₱' . number_format($itemTotal, 2) . ') — old total: ₱' . number_format($totalBefore, 2);

        $user = auth()->user();
        $changeId = \DB::table('prototype_sale_changes')->insertGetId([
            'sale_id' => $id,
            'services_before' => json_encode($servicesBefore),
            'services_after' => json_encode($servicesAfter),
            'total_before' => $totalBefore,
            'total_after' => $totalAfter,
            'deposit_before' => $sale->deposit_paid ?? 0,
            'deposit_after' => $sale->deposit_paid ?? 0,
            'change_summary' => $summary,
            'status' => 'pending',
            'type' => 'reprocess',
            'submitted_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $id,
            'user_id' => $user->id,
            'action' => 'reprocess_pending',
            'description' => $summary . ' — awaiting manager approval',
            'details' => json_encode(['change_id' => $changeId, 'total_before' => $totalBefore, 'total_after' => $totalAfter]),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reprocess submitted! Waiting for Manager approval. Old services will be replaced upon approval.',
            'change_id' => $changeId,
        ]);
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
     * Generate and download print slip as PDF.
     */
    /**
     * Compress a base64-encoded image for PDF embedding.
     * Rescales to max 800px and saves as JPEG 70% quality.
     */
    private function compressMockupImage(string $dataUrl): string
    {
        if (!str_starts_with($dataUrl, 'data:image/')) {
            return $dataUrl; // not a data URL, keep as-is
        }
        
        // Extract base64 data
        if (!preg_match('#^data:image/(\w+);base64,(.+)$#', $dataUrl, $m)) {
            return $dataUrl;
        }
        $ext = strtolower($m[1]);
        $base64 = $m[2];
        $rawData = base64_decode($base64, true);
        if ($rawData === false || strlen($rawData) < 50000) {
            return $dataUrl; // small image or invalid, skip
        }
        
        // Only compress images larger than 500KB
        if (strlen($rawData) < 512000) {
            return $dataUrl;
        }
        
        // Create GD image from source
        $img = @imagecreatefromstring($rawData);
        if (!$img) {
            return $dataUrl;
        }
        
        $origW = imagesx($img);
        $origH = imagesy($img);
        $maxDim = 800;
        
        // Resize only if larger than maxDim
        if ($origW <= $maxDim && $origH <= $maxDim) {
            imagedestroy($img);
            return $dataUrl;
        }
        
        $ratio = min($maxDim / $origW, $maxDim / $origH);
        $newW = (int)round($origW * $ratio);
        $newH = (int)round($origH * $ratio);
        
        $resized = imagecreatetruecolor($newW, $newH);
        // Preserve transparency for PNG
        if ($ext === 'png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($img);
        
        ob_start();
        imagejpeg($resized, null, 70);
        $compressed = ob_get_clean();
        imagedestroy($resized);
        
        return 'data:image/jpeg;base64,' . base64_encode($compressed);
    }

    public function printSlipPdf(string $id, Request $request)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }
        
        $services = json_decode($sale->services, true);
        if (!is_array($services)) {
            $services = [];
        }
        
        // Filter by selected item indices (comma-separated, e.g. ?items=0,1)
        $selectedItems = $request->query('items');
        if ($selectedItems !== null) {
            $indices = array_map('intval', explode(',', $selectedItems));
            $filtered = [];
            foreach ($indices as $idx) {
                if (isset($services[$idx])) {
                    $filtered[] = $services[$idx];
                }
            }
            if (!empty($filtered)) {
                $services = $filtered;
            }
        }
        
        // Compress large mockup images before generating PDF
        foreach ($services as &$svc) {
            $sf = &$svc['sublimationForm'];
            if (!empty($sf)) {
                if (!empty($sf['mockupUrl'])) {
                    $sf['mockupUrl'] = $this->compressMockupImage($sf['mockupUrl']);
                }
                if (!empty($sf['mockup'])) {
                    $sf['mockup'] = $this->compressMockupImage($sf['mockup']);
                }
            }
        }
        unset($svc, $sf);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.prototype.print-slip', compact('sale', 'services'));
        $pdf->setPaper('A4', 'landscape');
        
        $itemLabel = count($services) === 1 ? \Illuminate\Support\Str::slug(end($services)['name'] ?? 'item') : '';
        $filename = $sale->sales_number . ($itemLabel ? '-' . $itemLabel : '') . '-print-slip.pdf';
        return $pdf->download($filename);
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

        // Roster data — only from the primary/first service (not additional services)
        $allRosters = $main['roster'] ?? [];

        // Sizes (from sublimation or fallback)
        $sizes = $main['sizes'] ?? [];

        // Total QTY — count from the primary service only (matches the roster/sizes shown on the slip)
        // This avoids pulling in additional-order quantities (e.g. reprocess + 2 add-ons = 13+5+5)
        $totalQty = 0;
        $primarySf = $main ?: [];
        foreach ($primarySf['sizes'] ?? [] as $s) {
            $totalQty += intval($s['quantity'] ?? $s['qty'] ?? 0);
        }
        if ($totalQty === 0) {
            foreach ($primarySf['roster'] ?? [] as $r) {
                $totalQty += intval($r['qty'] ?? $r['number'] ?? 1);
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
    /**
     * Get production checklist showing ALL products (not just first one).
     * Used by the "Additional Production Slip" tab on kanban.
     */
    public function getAdditionalProductionChecklist(string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        
        // Get all approved changes for this sale that added products
        $approvedChanges = \DB::table('prototype_sale_changes')
            ->where('sale_id', $id)
            ->where('status', 'approved')
            ->whereRaw('JSON_LENGTH(services_after) > JSON_LENGTH(services_before)')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Collect all ADDITIONAL items (items in services_after but NOT in services_before)
        $additionalItems = [];
        foreach ($approvedChanges as $change) {
            $before = json_decode($change->services_before, true) ?: [];
            $after = json_decode($change->services_after, true) ?: [];
            $beforeIds = array_column($before, 'id');
            foreach ($after as $item) {
                if (!in_array($item['id'] ?? null, $beforeIds)) {
                    $additionalItems[] = $item;
                }
            }
        }
        
        // Also check current services for items that didn't exist at sale creation (id > max original)
        // This handles approved changes that were merged into sale.services
        $services = json_decode($sale->services, true) ?: [];
        
        // Get all submission IDs from change histories to know original items
        $allChanges = \DB::table('prototype_sale_changes')
            ->where('sale_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $originalItemIds = [];
        $additionalFromServices = [];
        if ($allChanges->isNotEmpty()) {
            // Item IDs from the very first services_before = original items
            $firstChange = $allChanges->first();
            $firstBefore = json_decode($firstChange->services_before, true) ?: [];
            $originalItemIds = array_column($firstBefore, 'id');
            
            // Collect all item IDs introduced by reprocess changes (these are replacements, not additions)
            $reprocessedItemIds = [];
            foreach ($allChanges as $c) {
                if (($c->type ?? '') === 'reprocess' && $c->status === 'approved') {
                    $after = json_decode($c->services_after, true) ?: [];
                    foreach ($after as $a) {
                        if (!empty($a['id'])) $reprocessedItemIds[] = $a['id'];
                    }
                }
            }

            foreach ($services as $item) {
                $itemId = $item['id'] ?? null;
                // An item is additional only if: (a) its ID wasn't original, AND (b) it didn't come from a reprocess change
                if ($itemId && !in_array($itemId, $originalItemIds) && !in_array($itemId, $reprocessedItemIds)) {
                    // This item was added via a change request
                    $additionalFromServices[] = $item;
                }
            }
        }
        
        // Filter: only include items that still exist in current services (reprocess removes old items)
        $currentServiceIds = array_column($services, 'id');

        // Merge: items in current services take precedence (they have the latest data)
        // Over change request data (which can become stale)
        $allAdditional = [];
        $seenIds = [];
        foreach (array_merge($additionalFromServices, $additionalItems) as $item) {
            $itemId = $item['id'] ?? 0;
            // Skip items that were removed from services (e.g., by reprocess)
            if ($itemId && !in_array($itemId, $currentServiceIds)) {
                continue;
            }
            if (!isset($seenIds[$itemId])) {
                $seenIds[$itemId] = true;
                $allAdditional[] = $item;
            }
        }
        
        // Build enriched product response (full CUSTOMER FORM SPECIFICATIONS format)
        $specPartsMap = [
            'neckRibbingColor' => 'Neck Ribbing', 'neckTape' => 'Neck Tape', 'cuffs' => 'Cuffs',
            'slit' => 'Slit', 'pocket' => 'Pocket', 'collar' => 'Collar', 'neckShape' => 'Neck Shape',
            'cutType' => 'Cut Type', 'inner' => 'Inner', 'buttonColor' => 'Button',
            'zipperColor' => 'Zipper', 'innerStr' => 'Inner String', 'jersey' => 'Jersey',
            'defaultDesign' => 'Design', 'armsleeve' => 'Arm Sleeve', 'shoulder' => 'Shoulder',
            'sizeLabel' => 'Size Label'
        ];
        
        $productCards = [];
        foreach ($allAdditional as $item) {
            $name = $item['name'] ?? 'Unknown Product';
            $qty = $item['quantity'] ?? 0;
            $price = $item['totalPrice'] ?? 0;
            $sf = $item['sublimationForm'] ?? [];
            $mockup = $sf['mockup'] ?? $sf['mockupData'] ?? $sf['mockupUrl'] ?? null;
            $rawFabric = $sf['fabric'] ?? '';
            $fabric = is_string($rawFabric) ? $rawFabric : ($rawFabric['name'] ?? '');
            $sizes = $sf['sizes'] ?? [];
            
            // Build partRows from specs — check both normalized and non-normalized keys
            $partRows = [];
            $garmentType = $sf['garmentType'] ?? '';
            $garmentName = $sf['garment']['name'] ?? '';
            $gName = $garmentType ?: $garmentName;
            if ($gName) {
                $partRows[] = ['part' => 'Garment', 'detail' => $gName];
            }
            // Check both 'specs' (js key) and 'specifications' (normalized key)
            $specs = $sf['specs'] ?? $sf['specifications'] ?? [];
            foreach ($specs as $label => $val) {
                $v = is_string($val) ? trim($val) : '';
                if ($v !== '') {
                    $partRows[] = ['part' => $label, 'detail' => $v];
                }
            }
            // Parts added
            $partsAdded = $sf['parts'] ?? [];
            if (!empty($partsAdded)) {
                $partDetails = implode(', ', array_map(function($p) { return $p['name'] ?? ''; }, $partsAdded));
                if ($partDetails) {
                    $partRows[] = ['part' => 'Parts Added', 'detail' => $partDetails];
                }
            }
            
            // Use sublimateForm.roster if available (has full Excel columns), otherwise rebuild from sizes
            $rawRoster = $sf['roster'] ?? [];
            $roster = [];
            $cleanSizes = [];
            if (!empty($rawRoster)) {
                // Preserve full roster data including Excel columns
                foreach ($rawRoster as $r) {
                    $entry = [
                        'name' => $r['name'] ?? '',
                        'backNumber' => $r['backNumber'] ?? $r['number'] ?? '',
                        'size' => $r['size'] ?? '',
                        'number' => $r['number'] ?? 1,
                        'qty' => $r['qty'] ?? 1,
                    ];
                    // Preserve Excel columns for print slip / name list rendering
                    if (!empty($r['columns'])) {
                        $entry['columns'] = $r['columns'];
                    }
                    $roster[] = $entry;
                }
            } else {
                // Fallback: rebuild from sizes (backward compat for older data)
                foreach ($sizes as $s) {
                    if (!empty($s['name'])) {
                        $roster[] = [
                            'name' => $s['name'] ?? '',
                            'backNumber' => $s['backNumber'] ?? $s['bckNumber'] ?? $s['number'] ?? '',
                            'size' => $s['size'] ?? '',
                            'number' => $s['number'] ?? 1,
                            'qty' => $s['qty'] ?? 1,
                        ];
                    } else {
                        $cleanSizes[] = $s;
                    }
                }
            }
            
            $productCards[] = [
                'name' => $name,
                'quantity' => $qty,
                'totalPrice' => $price,
                'fabric' => $fabric,
                'sizes' => $cleanSizes,
                'roster' => $roster,
                'partRows' => $partRows,
                'hasMockup' => !empty($mockup),
                'mockupUrl' => $mockup ? (is_string($mockup) ? $mockup : (is_array($mockup) && !empty($mockup[0]['url']) ? $mockup[0]['url'] : null)) : null,
                'description' => $sf['description'] ?? '',
                'designer' => $sf['designer'] ?? '',
                'dateNeeded' => $sf['dateNeeded'] ?? '',
                'rosterMode' => $sf['rosterMode'] ?? false,
            ];
        }
        
        $salesNumber = $sale->sales_number ?? '';
        $customerName = $sale->customer_name ?? '';
        $agentName = $sale->sales_agent_name ?? '';
        
        return response()->json([
            'has_additional' => count($productCards) > 0,
            'products' => $productCards,
            'sales_number' => $salesNumber,
            'customer_name' => $customerName,
            'agent' => $agentName,
        ]);
    }
    
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
        $kanbanOrder = ['new', 'sample_approval', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $kanbanLabels = [
            'new'                => 'New',
            'sample_approval'    => 'Sample/Approval',
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
        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])
            ->whereIn('status', ['confirmed', 'in_production', 'pending', 'completed'])
            ->whereNull('archived_at');
        
        if (!$showAll) {
            $deptId = $deptCodeMap[$activeDept];
            $query->where('department_id', $deptId);
        }
        
        // Non-admin users only see their own sales
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $query->where('sales_agent_id', $user ? $user->id : null);
        }
        // Manager/admin can override photo-completeness restriction on moves
        $canOverride = $user && ($user->isAdmin() || $user->role === 'manager');
        
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

        // Count of archived projects (for the Archive link badge)
        $archivedCount = \App\Models\PrototypeSale::whereNotNull('archived_at')->count();
        
        // Determine which sales have approved additional products (via change requests)
        $approvedAdditions = [];
        if ($user && ($user->isAdmin() || $user->role === 'manager')) {
            $approvedChanges = \DB::table('prototype_sale_changes')
                ->where('status', 'approved')
                ->whereRaw('JSON_LENGTH(services_after) > JSON_LENGTH(services_before)')
                ->select('sale_id', 'services_before', 'services_after')
                ->get();
            foreach ($approvedChanges as $ac) {
                $before = json_decode($ac->services_before, true) ?: [];
                $after = json_decode($ac->services_after, true) ?: [];
                if (count($after) > count($before)) {
                    $approvedAdditions[$ac->sale_id] = true;
                }
            }
        }

        // Which visible sales have pending add-on requests or pending change requests
        // (Add Product from the sales page creates a change request — both count)
        $pendingAddonSaleIds = [];
        $pendingAddonCount = 0;
        if ($user && ($user->isAdmin() || $user->role === 'manager')) {
            $pendingAddons = \DB::table('sale_addon_requests')
                ->where('status', 'pending')
                ->select('sale_id')
                ->get();
            $pendingAddonSaleIds = $pendingAddons->pluck('sale_id')->map(fn($id) => (int) $id)->all();
            // Also count pending change requests (Add Product from sales page)
            $pendingChanges = \DB::table('prototype_sale_changes')
                ->where('status', 'pending')
                ->select('sale_id')
                ->get();
            $pendingChangeSaleIds = $pendingChanges->pluck('sale_id')->map(fn($id) => (int) $id)->all();
            $pendingAddonSaleIds = array_values(array_unique(array_merge($pendingAddonSaleIds, $pendingChangeSaleIds)));
            $pendingAddonCount = count($pendingAddonSaleIds);
        }
        
        return view('sales.prototype.kanban', compact(
            'columns', 'activeDept', 'allowedDepts', 'kanbanLabels', 'kanbanOrder',
            'showAll', 'departmentLabels', 'departmentColors', 'approvedAdditions',
            'canOverride', 'pendingAddonSaleIds', 'pendingAddonCount', 'archivedCount'
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
        $kanbanStatuses = ["new", "sample_approval", "design", "production", "quality_check", "ready_for_delivery", "delivered", "completed"];
        $kanbanLabels = [
            "new"                => "New",
            "sample_approval"    => "Sample/Approval",
            "design"            => "Design",
            "production"        => "Production",
            "quality_check"      => "QC",
            "ready_for_delivery" => "Ready",
            "delivered"         => "Delivered",
            "completed"         => "Completed",
        ];

        // Production stage tags → kanban status mapping (Production Status dropdown)
        $prodStageMap = [
            'FOR SAMPLE'   => 'sample_approval',
            'FOR APPROVAL' => 'sample_approval',
            'FOR FORMAT'   => 'design',
            'PRINTING'     => 'design',
            'PRESSING'     => 'production',
            'CUTTING'      => 'production',
            'SEWING'       => 'production',
            'QA'           => 'quality_check',
            'HOLD'         => 'new',
            'DISPATCH'     => 'ready_for_delivery',
            'UNPAID'       => 'delivered',
            'DONE'         => 'completed',
        ];

        // Reverse: kanban status → representative stage (for dropdown display)
        $statusToStage = [
            'new'                => 'HOLD',
            'sample_approval'    => 'FOR SAMPLE',
            'design'             => 'FOR FORMAT',
            'production'         => 'PRESSING',
            'quality_check'      => 'QA',
            'ready_for_delivery' => 'DISPATCH',
            'delivered'          => 'UNPAID',
            'completed'          => 'DONE',
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

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])->whereIn("status", ["confirmed", "in_production", "pending", "completed"]);
        
        // Non-admin users only see their own sales
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            $query->where('sales_agent_id', $user ? $user->id : null);
        }
        
        $sales = $query->orderBy("created_at", "desc")
            ->paginate(50);
        
        // Determine if current user is an agent-type user
        $isAgent = $user && !$user->isAdmin() && ($user->isSalesAgent() || $user->isSalesRepresentative());
        
        // Count pending changes per sale for manager notification badges
        $pendingCounts = [];
        $totalPending = 0;
        $pendingChangesList = collect();
        if ($user && ($user->isAdmin() || $user->role === 'manager')) {
            $saleIds = $sales->pluck('id');
            $pendingRows = \DB::table('prototype_sale_changes')
                ->where('status', 'pending')
                ->whereIn('sale_id', $saleIds)
                ->groupBy('sale_id')
                ->selectRaw('sale_id, COUNT(*) as pending_count')
                ->pluck('pending_count', 'sale_id');
            $pendingCounts = $pendingRows->toArray();
            $totalPending = array_sum($pendingCounts);
            
            // Get full pending changes data for the notification modal
            $pendingChangesList = \DB::table('prototype_sale_changes')
                ->join('prototype_sales', 'prototype_sale_changes.sale_id', '=', 'prototype_sales.id')
                ->where('prototype_sale_changes.status', 'pending')
                ->whereIn('prototype_sale_changes.sale_id', $saleIds)
                ->orderBy('prototype_sale_changes.created_at', 'desc')
                ->select([
                    'prototype_sale_changes.id as change_id',
                    'prototype_sale_changes.sale_id',
                    'prototype_sale_changes.change_summary',
                    'prototype_sale_changes.total_before',
                    'prototype_sale_changes.total_after',
                    'prototype_sale_changes.created_at as change_created_at',
                    'prototype_sales.sales_number',
                    'prototype_sales.customer_name',
                ])
                ->limit(50)
                ->get();
        }
        
        // Get last notification info per sale+type for cooldown display
        $lastNotifs = collect();
        if ($user && ($user->isAdmin() || $user->role === 'manager')) {
            $saleIds = $sales->pluck('id');
            $lastNotifs = \App\Models\SaleNotification::whereIn('sale_id', $saleIds)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('sale_id')
                ->map(function ($group) {
                    return $group->groupBy('type')->map(function ($typeGroup) {
                        $latest = $typeGroup->first();
                        return [
                            'last_at' => $latest->created_at,
                            'reminder_count' => $typeGroup->max('reminder_count'),
                        ];
                    });
                });
        }

        return view("sales.prototype.list", compact(
            "sales", "kanbanStatuses", "kanbanLabels", "prodStageMap", "statusToStage",
            "departmentLabels", "departmentColors", "isAgent",
            "pendingCounts", "totalPending", "pendingChangesList",
            "lastNotifs"
        ));
    }

    public function updateKanbanStatus(Request $request, $id)
    {
        $request->validate([
            'kanban_status' => 'required|in:new,sample_approval,design,production,quality_check,ready_for_delivery,delivered,completed'
        ]);
        
        $sale = \App\Models\PrototypeSale::findOrFail($id);

        // PAYMENT LOCK (server-side): cannot move to Completed while there is a pending balance due
        $balanceDue = $sale->balance_due_computed;
        if ($request->kanban_status === 'completed' && $balanceDue > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Hindi ma-move sa Completed: may pending balance pa na ₱' . number_format($balanceDue, 2) . '. Kailangan munang mabayaran bago i-DONE.',
            ], 422);
        }

        // PHOTO LOCK (server-side): non-manager users cannot move a sale to Design and beyond
        // until both file screenshot + approved sample color are uploaded.
        $lockedStatuses = ['sample_approval', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $user = auth()->user();
        $canOverride = $user && ($user->isAdmin() || $user->role === 'manager');
        if (in_array($request->kanban_status, $lockedStatuses) && !$canOverride) {
            $dImgs = is_string($sale->design_images) ? json_decode($sale->design_images, true) : ($sale->design_images ?? []);
            $hasFileShot = collect($dImgs)->contains('type', 'file_screenshot');
            $hasColorShot = collect($dImgs)->contains('type', 'sample_color');
            if (!($hasFileShot && $hasColorShot)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hindi ma-move: kulang pang photos (File Screenshot / Sample Color). Kailangan muna kumpleto bago lumipat sa ' . $request->kanban_status . '.',
                    ], 422);
                }
                return redirect()->back()->with('error', 'Hindi ma-move: kulang pang photos (File Screenshot / Sample Color).');
            }
        }

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

        // PAYMENT LOCK (server-side): cannot mark as DONE/completed while there is a pending balance due
        $balanceDue = $sale->balance_due_computed;
        if ($request->kanban_status === 'completed' && $balanceDue > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Hindi ma-mark na DONE: may pending balance pa na ₱' . number_format($balanceDue, 2) . '. Kailangan munang mabayaran bago i-DONE.',
            ], 422);
        }

        // PHOTO LOCK (server-side): non-manager users cannot move a sale to Design and beyond
        // until both file screenshot + approved sample color are uploaded.
        $lockedStatuses = ['sample_approval', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $user = auth()->user();
        $canOverride = $user && ($user->isAdmin() || $user->role === 'manager');
        if (in_array($request->kanban_status, $lockedStatuses) && !$canOverride) {
            $dImgs = is_string($sale->design_images) ? json_decode($sale->design_images, true) : ($sale->design_images ?? []);
            $hasFileShot = collect($dImgs)->contains('type', 'file_screenshot');
            $hasColorShot = collect($dImgs)->contains('type', 'sample_color');
            if (!($hasFileShot && $hasColorShot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hindi ma-move: kulang pang photos (File Screenshot / Sample Color). Kailangan muna kumpleto bago lumipat sa ' . $request->kanban_status . '.',
                ], 422);
            }
        }

        $sale->kanban_status = $request->kanban_status;
        if ($request->filled('production_stage')) {
            $sale->production_stage = $request->production_stage;
        }
        $sale->save();

        return response()->json(['success' => true, 'status' => $sale->kanban_status, 'production_stage' => $sale->production_stage]);
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

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])->whereIn('status', ['pending', 'confirmed', 'in_production', 'completed']);
        
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
                $totalQty = 0;
                foreach ($services as $s) {
                    if (is_string($s)) {
                        $items[] = ['name' => $s, 'qty' => 1];
                        $totalQty += 1;
                    } elseif (is_array($s)) {
                        $items[] = $s;
                        $totalQty += (int)($s['quantity'] ?? $s['qty'] ?? 1);
                    }
                }
            }

            // Mockup thumbnail (same logic as manager list)
            $mockups = is_string($p->mockup_images) ? json_decode($p->mockup_images, true) : ($p->mockup_images ?? []);
            $firstMockup = $mockups[0] ?? null;
            $firstMockupUrl = is_string($firstMockup) ? $firstMockup : ($firstMockup['url'] ?? '');

            // Description summary (same as manager list)
            $descParts = [];
            foreach ($items as $it) {
                if (is_array($it) && !empty($it['name'])) {
                    $descParts[] = $it['name'];
                }
            }
            $description = $descParts ? implode(' + ', array_slice($descParts, 0, 2)) : '';

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
                'production_stage' => $p->production_stage,
                'services' => $items,
                'services_raw' => $services,
                'total_qty' => $totalQty,
                'mockup_url' => $firstMockupUrl,
                'description' => $description,
                'date_needed' => $p->estimated_completion_date,
                'estimated_completion_date' => $p->estimated_completion_date,
                'rescheduled_date' => $p->rescheduled_date,
                'created_at' => $p->created_at,
                'status' => $p->status,
            ];
        });
        
        return response()->json(['projects' => $projectsFormatted]);
    }

    /**
     * Archive a completed project — sets archived_at. Only completed sales can be archived.
     */
    public function archive(Request $request, $id)
    {
        $sale = \App\Models\PrototypeSale::findOrFail($id);

        // Managers/admins only
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->role === 'manager')) {
            return response()->json(['success' => false, 'message' => 'Only managers can archive projects.'], 403);
        }

        if ($sale->kanban_status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Only Completed projects can be archived.'], 422);
        }

        if ($sale->archived_at) {
            return response()->json(['success' => false, 'message' => 'Project is already archived.'], 422);
        }

        $sale->archived_at = now();
        $sale->save();

        return response()->json([
            'success' => true,
            'message' => '📦 Project ' . ($sale->sales_number ?: '#' . $sale->id) . ' archived.',
        ]);
    }

    /**
     * Archive page — list of archived projects.
     */
    public function archived(Request $request)
    {
        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])
            ->whereNotNull('archived_at')
            ->orderByDesc('archived_at');

        // Department filter
        if ($request->filled('department') && $request->department !== 'all') {
            $query->where('department_id', $request->department);
        }

        $sales = $query->paginate(25)->withQueryString();

        // Reuse the list view data helpers where possible
        $departments = \DB::table('sales_departments')->where('is_active', true)->get();

        return view('sales.prototype.archived', compact('sales', 'departments'));
    }

    /**
     * Restore an archived project back to the kanban board (Completed column).
     */
    public function restore(Request $request, $id)
    {
        $sale = \App\Models\PrototypeSale::findOrFail($id);

        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->role === 'manager')) {
            return response()->json(['success' => false, 'message' => 'Only managers can restore projects.'], 403);
        }

        if (!$sale->archived_at) {
            return response()->json(['success' => false, 'message' => 'Project is not archived.'], 422);
        }

        $sale->archived_at = null;
        $sale->save();

        return response()->json([
            'success' => true,
            'message' => '↩ Project restored to the kanban board.',
        ]);
    }

    /**
     * Reschedule a project on the calendar — stores the new date in
     * rescheduled_date WITHOUT touching the original estimated_completion_date.
     */
    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $sale = \App\Models\PrototypeSale::findOrFail($id);

        // Only managers/admins/staff can reschedule
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only managers can reschedule projects.',
            ], 403);
        }

        $newDate = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
        $originalDate = $sale->estimated_completion_date ? \Carbon\Carbon::parse($sale->estimated_completion_date)->format('Y-m-d') : null;

        $sale->rescheduled_date = $newDate;
        $sale->save();

        return response()->json([
            'success' => true,
            'message' => 'Project moved to ' . \Carbon\Carbon::parse($newDate)->format('M d, Y') . '. Original date (' . ($originalDate ? \Carbon\Carbon::parse($originalDate)->format('M d, Y') : 'none') . ') is kept.',
            'rescheduled_date' => $newDate,
            'original_date' => $originalDate,
        ]);
    }

    public function verifyPayment(Request $request, $id)
    {
        // If a payment_id is specified, verify that specific payment
        $paymentId = $request->payment_id;

        if ($paymentId) {
            return $this->verifyIndividualPayment($request, $id, $paymentId);
        }

        // No payment_id = verify/reject the initial deposit on prototype_sales directly
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        $action = $request->action;
        $remark = $request->remark;

        if ($action === 'verify') {
            // Require a tagged account and positive deposit before verifying
            if (!$sale->payment_account_id) {
                return response()->json(['error' => 'Cannot verify — no payment account assigned. Please re-tag an account first.'], 400);
            }
            if ((float) $sale->deposit_paid <= 0) {
                return response()->json(['error' => 'Cannot verify — no deposit amount recorded.'], 400);
            }

            // Determine if deposit equals full amount
            $newStatus = ($sale->deposit_paid >= $sale->total_amount) ? 'full_payment_verified' : 'down_payment_verified';

            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_status' => $newStatus,
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

            $msg = 'Payment verified!';
        } elseif ($action === 'reject') {
            if (!$remark || !trim($remark)) {
                return response()->json(['error' => 'Rejection reason is required.'], 400);
            }
            // Two-verifier approval: first verifier only requests rejection
            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_status' => 'reject_pending',
                'reject_requested_by' => auth()->id(),
                'reject_requested_at' => now(),
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $sale->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'reject_requested',
                'remarks' => $remark,
            ]);

            $msg = 'Rejection requested — waiting for a second verifier to confirm.';
        } elseif ($action === 'confirm_reject') {
            if (!$remark || !trim($remark)) {
                return response()->json(['error' => 'Confirmation reason is required.'], 400);
            }
            if ($sale->payment_status !== 'reject_pending') {
                return response()->json(['error' => 'This rejection is not pending approval.'], 400);
            }
            if ($sale->reject_requested_by == auth()->id()) {
                return response()->json(['error' => 'You cannot confirm your own rejection request — another verifier is required.'], 400);
            }

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

            $msg = 'Rejection confirmed — payment is now rejected.';
        } elseif ($action === 'cancel_reject') {
            if ($sale->payment_status !== 'reject_pending') {
                return response()->json(['error' => 'This rejection is not pending approval.'], 400);
            }
            if ($sale->reject_requested_by != auth()->id()) {
                return response()->json(['error' => 'Only the verifier who requested the rejection can cancel it.'], 400);
            }

            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_status' => 'pending',
                'reject_requested_by' => null,
                'reject_requested_at' => null,
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $sale->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'reject_cancelled',
                'remarks' => $remark,
            ]);

            $msg = 'Rejection request cancelled — back to pending.';
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
        } elseif ($action === 're_tag') {
            $newAccountId = $request->new_account_id;
            if (!$newAccountId) {
                return response()->json(['error' => 'Please select a new account'], 400);
            }

            $oldAccount = \App\Models\PaymentAccount::find($sale->payment_account_id);
            $newAccount = \App\Models\PaymentAccount::find($newAccountId);

            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_account_id' => $newAccountId,
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
            $newAmount = $request->new_amount;

            $changes = [];
            $oldParts = [];
            $newParts = [];

            if ($newRef && $newRef !== $sale->reference_number) {
                $changes['reference_number'] = $newRef;
                $oldParts[] = 'Ref: ' . ($sale->reference_number ?: '—');
                $newParts[] = 'Ref: ' . $newRef;
            }
            if ($newDate && $newDate !== optional($sale->payment_date)->format('Y-m-d')) {
                $changes['payment_date'] = $newDate;
                $oldParts[] = 'Date: ' . (optional($sale->payment_date)->format('Y-m-d') ?: '—');
                $newParts[] = 'Date: ' . $newDate;
            }
            if ($newAmount !== null && $newAmount !== '' && (float) $newAmount != (float) $sale->deposit_paid) {
                $changes['deposit_paid'] = $newAmount;
                $changes['balance_due'] = max($sale->total_amount - (float) $newAmount, 0);
                $oldParts[] = 'Amount: ₱' . number_format((float) $sale->deposit_paid, 2);
                $newParts[] = 'Amount: ₱' . number_format((float) $newAmount, 2);
            }

            if (empty($changes)) {
                return response()->json(['error' => 'No changes to save — please fill in Reference #, Payment Date, or Amount.'], 400);
            }

            // Two-verifier approval: store as pending edit, apply after a second verifier confirms
            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_status' => 'edit_pending',
                'edit_requested_by' => auth()->id(),
                'edit_requested_at' => now(),
                'edit_original_status' => $sale->payment_status,
                'pending_reference_number' => $changes['reference_number'] ?? null,
                'pending_payment_date' => $changes['payment_date'] ?? null,
                'pending_amount' => $changes['deposit_paid'] ?? null,
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $sale->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'edit_requested',
                'old_value' => implode(', ', $oldParts),
                'new_value' => implode(', ', $newParts),
                'remarks' => $remark,
            ]);

            $msg = 'Edit requested — awaiting a second verifier to confirm.';
        } elseif ($action === 'confirm_edit') {
            if ($sale->payment_status !== 'edit_pending') {
                return response()->json(['error' => 'This sale has no pending edit request.'], 400);
            }
            if (!$remark || !trim($remark)) {
                return response()->json(['error' => 'Confirmation reason is required.'], 400);
            }
            if ($sale->edit_requested_by == auth()->id()) {
                return response()->json(['error' => 'You cannot confirm your own edit request — another verifier is required.'], 400);
            }

            $changes = [];
            $oldParts = [];
            $newParts = [];

            if ($sale->pending_reference_number) {
                $changes['reference_number'] = $sale->pending_reference_number;
                $oldParts[] = 'Ref: ' . ($sale->reference_number ?: '—');
                $newParts[] = 'Ref: ' . $sale->pending_reference_number;
            }
            if ($sale->pending_payment_date) {
                $changes['payment_date'] = $sale->pending_payment_date;
                $oldParts[] = 'Date: ' . (optional($sale->payment_date)->format('Y-m-d') ?: '—');
                $newParts[] = 'Date: ' . $sale->pending_payment_date;
            }
            if ($sale->pending_amount !== null) {
                $changes['deposit_paid'] = $sale->pending_amount;
                $changes['balance_due'] = max($sale->total_amount - (float) $sale->pending_amount, 0);
                $oldParts[] = 'Amount: ₱' . number_format((float) $sale->deposit_paid, 2);
                $newParts[] = 'Amount: ₱' . number_format((float) $sale->pending_amount, 2);
            }

            $changes['payment_status'] = $sale->edit_original_status ?: 'pending';
            $changes['edit_requested_by'] = null;
            $changes['edit_requested_at'] = null;
            $changes['edit_original_status'] = null;
            $changes['pending_reference_number'] = null;
            $changes['pending_payment_date'] = null;
            $changes['pending_amount'] = null;
            $changes['updated_at'] = now();

            \DB::table('prototype_sales')->where('id', $id)->update($changes);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $sale->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'edited_ref',
                'old_value' => implode(', ', $oldParts),
                'new_value' => implode(', ', $newParts),
                'remarks' => $remark,
            ]);

            $msg = 'Edit confirmed and applied.';
        } elseif ($action === 'cancel_edit') {
            if ($sale->payment_status !== 'edit_pending') {
                return response()->json(['error' => 'This sale has no pending edit request.'], 400);
            }

            \DB::table('prototype_sales')->where('id', $id)->update([
                'payment_status' => $sale->edit_original_status ?: 'pending',
                'edit_requested_by' => null,
                'edit_requested_at' => null,
                'edit_original_status' => null,
                'pending_reference_number' => null,
                'pending_payment_date' => null,
                'pending_amount' => null,
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_account_id' => $sale->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'edit_cancelled',
                'old_value' => 'Pending edit',
                'new_value' => 'Cancelled',
                'remarks' => $remark,
            ]);

            $msg = 'Edit request cancelled.';
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
     * Verify/reject an individual payment record.
     */
    protected function verifyIndividualPayment(Request $request, $saleId, $paymentId)
    {
        $payment = \App\Models\PrototypePayment::findOrFail($paymentId);
        $sale = \DB::table('prototype_sales')->find($saleId);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        $action = $request->action;
        $remark = $request->remark;

        if ($action === 'verify') {
            // Require account and amount before verifying
            if (!$payment->payment_account_id) {
                return response()->json(['error' => 'Cannot verify — no payment account assigned. Please re-tag an account first.'], 400);
            }
            if ((float) $payment->amount <= 0) {
                return response()->json(['error' => 'Cannot verify — payment amount must be greater than 0.'], 400);
            }

            // Determine payment status based on the payment's own type
            if ($payment->payment_type === 'down_payment') {
                $newStatus = 'down_payment_verified';
            } elseif (in_array($payment->payment_type, ['fullpayment', 'full_payment'])) {
                $newStatus = 'full_payment_verified';
            } else {
                $newStatus = 'additional_payment_verified';
            }

            $payment->update([
                'payment_status' => $newStatus,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // Recompute sale-level deposit_paid from all verified payments
            $totalVerified = \App\Models\PrototypePayment::where('prototype_sale_id', $saleId)
                ->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])
                ->sum('amount');
            // Net paid = verified payments minus completed refunds (refunds must reduce what's owed/overpaid)
            $totalRefunded = \App\Models\PrototypeRefund::where('prototype_sale_id', $saleId)
                ->where('refund_status', 'completed')
                ->sum('refund_amount');
            $netPaid = max($totalVerified - $totalRefunded, 0);
            $newBalanceDue = max($sale->total_amount - $netPaid, 0);
            $newOverpayment = $netPaid > $sale->total_amount ? ($netPaid - $sale->total_amount) : 0;
            \DB::table('prototype_sales')->where('id', $saleId)->update([
                'deposit_paid' => $totalVerified,
                'balance_due' => $newBalanceDue,
                'overpayment' => $newOverpayment,
                'updated_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $saleId,
                'payment_id' => $payment->id,
                'payment_account_id' => $payment->payment_account_id,
                'user_id' => auth()->id(),
                'action' => $newStatus,
                'remarks' => $remark,
            ]);

            $msg = 'Payment verified — ' . str_replace('_', ' ', $newStatus) . '!';

        } elseif ($action === 'reject') {
            if (!$remark || !trim($remark)) {
                return response()->json(['error' => 'Rejection reason is required.'], 400);
            }
            // Two-verifier approval: first verifier only requests rejection
            $payment->update([
                'payment_status' => 'reject_pending',
                'reject_requested_by' => auth()->id(),
                'reject_requested_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $saleId,
                'payment_id' => $payment->id,
                'payment_account_id' => $payment->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'reject_requested',
                'remarks' => $remark,
            ]);

            $msg = 'Rejection requested — waiting for a second verifier to confirm.';

        } elseif ($action === 'confirm_reject') {
            if (!$remark || !trim($remark)) {
                return response()->json(['error' => 'Confirmation reason is required.'], 400);
            }
            if ($payment->payment_status !== 'reject_pending') {
                return response()->json(['error' => 'This rejection is not pending approval.'], 400);
            }
            if ($payment->reject_requested_by == auth()->id()) {
                return response()->json(['error' => 'You cannot confirm your own rejection request — another verifier is required.'], 400);
            }

            $payment->update([
                'payment_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $saleId,
                'payment_id' => $payment->id,
                'payment_account_id' => $payment->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'rejected',
                'remarks' => $remark,
            ]);

            $msg = 'Rejection confirmed — payment is now rejected.';

        } elseif ($action === 'cancel_reject') {
            if ($payment->payment_status !== 'reject_pending') {
                return response()->json(['error' => 'This rejection is not pending approval.'], 400);
            }
            if ($payment->reject_requested_by != auth()->id()) {
                return response()->json(['error' => 'Only the verifier who requested the rejection can cancel it.'], 400);
            }

            $payment->update([
                'payment_status' => 'pending',
                'reject_requested_by' => null,
                'reject_requested_at' => null,
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $saleId,
                'payment_id' => $payment->id,
                'payment_account_id' => $payment->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'reject_cancelled',
                'remarks' => $remark,
            ]);

            $msg = 'Rejection request cancelled — back to pending.';

        } elseif ($action === 're_tag') {
            $newAccountId = $request->new_account_id;
            if (!$newAccountId) {
                return response()->json(['error' => 'Please select a new account'], 400);
            }

            $oldAccount = \App\Models\PaymentAccount::find($payment->payment_account_id);
            $newAccount = \App\Models\PaymentAccount::find($newAccountId);

            $payment->update([
                'payment_account_id' => $newAccountId,
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $saleId,
                'payment_id' => $payment->id,
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
            $newAmount = $request->new_amount;

            $changes = [];
            $oldParts = [];
            $newParts = [];

            if ($newRef && $newRef !== $payment->reference_number) {
                $changes['reference_number'] = $newRef;
                $oldParts[] = 'Ref: ' . ($payment->reference_number ?: '—');
                $newParts[] = 'Ref: ' . $newRef;
            }
            if ($newDate && $newDate !== optional($payment->payment_date)->format('Y-m-d')) {
                $changes['payment_date'] = $newDate;
                $oldParts[] = 'Date: ' . (optional($payment->payment_date)->format('Y-m-d') ?: '—');
                $newParts[] = 'Date: ' . $newDate;
            }
            if ($newAmount !== null && $newAmount !== '' && (float) $newAmount != (float) $payment->amount) {
                $changes['amount'] = $newAmount;
                $oldParts[] = 'Amount: ₱' . number_format((float) $payment->amount, 2);
                $newParts[] = 'Amount: ₱' . number_format((float) $newAmount, 2);
            }

            if (empty($changes)) {
                return response()->json(['error' => 'No changes to save — please fill in Reference #, Payment Date, or Amount.'], 400);
            }

            // Two-verifier approval: store as pending edit, apply after a second verifier confirms
            $payment->update([
                'payment_status' => 'edit_pending',
                'edit_requested_by' => auth()->id(),
                'edit_requested_at' => now(),
                'edit_original_status' => $payment->payment_status,
                'pending_reference_number' => $changes['reference_number'] ?? null,
                'pending_payment_date' => $changes['payment_date'] ?? null,
                'pending_amount' => $changes['amount'] ?? null,
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $saleId,
                'payment_id' => $payment->id,
                'payment_account_id' => $payment->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'edit_requested',
                'old_value' => implode(', ', $oldParts),
                'new_value' => implode(', ', $newParts),
                'remarks' => $remark,
            ]);

            $msg = 'Edit requested — awaiting a second verifier to confirm.';
        } elseif ($action === 'confirm_edit') {
            if ($payment->payment_status !== 'edit_pending') {
                return response()->json(['error' => 'This payment has no pending edit request.'], 400);
            }
            if (!$remark || !trim($remark)) {
                return response()->json(['error' => 'Confirmation reason is required.'], 400);
            }
            if ($payment->edit_requested_by == auth()->id()) {
                return response()->json(['error' => 'You cannot confirm your own edit request — another verifier is required.'], 400);
            }

            $changes = [];
            $oldParts = [];
            $newParts = [];

            if ($payment->pending_reference_number) {
                $changes['reference_number'] = $payment->pending_reference_number;
                $oldParts[] = 'Ref: ' . ($payment->reference_number ?: '—');
                $newParts[] = 'Ref: ' . $payment->pending_reference_number;
            }
            if ($payment->pending_payment_date) {
                $changes['payment_date'] = $payment->pending_payment_date;
                $oldParts[] = 'Date: ' . (optional($payment->payment_date)->format('Y-m-d') ?: '—');
                $newParts[] = 'Date: ' . $payment->pending_payment_date;
            }
            if ($payment->pending_amount !== null) {
                $changes['amount'] = $payment->pending_amount;
                $oldParts[] = 'Amount: ₱' . number_format((float) $payment->amount, 2);
                $newParts[] = 'Amount: ₱' . number_format((float) $payment->pending_amount, 2);
            }

            $changes['payment_status'] = $payment->edit_original_status ?: 'pending';
            $changes['edit_requested_by'] = null;
            $changes['edit_requested_at'] = null;
            $changes['edit_original_status'] = null;
            $changes['pending_reference_number'] = null;
            $changes['pending_payment_date'] = null;
            $changes['pending_amount'] = null;

            $payment->update($changes);

            // Recompute sale-level deposit_paid/balance_due if amount changed
            if (array_key_exists('amount', $changes)) {
                $totalVerified = \App\Models\PrototypePayment::where('prototype_sale_id', $saleId)
                    ->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])
                    ->sum('amount');
                // Net paid = verified payments minus completed refunds
                $totalRefunded = \App\Models\PrototypeRefund::where('prototype_sale_id', $saleId)
                    ->where('refund_status', 'completed')
                    ->sum('refund_amount');
                $netPaid = max($totalVerified - $totalRefunded, 0);
                $newBalanceDue = max($sale->total_amount - $netPaid, 0);
                $newOverpayment = $netPaid > $sale->total_amount ? ($netPaid - $sale->total_amount) : 0;
                \DB::table('prototype_sales')->where('id', $saleId)->update([
                    'deposit_paid' => $totalVerified,
                    'balance_due' => $newBalanceDue,
                    'overpayment' => $newOverpayment,
                    'updated_at' => now(),
                ]);
            }

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $saleId,
                'payment_id' => $payment->id,
                'payment_account_id' => $payment->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'edited_ref',
                'old_value' => implode(', ', $oldParts),
                'new_value' => implode(', ', $newParts),
                'remarks' => $remark,
            ]);

            $msg = 'Edit confirmed and applied.';
        } elseif ($action === 'cancel_edit') {
            if ($payment->payment_status !== 'edit_pending') {
                return response()->json(['error' => 'This payment has no pending edit request.'], 400);
            }

            $payment->update([
                'payment_status' => $payment->edit_original_status ?: 'pending',
                'edit_requested_by' => null,
                'edit_requested_at' => null,
                'edit_original_status' => null,
                'pending_reference_number' => null,
                'pending_payment_date' => null,
                'pending_amount' => null,
            ]);

            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $saleId,
                'payment_id' => $payment->id,
                'payment_account_id' => $payment->payment_account_id,
                'user_id' => auth()->id(),
                'action' => 'edit_cancelled',
                'old_value' => 'Pending edit',
                'new_value' => 'Cancelled',
                'remarks' => $remark,
            ]);

            $msg = 'Edit request cancelled.';
        } else {
            return response()->json(['error' => 'Invalid action'], 400);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('sales.prototype.verification')->with('success', $msg);
    }

    /**
     * Payment verification dashboard - shows all pending and recent payments.
     */
    public function paymentVerification()
    {
        // Include initial deposits from prototype_sales that don't have matching prototype_payments yet
        $pendingPayments = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as verifier', 'prototype_payments.verified_by', '=', 'verifier.id')
            ->select([
                'prototype_payments.*',
                'prototype_payments.id as payment_id',
                'prototype_payments.screenshot_path as payment_screenshot_path',
                'prototype_sales.id as sale_id',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.total_amount',
                'prototype_sales.deposit_paid',
                'prototype_sales.sales_agent_id',
                'payment_accounts.name as account_name',
                'payment_accounts.user_id as account_user_id',
                'verifier.name as verified_by_name',
                \DB::raw("'prototype_payments' as payment_source"),
            ]);

        // Get sales with pending initial deposits that don't have any prototype_payment yet
        $initialDeposits = \DB::table('prototype_sales')
            ->leftJoin('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as verifier', 'prototype_sales.verified_by', '=', 'verifier.id')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('prototype_payments')
                    ->whereColumn('prototype_payments.prototype_sale_id', '=', 'prototype_sales.id');
            })
            ->where('prototype_sales.deposit_paid', '>', 0)
            ->whereNull('prototype_sales.deleted_at')
            ->where(function ($q) {
                $q->where('prototype_sales.payment_status', 'pending')
                  ->orWhereNull('prototype_sales.payment_status');
            })
            ->select([
                \DB::raw('NULL as id'),
                \DB::raw('prototype_sales.id as prototype_sale_id'),
                \DB::raw("'down_payment' as payment_type"),
                \DB::raw('prototype_sales.deposit_paid as amount'),
                \DB::raw('prototype_sales.payment_method as payment_method'),
                \DB::raw('prototype_sales.payment_account_id as payment_account_id'),
                \DB::raw('prototype_sales.reference_number as reference_number'),
                \DB::raw('prototype_sales.payment_screenshot_path as screenshot_path'),
                \DB::raw('prototype_sales.payment_status as payment_status'),
                \DB::raw('NULL as verified_by'),
                \DB::raw('NULL as verified_at'),
                \DB::raw('NULL as reject_requested_by'),
                \DB::raw('NULL as reject_requested_at'),
                \DB::raw('NULL as edit_requested_by'),
                \DB::raw('NULL as edit_requested_at'),
                \DB::raw('NULL as edit_original_status'),
                \DB::raw('NULL as pending_reference_number'),
                \DB::raw('NULL as pending_payment_date'),
                \DB::raw('NULL as pending_amount'),
                \DB::raw('prototype_sales.payment_date as payment_date'),
                \DB::raw('NULL as notes'),
                \DB::raw('prototype_sales.created_at as created_at'),
                \DB::raw('prototype_sales.updated_at as updated_at'),
                \DB::raw('NULL as payment_id'),
                \DB::raw('prototype_sales.payment_screenshot_path as payment_screenshot_path'),
                \DB::raw('prototype_sales.id as sale_id'),
                \DB::raw('prototype_sales.sales_number as sales_number'),
                \DB::raw('prototype_sales.customer_name as customer_name'),
                \DB::raw('prototype_sales.total_amount as total_amount'),
                \DB::raw('prototype_sales.deposit_paid as deposit_paid'),
                \DB::raw('prototype_sales.sales_agent_id as sales_agent_id'),
                \DB::raw('payment_accounts.name as account_name'),
                \DB::raw('payment_accounts.user_id as account_user_id'),
                \DB::raw('NULL as verified_by_name'),
                \DB::raw("'initial_deposit' as payment_source"),
            ]);

        // Merge: pending/additional deposits from prototype_payments + initial deposits from prototype_sales
        $pendingPayments = $pendingPayments
            ->whereIn('prototype_payments.payment_status', ['pending', 'rejected'])
            ->union($initialDeposits)
            ->orderBy('created_at', 'desc')
            ->get();

        // Verified payments remain unchanged (querying only prototype_payments)
        $verifiedPayments = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as verifier', 'prototype_payments.verified_by', '=', 'verifier.id')
            ->select([
                'prototype_payments.*',
                'prototype_payments.id as payment_id',
                'prototype_payments.screenshot_path as payment_screenshot_path',
                'prototype_sales.id as sale_id',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.total_amount',
                'prototype_sales.deposit_paid',
                'prototype_sales.sales_agent_id',
                'payment_accounts.name as account_name',
                'verifier.name as verified_by_name',
            ])
            ->whereIn('prototype_payments.payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])
            ->orderBy('prototype_payments.verified_at', 'desc')
            ->limit(50)
            ->get();

        $accounts = \App\Models\PaymentAccount::with('user')->where('is_active', true)->get();

        // Pending rejections awaiting a second verifier (two-verifier approval)
        $pendingRejections = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as requester', 'prototype_payments.reject_requested_by', '=', 'requester.id')
            ->where('prototype_payments.payment_status', 'reject_pending')
            ->select([
                'prototype_sales.id as sale_id',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.total_amount',
                'prototype_sales.deposit_paid',
                'prototype_payments.amount',
                'prototype_payments.payment_method',
                'prototype_payments.reference_number',
                'prototype_payments.payment_date',
                'prototype_payments.screenshot_path as payment_screenshot_path',
                'prototype_payments.reject_requested_by',
                'prototype_payments.reject_requested_at',
                'payment_accounts.name as account_name',
                'requester.name as requester_name',
                \DB::raw("'additional_payment' as payment_source"),
                'prototype_payments.id as payment_id',
            ])
            ->union(\DB::table('prototype_sales')
                ->leftJoin('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
                ->leftJoin('users as requester', 'prototype_sales.reject_requested_by', '=', 'requester.id')
                ->where('prototype_sales.payment_status', 'reject_pending')
                ->whereNull('prototype_sales.deleted_at')
                ->select([
                    'prototype_sales.id as sale_id',
                    'prototype_sales.sales_number',
                    'prototype_sales.customer_name',
                    'prototype_sales.total_amount',
                    'prototype_sales.deposit_paid',
                    \DB::raw('prototype_sales.deposit_paid as amount'),
                    'prototype_sales.payment_method',
                    'prototype_sales.reference_number',
                    'prototype_sales.payment_date',
                    'prototype_sales.payment_screenshot_path',
                    'prototype_sales.reject_requested_by',
                    'prototype_sales.reject_requested_at',
                    'payment_accounts.name as account_name',
                    'requester.name as requester_name',
                    \DB::raw("'initial_deposit' as payment_source"),
                    \DB::raw('NULL as payment_id'),
                ]))
            ->orderBy('reject_requested_at', 'desc')
            ->get();

        // Pending edit requests (change ref/amount/date) awaiting a second verifier
        $pendingEdits = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as requester', 'prototype_payments.edit_requested_by', '=', 'requester.id')
            ->where('prototype_payments.payment_status', 'edit_pending')
            ->select([
                'prototype_sales.id as sale_id',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.total_amount',
                'prototype_sales.deposit_paid',
                'prototype_payments.amount',
                'prototype_payments.payment_method',
                'prototype_payments.reference_number',
                'prototype_payments.payment_date',
                'prototype_payments.screenshot_path as payment_screenshot_path',
                'prototype_payments.edit_requested_by',
                'prototype_payments.edit_requested_at',
                'prototype_payments.edit_original_status',
                'prototype_payments.pending_reference_number',
                'prototype_payments.pending_payment_date',
                'prototype_payments.pending_amount',
                'payment_accounts.name as account_name',
                'requester.name as requester_name',
                \DB::raw("'additional_payment' as payment_source"),
                'prototype_payments.id as payment_id',
            ])
            ->union(\DB::table('prototype_sales')
                ->leftJoin('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
                ->leftJoin('users as requester', 'prototype_sales.edit_requested_by', '=', 'requester.id')
                ->where('prototype_sales.payment_status', 'edit_pending')
                ->whereNull('prototype_sales.deleted_at')
                ->select([
                    'prototype_sales.id as sale_id',
                    'prototype_sales.sales_number',
                    'prototype_sales.customer_name',
                    'prototype_sales.total_amount',
                    'prototype_sales.deposit_paid',
                    \DB::raw('prototype_sales.deposit_paid as amount'),
                    'prototype_sales.payment_method',
                    'prototype_sales.reference_number',
                    'prototype_sales.payment_date',
                    'prototype_sales.payment_screenshot_path',
                    'prototype_sales.edit_requested_by',
                    'prototype_sales.edit_requested_at',
                    'prototype_sales.edit_original_status',
                    'prototype_sales.pending_reference_number',
                    'prototype_sales.pending_payment_date',
                    'prototype_sales.pending_amount',
                    'payment_accounts.name as account_name',
                    'requester.name as requester_name',
                    \DB::raw("'initial_deposit' as payment_source"),
                    \DB::raw('NULL as payment_id'),
                ]))
            ->orderBy('edit_requested_at', 'desc')
            ->get();

        return view('sales.prototype.verification', compact('pendingPayments', 'verifiedPayments', 'accounts', 'pendingRejections', 'pendingEdits'));
    }

    /**
     * Cash flow view - per account breakdown.
     */
    public function cashFlow(Request $request)
    {
        $accountId = $request->account_id;
        $agentId = $request->agent_id;
        $method = $request->method;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $search = trim((string) $request->search);

        $accounts = \App\Models\PaymentAccount::where('is_active', true)->get();
        $agents = \App\Models\User::where('role', 'sales_agent')->orderBy('name')->get();
        $paymentMethods = ['cash', 'bank_transfer', 'gcash', 'paymaya', 'credit_card', 'other'];

        $query = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as verifier', 'prototype_payments.verified_by', '=', 'verifier.id')
            ->select([
                'prototype_payments.*',
                'prototype_sales.id as sale_id',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.total_amount',
                'prototype_sales.deposit_paid',
                'prototype_sales.sales_agent_id',
                'payment_accounts.name as account_name',
                'verifier.name as verified_by_name',
            ])
            ->whereIn('prototype_payments.payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified']);

        if ($accountId) {
            $query->where('prototype_payments.payment_account_id', $accountId);
        }

        if ($agentId) {
            $query->where('prototype_sales.sales_agent_id', $agentId);
        }

        if ($method) {
            $query->where('prototype_payments.payment_method', $method);
        }

        if ($dateFrom) {
            $query->whereDate('prototype_payments.payment_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('prototype_payments.payment_date', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('prototype_sales.customer_name', 'like', '%' . $search . '%')
                  ->orWhere('prototype_sales.sales_number', 'like', '%' . $search . '%')
                  ->orWhere('prototype_payments.reference_number', 'like', '%' . $search . '%');
            });
        }

        $payments = $query->orderBy('prototype_payments.verified_at', 'desc')->get();

        // Calculate totals per account for the summary
        $accountTotals = \DB::table('prototype_payments')
            ->select([
                'payment_account_id',
                \DB::raw('COUNT(*) as total_count'),
                \DB::raw('COALESCE(SUM(amount), 0) as total_deposit'),
            ])
            ->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])
            ->groupBy('payment_account_id')
            ->get()
            ->keyBy('payment_account_id');

        // Total sale value per account (distinct sales, avoids double-counting multi-payment sales)
        $accountSaleTotals = \DB::table(\DB::raw('(SELECT DISTINCT p.payment_account_id, p.prototype_sale_id, s.total_amount FROM prototype_payments p JOIN prototype_sales s ON s.id = p.prototype_sale_id WHERE p.payment_status IN (\'verified\', \'down_payment_verified\', \'additional_payment_verified\', \'full_payment_verified\')) as t'))
            ->select([
                't.payment_account_id',
                \DB::raw('COUNT(*) as sale_count'),
                \DB::raw('COALESCE(SUM(t.total_amount), 0) as total_value'),
            ])
            ->groupBy('t.payment_account_id')
            ->get()
            ->keyBy('payment_account_id');

        // Completed refunds per account (subtract from collected: refunds reduce money in)
        // Use DISTINCT to avoid multiplying refund amount across multiple payments of the same sale
        $accountRefundTotals = \DB::table(\DB::raw('(SELECT DISTINCT p.payment_account_id, r.prototype_sale_id, r.refund_amount FROM prototype_payments p JOIN prototype_refunds r ON r.prototype_sale_id = p.prototype_sale_id WHERE r.refund_status = \'completed\' AND p.payment_status IN (\'verified\', \'down_payment_verified\', \'additional_payment_verified\', \'full_payment_verified\')) as t'))
            ->select([
                't.payment_account_id',
                \DB::raw('COALESCE(SUM(t.refund_amount), 0) as total_refunded'),
            ])
            ->groupBy('t.payment_account_id')
            ->get()
            ->keyBy('payment_account_id');

        // Pending payments count per account (payments awaiting verification)
        $pendingCounts = \DB::table('prototype_payments')
            ->select([
                'payment_account_id',
                \DB::raw('COUNT(*) as pending_count'),
            ])
            ->whereIn('payment_status', ['pending', 'reject_pending', 'edit_pending'])
            ->groupBy('payment_account_id')
            ->get()
            ->keyBy('payment_account_id');

        // Pending initial deposits per account (sales with deposit but no payment record yet)
        $pendingDepositCounts = \DB::table('prototype_sales')
            ->select([
                'payment_account_id',
                \DB::raw('COUNT(*) as pending_count'),
            ])
            ->where('payment_status', 'pending')
            ->where('deposit_paid', '>', 0)
            ->whereNull('deleted_at')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('prototype_payments')
                    ->whereColumn('prototype_payments.prototype_sale_id', '=', 'prototype_sales.id');
            })
            ->groupBy('payment_account_id')
            ->get()
            ->keyBy('payment_account_id');

        // Recent audit trail
        $auditLogs = \App\Models\PaymentAuditLog::with(['user', 'prototypeSale', 'paymentAccount'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Pending rejections awaiting a second verifier (two-verifier approval)
        $pendingRejections = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as requester', 'prototype_payments.reject_requested_by', '=', 'requester.id')
            ->where('prototype_payments.payment_status', 'reject_pending')
            ->select([
                'prototype_sales.id as sale_id',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.total_amount',
                'prototype_sales.deposit_paid',
                'prototype_payments.amount',
                'prototype_payments.payment_method',
                'prototype_payments.reference_number',
                'prototype_payments.payment_date',
                'prototype_payments.screenshot_path as payment_screenshot_path',
                'prototype_payments.reject_requested_by',
                'prototype_payments.reject_requested_at',
                'payment_accounts.name as account_name',
                'requester.name as requester_name',
                \DB::raw("'additional_payment' as payment_source"),
                'prototype_payments.id as payment_id',
            ])
            ->union(\DB::table('prototype_sales')
                ->leftJoin('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
                ->leftJoin('users as requester', 'prototype_sales.reject_requested_by', '=', 'requester.id')
                ->where('prototype_sales.payment_status', 'reject_pending')
                ->whereNull('prototype_sales.deleted_at')
                ->select([
                    'prototype_sales.id as sale_id',
                    'prototype_sales.sales_number',
                    'prototype_sales.customer_name',
                    'prototype_sales.total_amount',
                    'prototype_sales.deposit_paid',
                    \DB::raw('prototype_sales.deposit_paid as amount'),
                    'prototype_sales.payment_method',
                    'prototype_sales.reference_number',
                    'prototype_sales.payment_date',
                    'prototype_sales.payment_screenshot_path',
                    'prototype_sales.reject_requested_by',
                    'prototype_sales.reject_requested_at',
                    'payment_accounts.name as account_name',
                    'requester.name as requester_name',
                    \DB::raw("'initial_deposit' as payment_source"),
                    \DB::raw('NULL as payment_id'),
                ]))
            ->orderBy('reject_requested_at', 'desc')
            ->get();

        // Pending edit requests (change ref/amount/date) awaiting a second verifier
        $pendingEdits = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as requester', 'prototype_payments.edit_requested_by', '=', 'requester.id')
            ->where('prototype_payments.payment_status', 'edit_pending')
            ->select([
                'prototype_sales.id as sale_id',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.total_amount',
                'prototype_sales.deposit_paid',
                'prototype_payments.amount',
                'prototype_payments.payment_method',
                'prototype_payments.reference_number',
                'prototype_payments.payment_date',
                'prototype_payments.screenshot_path as payment_screenshot_path',
                'prototype_payments.edit_requested_by',
                'prototype_payments.edit_requested_at',
                'prototype_payments.edit_original_status',
                'prototype_payments.pending_reference_number',
                'prototype_payments.pending_payment_date',
                'prototype_payments.pending_amount',
                'payment_accounts.name as account_name',
                'requester.name as requester_name',
                \DB::raw("'additional_payment' as payment_source"),
                'prototype_payments.id as payment_id',
            ])
            ->union(\DB::table('prototype_sales')
                ->leftJoin('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
                ->leftJoin('users as requester', 'prototype_sales.edit_requested_by', '=', 'requester.id')
                ->where('prototype_sales.payment_status', 'edit_pending')
                ->whereNull('prototype_sales.deleted_at')
                ->select([
                    'prototype_sales.id as sale_id',
                    'prototype_sales.sales_number',
                    'prototype_sales.customer_name',
                    'prototype_sales.total_amount',
                    'prototype_sales.deposit_paid',
                    \DB::raw('prototype_sales.deposit_paid as amount'),
                    'prototype_sales.payment_method',
                    'prototype_sales.reference_number',
                    'prototype_sales.payment_date',
                    'prototype_sales.payment_screenshot_path',
                    'prototype_sales.edit_requested_by',
                    'prototype_sales.edit_requested_at',
                    'prototype_sales.edit_original_status',
                    'prototype_sales.pending_reference_number',
                    'prototype_sales.pending_payment_date',
                    'prototype_sales.pending_amount',
                    'payment_accounts.name as account_name',
                    'requester.name as requester_name',
                    \DB::raw("'initial_deposit' as payment_source"),
                    \DB::raw('NULL as payment_id'),
                ]))
            ->orderBy('edit_requested_at', 'desc')
            ->get();

        return view('sales.prototype.cashflow', compact('accounts', 'agents', 'paymentMethods', 'payments', 'accountTotals', 'accountSaleTotals', 'accountRefundTotals', 'pendingCounts', 'pendingDepositCounts', 'auditLogs', 'pendingRejections', 'pendingEdits', 'accountId', 'agentId', 'method', 'dateFrom', 'dateTo', 'search'));
    }

    /**
     * Get audit logs for a specific sale (AJAX).
     */
    public function getAuditLogs($saleId = null)
    {
        $query = \App\Models\PaymentAuditLog::with(['user', 'paymentAccount']);

        if ($saleId) {
            $query->where('prototype_sale_id', $saleId);
        }

        // Apply payment_id filter if present (from new prototype_payments system)
        $paymentId = request('payment_id');
        if ($paymentId) {
            $query->where('payment_id', $paymentId);
        }

        $limit = request('limit', 50);
        $logs = $query->orderBy('created_at', 'desc')->limit($limit)->get();

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

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])
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

        $statuses = ['new', 'sample_approval', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $statusLabels = [
            'new'                => 'New',
            'sample_approval'    => 'Sample/Approval',
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

        // Unread notifications for the agent
        $notifications = \App\Models\SaleNotification::with(['sale', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        $unreadCount = $notifications->where('is_read', false)->count();

        return view('sales.prototype.agent-dashboard', compact('sales', 'statuses', 'statusLabels', 'departments', 'filters', 'notifications', 'unreadCount'));
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

        $sale = \App\Models\PrototypeSale::with(['payments', 'refunds'])->find($id);
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
            'payment_method' => 'nullable|string',
            'payment_type' => 'required|string|in:additional,fullpayment',
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

        // Save payment status: 'pending' always (requires verifier approval)
        $paymentStatus = 'pending';

        // Create a separate payment record
        $payment = \App\Models\PrototypePayment::create([
            'prototype_sale_id' => $id,
            'payment_type' => $request->payment_type,
            'amount' => $request->payment_amount,
            'payment_method' => $request->payment_method ?? 'online',
            'payment_account_id' => $request->payment_account_id ?? $sale->payment_account_id,
            'reference_number' => $request->reference_number,
            'screenshot_path' => $paymentScreenshotPath,
            'payment_status' => $paymentStatus,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        // Mark sale as having a pending payment
        \DB::table('prototype_sales')->where('id', $id)->update([
            'payment_status' => 'pending',
            'verify_requested_at' => now(),
            'verify_requested_by' => $user->id,
            'updated_at' => now(),
        ]);

        // Log payment addition
        try {
            \App\Models\PaymentAuditLog::create([
                'prototype_sale_id' => $id,
                'payment_id' => $payment->id,
                'payment_account_id' => $request->payment_account_id ?? $sale->payment_account_id,
                'user_id' => $user->id,
                'action' => 'balance_payment_' . $request->payment_type,
                'remarks' => ($request->payment_type === 'fullpayment' ? 'Full payment' : 'Additional payment') . ' of ₱' . number_format($request->payment_amount, 2) . ' via ' . $request->payment_method . ($request->notes ? ' — ' . $request->notes : ''),
            ]);
        } catch (\Exception $e) {
            // Non-critical — don't break the flow
        }

        return redirect()->route('sales.prototype.show', $id)
            ->with('success', 'Payment added successfully!');
    }

    /**
     * Submit a refund request for a prototype sale.
     * Auto-detected from reprocess overpayment or manual cancellation.
     */
    public function submitRefund(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Only managers can request refunds.']);
        }

        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        // Check if refund already exists for this sale
        $existingRefund = \DB::table('prototype_refunds')
            ->where('prototype_sale_id', $id)
            ->whereIn('refund_status', ['pending', 'accepted', 'approved'])
            ->first();
        if ($existingRefund) {
            return response()->json(['success' => false, 'message' => 'There is already a pending or active refund for this sale.']);
        }

        $request->validate([
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_reason' => 'required|in:reprocess_overpayment,cancellation,other',
            'reason_details' => 'required|string|max:1000',
            'refund_method' => 'required|in:cash,bank_transfer,gcash,paymaya,credit_card,other',
            'refund_account_id' => 'nullable|integer|exists:payment_accounts,id',
            'refund_account_name' => 'required|string|max:255',
            'refund_account_number' => 'required|string|max:255',
        ]);

        $refundId = \DB::table('prototype_refunds')->insertGetId([
            'prototype_sale_id' => $id,
            'refund_amount' => $request->refund_amount,
            'refund_reason' => $request->refund_reason,
            'reason_details' => $request->reason_details,
            'refund_method' => $request->refund_method,
            'refund_account_id' => $request->refund_account_id,
            'refund_account_name' => $request->refund_account_name,
            'refund_account_number' => $request->refund_account_number,
            'refund_status' => 'pending',
            'requested_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Audit log
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $id,
            'user_id' => $user->id,
            'action' => 'refund_requested',
            'description' => 'Refund of ₱' . number_format($request->refund_amount, 2) . ' requested (' . $request->refund_reason . ').',
            'details' => json_encode([
                'refund_id' => $refundId,
                'refund_amount' => $request->refund_amount,
                'refund_reason' => $request->refund_reason,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Refund request submitted successfully.', 'refund_id' => $refundId]);
    }

    /**
     * Process a refund (approve, complete, or reject).
     */
    public function processRefund(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['success' => false, 'message' => 'Only managers can process refunds.']);
        }

        $refund = \DB::table('prototype_refunds')->find($id);
        if (!$refund) {
            return response()->json(['success' => false, 'message' => 'Refund not found.'], 404);
        }

        $request->validate([
            'refund_action' => 'required|in:accept,complete,reject',
            'admin_notes' => 'nullable|string|max:1000',
            'refund_reference' => 'nullable|string|max:255',
            'refund_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        $action = $request->refund_action;

        // For complete action, require extra fields
        if ($action === 'complete') {
            // Check proof screenshot
            if (!$request->hasFile('refund_proof')) {
                return response()->json(['success' => false, 'message' => 'Refund proof screenshot is required to complete the refund.']);
            }
            if (!$request->filled('refund_amount')) {
                return response()->json(['success' => false, 'message' => 'Refund amount is required to complete the refund.']);
            }
            if (!$request->filled('refund_reference')) {
                return response()->json(['success' => false, 'message' => 'Reference number is required to complete the refund.']);
            }
            if (!$request->filled('admin_notes')) {
                return response()->json(['success' => false, 'message' => 'Notes are required to complete the refund.']);
            }
        }

        $now = now();
        $updateData = ['updated_at' => $now];
        $auditAction = '';
        $auditDesc = '';

        switch ($action) {
            case 'accept':
                if ($refund->refund_status !== 'pending') {
                    return response()->json(['success' => false, 'message' => 'Only pending refunds can be accepted.']);
                }
                // Check if someone already accepted
                if ($refund->accepted_by) {
                    $acceptor = \DB::table('users')->find($refund->accepted_by);
                    $acceptorName = $acceptor ? $acceptor->name : 'Unknown';
                    return response()->json(['success' => false, 'message' => 'This refund was already accepted by ' . $acceptorName . '.']);
                }
                $updateData['refund_status'] = 'accepted';
                $updateData['accepted_by'] = $user->id;
                $updateData['accepted_at'] = $now;
                $auditAction = 'refund_accepted';
                $auditDesc = 'Refund of ₱' . number_format($refund->refund_amount, 2) . ' accepted by ' . $user->name . '.';
                break;
            case 'complete':
                if ($refund->refund_status !== 'accepted') {
                    return response()->json(['success' => false, 'message' => 'Only accepted refunds can be marked as completed.']);
                }
                // Only the acceptor can mark as complete
                if ($refund->accepted_by !== $user->id) {
                    $acceptor = \DB::table('users')->find($refund->accepted_by);
                    $acceptorName = $acceptor ? $acceptor->name : 'Another manager';
                    return response()->json(['success' => false, 'message' => 'Only ' . $acceptorName . ' (who accepted this refund) can mark it as completed.']);
                }
                $updateData['refund_status'] = 'completed';
                $updateData['completed_by'] = $user->id;
                $updateData['completed_at'] = $now;
                $updateData['refund_reference'] = $request->refund_reference;
                $updateData['refund_amount'] = $request->refund_amount;
                $actualAmount = $request->refund_amount;
                $auditAction = 'refund_completed';
                $auditDesc = 'Refund of ₱' . number_format($actualAmount, 2) . ' completed.';

                // Handle proof screenshot upload
                if ($request->hasFile('refund_proof')) {
                    $proofPath = $request->file('refund_proof')->store('refund-proofs', 'public');
                    $updateData['refund_proof_path'] = $proofPath;
                    $auditDesc .= ' Proof attached.';
                }

                if ($request->refund_reference) {
                    $auditDesc .= ' Reference: ' . $request->refund_reference;
                }

                // Clear overpayment if completed
                \DB::table('prototype_sales')
                    ->where('id', $refund->prototype_sale_id)
                    ->update(['overpayment' => 0, 'updated_at' => $now]);
                break;
            case 'reject':
                if ($refund->refund_status !== 'pending') {
                    return response()->json(['success' => false, 'message' => 'Only pending refunds can be rejected.']);
                }
                $updateData['refund_status'] = 'rejected';
                $auditAction = 'refund_rejected';
                $auditDesc = 'Refund of ₱' . number_format($refund->refund_amount, 2) . ' rejected.';
                break;
        }

        if ($request->admin_notes) {
            $updateData['admin_notes'] = $request->admin_notes;
            $auditDesc .= ' Notes: ' . $request->admin_notes;
        }

        \DB::table('prototype_refunds')->where('id', $id)->update($updateData);

        // Audit log
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $refund->prototype_sale_id,
            'user_id' => $user->id,
            'action' => $auditAction,
            'description' => $auditDesc,
            'details' => json_encode([
                'refund_id' => $id,
                'action' => $action,
                'refund_amount' => $refund->refund_amount,
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json(['success' => true, 'message' => 'Refund ' . $action . 'd successfully.']);
    }

    /**
     * Show refund list page for managers.
     */
    public function refundList(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = \DB::table('prototype_refunds')
            ->join('prototype_sales', 'prototype_refunds.prototype_sale_id', '=', 'prototype_sales.id')
            ->join('users', 'prototype_refunds.requested_by', '=', 'users.id')
            ->leftJoin('users as acceptors', 'prototype_refunds.accepted_by', '=', 'acceptors.id')
            ->select(
                'prototype_refunds.*',
                'prototype_sales.sales_number',
                'prototype_sales.customer_name',
                'prototype_sales.total_amount',
                'prototype_sales.deposit_paid',
                'users.name as requested_by_name',
                'acceptors.name as accepted_by_name'
            );

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('prototype_refunds.refund_status', $request->status);
        }

        // Filter by reason type
        if ($request->filled('reason') && $request->reason !== 'all') {
            $query->where('prototype_refunds.refund_reason', $request->reason);
        }

        $refunds = $query->orderBy('prototype_refunds.created_at', 'desc')->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'refunds' => $refunds,
            ]);
        }

        return view('sales.prototype.refunds', compact('refunds'));
    }

    /**
     * Upload a design file screenshot or approved sample color screenshot for a sale.
     * Types: 'file_screenshot' | 'sample_color'
     */
    public function uploadDesignImage(Request $request, $id)
    {
        $request->validate([
            'design_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'type' => 'required|in:file_screenshot,sample_color',
        ]);

        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $file = $request->file('design_image');
        $filename = 'design_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('uploads/sales/' . $id, $filename, 'public');
        $url = '/storage/' . $filePath;

        $images = $sale->design_images ?? [];
        $images[] = [
            'type' => $request->type,
            'url' => $url,
            'name' => $file->getClientOriginalName(),
            'uploaded_by' => auth()->user()->name ?? 'Unknown',
            'uploaded_at' => now()->toDateTimeString(),
        ];
        $sale->design_images = $images;
        $sale->save();

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'image' => [
                'type' => $request->type,
                'url' => $url,
                'name' => $file->getClientOriginalName(),
                'uploaded_by' => auth()->user()->name ?? 'Unknown',
                'uploaded_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Delete a design image (file screenshot or approved sample color).
     * Records the deletion in the audit history so accidental deletes can be traced.
     */
    public function deleteDesignImage(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:file_screenshot,sample_color',
            'url' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $images = is_array($sale->design_images) ? $sale->design_images : (is_string($sale->design_images) ? (json_decode($sale->design_images, true) ?: []) : []);
        $targetUrl = $request->url;
        $removed = null;
        $kept = [];

        foreach ($images as $img) {
            $url = $img['url'] ?? '';
            if (($img['type'] ?? '') === $request->type && $url === $targetUrl) {
                $removed = $img;
                continue;
            }
            $kept[] = $img;
        }

        if (!$removed) {
            return response()->json(['success' => false, 'message' => 'Image not found.'], 404);
        }

        // Remove the physical file (best-effort; keep going even if file already gone)
        try {
            $rel = ltrim(str_replace('/storage/', '', $targetUrl), '/');
            if ($rel) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($rel);
            }
        } catch (\Throwable $e) {
            // ignore file deletion errors — the DB record is what matters
        }

        $sale->design_images = $kept;
        $sale->save();

        // Audit history entry
        $typeLabel = $request->type === 'sample_color' ? 'Approved Sample Color' : 'File Screenshot';
        $reasonText = $request->reason ? ' Reason: ' . $request->reason : '';
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $sale->id,
            'user_id' => auth()->id() ?? 1,
            'action' => 'design_image_deleted',
            'description' => 'Deleted ' . $typeLabel . ' ("' . ($removed['name'] ?? 'image') . '")' . $reasonText,
            'details' => json_encode([
                'type' => $request->type,
                'url' => $targetUrl,
                'name' => $removed['name'] ?? null,
                'uploaded_by' => $removed['uploaded_by'] ?? null,
                'uploaded_at' => $removed['uploaded_at'] ?? null,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $typeLabel . ' deleted.',
        ]);
    }

    /**
     * Notify the sales agent assigned to a sale (photo upload reminder or payment reminder).
     * Types: 'photo_reminder' | 'payment_reminder'
     */
    public function notifyAgent(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:photo_reminder,payment_reminder',
            'urgent' => 'nullable|boolean',
        ]);

        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $agentId = $sale->sales_agent_id;
        if (!$agentId) {
            return response()->json(['success' => false, 'message' => 'No sales agent assigned to this sale.']);
        }

        $from = auth()->user();
        $type = $request->type;
        $isUrgent = (bool) $request->boolean('urgent');

        // Cooldown check: if last notification for this sale+type is within 24h and not urgent, block it
        $lastNotif = \App\Models\SaleNotification::where('sale_id', $sale->id)
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastNotif && !$isUrgent) {
            $minutesSince = (int) $lastNotif->created_at->diffInMinutes(now());
            if ($minutesSince < 1440) {
                $remainingMin = 1440 - $minutesSince;
                $agoText = $minutesSince < 60 ? $minutesSince . ' min' : round($minutesSince / 60) . 'h';
                $remainingText = $remainingMin < 60 ? $remainingMin . ' min' : round($remainingMin / 60) . 'h';
                return response()->json([
                    'success' => false,
                    'cooldown' => true,
                    'message' => "Na-notify na ang agent {$agoText} ago. Pwede ulit i-notify pagkatapos ng {$remainingText}, o gamitin ang urgent reminder.",
                ]);
            }
        }

        // Reminder count: how many times has this agent been notified for this sale+type
        $reminderCount = \App\Models\SaleNotification::where('sale_id', $sale->id)
            ->where('type', $type)
            ->count() + 1;

        $customer = $sale->customer_name ?: 'customer';
        if ($type === 'photo_reminder') {
            $baseTitle = '📸 Photo upload needed: ' . $sale->sales_number;
            $message = "May kulang pang photos (File Screenshot / Approved Sample Color) para sa order ni {$customer}. Pakiupload na lang po.";
        } else {
            $baseTitle = '💰 Payment needed: ' . $sale->sales_number;
            $message = 'May balance due na ₱' . number_format($sale->balance_due_computed, 2) . " para sa order ni {$customer}. Pakipag-ayos na lang po ang payment.";
        }

        if ($isUrgent && $reminderCount > 1) {
            $title = "🚨 URGENT ({$reminderCount}nd reminder): " . substr($baseTitle, strpos($baseTitle, ':') + 2);
            $message = "⚠️ URGENT — {$message}";
        } else {
            $title = $baseTitle;
        }

        \App\Models\SaleNotification::create([
            'sale_id' => $sale->id,
            'from_user_id' => $from ? $from->id : null,
            'to_user_id' => $agentId,
            'type' => $type,
            'is_urgent' => $isUrgent,
            'reminder_count' => $reminderCount,
            'title' => $title,
            'message' => $message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agent notified! ✅',
            'title' => $title,
            'reminder_count' => $reminderCount,
        ]);
    }

    /**
     * Mark a single sale notification as read.
     */
    public function notificationRead($id)
    {
        $notif = \App\Models\SaleNotification::where('id', $id)
            ->where('to_user_id', auth()->id())
            ->first();
        if ($notif) {
            $notif->update(['is_read' => true, 'read_at' => now()]);
        }
        return response()->json(['success' => true]);
    }

    /**
     * Mark all sale notifications for the current user as read.
     */
    public function notificationsReadAll()
    {
        \App\Models\SaleNotification::where('to_user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true]);
    }
}
