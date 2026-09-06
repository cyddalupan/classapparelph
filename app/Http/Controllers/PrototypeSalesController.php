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
     * Compute effective pcs for a list of sale items (JERSEY UP AND DOWN counts 2x).
     * Mirrors the calendar JS getProjectGarmentTotals logic.
     */
    private function computeEffectivePcsFromItems($items)
    {
        $group1 = ['TSHIRT ROUNDNECK', 'TSHIRT VNECK', 'JERSEY UP'];
        $group2 = ['JERSEY UP AND DOWN'];
        $g1 = 0; $g2 = 0; $g3 = 0;
        foreach ($items as $it) {
            $g = strtoupper(trim($it['sublimationForm']['garment']['name'] ?? ''));
            $qty = (int)($it['quantity'] ?? $it['qty'] ?? 1) ?: 1;
            if (in_array($g, $group1)) $g1 += $qty;
            elseif (in_array($g, $group2)) $g2 += $qty;
            else $g3 += $qty;
        }
        return $g1 + ($g2 * 2) + $g3;
    }

    /**
     * Get the current effective pcs load for a given date (Class department only).
     * Only counts active sales (pending/confirmed/in_production/completed) — pending_approval excluded until approved.
     */
    private function getClassDayLoad($date)
    {
        if (!$date) return 0;
        $dateStr = date('Y-m-d', strtotime($date));
        $sales = \App\Models\PrototypeSale::where('department_name', 'Class')
            ->whereIn('status', ['pending', 'confirmed', 'in_production', 'completed'])
            ->whereNull('archived_at')
            ->where(function ($q) use ($dateStr) {
                $q->whereDate('rescheduled_date', $dateStr)
                  ->orWhereDate('estimated_completion_date', $dateStr)
                  ->orWhereDate('created_at', $dateStr);
            })
            ->get();
        $total = 0;
        foreach ($sales as $s) {
            $total += $this->computeEffectivePcsFromItems($s->services ?? []);
        }
        return $total;
    }

    /**
     * AJAX endpoint: day load check for the create modal (Class capacity = 180 effective pcs).
     */
    public function dayLoad(Request $request)
    {
        $date = $request->date;
        $limit = 180;
        $effective = $this->getClassDayLoad($date);
        return response()->json([
            'date' => $date,
            'limit' => $limit,
            'effective' => $effective,
            'overloaded' => $effective > $limit,
        ]);
    }

    /**
     * Approve an overloaded Class sale: moves pending_approval → pending
     * (it now counts toward the day load and appears in kanban/calendar).
     */
    public function approveOverload(string $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->isManager() && !$user->isCoo())) {
            return response()->json(['success' => false, 'message' => 'Only managers can approve overloaded sales.'], 403);
        }
        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale || $sale->status !== 'pending_approval') {
            return response()->json(['success' => false, 'message' => 'This sale is not pending approval.']);
        }
        // Capture the requester BEFORE clearing the fields (approval_requested_by is reset below)
        $requesterId = $sale->approval_requested_by ?: ($sale->sales_agent_id ?? null);
        $sale->status = 'pending';
        $sale->approval_requested_at = null;
        $sale->approval_requested_by = null;
        $sale->save();

        // Notify the requester (agent) that their overloaded sale was approved
        if ($requesterId && (int) $requesterId !== (int) $user->id) {
            \App\Models\SaleNotification::create([
                'sale_id' => $sale->id,
                'from_user_id' => $user->id,
                'to_user_id' => $requesterId,
                'type' => 'approval',
                'title' => 'Sale Approved ✅',
                'message' => 'Na-approve na ang sale ' . ($sale->sales_number ?? ('#' . $sale->id)) . ' — kasama na sa day load.',
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Sale approved — it now counts toward the day load.']);
    }

    /**
     * Dedicated page: lahat ng pending_approval sales (Class overload) — managers/COO only.
     */
    public function pendingApprovalsPage()
    {
        $user = auth()->user();
        if (!$user || (!$user->isManager() && !$user->isCoo())) {
            abort(403, 'Only managers can view pending approvals.');
        }
        $pendingApprovals = \App\Models\PrototypeSale::with(['payments', 'refunds'])
            ->where('status', 'pending_approval')
            ->whereNull('archived_at')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('sales.prototype.pending-approvals', compact('pendingApprovals'));
    }

    /**
     * Reject an overloaded Class sale: pending_approval → cancelled (does NOT count toward capacity).
     */
    public function rejectOverload(string $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->isManager() && !$user->isCoo())) {
            return response()->json(['success' => false, 'message' => 'Only managers can reject overloaded sales.'], 403);
        }
        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale || $sale->status !== 'pending_approval') {
            return response()->json(['success' => false, 'message' => 'This sale is not pending approval.']);
        }
        $sale->status = 'cancelled';
        $sale->approval_requested_at = null;
        $sale->approval_requested_by = null;
        $sale->save();
        return response()->json(['success' => true, 'message' => 'Sale rejected and cancelled.']);
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
        
        // Use existing customer_id if provided and still valid; otherwise fall back to phone lookup/create
        // (stale customer_id from Smart Customer Detection must not block sale creation)
        // IMPORTANT: placeholder phones ("N/A") must NEVER auto-link a sale to an existing
        // customer via phone match — the UNIQUE phone column glued unrelated buyers onto
        // one record (MAAM FAITHFUL bug). An explicit customer_id is only trusted when the
        // selected customer's name matches the submitted name (genuine repeat purchase).
        $customer = null;
        $phoneIsPlaceholder = \App\Models\Customer::isPlaceholderPhone($request->customer_phone);
        $submittedName = trim((string) $request->customer_name);
        if ($request->customer_id) {
            $candidate = \App\Models\Customer::find($request->customer_id);
            if ($candidate && strcasecmp(trim((string) $candidate->name), $submittedName) === 0) {
                $customer = $candidate;
            }
        }
        // Real phone: look up / create by phone (unchanged behaviour for real numbers).
        if (!$customer && !$phoneIsPlaceholder) {
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
        // Placeholder phone: reuse the CURRENT USER's own placeholder customer with the
        // exact same name; otherwise create a FRESH unique record (never reuse "N/A").
        if (!$customer) {
            $normalized = mb_strtolower($submittedName);
            $customer = \App\Models\Customer::where('created_by', auth()->id())
                ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
                ->get()
                ->first(function ($c) {
                    return \App\Models\Customer::isPlaceholderPhone($c->phone);
                });
            if (!$customer) {
                $customer = \App\Models\Customer::create([
                    'name' => $request->customer_name,
                    'phone' => \App\Models\Customer::uniquePlaceholderPhone(),
                    'email' => $request->customer_email,
                    'marketplace' => $request->marketplace,
                    'location' => $request->customer_address,
                    'company' => $request->customer_company,
                    'created_by' => auth()->id(),
                    'customer_tier' => \App\Models\Customer::TIER_BRONZE,
                    'total_orders' => 0,
                    'total_spent' => 0,
                    'average_order_value' => 0,
                ]);
            }
        }
        
        // If customer already exists, update their info if provided
        if (!$phoneIsPlaceholder && $customer->wasRecentlyCreated === false) {
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
        
        // PO (purchase order) handling: no payment now, only PO reference + PO form photo
        $isPo = ($request->payment_type === 'po');
        $poReference = $isPo ? ($request->po_reference ?: null) : null;
        
        // Generate base sales number
        $baseUid = strtoupper(uniqid());
        $isMultiDept = count($deptGroups) > 1;
        
        // Generate a group_id to link multi-department sales
        $group_id = $isMultiDept ? \Illuminate\Support\Str::uuid()->toString() : null;
        $firstSaleId = null;
        $saleIds = [];
        $pendingApprovalCount = 0;
        
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

            // Phase 3: Class capacity check — if this Class sale pushes the day over 180
            // effective pcs, it goes to pending_approval instead of pending (not counted
            // toward capacity until a manager approves it).
            $saleStatus = 'pending';
            $approvalRequestedAt = null;
            $approvalRequestedBy = null;
            if ($deptCode === 'class' && $deptDateNeeded) {
                $newEff = $this->computeEffectivePcsFromItems($items);
                $existingLoad = $this->getClassDayLoad($deptDateNeeded);
                if (($existingLoad + $newEff) > 180) {
                    $saleStatus = 'pending_approval';
                    $approvalRequestedAt = now();
                    $approvalRequestedBy = auth()->id();
                    $pendingApprovalCount++;
                }
            }
            
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
                'payment_method' => $isPo ? 'po' : ($request->payment_method ?: 'cash'),
                'payment_owner' => $request->payment_owner ?: ($request->payment_account_id ? \App\Models\PaymentAccount::find($request->payment_account_id)?->name : 'company'),
                'payment_account_id' => $isPo ? null : ($request->payment_account_id ?: null),
                'payment_date' => $isPo ? null : ($request->payment_date ?: null),
                'reference_number' => $isPo ? null : ($request->reference_number ?: null),
                'po_reference' => $poReference,
                'payment_status' => $isPo ? 'po' : 'pending',
                'payment_screenshot_path' => $paymentScreenshotPath,
                'customer_notes' => $request->customer_notes,
                'internal_notes' => $request->internal_notes,
                'estimated_completion_date' => $deptDateNeeded,
                'kanban_status' => 'new',
                'status' => $saleStatus,
                'approval_requested_at' => $approvalRequestedAt,
                'approval_requested_by' => $approvalRequestedBy,
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
                        'type' => 'sublimation',
                        'is_main' => true,
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

        if ($pendingApprovalCount > 0) {
            $successMsg .= ' ⏳ Class capacity exceeded — ' . $pendingApprovalCount . ' sale(s) are pending manager approval and will not count toward the day load until approved.';
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

        // Class Production Manager: Class department only
        if (auth()->user() && auth()->user()->isClassScoped() && (int) $sale->department_id !== 4) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }
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
            ->select('prototype_sale_comments.*', 'users.name as user_name', 'users.position as user_position')
            ->where('prototype_sale_comments.sale_id', $id)
            ->orderBy('prototype_sale_comments.created_at', 'desc')
            ->get();
        if ($comments->isNotEmpty()) {
            $html .= '<div class="sale-detail-section">';
            $html .= '<h6><i class="fas fa-comments me-2"></i>Comments (' . $comments->count() . ')</h6>';
            foreach ($comments as $c) {
                $userLabel = $c->user_name;
                $firstName = $c->user_name ? trim(explode(' ', $c->user_name)[0]) : '';
                if ($firstName) {
                    $userLabel = $firstName . ($c->user_position ? ' - ' . $c->user_position : '');
                }
                $html .= '<div class="item-card" style="padding:8px 12px;margin-bottom:6px;">';
                $html .= '<div class="d-flex justify-content-between">';
                $html .= '<strong class="small">' . e($userLabel ?? 'User #' . $c->user_id) . '</strong>';
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
        
        // Modal title: product type + agent (consistent with calendar card), fallback to customer name
        $productLabel = '';
        foreach ($services as $svc) {
            if (is_array($svc)) {
                $sf = $svc['sublimationForm'] ?? null;
                if (is_array($sf)) {
                    $garment = $sf['garment'] ?? null;
                    if (is_array($garment) && !empty($garment['name'])) {
                        $productLabel = trim($garment['name']);
                        break;
                    }
                    if (!empty($sf['description'])) {
                        $productLabel = trim(explode(' - ', $sf['description'])[0]);
                        break;
                    }
                }
            }
        }
        $agentShort = $sale->sales_agent_name ? trim(explode(' ', trim($sale->sales_agent_name))[0]) : '';
        $modalTitle = $productLabel ? $productLabel . ($agentShort ? ' - ' . $agentShort : '') : ('Sale: ' . $sale->customer_name);
        
        return response()->json([
            'html' => $html,
            'title' => $modalTitle . ' (#' . $sale->sales_number . ')',
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
        // Class Production Manager: Class department only
        if (auth()->user() && auth()->user()->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
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
        $isManager = $currentUser && $currentUser->isManager();
        // GA role: simplified read-only view (no phone, payments, order items, audit history)
        $isGa = $currentUser && $currentUser->isGa();
        // Sales agents can view the production slip but cannot modify it (checklist, GA/QA boxes, comments)
        $canEditProdSlip = $currentUser && !$currentUser->isSalesAgent();
        // COO can give production feedback too (but is NOT a manager otherwise)
        $canGiveFeedback = $currentUser && ($isManager || $currentUser->isCoo() || $currentUser->isQa());
        
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
            ->select('prototype_sale_audit_logs.*', 'users.name as user_name', 'users.position as user_position')
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

        // Overpayment only exists when CONFIRMED (verified) money exceeds the total.
        // Override the stored column: pending/reject_pending payments are not confirmed
        // money yet, so no refund offer should appear until the payment is verified.
        $overpayment = max($netPaid - (float) ($sale->total_amount ?? 0), 0);
        $sale->overpayment = $overpayment;

        // Production feedback for this sale (visible to manager + assigned agent)
        $productionFeedbacks = \App\Models\ProductionFeedback::with(['fromUser', 'toUser'])
            ->where('sale_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Recipients available for manager/COO to direct feedback to (dropdown in the Give Feedback modal)
        // Artists + Admin (C.E.O.) + COO + CPO can be tagged; the sale's own agent always receives feedback.
        $artists = \App\Models\User::whereIn('role', ['artist', 'admin', 'coo', 'cpo', 'cmo'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Damage reports linked to this sale
        $damageReports = \App\Models\DamageReport::with(['shop', 'reporter', 'accountableUsers.user'])
            ->where('sale_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return view('sales.prototype.show', compact(
            'sale', 'services', 'kanbanItem', 'relatedSales',
            'overallGroupSubtotal', 'overallGroupTotal', 'overallGroupDeposit', 'overallGroupBalance',
            'progressPercent', 'pendingChanges', 'isManager', 'isGa', 'canGiveFeedback', 'canEdit', 'canEditProdSlip',
            'refunds', 'activeRefund', 'refundLogs', 'completedRefunds', 'totalRefunded',
            'payments', 'totalPaid', 'netPaid', 'balanceDue',
            'productionFeedbacks', 'artists', 'damageReports'
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
        if (!$user || !$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Only managers can approve changes.']);
        }
        
        if ($change->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This change request has already been ' . $change->status . '.']);
        }
        
        $servicesAfter = json_decode($change->services_after, true);
        
        // Calculate new totals
        $subtotal = $servicesAfter ? array_sum(array_column($servicesAfter, 'totalPrice')) : 0;
        $totalAmount = $subtotal; // no 12% tax per Andrew's rule
        
        // Compute NET paid: VERIFIED payments only minus completed refunds.
        // (Pending/reject_pending/rejected payments are NOT confirmed money —
        //  they must not create phantom overpayments or refund offers.)
        $payments = \App\Models\PrototypePayment::where('prototype_sale_id', $change->sale_id)
            ->get(['payment_status', 'amount']);
        if ($payments->isNotEmpty()) {
            $totalPaid = (float) $payments->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])->sum('amount');
        } else {
            // Legacy fallback: no payment records at all — use deposit_after as-is.
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

        // Reprocess: write back the new Date Needed from the item JSON to the
        // sale-level date fields. Otherwise the manager order list / My Sales due
        // badges keep computing from the OLD estimated_completion_date.
        if ($isReprocess && !empty($servicesAfter[0]['sublimationForm']['dateNeeded'])) {
            try {
                $newDate = \Carbon\Carbon::parse($servicesAfter[0]['sublimationForm']['dateNeeded'])->format('Y-m-d');
                $updateData['estimated_completion_date'] = $newDate;
                $updateData['date_needed'] = $newDate;
            } catch (\Exception $e) {
                // leave dates untouched if unparseable
            }
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
                    'type' => 'sublimation',
                    'is_main' => true,
                ]];
                \DB::table('prototype_sales')
                    ->where('id', $change->sale_id)
                    ->update(['mockup_images' => json_encode($mockupImages)]);
            }
        }

        // Reprocess replaces the product entirely — drop any stale production
        // checklist so it regenerates with the correct sizes/quantities
        if ($isReprocess) {
            \DB::table('production_checklists')->where('sale_id', $change->sale_id)->delete();
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
        
        // Notify the requester (agent) that their change/reprocess request was approved
        if (!empty($change->submitted_by) && (int) $change->submitted_by !== (int) $user->id) {
            \App\Models\SaleNotification::create([
                'sale_id' => $change->sale_id,
                'from_user_id' => $user->id,
                'to_user_id' => $change->submitted_by,
                'type' => 'approval',
                'title' => $isReprocess ? 'Reprocess Approved ✅' : 'Change Approved ✅',
                'message' => 'Na-approve na ng manager ang iyong request. ' . $desc,
            ]);
        }

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
        if (!$user || !$user->isManager()) {
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
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Please log in to add comments.']);
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
            'description' => ($user->name ?? 'User') . ' added a comment: ' . substr($request->comment, 0, 100) . (strlen($request->comment) > 100 ? '...' : ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'comment_id' => $commentId]);
        }
        
        return redirect()->back()->with('success', 'Comment added.');
    }

    /**
     * Manager gives production feedback to the sale's sales agent.
     * Creates a notification so the agent sees it in My Sales.
     */
    public function storeProductionFeedback(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user || !($user->isManager() || $user->isCoo() || $user->isQa())) {
            return response()->json(['success' => false, 'message' => 'Only managers, the COO, and QA can give production feedback.']);
        }

        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }

        // Prod manager is Class-only
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
        }

        if (!$sale->sales_agent_id) {
            return response()->json(['success' => false, 'message' => 'This sale has no assigned sales agent.']);
        }

        $request->validate([
            'category' => 'required|in:' . implode(',', array_keys(\App\Models\ProductionFeedback::CATEGORIES)),
            'message' => 'required|string|max:2000',
            'to_user_id' => 'nullable|integer|exists:users,id',
        ]);

        // Sales agent ALWAYS receives the feedback.
        $agentId = (int) $sale->sales_agent_id;

        // Optional artist to also involve (CC) — a copy goes to them too.
        $artistId = null;
        $artist = null;
        if ($request->filled('to_user_id') && (int) $request->input('to_user_id') !== $agentId) {
            $artist = \App\Models\User::find($request->input('to_user_id'));
            if (!$artist || !in_array($artist->role, ['artist', 'admin', 'coo'])) {
                return response()->json(['success' => false, 'message' => 'Selected recipient is invalid.']);
            }
            $artistId = $artist->id;
        }

        // 1) ONE feedback record for this action — primary recipient is the agent.
        //    The tagged artist (if any) is stored as involved_user_id, so BOTH see
        //    the same single feedback entry (no more duplicate records).
        $feedback = \App\Models\ProductionFeedback::create([
            'sale_id' => $id,
            'from_user_id' => $user->id,
            'to_user_id' => $agentId,
            'involved_user_id' => $artistId,
            'category' => $request->category,
            'message' => $request->message,
            'status' => 'open',
        ]);

        // Notification for the sales agent (always)
        \App\Models\SaleNotification::create([
            'sale_id' => $id,
            'from_user_id' => $user->id,
            'to_user_id' => $agentId,
            'type' => 'production_feedback',
            'title' => 'Production Feedback',
            'message' => 'You received production feedback: ' . (\App\Models\ProductionFeedback::CATEGORIES[$request->category] ?? $request->category) . ' — ' . substr($request->message, 0, 120) . (strlen($request->message) > 120 ? '...' : ''),
        ]);

        // Notification for the involved artist too (if selected)
        if ($artistId) {
            \App\Models\SaleNotification::create([
                'sale_id' => $id,
                'from_user_id' => $user->id,
                'to_user_id' => $artistId,
                'type' => 'production_feedback',
                'title' => 'Production Feedback',
                'message' => 'You received production feedback: ' . (\App\Models\ProductionFeedback::CATEGORIES[$request->category] ?? $request->category) . ' — ' . substr($request->message, 0, 120) . (strlen($request->message) > 120 ? '...' : ''),
            ]);
        }

        // Audit log — mentions both recipients when an artist is involved
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $id,
            'user_id' => $user->id,
            'action' => 'production_feedback_added',
            'description' => 'Manager gave production feedback to ' . ($sale->sales_agent_name ?? 'agent') . ($artist ? ' and artist ' . $artist->name : '') . ': ' . substr($request->message, 0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'feedback_id' => $feedback->id]);
        }
        return redirect()->back()->with('success', 'Production feedback sent.');
    }

    /**
     * Sales agent updates the status of production feedback (acknowledge / resolve).
     */
    public function updateProductionFeedback(Request $request, string $feedbackId)
    {
        $user = auth()->user();
        $feedback = \App\Models\ProductionFeedback::findOrFail($feedbackId);

        $isManager = $user && ($user->isManager() || $user->isCoo());
        $isTargetAgent = $user && ($feedback->to_user_id === $user->id || $feedback->involved_user_id === $user->id);
        if (!$isManager && !$isTargetAgent) {
            return response()->json(['success' => false, 'message' => 'You cannot update this feedback.']);
        }

        $request->validate([
            'status' => 'required|in:open,acknowledged,resolved',
            'acknowledgement' => 'nullable|string|max:2000',
        ]);

        $status = $request->status;

        // Only managers / COO can RESOLVE; recipients can only acknowledge.
        if ($status === 'resolved' && !$isManager) {
            return response()->json(['success' => false, 'message' => 'Only managers and the COO can resolve production feedback.']);
        }

        // Only the recipient (or involved user) can acknowledge.
        if ($status === 'acknowledged' && !$isTargetAgent) {
            return response()->json(['success' => false, 'message' => 'Only the recipient can acknowledge this feedback.']);
        }

        // Must be acknowledged BEFORE it can be resolved.
        if ($status === 'resolved' && $feedback->status !== 'acknowledged') {
            return response()->json(['success' => false, 'message' => 'The recipient must acknowledge the feedback before it can be resolved.']);
        }

        // Acknowledgement note required when the recipient acknowledges.
        if ($status === 'acknowledged') {
            $ack = trim((string) $request->input('acknowledgement', ''));
            if ($ack === '') {
                return response()->json(['success' => false, 'message' => 'Please leave an acknowledgement note.']);
            }
            $feedback->acknowledgement = $ack;
        }

        $feedback->status = $status;
        $feedback->acknowledged_at = $status === 'acknowledged' || $status === 'resolved' ? now() : null;
        $feedback->resolved_at = $status === 'resolved' ? now() : null;
        $feedback->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Feedback updated.');
    }

    /**
     * Manager's view: all production feedback given across all sales agents.
     */
    public function productionFeedbackList(Request $request)
    {
        $user = auth()->user();
        $isManager = $user && (in_array($user->role, ['admin', 'manager']) || $user->isClassScoped());
        $isArtist = $user && !$isManager && $user->isArtist();
        $isAgent = $user && !$isManager && ($user->isSalesAgent() || $user->isSalesRepresentative() || $user->isArtist() || $user->isCoo() || $user->isCpo() || $user->isCmo() || $user->isGa());
        if (!$isManager && !$isAgent) {
            abort(403);
        }

        // Class Production Manager: Class department feedback only
        $isProdManager = $user && $user->isClassScoped();

        // COO: manager order list entry (no scope param) → sees ALL feedback like a manager.
        // My Sales entry (?scope=mine) → only feedback addressed to him or created by him.
        $ownOnly = $user && $user->isCoo() && $request->query('scope') === 'mine';
        $canViewAll = $isManager || ($user && $user->isCoo() && !$ownOnly);

        $query = \App\Models\ProductionFeedback::with(['sale', 'fromUser', 'toUser']);
        if ($isProdManager) {
            $query->whereHas('sale', function ($q) {
                $q->where('department_id', 4);
            });
        }

        // Non-managers see only their own feedback: addressed to them, or (for COO) created by them.
        // Involved/tagged users (e.g. Artists) also see the same single feedback entry.
        if (!$canViewAll) {
            if ($user->isCoo()) {
                $query->where(function ($q) use ($user) {
                    $q->where('to_user_id', $user->id)->orWhere('from_user_id', $user->id);
                });
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('to_user_id', $user->id)->orWhere('involved_user_id', $user->id);
                });
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('agent_id') && $canViewAll) {
            $query->where('to_user_id', $request->agent_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        $agents = \App\Models\User::whereIn('role', ['sales_agent', 'sales_representative', 'artist', 'admin', 'coo', 'cpo', 'cmo'])->orderBy('name')->get();

        // Who may RESOLVE: managers and the COO only (recipients can only acknowledge).
        $canResolve = $user && ($user->isManager() || $user->isCoo());

        // Status counts must match what THIS user actually sees (agents: own only, managers: + agent filter)
        $countQuery = \App\Models\ProductionFeedback::query();
        if ($isProdManager) {
            $countQuery->whereHas('sale', function ($q) {
                $q->where('department_id', 4);
            });
        }
        if (!$canViewAll) {
            if ($user->isCoo()) {
                $countQuery->where(function ($q) use ($user) {
                    $q->where('to_user_id', $user->id)->orWhere('from_user_id', $user->id);
                });
            } else {
                $countQuery->where(function ($q) use ($user) {
                    $q->where('to_user_id', $user->id)->orWhere('involved_user_id', $user->id);
                });
            }
        } elseif ($request->filled('agent_id')) {
            $countQuery->where('to_user_id', $request->agent_id);
        }
        if ($request->filled('category')) {
            $countQuery->where('category', $request->category);
        }
        $statusCounts = $countQuery->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status')->toArray();

        return view('sales.prototype.production-feedback-list', compact('feedbacks', 'agents', 'statusCounts', 'isManager', 'isArtist', 'canViewAll', 'ownOnly', 'canResolve'));
    }

    /**
     * Agent responds to an urgent notification (2nd reminder+).
     * The reason is posted to the sale's Comments section so the notifier can see it.
     */
    public function respondUrgent(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Please log in first.'], 401);
        }

        $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $notif = \App\Models\SaleNotification::where('id', $id)
            ->where('to_user_id', $user->id)
            ->where('is_urgent', true)
            ->first();

        if (!$notif) {
            return response()->json(['success' => false, 'message' => 'Notification not found.']);
        }

        if ($notif->response) {
            return response()->json(['success' => false, 'message' => 'You already responded to this notification.']);
        }

        $comment = trim($request->response);

        // Post to the sale's Comments section (visible to everyone)
        $commentId = \DB::table('prototype_sale_comments')->insertGetId([
            'sale_id' => $notif->sale_id,
            'user_id' => $user->id,
            'comment' => '[Response to urgent notification] ' . $comment,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mark notification as responded + read
        $notif->update([
            'response' => $comment,
            'responded_at' => now(),
            'is_read' => true,
            'read_at' => now(),
        ]);

        // Audit log
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $notif->sale_id,
            'user_id' => $user->id,
            'action' => 'comment_added',
            'description' => 'Agent responded to urgent notification: ' . substr($comment, 0, 100) . (strlen($comment) > 100 ? '...' : ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'comment_id' => $commentId,
            'message' => 'Response posted to the sale comments.',
        ]);
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
                'users.name as user_name',
                'users.position as user_position'
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
                'quantity' => $qty, // normalized: both keys so views never show 0
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

            // Normalize size entries: ensure BOTH 'qty' and 'quantity' keys exist
            // (forms/views may read either key — prevents "SMALL ×0" display bugs)
            if (!empty($normalized['sizes']) && is_array($normalized['sizes'])) {
                foreach ($normalized['sizes'] as $_i => $_sz) {
                    if (is_array($_sz)) {
                        $_q = $_sz['qty'] ?? $_sz['quantity'] ?? null;
                        if ($_q !== null) {
                            $normalized['sizes'][$_i]['qty'] = (int) $_q;
                            $normalized['sizes'][$_i]['quantity'] = (int) $_q;
                        }
                    }
                }
            }

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
            $entry = ['size' => $sd['size'] ?? 'M', 'qty' => $qty, 'quantity' => $qty]; // normalized: both keys so views never show 0
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

            // Normalize size entries: ensure BOTH 'qty' and 'quantity' keys exist
            // (forms/views may read either key — prevents "SMALL ×0" display bugs)
            if (!empty($normalized['sizes']) && is_array($normalized['sizes'])) {
                foreach ($normalized['sizes'] as $_i => $_sz) {
                    if (is_array($_sz)) {
                        $_q = $_sz['qty'] ?? $_sz['quantity'] ?? null;
                        if ($_q !== null) {
                            $normalized['sizes'][$_i]['qty'] = (int) $_q;
                            $normalized['sizes'][$_i]['quantity'] = (int) $_q;
                        }
                    }
                }
            }

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
        
                // Class Production Manager: only Class department sales (department_id = 4)
        $user = auth()->user();
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
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
     * Normalize/compress a base64-encoded mockup image for PDF embedding.
     * - Converts WebP (and other non-JPEG/PNG formats) to JPEG so DomPDF renders them.
     * - Rescales large images to max 800px and saves as JPEG 70% quality.
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
            // Tiny/invalid image: if it's WebP, still try to convert (DomPDF can't render WebP)
            if ($ext === 'webp') {
                $img = @imagecreatefromstring($rawData ?: '');
                if ($img) {
                    ob_start();
                    imagejpeg($img, null, 80);
                    $jpg = ob_get_clean();
                    imagedestroy($img);
                    if ($jpg) {
                        return 'data:image/jpeg;base64,' . base64_encode($jpg);
                    }
                }
            }
            return $dataUrl;
        }

        // Always convert WebP to JPEG regardless of size (DomPDF WebP support is unreliable)
        if ($ext === 'webp') {
            $img = @imagecreatefromstring($rawData);
            if ($img) {
                ob_start();
                imagejpeg($img, null, 80);
                $jpg = ob_get_clean();
                imagedestroy($img);
                if ($jpg) {
                    return 'data:image/jpeg;base64,' . base64_encode($jpg);
                }
            }
            return $dataUrl;
        }
        
        // Only compress other images larger than 500KB
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
        
                // Class Production Manager: only Class department sales (department_id = 4)
        $user = auth()->user();
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
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
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.prototype.print-slip', compact('sale', 'services') + ['pdfMode' => true]);
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

                // Class Production Manager: only Class department sales (department_id = 4)
        $user = auth()->user();
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
        }
        $services = json_decode($sale->services, true) ?: [];

        // Determine which items are "additional" (added later via Add Product / change requests).
        // Those belong on the Additional Production Slip; the MAIN slip shows original add-to-cart items only.
        $allChanges = \DB::table('prototype_sale_changes')
            ->where('sale_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $originalItemIds = [];
        $reprocessedItemIds = [];
        if ($allChanges->isNotEmpty()) {
            $firstChange = $allChanges->first();
            $firstBefore = json_decode($firstChange->services_before, true) ?: [];
            $originalItemIds = array_column($firstBefore, 'id');
            foreach ($allChanges as $c) {
                if (($c->type ?? '') === 'reprocess' && $c->status === 'approved') {
                    $after = json_decode($c->services_after, true) ?: [];
                    foreach ($after as $a) {
                        if (!empty($a['id'])) $reprocessedItemIds[] = $a['id'];
                    }
                }
            }
        }
        // Only classify items as "additional" when the sale has change history.
        // Sales with NO changes (e.g. multiple add-to-cart items at creation) have all-original items.
        $additionalIds = [];
        if ($allChanges->isNotEmpty()) {
            foreach ($services as $item) {
                $itemId = $item['id'] ?? null;
                if ($itemId && !in_array($itemId, $originalItemIds) && !in_array($itemId, $reprocessedItemIds)) {
                    $additionalIds[$itemId] = true;
                }
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

        // Collect ALL original sublimation items (add-to-cart items) — each becomes its own slip card.
        // No more `break` after the first one.
        $slips = [];
        foreach ($services as $si => $item) {
            if (!isset($item['sublimationForm'])) continue;
            $itemId = $item['id'] ?? null;
            if ($itemId && isset($additionalIds[$itemId])) continue; // additional -> separate slip

            $sf = $item['sublimationForm'];
            $specs = $sf['specifications'] ?? [];
            $partRows = [];
            $garmentName = $sf['garment']['name'] ?? '';
            $partsAdded = $sf['parts'] ?? [];

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

            // Roster data — from this item only
            $allRosters = $sf['roster'] ?? [];

            // Sizes (from sublimation or fallback)
            $sizes = $sf['sizes'] ?? [];

            // Total QTY for THIS product only (avoids pulling in additional-order quantities)
            $totalQty = 0;
            foreach ($sizes as $s) {
                $totalQty += intval($s['quantity'] ?? $s['qty'] ?? 0);
            }
            if ($totalQty === 0) {
                foreach ($allRosters as $r) {
                    $totalQty += intval($r['qty'] ?? $r['number'] ?? 1);
                }
            }

            // Mockup for THIS product
            $mockup = $sf['mockup'] ?? null;
            $mockupUrl = $mockup ? (is_string($mockup) ? $mockup : (is_array($mockup) && !empty($mockup[0]['url']) ? $mockup[0]['url'] : null)) : null;

            $slips[] = [
                'product' => count($slips),
                'itemId' => $item['id'] ?? null,
                'itemName' => $item['name'] ?? ('Product #' . (count($slips) + 1)),
                'projectName' => $sf['projectName'] ?? '',
                'description' => $sf['description'] ?? '',
                'fabric' => $sf['fabric']['name'] ?? '',
                'designer' => $sf['designer'] ?? '',
                'totalQty' => $totalQty,
                'dateNeeded' => $sf['dateNeeded'] ?? '',
                'partRows' => $partRows,
                'allRosters' => $allRosters,
                'sizes' => $sizes,
                'hasRoster' => !empty($allRosters),
                'mockupUrl' => $mockupUrl,
            ];
        }

        // Fallback: no sublimation items at all
        if (empty($slips)) {
            $slips[] = [
                'product' => 0,
                'itemId' => null,
                'itemName' => '',
                'projectName' => '',
                'description' => '',
                'fabric' => '',
                'designer' => '',
                'totalQty' => 0,
                'dateNeeded' => '',
                'partRows' => [],
                'allRosters' => [],
                'sizes' => [],
                'hasRoster' => false,
                'mockupUrl' => null,
            ];
        }

        // Customer info
        $customerName = $sale->customer_name ?? '';
        $salesNumber = $sale->sales_number ?? '';
        $salesAgent = $sale->sales_agent_name ?? '';
        $notes = $services[0]['notes'] ?? '';

        // Build checklist items for ALL products (each item tagged with its product index)
        $checklist = \App\Models\ProductionChecklist::where('sale_id', $id)->first();

        $expectedItems = [];
        foreach ($slips as $slip) {
            $p = $slip['product'];
            foreach ($slip['partRows'] as $pi) {
                $expectedItems[] = [
                    'type' => 'part',
                    'product' => $p,
                    'label' => $pi['part'] . ': ' . $pi['detail'],
                    'value' => '',
                    'status' => 'pending',
                ];
            }
            foreach ($slip['allRosters'] as $r) {
                $expectedItems[] = [
                    'type' => 'roster',
                    'product' => $p,
                    'label' => $r['name'] ?? 'Unknown',
                    'value' => ($r['size'] ?? '') . ' ×' . ($r['number'] ?? 1),
                    'status' => 'pending',
                ];
            }
            foreach ($slip['sizes'] as $s) {
                $expectedItems[] = [
                    'type' => 'size',
                    'product' => $p,
                    'label' => ($s['size'] ?? 'Size') . ' ×' . ($s['quantity'] ?? $s['qty'] ?? 0),
                    'value' => '',
                    'status' => 'pending',
                ];
            }
        }

        if (!$checklist) {
            $checklist = \App\Models\ProductionChecklist::create([
                'sale_id' => $id,
                'items' => $expectedItems,
            ]);
        } else {
            // Rebuild items from all slips while preserving existing status/GA/QA flags
            // by matching on (type, label, product).
            $oldItems = $checklist->items ?? [];
            $oldMap = [];
            foreach ($oldItems as $oi) {
                $key = ($oi['type'] ?? '') . '|' . ($oi['label'] ?? '') . '|' . ($oi['product'] ?? 0);
                $oldMap[$key] = $oi;
            }
            $merged = [];
            foreach ($expectedItems as $ni) {
                $key = ($ni['type'] ?? '') . '|' . ($ni['label'] ?? '') . '|' . ($ni['product'] ?? 0);
                if (isset($oldMap[$key])) {
                    $oi = $oldMap[$key];
                    $ni['status'] = $oi['status'] ?? 'pending';
                    if (isset($oi['ga_done'])) $ni['ga_done'] = $oi['ga_done'];
                    if (isset($oi['qa1_done'])) $ni['qa1_done'] = $oi['qa1_done'];
                    if (isset($oi['qa2_done'])) $ni['qa2_done'] = $oi['qa2_done'];
                }
                $merged[] = $ni;
            }
            if (json_encode($merged) !== json_encode($oldItems)) {
                $checklist->items = $merged;
                $checklist->save();
            }
        }

        // Lazy-migrate legacy sale-level ga_notes into per-product comments (first product)
        if (empty($checklist->product_comments) && !empty($checklist->ga_notes)) {
            $legacy = json_decode($checklist->ga_notes, true);
            if (is_array($legacy) && count($legacy) > 0 && isset($slips[0]['itemId']) && $slips[0]['itemId'] !== null) {
                $map = [(string) $slips[0]['itemId'] => $legacy];
                $checklist->product_comments = json_encode($map);
                $checklist->ga_notes = '';
                $checklist->save();
            }
        }

        // Build mockupImages with fallback (kept for backward compat / print slip)
        $mockupImages_final = [];
        $mockupsRaw_svc = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
        if (!empty($mockupsRaw_svc)) {
            $mockupImages_final = $mockupsRaw_svc;
        } else {
            foreach ($slips as $slip) {
                if (!empty($slip['mockupUrl'])) {
                    $mockupImages_final = [[
                        'name' => ($slip['projectName'] ?: 'mockup') . '-mockup.png',
                        'url' => $slip['mockupUrl'],
                        'type' => 'sublimation'
                    ]];
                    break;
                }
            }
        }

        // Shared sale-level fields (attached to each slip for the frontend)
        foreach ($slips as &$slip) {
            $slip['salesNumber'] = $salesNumber;
            $slip['agent'] = $salesAgent;
            $slip['customer'] = $customerName;
            $slip['notes'] = $notes;
        }
        unset($slip);

        return response()->json([
            'checklist' => [
                'sale_id' => $checklist->sale_id,
                'id' => $checklist->id,
                'items' => $checklist->items ?? [],
                'ga_done' => $checklist->ga_done,
                'ga_done_at' => $checklist->ga_done_at ? $checklist->ga_done_at->toISOString() : null,
                'ga_notes' => $checklist->ga_notes,
                'additional_comments' => $checklist->additional_comments,
                'product_comments' => $checklist->product_comments ?? null,
                'qa1_done' => $checklist->qa1_done,
                'qa1_done_at' => $checklist->qa1_done_at ? $checklist->qa1_done_at->toISOString() : null,
                'qa1_notes' => $checklist->qa1_notes,
                'press_done' => $checklist->press_done,
                'press_done_at' => $checklist->press_done_at ? $checklist->press_done_at->toISOString() : null,
                'qa2_done' => $checklist->qa2_done,
                'qa2_done_at' => $checklist->qa2_done_at ? $checklist->qa2_done_at->toISOString() : null,
                'qa2_notes' => $checklist->qa2_notes,
            ],
            'slips' => $slips,
            // Backward compat: first slip kept as `slip`
            'slip' => $slips[0] ?? [],
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
        
                // Class Production Manager: only Class department sales (department_id = 4)
        $user = auth()->user();
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
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
                            'qty' => $s['qty'] ?? $s['quantity'] ?? 1,
                        ];
                    } else {
                        $cleanSizes[] = $s;
                    }
                }
            }
            
            $productCards[] = [
                'item_id' => $itemId,
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
        
        $checklist = \App\Models\ProductionChecklist::where('sale_id', $id)->first();
        
        return response()->json([
            'has_additional' => count($productCards) > 0,
            'products' => $productCards,
            'sales_number' => $salesNumber,
            'customer_name' => $customerName,
            'agent' => $agentName,
            'additional_comments' => $checklist ? ($checklist->additional_comments ?? '{}') : '{}',
        ]);
    }
    
    public function saveProductionChecklist(string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        // Class Production Manager: only Class department sales (department_id = 4)
        $user = auth()->user();
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
        }

        // Sales agents can view the production slip but cannot modify it (checklist items, GA/QA boxes, comments)
        if ($user && $user->isSalesAgent()) {
            return response()->json(['error' => 'Sales agents cannot modify the production slip.'], 403);
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
        if (array_key_exists('additional_comments', $input)) {
            $checklist->additional_comments = $input['additional_comments'];
        }
        if (array_key_exists('product_comments', $input)) {
            $checklist->product_comments = $input['product_comments'];
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
                    'type' => 'sublimation',
                    'is_main' => true,
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
        
        // Class Production Manager: forced to Class department only
        $user = auth()->user();
        if ($user && $user->isClassScoped()) {
            $allowedDepts = ['class'];
            $activeDept = 'class';
        }
        
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
        
        // Non-admin users only see their own sales (admin & COO see everything)
        // Prod Manager sees ALL Class sales (no agent scoping)
        if (!$user || (!$user->isAdmin() && !$user->isCoo() && !$user->isClassScoped())) {
            $query->where('sales_agent_id', $user ? $user->id : null);
        }
        // Manager/admin can override photo-completeness restriction on moves
        $canOverride = $user && ($user->isAdmin() || $user->role === 'manager' || $user->isClassScoped());
        
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

        // Sort each column: priority-tagged first (Prio 1 → 2 → 3), then by created_at desc
        foreach ($columns as $status => &$col) {
            usort($col, function ($a, $b) {
                $pa = $a->priority ?? 99;
                $pb = $b->priority ?? 99;
                if ($pa !== $pb) return $pa <=> $pb;
                return strcmp($b->created_at ?? '', $a->created_at ?? '');
            });
        }
        unset($col);

        // Count of archived projects (for the Archive link badge)
        $archivedCount = \App\Models\PrototypeSale::whereNotNull('archived_at')->count();
        
        // Determine which sales have approved additional products (via change requests)
        $approvedAdditions = [];
        if ($user && $user->isManager()) {
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
        if ($user && $user->isManager()) {
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

        // Sales with OPEN damage reports (badge on kanban card)
        $damageSaleIds = \App\Models\DamageReport::whereNotNull('sale_id')
            ->whereNotIn('status', ['resolved', 'dismissed'])
            ->pluck('sale_id')
            ->map(fn($id) => (int) $id)
            ->all();

        // Freebie slip status per sale (kanban card badge):
        // amber = may pending request, red = approved pero hindi pa done, green = all done
        $freebiePendingSaleIds = [];
        $freebieOpenSaleIds = [];
        $freebieDoneSaleIds = [];
        if ($user && ($user->isManager() || $user->isCoo())) {
            $fbPending = \DB::table('freebie_requests')->where('status', 'pending')->pluck('sale_id');
            $fbOpen = \DB::table('freebie_slips')->where('status', 'open')->pluck('sale_id');
            $fbDone = \DB::table('freebie_slips')->where('status', 'done')->pluck('sale_id');
            $freebiePendingSaleIds = $fbPending->map(fn($id) => (int) $id)->unique()->values()->all();
            $freebieOpenSaleIds = $fbOpen->map(fn($id) => (int) $id)->unique()->values()->all();
            $freebieDoneSaleIds = $fbDone->map(fn($id) => (int) $id)->unique()->values()->all();
        }

        // Phase 3: pending Class overload approvals (visible to managers/COO)
        // NOTE: Panel is now on the Manager Order List header button + modal, hindi na sa kanban.

        return view('sales.prototype.kanban', compact(
            'columns', 'activeDept', 'allowedDepts', 'kanbanLabels', 'kanbanOrder',
            'showAll', 'departmentLabels', 'departmentColors', 'approvedAdditions',
            'canOverride', 'pendingAddonSaleIds', 'pendingAddonCount', 'archivedCount',
            'damageSaleIds', 'freebiePendingSaleIds', 'freebieOpenSaleIds', 'freebieDoneSaleIds'
        ));
    }

    /**
     * Display Manager List page with pipeline progress bar.
     */
    /**
     * GA Order List — read-only list for GA/Agent users.
     * Only shows orders tagged FOR SAMPLE / FOR APPROVAL / FOR FORMAT / PRINTING
     * (the production stages GA needs to work on). No editing, no sales details.
     */
    public function gaOrderList()
    {
        $user = auth()->user();
        if (!$user || !($user->isGa() || $user->isManager() || $user->isCoo() || $user->isQa())) {
            abort(403, 'Unauthorized access.');
        }

        $request = request();
        $q = trim($request->get('q', ''));
        $stage = $request->get('stage', '');
        $dept = $request->get('dept', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');
        $delayedOnly = $request->boolean('delayed');
        $priorityOnly = $request->boolean('priority');
        $myJobs = $request->boolean('my_jobs');
        $gaFilter = $request->get('ga', '');

        // Same production stage map + reverse map as the manager order list
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

        $sales = \App\Models\PrototypeSale::with(['payments', 'refunds'])
            ->whereIn('status', ['confirmed', 'in_production', 'pending', 'completed'])
            ->whereNull('archived_at')
            ->when($user && $user->isClassScoped(), function ($query) {
                $query->where('department_id', 4);
            })
            ->whereIn('production_stage', ['FOR SAMPLE', 'FOR APPROVAL', 'FOR FORMAT', 'PRINTING', 'PRESSING', 'CUTTING'])
            ->when(filled($q), function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('sales_number', 'like', '%' . $q . '%')
                        ->orWhere('customer_name', 'like', '%' . $q . '%');
                });
            })
            ->when(filled($stage), function ($query) use ($stage) {
                $query->where('production_stage', $stage);
            })
            ->when(filled($dept), function ($query) use ($dept) {
                $query->where('department_id', (int) $dept);
            })
            ->when(filled($dateFrom), function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when(filled($dateTo), function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($delayedOnly, function ($query) {
                $query->where('is_delayed', 1);
            })
            ->when($priorityOnly, function ($query) {
                $query->whereNotNull('priority');
            })
            ->when($myJobs, function ($query) use ($user) {
                $query->whereIn('id', function ($sub) use ($user) {
                    $sub->select('prototype_sale_id')
                        ->from('ga_assignments')
                        ->where('user_id', $user->id);
                });
            })
            ->when(filled($gaFilter), function ($query) use ($gaFilter) {
                $query->whereIn('id', function ($sub) use ($gaFilter) {
                    $sub->select('prototype_sale_id')
                        ->from('ga_assignments')
                        ->where('user_id', (int) $gaFilter);
                });
            })
            ->orderByRaw("CASE WHEN is_delayed = 1 THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN priority IS NOT NULL THEN 0 ELSE 1 END")
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // GA users + assignments map for the current page
        $gaUsers = \App\Models\User::where('role', 'ga')->orderBy('name')->get();
        $saleIds = collect($sales->items())->pluck('id')->all();
        $assignments = \App\Models\GaAssignment::with('user')
            ->whereIn('prototype_sale_id', $saleIds)
            ->get()
            ->groupBy('prototype_sale_id');

        // Recent activity log (assign / unassign / done) — latest 30
        $activityLogs = \App\Models\GaAssignmentLog::with(['user', 'actor', 'sale'])
            ->latest()
            ->limit(30)
            ->get();

        // Completed jobs count: GA work counts kapag na-tag na ng Manager ang sale as SEWING or beyond
        // (permanent marker ga_counted_at — kahit pa binalik sa PRINTING, counted pa rin)
        $completedCount = \App\Models\GaAssignment::whereNotNull('completed_at')
            ->whereHas('sale', function ($q) {
                $q->whereNotNull('ga_counted_at');
            })
            ->when($user->isGa(), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->distinct()
            ->count('prototype_sale_id');

        return view('sales.prototype.ga-order-list', compact('sales', 'prodStageMap', 'statusToStage', 'departmentLabels', 'departmentColors', 'q', 'stage', 'dept', 'dateFrom', 'dateTo', 'delayedOnly', 'priorityOnly', 'myJobs', 'gaFilter', 'gaUsers', 'assignments', 'activityLogs', 'completedCount'));
    }

    /**
     * GA Dashboard — performance breakdown per GA, per stage, at monthly stats.
     * Counted lang ang mga sale na na-tag na ng Manager as SEWING or beyond (ga_counted_at set).
     */
    public function gaDashboard()
    {
        $user = auth()->user();
        if (!$user || !($user->isGa() || $user->isManager())) {
            abort(403, 'Unauthorized access.');
        }

        $request = request();
        $month = $request->get('month', '');
        $year = $request->get('year', '');

        // Scope ng GA: sarili lang. Manager: lahat.
        $gaUsers = \App\Models\User::where('role', 'ga')
            ->when(!$user->isManager(), fn ($q) => $q->where('id', $user->id))
            ->orderBy('name')
            ->get();
        $gaIds = $gaUsers->pluck('id')->all();

        // Base query: counted sales (naabot ang SEWING+)
        $countedQuery = \App\Models\PrototypeSale::whereNotNull('ga_counted_at')
            ->when(filled($month), fn ($q) => $q->whereMonth('ga_counted_at', (int) $month))
            ->when(filled($year), fn ($q) => $q->whereYear('ga_counted_at', (int) $year));
        $countedSaleIds = (clone $countedQuery)->pluck('id')->all();

        // Kabuuang counted jobs
        $totalCounted = count($countedSaleIds);

        // Data ng counted sales (services) — para sa item breakdown at per-stage piece count
        $countedSales = (clone $countedQuery)->get(['id', 'services']);

        // Per-GA stats
        $perGa = [];

        // Mapa ng kabuuang piraso per counted sale (para sa per-stage piece count)
        $salePiecesMap = [];
        foreach ($countedSales as $cs) {
            $qtySum = 0;
            $csItems = is_string($cs->services) ? json_decode($cs->services, true) : ($cs->services ?? []);
            foreach ((array) $csItems as $ci) {
                if (is_array($ci)) $qtySum += (int) ($ci['quantity'] ?? 1);
            }
            $salePiecesMap[$cs->id] = $qtySum;
        }

        foreach ($gaUsers as $ga) {
            $assignments = \App\Models\GaAssignment::where('user_id', $ga->id)
                ->whereIn('prototype_sale_id', $countedSaleIds)
                ->get();

            $jobs = $assignments->pluck('prototype_sale_id')->unique()->count();
            $doneStages = $assignments->whereNotNull('completed_at');
            $doneCount = $doneStages->count();

            // Per-stage breakdown (completed)
            $stageBreakdown = [];
            foreach (['FOR SAMPLE', 'FOR APPROVAL', 'FOR FORMAT', 'PRINTING'] as $st) {
                $stageBreakdown[$st] = $doneStages->where('stage', $st)->count();
            }

            // Average time: claimed → DONE (hours) + per-stage pieces & min-per-piece
            $durations = [];
            $stagePieces = [];      // stage => pirasong na-process
            $stageDur = [];         // stage => kabuuang oras
            foreach ($doneStages as $asg) {
                $pieces = $salePiecesMap[$asg->prototype_sale_id] ?? 0;
                $stagePieces[$asg->stage] = ($stagePieces[$asg->stage] ?? 0) + $pieces;
                if ($asg->assigned_at && $asg->completed_at) {
                    $d = \Carbon\Carbon::parse($asg->assigned_at)->diffInHours(\Carbon\Carbon::parse($asg->completed_at));
                    $durations[] = $d;
                    $stageDur[$asg->stage] = ($stageDur[$asg->stage] ?? 0) + $d;
                }
            }
            $avgHours = count($durations) ? round(array_sum($durations) / count($durations), 1) : 0;

            // Oras-per-piraso per stage (min/pc) — justification ng avg time
            $stageMinsPerPiece = [];
            foreach ($stagePieces as $st => $pc) {
                $hrs = $stageDur[$st] ?? 0;
                $stageMinsPerPiece[$st] = ($pc > 0 && $hrs > 0) ? round(($hrs * 60) / $pc, 1) : 0;
            }
            // Overall: total na oras / kabuuang piraso (lahat ng stages)
            $totalStagePieces = array_sum($stagePieces);
            $overallMinsPerPiece = ($totalStagePieces > 0 && array_sum($durations) > 0)
                ? round((array_sum($durations) * 60) / $totalStagePieces, 1)
                : 0;

            $perGa[$ga->id] = [
                'user'             => $ga,
                'jobs'             => $jobs,
                'doneStages'       => $doneCount,
                'stages'           => $stageBreakdown,
                'stagePieces'      => $stagePieces,
                'stageMinsPerPiece'=> $stageMinsPerPiece,
                'overallMinsPerPiece' => $overallMinsPerPiece,
                'avgHours'         => $avgHours,
            ];
        }

        // Monthly trend (all GAs) — huling 12 buwan na may counted jobs
        $monthlyTrend = \DB::table('prototype_sales')
            ->selectRaw("DATE_FORMAT(ga_counted_at, '%Y-%m') as ym, COUNT(*) as total")
            ->whereNotNull('ga_counted_at')
            ->groupBy('ym')
            ->orderByDesc('ym')
            ->limit(12)
            ->get();

        // Recent counted jobs (para makita kung ano ang na-count) — Manager view only
        $recentCounted = \App\Models\PrototypeSale::whereNotNull('ga_counted_at')
            ->when(filled($month), fn ($q) => $q->whereMonth('ga_counted_at', (int) $month))
            ->when(filled($year), fn ($q) => $q->whereYear('ga_counted_at', (int) $year))
            ->orderByDesc('ga_counted_at')
            ->limit(15)
            ->get(['id', 'sales_number', 'customer_name', 'department_id', 'production_stage', 'ga_counted_at']);

        // ── Item quantity breakdown: kung ilang piraso per item type ang na-process ──
        // Group by base garment type (hal. 'POLO ZIPPER' at 'POLO BUTTON' → 'POLO') para mag-total nang tama.
        $itemBreakdown = [];      // ['POLO' => 12, 'JERSEY' => 8, ...]
        $itemDetail = [];         // variant-level: ['POLO BUTTON' => 3, 'POLO ZIPPER' => 5]
        $perGaPieces = [];        // GA id => kabuuang piraso
        foreach ($countedSales as $s) {
            $svcItems = is_string($s->services) ? json_decode($s->services, true) : ($s->services ?? []);
            foreach ((array) $svcItems as $svc) {
                if (!is_array($svc)) continue;
                $qty = (int) ($svc['quantity'] ?? 1);
                // Pangalan ng item: garment name > productType > name
                $sf = $svc['sublimationForm'] ?? [];
                $itemName = $sf['garment']['name']
                    ?? $svc['productType']
                    ?? $svc['name']
                    ?? $svc['product_name']
                    ?? 'Item';
                if (!is_string($itemName) || trim($itemName) === '') $itemName = 'Item';
                // Base type: unang salita ng garment name (POLO ZIPPER → POLO, JERSEY → JERSEY)
                $itemType = strtoupper(trim(explode(' ', trim($itemName))[0]));
                $itemBreakdown[$itemType] = ($itemBreakdown[$itemType] ?? 0) + $qty;
                // Detalyadong variant (POLO BUTTON, POLO ZIPPER, atbp.) — para makita kung ilan kada isa
                $itemDetail[$itemName] = ($itemDetail[$itemName] ?? 0) + $qty;
                // Per-GA: kung sinong GA may assignment sa sale na ito, idagdag ang piraso sa kanya
                $saleGAs = \App\Models\GaAssignment::where('prototype_sale_id', $s->id)
                    ->whereNotNull('completed_at')
                    ->pluck('user_id');
                foreach ($saleGAs as $gid) {
                    $perGaPieces[$gid] = ($perGaPieces[$gid] ?? 0) + $qty;
                }
            }
        }
        arsort($itemBreakdown);
        arsort($itemDetail);
        $totalPieces = array_sum($itemBreakdown);

        return view('sales.prototype.ga-dashboard', compact('gaUsers', 'perGa', 'totalCounted', 'monthlyTrend', 'recentCounted', 'month', 'year', 'itemBreakdown', 'itemDetail', 'totalPieces', 'perGaPieces'));
    }

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

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])->whereIn("status", ["confirmed", "in_production", "pending", "completed"])
        ->whereNull('archived_at');

        // SAFETY NET (auto-clear + auto-promote): kung may na-left na PRIO sa mga sale na
        // DISPATCH/UNPAID/DONE na (dati kasi pwedeng ma-tag ang PRIO kahit DISPATCH na — stage
        // guard sa updatePriority ang fix), i-clear ito automatic at i-shift pataas ang mga
        // natitirang PRIO. Para hindi na kailangan i-re-tag ang DISPATCH para lang mawala ang PRIO.
        // (Requested by Andrew 2026-09-01)
        $stalePrioIds = \App\Models\PrototypeSale::whereIn('production_stage', ['DISPATCH', 'UNPAID', 'DONE'])
            ->whereNotNull('priority')
            ->whereNull('deleted_at')
            ->pluck('id');
        if ($stalePrioIds->isNotEmpty()) {
            \App\Models\PrototypeSale::whereIn('id', $stalePrioIds)->update(['priority' => null]);
            $this->reindexPriorities();
        }

        // Class Production Manager: Class department only
        $user = auth()->user();
        if ($user && $user->isClassScoped()) {
            $query->where('department_id', 4);
        }
        
        // Non-admin users only see their own sales (admin & COO see everything)
        // Prod Manager sees ALL Class sales (no agent scoping)
        if (!$user || (!$user->isAdmin() && !$user->isCoo() && !$user->isClassScoped())) {
            $query->where('sales_agent_id', $user ? $user->id : null);
        }
        
        $sales = $query->orderByRaw("CASE WHEN is_delayed = 1 THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN priority IS NOT NULL THEN 0 ELSE 1 END")
            ->orderBy('priority', 'asc')
            ->orderBy("created_at", "desc")
            ->paginate(50);

        // Priorities already in use (unique prio enforcement) — same scope as the list
        $usedPrioQuery = \App\Models\PrototypeSale::whereIn("status", ["confirmed", "in_production", "pending", "completed"])
            ->whereNull('deleted_at')
            ->whereNotNull('priority');
        if ($user && $user->isClassScoped()) {
            $usedPrioQuery->where('department_id', 4);
        }
        if (!$user || (!$user->isAdmin() && !$user->isCoo() && !$user->isClassScoped())) {
            $usedPrioQuery->where('sales_agent_id', $user ? $user->id : null);
        }
        $usedPriorities = $usedPrioQuery->pluck('sales_number', 'priority')->toArray();
        
        // Determine if current user is an agent-type user
        $isAgent = $user && !$user->isAdmin() && ($user->isSalesAgent() || $user->isSalesRepresentative());
        
        // Count pending changes per sale for manager notification badges
        $pendingCounts = [];
        $totalPending = 0;
        $pendingChangesList = collect();
        if ($user && $user->isManager()) {
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
        if ($user && $user->isManager()) {
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

        // Open production feedback count (badge on header button) — same scope as the
        // Production Feedback list page: managers see ALL feedback (Class-scoped for
        // prod_manager), and it counts anything NOT yet resolved (open + acknowledged).
        $openFeedbackCount = 0;
        if ($user && $user->isManager()) {
            $fbQuery = \App\Models\ProductionFeedback::whereIn('status', ['open', 'acknowledged']);
            if ($user->isClassScoped()) {
                $fbQuery->whereHas('sale', function ($q) {
                    $q->where('department_id', 4);
                });
            }
            $openFeedbackCount = $fbQuery->count();
        }

        // ⚠️ Delay count — same scope as delayList()
        $delayCount = \App\Models\PrototypeSale::where('is_delayed', 1);
        if ($user && $user->isClassScoped()) {
            $delayCount->where('department_id', 4);
        }
        $delayCount = $delayCount->count();

        // 🔧 Backjob count — same FIFO logic as backjobList():
        // main slip counts once if it has any active comment;
        // each additional project counts once if it has an active comment.
        $backjobCount = 0;
        $bjChecklists = \App\Models\ProductionChecklist::where(function ($q) {
            $q->whereNotNull('ga_notes')->where('ga_notes', '!=', '')
              ->orWhereNotNull('additional_comments')->where('additional_comments', '!=', '')
              ->orWhereNotNull('product_comments')->where('product_comments', '!=', '');
        })->get();
        foreach ($bjChecklists as $chk) {
            $bjSale = \DB::table('prototype_sales')->find($chk->sale_id);
            if (!$bjSale) continue;
            if ($user && $user->isClassScoped() && (int) $bjSale->department_id !== 4) continue;

            $bjMain = json_decode($chk->ga_notes ?? '', true) ?: [];
            if (is_array($bjMain)) {
                foreach ($bjMain as $c) {
                    if (is_array($c) && empty($c['deleted']) && empty($c['done'])) { $backjobCount++; break; }
                }
            }

            $bjAdd = json_decode($chk->additional_comments ?? '', true) ?: [];
            if (is_array($bjAdd)) {
                foreach ($bjAdd as $itemId => $comments) {
                    if (!is_array($comments)) continue;
                    foreach ($comments as $c) {
                        if (is_array($c) && empty($c['deleted']) && empty($c['done'])) { $backjobCount++; break; }
                    }
                }
            }

            $bjProd = json_decode($chk->product_comments ?? '', true) ?: [];
            if (is_array($bjProd)) {
                foreach ($bjProd as $itemId => $comments) {
                    if (!is_array($comments)) continue;
                    foreach ($comments as $c) {
                        if (is_array($c) && empty($c['deleted']) && empty($c['done'])) { $backjobCount++; break; }
                    }
                }
            }
        }

        // 🔁 Repeat customer detection (READ-ONLY display — walang touch sa existing logic)
        // Customer na may >1 sale = repeat. First sale nila ay HINDI flagged; ang mga kasunod lang.
        $repeatCustomers = \App\Models\PrototypeSale::whereNotNull('customer_id')
            ->whereNull('deleted_at')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->selectRaw('customer_id, MIN(id) as first_id')
            ->get()
            ->keyBy('customer_id');
        $repeatEmails = \DB::table('prototype_sales')
            ->whereNotNull('customer_email')
            ->whereNull('deleted_at')
            ->selectRaw('LOWER(TRIM(customer_email)) as email, MIN(id) as first_id')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->keyBy('email');

        // Pending approval panel — managers/COO only (Phase 3)
        $pendingApprovals = collect();
        $listUser = auth()->user();
        if ($listUser && ($listUser->isManager() || $listUser->isCoo())) {
            $pendingApprovals = \App\Models\PrototypeSale::with(['payments', 'refunds'])
                ->where('status', 'pending_approval')
                ->whereNull('archived_at')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Freebie indicator per sale (row badge): amber pending / red open slip / green done
        $fbPendingIds = [];
        $fbOpenIds = [];
        $fbDoneIds = [];
        if ($listUser && ($listUser->isManager() || $listUser->isCoo())) {
            $fbPendingIds = \DB::table('freebie_requests')->where('status', 'pending')->pluck('sale_id')->map(fn($id) => (int) $id)->unique()->values()->all();
            $fbOpenIds = \DB::table('freebie_slips')->where('status', 'open')->pluck('sale_id')->map(fn($id) => (int) $id)->unique()->values()->all();
            $fbDoneIds = \DB::table('freebie_slips')->where('status', 'done')->pluck('sale_id')->map(fn($id) => (int) $id)->unique()->values()->all();
        }

        return view("sales.prototype.list", compact(
            "sales", "kanbanStatuses", "kanbanLabels", "prodStageMap", "statusToStage",
            "departmentLabels", "departmentColors", "isAgent",
            "pendingCounts", "totalPending", "pendingChangesList",
            "lastNotifs", "openFeedbackCount", "usedPriorities",
            "delayCount", "backjobCount", "repeatCustomers", "repeatEmails",
            "pendingApprovals", "fbPendingIds", "fbOpenIds", "fbDoneIds"
        ));
    }

    public function updateKanbanStatus(Request $request, $id)
    {
        $request->validate([
            'kanban_status' => 'required|in:new,sample_approval,design,production,quality_check,ready_for_delivery,delivered,completed'
        ]);
        
        $sale = \App\Models\PrototypeSale::findOrFail($id);

        // PAYMENT LOCK (server-side): cannot move to Completed while there is a pending balance due.
        // (Restored 2026-09-05 — this lock was removed 2026-09-01 in commit 211ab16. Andrew: block lahat,
        // walang makaka-DONE habang may balance kahit ₱1.)
        $balanceDue = (float) $sale->balance_due_computed;
        if ($request->kanban_status === 'completed' && $balanceDue > 0) {
            $msg = 'Hindi ma-move sa Completed: may pending balance pa na ₱' . number_format($balanceDue, 2) . '. Kailangan munang mabayaran bago i-DONE.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        // PHOTO LOCK (server-side): non-manager users cannot move a sale to Design and beyond
        // until both file screenshot + approved sample color are uploaded.
        $lockedStatuses = ['sample_approval', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $user = auth()->user();
        $canOverride = $user && ($user->isAdmin() || $user->role === 'manager' || $user->isClassScoped());
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

        // AUTO-CLEAR PRIO: kapag na-move sa ready_for_delivery (DISPATCH) o higit pa,
        // tanggalin ang priority tag automatic. (Requested by Andrew 2026-09-01)
        $clearedPriority = false;
        if (in_array($request->kanban_status, ['ready_for_delivery', 'delivered', 'completed']) && $sale->priority) {
            $sale->priority = null;
            $clearedPriority = true;
        }

        $sale->save();

        // AUTO-PROMOTE (reindex): i-shift pataas ang mga natitirang PRIO kapag may na-clear na slot.
        if ($clearedPriority) {
            $this->reindexPriorities();
        }
        
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

        // Class Production Manager: Class department only
        if (auth()->user() && auth()->user()->isClassScoped() && (int) $sale->department_id !== 4) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        // PAYMENT LOCK (server-side): cannot mark as DONE/completed while there is a pending balance due.
        // (Restored 2026-09-05 — this lock was removed 2026-09-01 in commit 211ab16. Andrew: block lahat,
        // walang makaka-DONE habang may balance kahit ₱1.) Unconditional — applies to ALL roles.
        $balanceDue = (float) $sale->balance_due_computed;
        $targetIsDone = $request->kanban_status === 'completed' || $request->input('production_stage') === 'DONE';
        if ($targetIsDone && $balanceDue > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Hindi ma-mark na DONE: may pending balance pa na ₱' . number_format($balanceDue, 2) . '. Kailangan munang mabayaran bago i-DONE.',
            ], 422);
        }

        // TIERED PHOTO LOCK (server-side):
        //  - Moving INTO sample_approval (FOR SAMPLE / FOR APPROVAL) requires the File Screenshot.
        //  - Moving past it (FOR FORMAT / PRINTING / CUTTING / etc.) requires File Screenshot + Approved Sample Color.
        $sampleStatuses = ['sample_approval'];
        $pastSampleStatuses = ['design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $user = auth()->user();
        $canOverride = $user && ($user->isAdmin() || $user->role === 'manager' || $user->isClassScoped());
        if (!$canOverride) {
            $dImgs = is_string($sale->design_images) ? json_decode($sale->design_images, true) : ($sale->design_images ?? []);
            $hasFileShot = collect($dImgs)->contains('type', 'file_screenshot');
            $hasColorShot = collect($dImgs)->contains('type', 'sample_color');
            if (in_array($request->kanban_status, $sampleStatuses) && !$hasFileShot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hindi ma-move sa Sample/Approval: kulang ang File Screenshot. I-upload muna bago i-tag.',
                ], 422);
            }
            if (in_array($request->kanban_status, $pastSampleStatuses) && !($hasFileShot && $hasColorShot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hindi ma-move: kulang pang photos (File Screenshot / Sample Color). Kailangan muna kumpleto bago lumipat sa ' . $request->kanban_status . '.',
                ], 422);
            }
        }

        $sale->kanban_status = $request->kanban_status;
        if ($request->filled('production_stage')) {
            $sale->production_stage = $request->production_stage;
        } else {
            // KEEP IN SYNC: kanban drags don't send a stage tag — derive it from the
            // kanban status so the manager order list never drifts from the kanban column.
            $stageFromKanban = [
                'new'                => 'HOLD',
                'sample_approval'    => 'FOR SAMPLE',
                'design'             => 'FOR FORMAT',
                'production'         => 'PRESSING',
                'quality_check'      => 'QA',
                'ready_for_delivery' => 'DISPATCH',
                'delivered'          => 'UNPAID',
                'completed'          => 'DONE',
            ];
            $sale->production_stage = $stageFromKanban[$request->kanban_status] ?? $sale->production_stage;
        }

        // GA count: kapag na-tag na ang sale as SEWING (or beyond), i-record kung kailan
        // — permanenteng counted na ito sa GA Dashboard (kahit pa binalik sa PRINTING).
        $gaCountingStages = ['SEWING', 'QA', 'DISPATCH', 'UNPAID', 'DONE'];
        if (in_array($sale->production_stage, $gaCountingStages) && empty($sale->ga_counted_at)) {
            $sale->ga_counted_at = now();
        }

        // AUTO-CLEAR PRIO: kapag na-DISPATCH na ang production status (o UNPAID/DONE),
        // hindi na kailangan ang priority tag — tanggalin automatic para makapag-tag ng bago
        // nang hindi manual. (Requested by Andrew 2026-09-01)
        $clearedPriority = false;
        if (in_array($sale->production_stage, ['DISPATCH', 'UNPAID', 'DONE']) && $sale->priority) {
            $sale->priority = null;
            $clearedPriority = true;
        }

        $sale->save();

        // AUTO-PROMOTE (reindex): kapag may na-clear na PRIO slot (e.g. na-DISPATCH ang PRIO 1),
        // i-shift pataas ang mga natitirang PRIO (dating PRIO 2 → PRIO 1) para walang gap.
        // Ang priority_map ay ginagamit ng frontend para i-update agad ang UI (no reload).
        $priorityMap = null;
        if ($clearedPriority) {
            $priorityMap = $this->reindexPriorities();
            $priorityMap[$sale->id] = null; // kasama ang na-clear na sale (para sa instant UI)
        }

        return response()->json([
            'success' => true,
            'status' => $sale->kanban_status,
            'production_stage' => $sale->production_stage,
            'priority' => $sale->priority,
            'priority_map' => $priorityMap,
        ]);
    }

    /**
     * Set priority tag (Prio 1/2/3) for a sale — manager order list.
     */
    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'nullable|integer|min:1|max:10',
        ]);

        $sale = \App\Models\PrototypeSale::findOrFail($id);

        // STAGE GUARD: hindi na pwedeng mag-tag ng PRIO ang sale na DISPATCH na (o UNPAID/DONE)
        // — auto-cleared na dapat ito. Payagan lang ang pag-clear (empty) kung may leftover.
        // Ito ang root cause ng "bumabalik ang PRIO kahit DISPATCH na": dati kasi walang check
        // dito, kaya pwedeng ma-tag ulit ang PRIO at hindi na ito dumadaan sa auto-clear.
        // (Fixed by Andrew request 2026-09-01)
        if ($request->filled('priority') && in_array($sale->production_stage, ['DISPATCH', 'UNPAID', 'DONE'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hindi na ma-tag ng PRIO ang order na ito — DISPATCH na ang production status (auto-cleared ang priority).',
            ], 422);
        }

        // Class Production Manager: Class department only
        if (auth()->user() && auth()->user()->isClassScoped() && (int) $sale->department_id !== 4) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        // Unique priority enforcement: a priority number can only be used by ONE sale at a time
        if ($request->filled('priority')) {
            $prio = (int) $request->priority;
            $holder = \App\Models\PrototypeSale::whereIn("status", ["confirmed", "in_production", "pending", "completed"])
                ->whereNull('deleted_at')
                ->where('priority', $prio)
                ->where('id', '!=', $sale->id)
                ->first();
            if ($holder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prio ' . $prio . ' ay nagamit na sa ' . $holder->sales_number . ' (' . ($holder->customer_name ?: 'no customer') . '). Alisin muna ang tag doon bago gamitin dito.',
                ], 422);
            }
        }

        $clearedPriority = !$request->filled('priority') && $sale->priority;
        $sale->priority = $request->filled('priority') ? (int) $request->priority : null;
        $sale->save();

        // AUTO-PROMOTE: kapag may na-clear na PRIO slot, i-shift pataas ang mga natitirang PRIO
        // (ex. dating PRIO 2 → PRIO 1) para walang gap. Ang priority_map ay para sa instant UI.
        $priorityMap = null;
        if ($clearedPriority) {
            $priorityMap = $this->reindexPriorities();
            $priorityMap[$sale->id] = null; // kasama ang na-clear na sale (para sa instant UI)
        }

        return response()->json([
            'success' => true,
            'priority' => $sale->priority,
            'priority_map' => $priorityMap,
            'message' => $sale->priority ? "Prio " . $sale->priority . " na ang order na ito" : 'Naalis ang priority tag',
        ]);
    }

    /**
     * AUTO-PROMOTE (reindex): kapag may na-clear na PRIO slot, i-shift pataas ang mga
     * natitirang PRIO numbers para walang gap — ex. na-DISPATCH ang PRIO 1, ang dating
     * PRIO 2 ay magiging PRIO 1. Same scope as the uniqueness check sa updatePriority.
     * Returns: [sale_id => priority] map ng lahat ng may PRIO (para sa instant UI update).
     * (Requested by Andrew 2026-09-01)
     */
    protected function reindexPriorities()
    {
        $sales = \App\Models\PrototypeSale::whereIn('status', ['confirmed', 'in_production', 'pending', 'completed'])
            ->whereNull('deleted_at')
            ->whereNotNull('priority')
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'priority']);

        $map = [];
        $next = 1;
        foreach ($sales as $sale) {
            if ((int) $sale->priority !== $next) {
                $sale->priority = $next;
                $sale->save();
            }
            $map[$sale->id] = $next;
            $next++;
        }

        return $map;
    }

    /**
     * Assign a GA to a stage of a job (claim button). GA can only assign themselves;
     * managers/admins can assign anyone.
     */
    public function assignGa(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !($user->isGa() || $user->isManager() || $user->isQa())) {
            abort(403, 'Unauthorized access.');
        }

        $sale = \App\Models\PrototypeSale::findOrFail($id);
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
        }
        $stage = $request->get('stage', '');
        $targetUserId = (int) $request->get('user_id', 0);

        $allowedStages = ['FOR SAMPLE', 'FOR APPROVAL', 'FOR FORMAT', 'PRINTING'];
        if (!in_array($stage, $allowedStages, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid stage.'], 422);
        }

        // Stage lock: stages that already finished (before current production stage) can't be assigned
        $stageOrder = ['FOR SAMPLE' => 0, 'FOR APPROVAL' => 1, 'FOR FORMAT' => 2, 'PRINTING' => 3, 'PRESSING' => 4, 'CUTTING' => 5];
        $curIdx = $stageOrder[$sale->production_stage] ?? 0;
        $targetIdx = $stageOrder[$stage] ?? 0;
        if ($targetIdx < $curIdx) {
            return response()->json([
                'success' => false,
                'message' => 'Tapos na ang ' . $stage . ' stage — hindi na pwedeng i-assign. Ibabalik muna ang job sa stage na ito para i-enable ulit.',
            ], 422);
        }

        // GA can only assign themselves; managers can assign anyone
        if ($user->isGa()) {
            // GA free choice: pwede mag-claim kung walang tag si manager.
            // Kapag may tag na sa ibang GA, hindi na pwedeng i-override.
            $existing = \App\Models\GaAssignment::where('prototype_sale_id', $sale->id)
                ->where('stage', $stage)
                ->first();
            if ($existing && $existing->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Claimed na ni ' . ($existing->user->name ?? 'isa pang GA') . ' ang ' . $stage . ' stage. Hingin mo kay Manager kung gusto mong palitan.',
                ], 409);
            }
            $targetUserId = $user->id;
        } else {
            if (!$targetUserId) {
                return response()->json(['success' => false, 'message' => 'Select a GA to assign.'], 422);
            }
        }

        $target = \App\Models\User::find($targetUserId);
        if (!$target || !$target->isGa()) {
            return response()->json(['success' => false, 'message' => 'Target user is not a GA.'], 422);
        }

        \App\Models\GaAssignment::updateOrCreate(
            ['prototype_sale_id' => $sale->id, 'stage' => $stage],
            [
                'user_id' => $targetUserId,
                'assigned_by' => $user->id,
                'assigned_at' => now(),
                'completed_at' => null,
            ]
        );

        \App\Models\GaAssignmentLog::create([
            'prototype_sale_id' => $sale->id,
            'stage' => $stage,
            'user_id' => $targetUserId,
            'action' => 'assigned',
            'actor_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => $stage . ' assigned to ' . $target->name,
        ]);
    }

    /**
     * Unassign a GA from a stage of a job.
     */
    public function unassignGa(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !($user->isGa() || $user->isManager() || $user->isQa())) {
            abort(403, 'Unauthorized access.');
        }

        $sale = \App\Models\PrototypeSale::findOrFail($id);
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
        }
        $stage = $request->get('stage', '');

        $assignment = \App\Models\GaAssignment::where('prototype_sale_id', $sale->id)
            ->where('stage', $stage)
            ->first();
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'No assignment found.'], 404);
        }

        // GA can only unassign themselves; managers can unassign anyone
        if ($user->isGa() && $assignment->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You can only unassign your own claim.'], 403);
        }

        \App\Models\GaAssignmentLog::create([
            'prototype_sale_id' => $sale->id,
            'stage' => $stage,
            'user_id' => $assignment->user_id,
            'action' => 'unassigned',
            'actor_id' => $user->id,
        ]);

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment removed.',
        ]);
    }

    /**
     * Mark a GA stage assignment as DONE (toggle). GA marks own work; managers can toggle anyone.
     */
    public function completeGa(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !($user->isGa() || $user->isManager() || $user->isQa())) {
            abort(403, 'Unauthorized access.');
        }

        $sale = \App\Models\PrototypeSale::findOrFail($id);
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
        }
        $stage = $request->get('stage', '');

        $allowedStages = ['FOR SAMPLE', 'FOR APPROVAL', 'FOR FORMAT', 'PRINTING'];
        if (!in_array($stage, $allowedStages, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid stage.'], 422);
        }

        $assignment = \App\Models\GaAssignment::where('prototype_sale_id', $sale->id)
            ->where('stage', $stage)
            ->first();
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'No assignment found.'], 404);
        }

        // GA can only toggle their own claim; managers can toggle anyone
        if ($user->isGa() && $assignment->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You can only mark your own claim as done.'], 403);
        }

        // Toggle: mark done, or bawiin kung nagkamali
        if ($assignment->completed_at) {
            $assignment->update(['completed_at' => null]);
            \App\Models\GaAssignmentLog::create([
                'prototype_sale_id' => $sale->id,
                'stage' => $stage,
                'user_id' => $assignment->user_id,
                'action' => 'uncompleted',
                'actor_id' => $user->id,
            ]);
            return response()->json(['success' => true, 'message' => 'DONE mark removed — binawi.', 'done' => false]);
        }

        $assignment->update(['completed_at' => now()]);
        \App\Models\GaAssignmentLog::create([
            'prototype_sale_id' => $sale->id,
            'stage' => $stage,
            'user_id' => $assignment->user_id,
            'action' => 'completed',
            'actor_id' => $user->id,
        ]);
        return response()->json(['success' => true, 'message' => 'Marked as DONE ✓', 'done' => true]);
    }

    /**
     * Verify payment for a sale.
     */
    public function calendar()
    {
        $departments = \DB::table('sales_departments')->where('is_active', true)->get();

        // Class Production Manager: Class department only
        $user = auth()->user();
        if ($user && $user->isClassScoped()) {
            $departments = collect([(object) ['id' => 4, 'name' => 'Class', 'code' => 'class', 'is_active' => true]]);
        }

        // Same production stage map + reverse map as the manager order list
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

        return view('sales.prototype.calendar', compact('departments', 'prodStageMap', 'statusToStage'));
    }

    public function calendarData(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $department = $request->department;

        // Class Production Manager: forced to Class department
        $user = auth()->user();
        if ($user && $user->isClassScoped()) {
            $department = 'Class';
        }

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])->whereIn('status', ['pending', 'confirmed', 'in_production', 'completed'])
            // Hide archived projects (consistent with kanban — archived = filed away)
            ->whereNull('archived_at');
        
        // Non-admin users only see their own sales (admin & COO see everything — consistent with manager list & kanban)
        // CPO/CMO/Sales Agents also see everything, but agent names/mockups are hidden in the formatted response
        // Prod Manager sees ALL Class sales (no agent scoping)
        if (!$user || (!$user->isAdmin() && !$user->isCoo() && !$user->isCpo() && !$user->isCmo() && !$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isClassScoped() && !$user->isGa())) {
            $query->where('sales_agent_id', $user ? $user->id : null);
        }
        
        // Filter by date range (use created_at, estimated_completion_date, or rescheduled_date)
        $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
              ->orWhereBetween('estimated_completion_date', [$startDate, $endDate])
              ->orWhereBetween('rescheduled_date', [$startDate, $endDate]);
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

            // Mockup thumbnail (same logic as manager list) — main cover first
            $mockups = is_string($p->mockup_images) ? json_decode($p->mockup_images, true) : ($p->mockup_images ?? []);
            $mainMockup = null;
            foreach ($mockups as $m) {
                if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
            }
            if (!$mainMockup && !empty($mockups)) $mainMockup = $mockups[0];
            $firstMockup = $mainMockup;
            $firstMockupUrl = is_string($firstMockup) ? $firstMockup : ($firstMockup['url'] ?? '');

            // Description summary (same as manager list)
            $descParts = [];
            foreach ($items as $it) {
                if (is_array($it) && !empty($it['name'])) {
                    $descParts[] = $it['name'];
                }
            }
            $description = $descParts ? implode(' + ', array_slice($descParts, 0, 2)) : '';

            // Product label (e.g. TSHIRT VNECK) — from first service's sublimationForm garment
            $productLabel = '';
            foreach ($services as $svc) {
                if (is_array($svc)) {
                    $sf = $svc['sublimationForm'] ?? null;
                    if (is_array($sf)) {
                        $garment = $sf['garment'] ?? null;
                        if (is_array($garment) && !empty($garment['name'])) {
                            $productLabel = trim($garment['name']);
                            break;
                        }
                        if (!empty($sf['description'])) {
                            $productLabel = trim(explode(' - ', $sf['description'])[0]);
                            break;
                        }
                    }
                }
            }
            if (!$productLabel) {
                // Prefer a short readable label extracted from the raw service name
                // e.g. "FS: MIKE/FS CC/WHITE ZIPPER/18 PCS/... - POLO ZIPPER (POLYDEX 180 GSM)" -> "POLO ZIPPER"
                $shortLabel = '';
                foreach ($services as $svc) {
                    $rawName = is_array($svc) ? ($svc['name'] ?? '') : (is_string($svc) ? $svc : '');
                    if (!$rawName) {
                        continue;
                    }
                    $clean = $rawName;
                    if (strpos($clean, ' - ') !== false) {
                        $clean = trim(substr($clean, strrpos($clean, ' - ') + 3));
                    }
                    $clean = trim(preg_replace('/\s*\([^)]*\)/', '', $clean));
                    $clean = trim(explode(' +', $clean)[0]);
                    $clean = trim($clean, " -/");
                    if ($clean !== '' && mb_strlen($clean) <= 60) {
                        $shortLabel = $clean;
                        break;
                    }
                }
                $productLabel = $shortLabel ?: ($description ? trim(explode(' + ', $description)[0]) : ($p->customer_name ?? ''));
            }

            // Photo lock (same rule as manager order list): non-admin/manager cannot move to Design+ without photos
            $dImgs = is_string($p->design_images) ? json_decode($p->design_images, true) : ($p->design_images ?? []);
            $hasPhotos = collect($dImgs)->contains('type', 'file_screenshot') && collect($dImgs)->contains('type', 'sample_color');
            $user = auth()->user();
            $canOverrideStatus = $user && $user->isManager();
            $isRestrictedViewer = $user && ($user->isCpo() || $user->isCmo() || $user->isSalesAgent() || $user->isSalesRepresentative() || $user->isGa());

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
                'balance_due_computed' => (float) $p->balance_due_computed,
                'kanban_status' => $p->kanban_status,
                'production_stage' => $p->production_stage,
                'priority' => $p->priority,
                'services' => $items,
                'services_raw' => $services,
                'total_qty' => $totalQty,
                'mockup_url' => $isRestrictedViewer ? null : $firstMockupUrl,
                'description' => $description,
                'product_label' => $productLabel,
                'sales_agent_name' => $isRestrictedViewer ? '' : ($p->sales_agent_name ? trim(explode(' ', trim($p->sales_agent_name))[0]) : ''),
                'has_photos' => $hasPhotos,
                'can_override' => $canOverrideStatus,
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

        // CEO (admin) and COO only — managers hanggang DONE lang, hindi pwede mag-archive.
        // (Requested by Andrew 2026-09-01)
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isCoo())) {
            return response()->json(['success' => false, 'message' => 'Only the CEO and COO can archive projects.'], 403);
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
        // CEO (admin) and COO only — managers hanggang DONE lang, hindi pwede sa Archive page.
        // (Requested by Andrew 2026-09-01)
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isCoo())) {
            abort(403, 'Only the CEO and COO can view archived projects.');
        }

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

        // CEO (admin) and COO only — same rule as archive.
        // (Requested by Andrew 2026-09-01)
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isCoo())) {
            return response()->json(['success' => false, 'message' => 'Only the CEO and COO can restore projects.'], 403);
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

        // Only managers/admins/staff/prod_manager can reschedule
        $user = auth()->user();
        if (!$user || !($user->isManager() || $user->role === 'staff')) {
            return response()->json([
                'success' => false,
                'message' => 'Only managers can reschedule projects.',
            ], 403);
        }

        $newDate = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
        $originalDate = $sale->estimated_completion_date ? \Carbon\Carbon::parse($sale->estimated_completion_date)->format('Y-m-d') : null;

        // NO-PAST RULE (server-side): the project can only be moved to TODAY (Manila) or a future
        // date. Pwede pa bumalik basta hindi pa tapos ang araw sa PH (e.g. Sept 1 pa ngayon → pwede
        // i-usog sa Sept 1). Once the day has passed (Sept 2 na), bawal na bumalik sa Sept 1.
        // (mirrors the frontend drag-drop check in calendar.blade.php)
        $todayManila = \Carbon\Carbon::now('Asia/Manila')->format('Y-m-d');
        if ($newDate < $todayManila) {
            return response()->json([
                'success' => false,
                'message' => 'Hindi pwedeng i-usog paurong sa nakaraang araw. Pwede lang sa ' . \Carbon\Carbon::parse($todayManila)->format('M d, Y') . ' (ngayon, PH) o mas future date.',
            ], 422);
        }

        $sale->rescheduled_date = $newDate;
        $sale->save();

        // Audit trail
        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $sale->id,
            'user_id' => $user->id,
            'action' => 'rescheduled',
            'description' => 'Project rescheduled from ' . ($originalDate ? \Carbon\Carbon::parse($originalDate)->format('M d, Y') : 'none') . ' to ' . \Carbon\Carbon::parse($newDate)->format('M d, Y') . ' on the calendar (original date kept).',
            'details' => json_encode([
                'from_date' => $originalDate,
                'to_date' => $newDate,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project moved to ' . \Carbon\Carbon::parse($newDate)->format('M d, Y') . '. Original date (' . ($originalDate ? \Carbon\Carbon::parse($originalDate)->format('M d, Y') : 'none') . ') is kept.',
            'rescheduled_date' => $newDate,
            'original_date' => $originalDate,
        ]);
    }

    /**
     * Recompute sale-level deposit_paid/balance_due/overpayment from VERIFIED payments only.
     * Called after payment status changes (reject/cancel/edit) so the stored overpayment
     * never shows a refund offer for money that isn't confirmed yet.
     */
    protected function recalcSalePaymentTotals(int $saleId): void
    {
        $sale = \DB::table('prototype_sales')->find($saleId);
        if (!$sale) {
            return;
        }
        $totalVerified = \App\Models\PrototypePayment::where('prototype_sale_id', $saleId)
            ->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])
            ->sum('amount');
        $totalRefunded = \App\Models\PrototypeRefund::where('prototype_sale_id', $saleId)
            ->where('refund_status', 'completed')
            ->sum('refund_amount');
        $netPaid = max($totalVerified - $totalRefunded, 0);
        $newBalanceDue = max((float) $sale->total_amount - $netPaid, 0);
        $newOverpayment = $netPaid > (float) $sale->total_amount ? ($netPaid - (float) $sale->total_amount) : 0;
        \DB::table('prototype_sales')->where('id', $saleId)->update([
            'deposit_paid' => $totalVerified,
            'balance_due' => $newBalanceDue,
            'overpayment' => $newOverpayment,
            'updated_at' => now(),
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

        // Two-verifier approval: confirm/cancel actions (confirm_reject, cancel_reject,
        // confirm_edit, cancel_edit) are done by a DIFFERENT verifier than the requester —
        // kaya ang account-ownership restriction sa ibaba ay HINDI dapat mag-apply sa kanila.
        $secondVerifierActions = ['confirm_reject', 'cancel_reject', 'confirm_edit', 'cancel_edit'];

        // Non-admin verifiers can only verify payments tagged to their own accounts
        if (!in_array($action, $secondVerifierActions, true)
            && !auth()->user()->isAdmin()
            && $sale->payment_account_id) {
            $accountOwner = \DB::table('payment_accounts')->where('id', $sale->payment_account_id)->value('user_id');
            if (!(int) $accountOwner || (int) $accountOwner !== (int) auth()->id()) {
                return response()->json(['error' => 'You can only verify payments for your own accounts.'], 403);
            }
        }

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

            $this->recalcSalePaymentTotals((int) $id);

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

            $this->recalcSalePaymentTotals((int) $id);

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

            $this->recalcSalePaymentTotals((int) $id);

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

        // Two-verifier approval: confirm/cancel actions (confirm_reject, cancel_reject,
        // confirm_edit, cancel_edit) are done by a DIFFERENT verifier than the requester —
        // kaya ang account-ownership restriction sa ibaba ay HINDI dapat mag-apply sa kanila.
        $secondVerifierActions = ['confirm_reject', 'cancel_reject', 'confirm_edit', 'cancel_edit'];

        // Non-admin verifiers can only verify payments tagged to their own accounts
        if (!in_array($action, $secondVerifierActions, true)
            && !auth()->user()->isAdmin()
            && $payment->payment_account_id) {
            $accountOwner = \DB::table('payment_accounts')->where('id', $payment->payment_account_id)->value('user_id');
            if (!(int) $accountOwner || (int) $accountOwner !== (int) auth()->id()) {
                return response()->json(['error' => 'You can only verify payments for your own accounts.'], 403);
            }
        }

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

            $this->recalcSalePaymentTotals((int) $saleId);

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

            $this->recalcSalePaymentTotals((int) $saleId);

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

            $this->recalcSalePaymentTotals((int) $saleId);

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

        // Recompute sale-level payment_status from actual payment records
        $this->syncSalePaymentStatus($saleId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('sales.prototype.verification')->with('success', $msg);
    }

    /**
     * Recompute a sale's payment_status from its prototype_payments.
     * Fixes stale 'pending' left behind when individual payments are verified
     * (the verify flow used to update only the payment row, never the sale).
     * Leaves the sale status untouched when no payment rows exist yet
     * (initial-deposit flow manages the sale status directly).
     */
    protected function syncSalePaymentStatus($saleId)
    {
        $payments = \App\Models\PrototypePayment::where('prototype_sale_id', $saleId)->get(['payment_status']);
        if ($payments->isEmpty()) {
            return;
        }
        $sale = \DB::table('prototype_sales')->find($saleId);
        if (!$sale) {
            return;
        }

        $statuses = $payments->pluck('payment_status');
        if ($statuses->contains('pending')) {
            $newStatus = 'pending';
        } elseif ($statuses->contains('reject_pending')) {
            $newStatus = 'reject_pending';
        } elseif ($statuses->contains('edit_pending')) {
            $newStatus = 'edit_pending';
        } elseif ((float) $sale->balance_due <= 0) {
            $newStatus = 'full_payment_verified';
        } elseif ($statuses->contains('full_payment_verified') || $statuses->contains('down_payment_verified')) {
            $newStatus = 'down_payment_verified';
        } elseif ($statuses->contains('additional_payment_verified')) {
            $newStatus = 'additional_payment_verified';
        } elseif ($statuses->contains('verified')) {
            $newStatus = 'verified';
        } else {
            return; // all rejected / nothing verified — leave as-is
        }

        \DB::table('prototype_sales')->where('id', $saleId)->update([
            'payment_status' => $newStatus,
            'updated_at' => now(),
        ]);
    }

    /**
     * Payment verification dashboard - shows all pending and recent payments.
     */
    public function paymentVerification()
    {
        $verifierUser = auth()->user();
        // Each verifier only sees payments tagged to their own payment accounts
        // (applies to admin too — Andrew sees only Drew GCash, not other verifiers' payments)
        $ownAccountFilter = $verifierUser ? $verifierUser->id : null;

        // Include initial deposits from prototype_sales that don't have matching prototype_payments yet
        $pendingPayments = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as verifier', 'prototype_payments.verified_by', '=', 'verifier.id')
            ->when($ownAccountFilter, fn($q) => $q->where('payment_accounts.user_id', $ownAccountFilter))
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
            ->when($ownAccountFilter, fn($q) => $q->where('payment_accounts.user_id', $ownAccountFilter))
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('prototype_payments')
                    ->whereColumn('prototype_payments.prototype_sale_id', '=', 'prototype_sales.id');
            })
            ->where('prototype_sales.deposit_paid', '>', 0)
            ->whereNull('prototype_sales.deleted_at')
            ->whereNull('prototype_sales.archived_at')
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
        // Only truly pending payments need verification — rejected ones are already handled (agent edits/resubmits)
        $pendingPayments = $pendingPayments
            ->where('prototype_payments.payment_status', 'pending')
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
            ->whereNull('prototype_sales.archived_at')
            ->when($ownAccountFilter, fn($q) => $q->where('payment_accounts.user_id', $ownAccountFilter))
            ->orderBy('prototype_payments.verified_at', 'desc')
            ->limit(50)
            ->get();

        // Dropdown for re-tagging shows ALL active accounts (incl. other verifiers'
        // and company accounts) so admin can re-tag payments between verifiers.
        // Card visibility above still stays scoped to the verifier's own accounts.
        $accounts = \App\Models\PaymentAccount::with('user')->where('is_active', true)
            ->get();

        // Pending rejections awaiting a second verifier (two-verifier approval)
        $pendingRejections = \DB::table('prototype_payments')
            ->leftJoin('prototype_sales', 'prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
            ->leftJoin('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->leftJoin('users as requester', 'prototype_payments.reject_requested_by', '=', 'requester.id')
            ->where('prototype_payments.payment_status', 'reject_pending')
            // Second-verifier approval: dapat makita ito ng LAHAT ng verifier (hindi lang
            // sa account owner) para may makapag-confirm — tinanggal ang ownAccountFilter dito
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
                ->whereNull('prototype_sales.archived_at')
                // Sale-level entries only for initial deposits — skip sales already showing
                // a reject_pending entry at the payment level (prevents duplicates)
                ->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                        ->from('prototype_payments')
                        ->whereColumn('prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
                        ->where('prototype_payments.payment_status', 'reject_pending');
                })
            // Second-verifier approval: visible sa lahat ng verifier (consistent sa cashflow page)
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
            // Second-verifier approval: visible sa lahat ng verifier (hindi lang sa account owner)
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
                ->whereNull('prototype_sales.archived_at')
                // Sale-level entries only for initial deposits — skip sales already showing
                // an edit_pending entry at the payment level (prevents duplicates)
                ->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                        ->from('prototype_payments')
                        ->whereColumn('prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
                        ->where('prototype_payments.payment_status', 'edit_pending');
                })
            // Second-verifier approval: visible sa lahat ng verifier (consistent sa cashflow page)
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
        $accountRefundTotals = \DB::table(\DB::raw('(SELECT DISTINCT COALESCE(r.refund_account_id, p.payment_account_id) as account_id, r.prototype_sale_id, r.refund_amount FROM prototype_refunds r LEFT JOIN prototype_payments p ON p.prototype_sale_id = r.prototype_sale_id AND p.payment_status IN (\'verified\', \'down_payment_verified\', \'additional_payment_verified\', \'full_payment_verified\') WHERE r.refund_status = \'completed\') as t'))
            ->select([
                't.account_id',
                \DB::raw('COALESCE(SUM(t.refund_amount), 0) as total_refunded'),
            ])
            ->groupBy('t.account_id')
            ->get()
            ->keyBy('account_id');

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
                // Sale-level entries only for initial deposits — skip sales already showing
                // a reject_pending entry at the payment level (prevents duplicates)
                ->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                        ->from('prototype_payments')
                        ->whereColumn('prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
                        ->where('prototype_payments.payment_status', 'reject_pending');
                })
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
                // Sale-level entries only for initial deposits — skip sales already showing
                // an edit_pending entry at the payment level (prevents duplicates)
                ->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                        ->from('prototype_payments')
                        ->whereColumn('prototype_payments.prototype_sale_id', '=', 'prototype_sales.id')
                        ->where('prototype_payments.payment_status', 'edit_pending');
                })
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
     * Show dedicated Sales Team dashboard — "My Sales" for agents/reps.
     */
    public function agentDashboard(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin() && !$user->isCoo() && !$user->isCpo() && !$user->isCmo() && !$user->isQa()) {
            abort(403, 'Unauthorized access.');
        }

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])
            ->where('sales_agent_id', $user->id);

        // Search: customer name or sales number
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('sales_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%');
            });
        }

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

        // Production stage counts for quick-view chips (computed BEFORE the stage filter narrows the list,
        // so all stage counts stay visible; respects search/date/payment/kanban/department/services filters)
        $stageRows = (clone $query)->get(['id', 'production_stage', 'kanban_status', 'services', 'time_requested_at', 'needed_by']);
        if ($request->filled('services')) {
            $serviceFilter = $request->services;
            $stageRows = $stageRows->filter(function ($sale) use ($serviceFilter) {
                $items = is_array($sale->services) ? $sale->services : (json_decode($sale->services, true) ?: []);
                foreach ($items as $item) {
                    if (($item['name'] ?? '') === $serviceFilter) {
                        return true;
                    }
                }
                return false;
            })->values();
        }
        $prodStageCounts = [];
        foreach ($stageRows as $sale) {
            $stage = $sale->production_stage ?: (match ($sale->kanban_status ?? 'new') {
                'new' => 'HOLD',
                'sample_approval' => 'FOR SAMPLE',
                'design' => 'FOR FORMAT',
                'production' => 'PRINTING',
                'quality_check' => 'QA',
                'ready_for_delivery' => 'DISPATCH',
                'delivered' => 'UNPAID',
                'completed' => 'DONE',
                default => 'HOLD',
            });
            $prodStageCounts[$stage] = ($prodStageCounts[$stage] ?? 0) + 1;
        }

        // Time Request counts for quick-view buttons (computed BEFORE the time_request filter narrows the list,
        // so all counts stay visible; respects search/date/payment/kanban/department/services/production_stage filters)
        $timeRequestCounts = [
            'pending' => 0,
            'set' => 0,
            'none' => 0,
        ];
        foreach ($stageRows as $sale) {
            if (!empty($sale->needed_by)) {
                $timeRequestCounts['set']++;
            } elseif (!empty($sale->time_requested_at)) {
                $timeRequestCounts['pending']++;
            } else {
                $timeRequestCounts['none']++;
            }
        }

        // Production stage filter — matches actual production_stage OR the stage derived from kanban status
        if ($request->filled('production_stage')) {
            $stageFilter = $request->production_stage;
            $stageToKanban = [
                'FOR SAMPLE' => 'sample_approval',
                'FOR APPROVAL' => 'sample_approval',
                'FOR FORMAT' => 'design',
                'PRINTING' => 'design',
                'PRESSING' => 'production',
                'CUTTING' => 'production',
                'SEWING' => 'production',
                'QA' => 'quality_check',
                'HOLD' => 'new',
                'DISPATCH' => 'ready_for_delivery',
                'UNPAID' => 'delivered',
                'DONE' => 'completed',
            ];
            $query->where(function ($q) use ($stageFilter, $stageToKanban) {
                $q->where('production_stage', $stageFilter)
                  ->orWhere(function ($q2) use ($stageFilter, $stageToKanban) {
                      $q2->whereNull('production_stage')
                         ->where('kanban_status', $stageToKanban[$stageFilter] ?? '');
                  });
            });
        }

        // Department/shop filter
        if ($request->filled('department')) {
            $query->where('department_name', $request->department);
        }

        // Time Request filter: pending (may request, wala pang set na oras), set (may needed_by na), none (walang request)
        if ($request->filled('time_request')) {
            $timeFilter = $request->time_request;
            if ($timeFilter === 'pending') {
                $query->whereNotNull('time_requested_at')->whereNull('needed_by');
            } elseif ($timeFilter === 'set') {
                $query->whereNotNull('needed_by');
            } elseif ($timeFilter === 'none') {
                $query->whereNull('time_requested_at')->whereNull('needed_by');
            }
        }

        // Completed sales sink to the bottom; newest first within each group, so the very bottom = oldest dates
        // Sort by days left (due date) or by needed-by date/time when requested — toggle asc/desc via sort & dir params
        $sort = $request->get('sort');
        $dir = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if ($sort === 'days_left') {
            $sales = $query
                ->orderByRaw("CASE WHEN kanban_status IN ('ready_for_delivery','delivered','completed') THEN 1 ELSE 0 END")
                ->orderByRaw("COALESCE(rescheduled_date, estimated_completion_date) IS NULL")
                ->orderByRaw("COALESCE(rescheduled_date, estimated_completion_date) " . $dir)
                ->get();
        } elseif ($sort === 'needed_by') {
            // Date & Time sort — by the agent-set needed_by time; sales without a set time sink to the bottom
            $sales = $query
                ->orderByRaw("CASE WHEN kanban_status IN ('ready_for_delivery','delivered','completed') THEN 1 ELSE 0 END")
                ->orderByRaw('needed_by IS NULL')
                ->orderByRaw('needed_by ' . $dir)
                ->get();
        } else {
            $sales = $query
                ->orderByRaw("CASE WHEN kanban_status = 'completed' THEN 1 ELSE 0 END")
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Services filter (services is a JSON array of items, so filter the collection)
        if ($request->filled('services')) {
            $serviceFilter = $request->services;
            $sales = $sales->filter(function ($sale) use ($serviceFilter) {
                $items = is_array($sale->services) ? $sale->services : (json_decode($sale->services, true) ?: []);
                foreach ($items as $item) {
                    if (($item['name'] ?? '') === $serviceFilter) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        // Verification request count per sale (kung ilang beses na nag-request ang agent)
        $verificationCounts = \App\Models\SaleNotification::where('type', 'verification_request')
            ->whereIn('sale_id', $sales->pluck('id'))
            ->selectRaw('sale_id, MAX(reminder_count) as cnt')
            ->groupBy('sale_id')
            ->pluck('cnt', 'sale_id');

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
        $filters = $request->only(['date_from', 'date_to', 'payment_status', 'kanban_status', 'department', 'search', 'services', 'production_stage', 'time_request', 'sort', 'dir']);

        // Totals summary: pieces, value, collected (net of refunds), balance
        $totalPieces = 0;
        $totalValue = 0;
        $totalCollected = 0;
        $totalBalance = 0;
        foreach ($sales as $sale) {
            $items = is_array($sale->services) ? $sale->services : (json_decode($sale->services, true) ?: []);
            foreach ($items as $item) {
                $totalPieces += (int) ($item['quantity'] ?? 0);
            }
            $totalValue += (float) $sale->total_amount;
            $collected = $sale->payments
                ->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])
                ->sum('amount');
            $refunded = $sale->refunds
                ->where('refund_status', 'completed')
                ->sum('refund_amount');
            $netCollected = max($collected - $refunded, 0);
            $totalCollected += $netCollected;
            $totalBalance += max((float) $sale->total_amount - $netCollected, 0);
        }

        // Unique services (item names) for the filter dropdown — from the agent's own sales
        $services = collect();
        foreach ($sales as $sale) {
            $items = is_array($sale->services) ? $sale->services : (json_decode($sale->services, true) ?: []);
            foreach ($items as $item) {
                if (!empty($item['name'])) {
                    $services->push($item['name']);
                }
            }
        }
        $services = $services->unique()->sort()->values()->toArray();

        // Production stage options for the filter dropdown (same tags as manager list)
        $prodStageOptions = [
            'HOLD' => 'Hold',
            'FOR SAMPLE' => 'For Sample',
            'FOR APPROVAL' => 'For Approval',
            'FOR FORMAT' => 'For Format',
            'PRINTING' => 'Printing',
            'PRESSING' => 'Pressing',
            'CUTTING' => 'Cutting',
            'SEWING' => 'Sewing',
            'QA' => 'QA',
            'DISPATCH' => 'Dispatched',
            'UNPAID' => 'Delivered (Unpaid)',
            'DONE' => 'Done',
        ];

        // Unread notifications for the agent
        $notifications = \App\Models\SaleNotification::with(['sale', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        $unreadCount = $notifications->where('is_read', false)->count();

        // Urgent notifications (2nd reminder+) that still need the agent's response
        $urgentNotifications = $notifications->filter(function ($n) {
            return $n->is_urgent
                && ($n->reminder_count ?? 1) >= 2
                && !$n->response
                && $n->is_read == false;
        })->values();

        return view('sales.prototype.agent-dashboard', compact('sales', 'statuses', 'statusLabels', 'departments', 'filters', 'notifications', 'urgentNotifications', 'unreadCount', 'totalPieces', 'totalValue', 'totalCollected', 'totalBalance', 'services', 'verificationCounts', 'prodStageOptions', 'prodStageCounts', 'timeRequestCounts'));
    }

    /**
     * Sales Dashboard — KPIs, trend chart, top products & opportunity table.
     * Agents see their own sales; admins/representatives see everything.
     */
    public function salesDashboard(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin() && !$user->isCoo() && !$user->isCpo() && !$user->isCmo() && !$user->isQa())) {
            abort(403, 'Unauthorized access.');
        }

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds']);

        // Scope: agents/COO/CPO/CMO see only their own sales
        if (($user->isSalesAgent() || $user->isCoo() || $user->isCpo() || $user->isCmo() || $user->isQa()) && !$user->isAdmin()) {
            $query->where('sales_agent_id', $user->id);
        }

        // Filters
        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'payment_status' => $request->payment_status,
            'kanban_status' => $request->kanban_status,
            'department' => $request->department,
            'product' => $request->product,
            'production_stage' => $request->production_stage,
        ];

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('kanban_status')) {
            $query->where('kanban_status', $request->kanban_status);
        }
        if ($request->filled('department')) {
            $query->where('department_name', $request->department);
        }

        // Production stage counts for quick-view chips (computed BEFORE the stage filter
        // narrows the list, so all stage counts stay visible)
        $stageRows = (clone $query)->get(['id', 'production_stage', 'kanban_status']);
        $prodStageCounts = [];
        foreach ($stageRows as $sale) {
            $stage = $sale->production_stage ?: (match ($sale->kanban_status ?? 'new') {
                'new' => 'HOLD',
                'sample_approval' => 'FOR SAMPLE',
                'design' => 'FOR FORMAT',
                'production' => 'PRINTING',
                'quality_check' => 'QA',
                'ready_for_delivery' => 'DISPATCH',
                'delivered' => 'UNPAID',
                'completed' => 'DONE',
                default => 'HOLD',
            });
            $prodStageCounts[$stage] = ($prodStageCounts[$stage] ?? 0) + 1;
        }

        // Production stage filter — matches actual production_stage OR the stage derived from kanban status
        if ($request->filled('production_stage')) {
            $stageFilter = $request->production_stage;
            $stageToKanban = [
                'FOR SAMPLE' => 'sample_approval',
                'FOR APPROVAL' => 'sample_approval',
                'FOR FORMAT' => 'design',
                'PRINTING' => 'design',
                'PRESSING' => 'production',
                'CUTTING' => 'production',
                'SEWING' => 'production',
                'QA' => 'quality_check',
                'HOLD' => 'new',
                'DISPATCH' => 'ready_for_delivery',
                'UNPAID' => 'delivered',
                'DONE' => 'completed',
            ];
            $query->where(function ($q) use ($stageFilter, $stageToKanban) {
                $q->where('production_stage', $stageFilter)
                  ->orWhere(function ($q2) use ($stageFilter, $stageToKanban) {
                      $q2->whereNull('production_stage')
                         ->where('kanban_status', $stageToKanban[$stageFilter] ?? '');
                  });
            });
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // ---- KPIs ----
        $totalOrders = $sales->count();
        $totalRevenue = $sales->sum('total_amount');
        $totalBalance = 0;
        $totalCollected = $sales->sum(function ($s) {
            $collected = $s->payments->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])->sum('amount');
            $refunded = $s->refunds->where('refund_status', 'completed')->sum('refund_amount');
            return max($collected - $refunded, 0);
        });
        $totalBalance = $sales->sum(function ($s) {
            $collected = $s->payments->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified'])->sum('amount');
            $refunded = $s->refunds->where('refund_status', 'completed')->sum('refund_amount');
            $netCollected = max($collected - $refunded, 0);
            return max((float) $s->total_amount - $netCollected, 0);
        });
        $totalPieces = 0;

        // ---- Product aggregation from services JSON ----
        $productMap = []; // name => ['qty' => int, 'revenue' => float, 'orders' => int, 'type' => string]
        $dailyTrend = []; // 'Y-m-d' => ['revenue' => float, 'orders' => int]

        foreach ($sales as $sale) {
            $day = $sale->created_at->format('Y-m-d');
            if (!isset($dailyTrend[$day])) {
                $dailyTrend[$day] = ['revenue' => 0, 'orders' => 0];
            }
            $dailyTrend[$day]['revenue'] += (float) $sale->total_amount;
            $dailyTrend[$day]['orders'] += 1;

            $items = $sale->services;
            if (is_string($items)) {
                $items = json_decode($items, true) ?: [];
            }
            $items = is_array($items) ? $items : [];

            foreach ($items as $item) {
                if (!is_array($item)) continue;

                $rawName = $item['name'] ?? ($item['garment']['name'] ?? null);
                if (!$rawName) continue;

                // Real product spec (e.g. "TSHIRT VNECK - DRIFIT | RAGLAN") instead of the
                // project name (which often defaults to "Additional Order - <customer>")
                $spec = \App\Models\PrototypeSale::itemSpecSummary($item);
                $name = ($spec && $spec !== 'Item') ? $spec : $rawName;

                $qty = (int) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unitPrice'] ?? 0);
                $lineTotal = (float) ($item['totalPrice'] ?? ($unitPrice * $qty));
                $type = $item['productType'] ?? ($item['department'] ?? 'General');

                if (!isset($productMap[$name])) {
                    $productMap[$name] = ['qty' => 0, 'revenue' => 0, 'orders' => 0, 'type' => $type, 'projects' => []];
                }
                $productMap[$name]['qty'] += $qty;
                $productMap[$name]['revenue'] += $lineTotal;
                $productMap[$name]['orders'] += 1;
                if (!in_array($rawName, $productMap[$name]['projects'])) {
                    $productMap[$name]['projects'][] = $rawName;
                }
                $totalPieces += $qty;
            }
        }

        // Sort products by revenue (best sellers first)
        uasort($productMap, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        // Product name filter (applied after aggregation)
        if ($request->filled('product')) {
            $needle = strtolower(trim($request->product));
            $productMap = array_filter($productMap, fn($p, $n) => str_contains(strtolower($n), $needle), ARRAY_FILTER_USE_BOTH);
        }

        // ---- Shop aggregation (department_name on the sale, fallback to item department) ----
        $shopMap = [];
        foreach ($sales as $sale) {
            $shop = trim((string) ($sale->department_name ?? ''));
            $items = $sale->services;
            if (is_string($items)) {
                $items = json_decode($items, true) ?: [];
            }
            $items = is_array($items) ? $items : [];
            if ($shop === '') {
                foreach ($items as $it) {
                    if (is_array($it) && !empty($it['department'])) {
                        $shop = trim((string) $it['department']);
                        break;
                    }
                }
            }
            if ($shop === '') {
                $shop = 'No Shop';
            }
            if (!isset($shopMap[$shop])) {
                $shopMap[$shop] = ['revenue' => 0, 'orders' => 0, 'pieces' => 0];
            }
            $shopMap[$shop]['revenue'] += (float) $sale->total_amount;
            $shopMap[$shop]['orders'] += 1;
            foreach ($items as $it) {
                if (is_array($it)) {
                    $shopMap[$shop]['pieces'] += (int) ($it['quantity'] ?? 0);
                }
            }
        }
        uasort($shopMap, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        $shopLabels = array_keys($shopMap);
        $shopRevenue = array_map(fn($s) => round($s['revenue'], 2), array_values($shopMap));
        $shopOrders = array_map(fn($s) => $s['orders'], array_values($shopMap));
        $shopPieces = array_map(fn($s) => $s['pieces'], array_values($shopMap));

        // Distinct shops for the filter dropdown (sale-level + item-level)
        $departments = \DB::table('prototype_sales')
            ->whereNotNull('department_name')
            ->distinct()
            ->orderBy('department_name')
            ->pluck('department_name');
        $itemDepts = collect();
        foreach (\DB::table('prototype_sales')->whereNotNull('services')->get(['services']) as $row) {
            $itms = json_decode($row->services, true) ?: [];
            foreach ((array) $itms as $it) {
                if (is_array($it) && !empty($it['department'])) {
                    $itemDepts->push(trim((string) $it['department']));
                }
            }
        }
        $departments = $departments->merge($itemDepts)->unique()->sort()->values();

        // ---- Chart data ----
        ksort($dailyTrend);

        // Fill gaps between first and last sale date so the trend line is continuous
        if (count($dailyTrend) > 1) {
            $dates = array_keys($dailyTrend);
            $start = \Carbon\Carbon::parse(min($dates));
            $end = \Carbon\Carbon::parse(max($dates));
            if ($start->diffInDays($end) <= 45) {
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    $key = $d->format('Y-m-d');
                    if (!isset($dailyTrend[$key])) {
                        $dailyTrend[$key] = ['revenue' => 0, 'orders' => 0];
                    }
                }
                ksort($dailyTrend);
            }
        }

        $trendLabels = array_keys($dailyTrend);
        $trendRevenue = array_map(fn($d) => round($d['revenue'], 2), array_values($dailyTrend));
        $trendOrders = array_map(fn($d) => $d['orders'], array_values($dailyTrend));

        $topProducts = array_slice($productMap, 0, 10, true);
        $productLabels = array_map(fn($name) => $topProducts[$name]['qty'] . 'x ' . $topProducts[$name]['type'], array_keys($topProducts));
        $productRevenue = array_map(fn($p) => round($p['revenue'], 2), $topProducts);

        // Payment status breakdown for pie chart
        $statusMap = [];
        foreach ($sales as $sale) {
            $st = $sale->payment_status ?: 'pending';
            if (!isset($statusMap[$st])) {
                $statusMap[$st] = 0;
            }
            $statusMap[$st] += (float) $sale->total_amount;
        }
        $paymentLabels = array_keys($statusMap);
        $paymentValues = array_map(fn($v) => round($v, 2), array_values($statusMap));

        $statuses = ['new', 'sample_approval', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $statusLabels = [
            'new' => 'New',
            'sample_approval' => 'Sample/Approval',
            'design' => 'Design',
            'production' => 'Production',
            'quality_check' => 'Quality Check',
            'ready_for_delivery' => 'Ready for Delivery',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
        ];

        $prodStageOptions = [
            'HOLD' => 'Hold',
            'FOR SAMPLE' => 'For Sample',
            'FOR APPROVAL' => 'For Approval',
            'FOR FORMAT' => 'For Format',
            'PRINTING' => 'Printing',
            'PRESSING' => 'Pressing',
            'CUTTING' => 'Cutting',
            'SEWING' => 'Sewing',
            'QA' => 'QA',
            'DISPATCH' => 'Dispatched',
            'UNPAID' => 'Delivered (Unpaid)',
            'DONE' => 'Done',
        ];

        return view('sales.prototype.sales-dashboard', compact(
            'sales', 'filters', 'statuses', 'statusLabels',
            'totalOrders', 'totalRevenue', 'totalBalance', 'totalCollected', 'totalPieces',
            'productMap', 'trendLabels', 'trendRevenue', 'trendOrders',
            'topProducts', 'productLabels', 'productRevenue',
            'paymentLabels', 'paymentValues',
            'shopLabels', 'shopRevenue', 'shopOrders', 'shopPieces', 'departments',
            'prodStageCounts', 'prodStageOptions'
        ));
    }

    /**
     * Mark a sale as delayed (agent confirms the item is delayed).
     * The manager order list will then float it to the top with a DELAYED badge.
     */
    public function markDelayed(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $sale = \App\Models\PrototypeSale::where('sales_agent_id', $user->id)->findOrFail($id);

        $sale->is_delayed = true;
        $sale->delayed_at = now();
        // Optional feedback/reason from the agent (customer feedback, reason for delay)
        $feedback = trim((string) $request->input('feedback'));
        if ($feedback !== '') {
            $sale->delay_feedback = $feedback;
            $sale->delay_feedback_updated_at = now();
        }
        $sale->save();

        return response()->json(['success' => true, 'is_delayed' => true]);
    }

    /**
     * Bulk request: manager enables the Set Time button for ALL sales that are
     * due within 1 day (or overdue) across all agents' My Sales dashboards.
     */
    public function requestTimeAll(Request $request)
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->role === 'manager' || $user->isCoo() || $user->isClassScoped())) {
            abort(403, 'Only managers can request times.');
        }

        $query = \App\Models\PrototypeSale::whereNull('deleted_at')
            ->whereNotNull('sales_agent_id')
            ->whereNotNull(\DB::raw('COALESCE(rescheduled_date, estimated_completion_date)'))
            ->where(\DB::raw('COALESCE(rescheduled_date, estimated_completion_date)'), '<=', now()->addDay())
            ->whereNotIn('kanban_status', ['ready_for_delivery', 'delivered', 'completed']);

        // Class Production Manager: Class department only
        if ($user->isClassScoped()) {
            $query->where('department_id', 4);
        }

        $sales = $query->get();
        $count = 0;
        foreach ($sales as $sale) {
            $sale->time_requested_at = now();
            $sale->time_requested_by = $user->id;
            $sale->save();

            // Notify the agent only if there's no pending time_request for this sale
            $already = \App\Models\SaleNotification::where('sale_id', $sale->id)
                ->where('type', 'time_request')
                ->where('to_user_id', $sale->sales_agent_id)
                ->whereNull('response')
                ->exists();
            if (!$already) {
                \App\Models\SaleNotification::create([
                    'sale_id' => $sale->id,
                    'from_user_id' => $user->id,
                    'to_user_id' => $sale->sales_agent_id,
                    'type' => 'time_request',
                    'is_urgent' => true,
                    'reminder_count' => 1,
                    'title' => '⏰ Set needed time: ' . $sale->sales_number,
                    'message' => 'Pakiset kung anong oras kailangan ang project (due within 1 day). I-click ang Set Time sa My Sales.',
                ]);
            }
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' sale(s) enabled for time setting. ✅',
            'count' => $count,
        ]);
    }

    /**
     * Manager requests the sales agent to set a needed-by time for the project.
     * Creates a notification to the agent so they know to set the time in My Sales.
     */
    public function requestTime(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->role === 'manager' || $user->isCoo() || $user->isClassScoped())) {
            abort(403, 'Only managers can request a time.');
        }

        $sale = \App\Models\PrototypeSale::findOrFail($id);

        // Class Production Manager: Class department only
        if ($user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
        }

        $sale->time_requested_at = now();
        $sale->time_requested_by = $user->id;
        $sale->save();

        // Notify the sales agent (if the sale has one)
        if ($sale->sales_agent_id) {
            \App\Models\SaleNotification::create([
                'sale_id' => $sale->id,
                'from_user_id' => $user->id,
                'to_user_id' => $sale->sales_agent_id,
                'type' => 'time_request',
                'is_urgent' => true,
                'reminder_count' => 1,
                'title' => '⏰ Set needed time: ' . $sale->sales_number,
                'message' => 'Pakiset kung anong oras kailangan ang project. I-click ang Set Time sa My Sales.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Time request sent to the sales agent. ✅',
        ]);
    }

    /**
     * Sales agent sets the needed-by time for the project (from My Sales).
     */
    public function submitTime(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative() && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $sale = \App\Models\PrototypeSale::where('sales_agent_id', $user->id)->findOrFail($id);

        $request->validate([
            'needed_by' => 'required|date',
        ]);

        $sale->needed_by = \Carbon\Carbon::parse($request->needed_by);
        $sale->save();

        // Mark any pending time_request notifications as responded
        \App\Models\SaleNotification::where('sale_id', $sale->id)
            ->where('type', 'time_request')
            ->where('to_user_id', $user->id)
            ->whereNull('response')
            ->update([
                'response' => 'Agent set needed time to ' . $sale->needed_by->format('M d, Y g:i A'),
                'responded_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Needed time saved! ✅',
            'needed_by' => $sale->needed_by->format('M d, Y g:i A'),
        ]);
    }

    /**
     * Delay review page — manager/admin reviews the agent's delay feedback
     * along with the project details.
     */
    public function delayReview($id)
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->role === 'manager' || $user->isCoo() || $user->isClassScoped())) {
            abort(403, 'Only managers can view delay reviews.');
        }

        $sale = \App\Models\PrototypeSale::with(['payments', 'refunds'])->findOrFail($id);

        // Class Production Manager: Class department only
        if ($user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
        }

        // Order items breakdown (same pattern as other views)
        $items = [];
        $services = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
        $services = is_array($services) ? $services : [];
        foreach ($services as $s) {
            if (is_string($s)) {
                $items[] = ['name' => $s, 'qty' => 1];
            } elseif (is_array($s)) {
                $items[] = $s;
            }
        }

        // Mockup thumbnail (main cover first)
        $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
        $mainMockup = null;
        foreach ($mockups as $m) {
            if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
        }
        if (!$mainMockup && !empty($mockups)) $mainMockup = $mockups[0];
        $mainMockupUrl = is_string($mainMockup) ? $mainMockup : ($mainMockup['url'] ?? '');

        // Stage colors (reuse same map as list view)
        $stageColors = [
            'FOR SAMPLE' => '#6f42c1',
            'FOR APPROVAL' => '#fd7e14',
            'FOR FORMAT' => '#0d6efd',
            'PRINTING' => '#198754',
            'PRESSING' => '#20c997',
            'CUTTING' => '#6c757d',
            'SEWING' => '#dc3545',
            'QA' => '#ffc107',
            'HOLD' => '#6c757d',
            'DISPATCH' => '#0dcaf0',
            'UNPAID' => '#d63384',
            'DONE' => '#198754',
        ];

        return view('sales.prototype.delay-review', compact('sale', 'items', 'mainMockupUrl', 'stageColors'));
    }

    /**
     * Save the manager/COO/CEO review of a delayed sale.
     * Status: acknowledged | resolved | dismissed. Sends a notification to the agent.
     */
    public function submitDelayReview(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->role === 'manager' || $user->isCoo() || $user->isClassScoped())) {
            abort(403, 'Only managers can review delays.');
        }

        $sale = \App\Models\PrototypeSale::findOrFail($id);
        $status = $request->input('delay_review_status');
        if (!in_array($status, ['acknowledged', 'resolved', 'dismissed'])) {
            return back()->with('error', 'Invalid review status.');
        }

        $sale->delay_review_status = $status;
        $sale->delay_review_notes = trim((string) $request->input('delay_review_notes'));
        $sale->delay_reviewed_by = $user->id;
        $sale->delay_reviewed_at = now();
        $sale->save();

        // Notify the agent who owns this sale
        if ($sale->sales_agent_id) {
            $statusLabels = ['acknowledged' => 'Acknowledged', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'];
            \App\Models\SaleNotification::create([
                'sale_id' => $sale->id,
                'from_user_id' => $user->id,
                'to_user_id' => $sale->sales_agent_id,
                'type' => 'delay_review',
                'title' => 'Delay reviewed: ' . $statusLabels[$status],
                'message' => $sale->sales_number . ' — ' . $statusLabels[$status] . '. ' . ($sale->delay_review_notes ?: 'Walang notes.') . ' (Reviewer: ' . $user->display_label . ')',
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Delay review saved — na-notify na ang agent. ✅');
    }

    /**
     * My Delays — compiles ALL delays reported by the logged-in agent,
     * with review status + reviewer feedback so they can quickly see
     * if their delay was acknowledged, resolved, or dismissed.
     */
    public function agentDelays()
    {
        $user = auth()->user();
        if (!$user || !($user->isSalesAgent() || $user->isSalesRepresentative() || $user->isAdmin() || $user->isCoo() || $user->isCpo() || $user->isCmo() || $user->isGa() || $user->isQa())) {
            abort(403, 'Unauthorized access.');
        }

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])
            ->where('is_delayed', 1);

        // Agents/COO/CPO/CMO see only their own delays
        if (($user->isSalesAgent() || $user->isSalesRepresentative() || $user->isCoo() || $user->isCpo() || $user->isCmo() || $user->isGa() || $user->isQa()) && !$user->isAdmin()) {
            $query->where('sales_agent_id', $user->id);
        }

        $delays = $query->orderBy('delayed_at', 'desc')->get();

        // Reviewer name cache
        $reviewerNames = [];
        $reviewerIds = $delays->pluck('delay_reviewed_by')->filter()->unique()->values();
        if ($reviewerIds->isNotEmpty()) {
            $reviewerNames = \App\Models\User::whereIn('id', $reviewerIds)->pluck('display_label', 'id')->toArray();
        }

        $statusLabels = [
            'acknowledged' => ['Acknowledged', '#f59e0b'],
            'resolved' => ['Resolved', '#22c55e'],
            'dismissed' => ['Dismissed', '#ef4444'],
        ];

        return view('sales.prototype.agent-delays', compact('delays', 'reviewerNames', 'statusLabels'));
    }

    /**
     * Delay list — all delayed sales in one page (with or without feedback),
     * so managers can review every delay in a single view.
     */
    public function delayList()
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->role === 'manager' || $user->isCoo() || $user->isClassScoped())) {
            abort(403, 'Only managers can view the delay list.');
        }

        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])
            ->where('is_delayed', 1);

        // Class Production Manager: Class department only
        if ($user->isClassScoped()) {
            $query->where('department_id', 4);
        }

        $query->orderByRaw('CASE WHEN delay_feedback IS NOT NULL AND delay_feedback != \'\' THEN 0 ELSE 1 END')
            ->orderBy('delayed_at', 'desc');

        $sales = $query->paginate(100);

        return view('sales.prototype.delay-list', compact('sales'));
    }

    /**
     * Backjob List — all sales with ACTIVE (pending) backjob comments from
     * production slips (main slip ga_notes + per-project additional_comments).
     * Looks like the manager order list so managers can see who still needs backjobs.
     */
    public function backjobList()
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->role === 'manager' || $user->isCoo() || $user->isClassScoped() || $user->isGa())) {
            abort(403, 'Only managers and GA can view the backjob list.');
        }

        $departmentLabels = [
            1 => "iPrint",
            2 => "Consol",
            3 => "Cinco",
            4 => "Class",
            5 => "MTO",
            6 => "Other",
        ];

        // All checklists that have any backjob comments
        $checklists = \App\Models\ProductionChecklist::where(function ($q) {
            $q->whereNotNull('ga_notes')->where('ga_notes', '!=', '')
              ->orWhereNotNull('additional_comments')->where('additional_comments', '!=', '')
              ->orWhereNotNull('product_comments')->where('product_comments', '!=', '');
        })->get();

        // Build one row per ACTIVE backjob comment (not done, not deleted)
        $rows = [];
        $mockupCache = [];
        $getMockup = function ($sale) use (&$mockupCache) {
            if (isset($mockupCache[$sale->id])) return $mockupCache[$sale->id];
            $mockups = is_string($sale->mockup_images ?? null) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
            $mainMockup = null;
            if (is_array($mockups)) {
                foreach ($mockups as $m) {
                    if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
                }
                if (!$mainMockup && !empty($mockups)) $mainMockup = $mockups[0];
            }
            $url = $mainMockup ? (is_string($mainMockup) ? $mainMockup : ($mainMockup['url'] ?? '')) : '';
            $mockupCache[$sale->id] = $url;
            return $url;
        };
        foreach ($checklists as $chk) {
            $sale = \DB::table('prototype_sales')->find($chk->sale_id);
            if (!$sale) continue;

            // Class Production Manager: Class department only
            if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
                continue;
            }

            // Main slip comments (ga_notes) — show only the FIRST active (FIFO)
            $mainNotes = [];
            try { $mainNotes = json_decode($chk->ga_notes ?? '', true) ?: []; } catch (\Exception $e) { $mainNotes = []; }
            if (is_array($mainNotes)) {
                foreach ($mainNotes as $c) {
                    if (!is_array($c)) continue;
                    if (!empty($c['deleted']) || !empty($c['done'])) continue;
                    // First active comment found → this is the current backjob
                    $rows[] = [
                        'sale_id' => $chk->sale_id,
                        'sales_number' => $sale->sales_number,
                        'customer' => $sale->customer_name,
                        'agent' => $sale->sales_agent_name,
                        'department_id' => $sale->department_id,
                        'project' => 'Main Production Slip',
                        'text' => $c['text'] ?? '',
                        'at' => $c['at'] ?? '',
                        'done' => false,
                        'mockup_url' => $getMockup($sale),
                        'priority' => $sale->priority ?? '',
                        'needed_by' => $sale->needed_by ?? '',
                    ];
                    break; // FIFO: only the earliest pending comment per section
                }
            }

            // Resolve item id → project name/mockup from sale services (shared by additional & product comments)
            $svcItems = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
            $nameById = [];
            foreach ((array) $svcItems as $svc) {
                if (is_array($svc) && !empty($svc['id'])) {
                    $nameById[(string) $svc['id']] = $svc['name'] ?? 'Additional Project';
                }
            }
            $mockupByItem = [];
            foreach ((array) $svcItems as $svc) {
                if (!is_array($svc) || empty($svc['id'])) continue;
                $sf = $svc['sublimationForm'] ?? [];
                $mock = $sf['mockup'] ?? $sf['mockupData'] ?? $sf['mockupUrl'] ?? null;
                if ($mock) {
                    $mockupByItem[(string) $svc['id']] = is_string($mock) ? $mock : (is_array($mock) && !empty($mock[0]['url']) ? $mock[0]['url'] : '');
                }
            }

            // Per-project comments (additional_comments keyed by item id)
            $addMap = [];
            try { $addMap = json_decode($chk->additional_comments ?? '', true) ?: []; } catch (\Exception $e) { $addMap = []; }
            if (is_array($addMap)) {
                foreach ($addMap as $itemId => $comments) {
                    if (!is_array($comments)) continue;
                    $projName = $nameById[(string) $itemId] ?? 'Additional Project';
                    $projMockup = $mockupByItem[(string) $itemId] ?? $getMockup($sale);
                    foreach ($comments as $c) {
                        if (!is_array($c)) continue;
                        if (!empty($c['deleted']) || !empty($c['done'])) continue;
                        // First active comment for this project → current backjob
                        $rows[] = [
                            'sale_id' => $chk->sale_id,
                            'sales_number' => $sale->sales_number,
                            'customer' => $sale->customer_name,
                            'agent' => $sale->sales_agent_name,
                            'department_id' => $sale->department_id,
                            'project' => $projName,
                            'text' => $c['text'] ?? '',
                            'at' => $c['at'] ?? '',
                            'done' => false,
                            'mockup_url' => $projMockup,
                            'priority' => $sale->priority ?? '',
                            'needed_by' => $sale->needed_by ?? '',
                        ];
                        break; // FIFO: only the earliest pending comment per project
                    }
                }
            }

            // Per-product comments (product_comments keyed by item id — main production slip)
            $prodMap = [];
            try { $prodMap = json_decode($chk->product_comments ?? '', true) ?: []; } catch (\Exception $e) { $prodMap = []; }
            if (is_array($prodMap) && !empty($prodMap)) {
                foreach ($prodMap as $itemId => $comments) {
                    if (!is_array($comments)) continue;
                    $projName = $nameById[(string) $itemId] ?? 'Main Product';
                    $projMockup = $mockupByItem[(string) $itemId] ?? $getMockup($sale);
                    foreach ($comments as $c) {
                        if (!is_array($c)) continue;
                        if (!empty($c['deleted']) || !empty($c['done'])) continue;
                        // First active comment for this product → current backjob
                        $rows[] = [
                            'sale_id' => $chk->sale_id,
                            'sales_number' => $sale->sales_number,
                            'customer' => $sale->customer_name,
                            'agent' => $sale->sales_agent_name,
                            'department_id' => $sale->department_id,
                            'project' => $projName,
                            'text' => $c['text'] ?? '',
                            'at' => $c['at'] ?? '',
                            'done' => false,
                            'mockup_url' => $projMockup,
                            'priority' => $sale->priority ?? '',
                            'needed_by' => $sale->needed_by ?? '',
                            'kind' => 'backjob',
                        ];
                        break; // FIFO: only the earliest pending comment per product
                    }
                }
            }
        }

        // OPEN FREEBIE SLIPS — approved freebie slips na hindi pa done (hanggang ma-done,
        // naka-display sa backjob list bilang reminder sa production).
        $freebieSlips = \DB::table('freebie_slips')
            ->join('freebie_requests', 'freebie_slips.freebie_request_id', '=', 'freebie_requests.id')
            ->join('prototype_sales', 'freebie_slips.sale_id', '=', 'prototype_sales.id')
            ->where('freebie_slips.status', 'open')
            ->select('freebie_slips.*', 'freebie_requests.requested_by', 'prototype_sales.sales_number',
                'prototype_sales.customer_name', 'prototype_sales.sales_agent_name',
                'prototype_sales.department_id', 'prototype_sales.priority', 'prototype_sales.needed_by')
            ->get();

        foreach ($freebieSlips as $fs) {
            // Class Production Manager: Class department only
            if ($user && $user->isClassScoped() && (int) $fs->department_id !== 4) {
                continue;
            }
            // Skip sales na wala na (cancelled/archived etc. — consistent sa checklists)
            $sale = \DB::table('prototype_sales')->find($fs->sale_id);
            if (!$sale) continue;

            $items = \DB::table('freebie_request_items')
                ->where('freebie_request_id', $fs->freebie_request_id)
                ->get();
            $summary = $items->map(fn($it) => $it->quantity . '× ' . $it->description)->join(', ');
            if ($summary === '') $summary = 'Freebie slip (open)';

            $rows[] = [
                'sale_id' => $fs->sale_id,
                'sales_number' => $fs->sales_number,
                'customer' => $fs->customer_name,
                'agent' => $fs->sales_agent_name,
                'department_id' => $fs->department_id,
                'project' => '🎁 Freebie Slip',
                'text' => $summary,
                'at' => \Carbon\Carbon::parse($fs->created_at)->format('M d, g:i A'),
                'done' => false,
                'mockup_url' => '',
                'priority' => $fs->priority ?? '',
                'needed_by' => $fs->needed_by ?? '',
                'kind' => 'freebie',
            ];
        }

        // Sort: newest backjob comment first
        usort($rows, function ($a, $b) {
            return strcmp($b['at'], $a['at']);
        });

        // Attach sale created date for display
        foreach ($rows as $i => $r) {
            $s = \DB::table('prototype_sales')->select('created_at', 'status', 'kanban_status')->find($r['sale_id']);
            $rows[$i]['created_at'] = $s->created_at ?? null;
            $rows[$i]['status'] = $s->status ?? '';
            $rows[$i]['kanban_status'] = $s->kanban_status ?? '';
        }

        return view('sales.prototype.backjob-list', compact('rows', 'departmentLabels'));
    }

    /**
     * Production Dashboard — live Class production overview.
     * Pulls together manager-list, kanban, calendar, delay, backjob & feedback data
     * for the Production (Class) dashboard. Prod managers see Class only.
     */
    public function productionDashboard(Request $request)
    {
        $user = auth()->user();

        // ---- Filters (date range, kanban status, search) ----
        $filters = [
            'date_from' => $request->date_from ?? '',
            'date_to'   => $request->date_to ?? '',
            'kanban'    => $request->kanban ?? '',
            'search'    => trim($request->search ?? ''),
        ];

        // Scope: Prod Manager → Class only; Admin/COO/Manager → all departments
        $deptFilter = function ($q) use ($user, $filters) {
            $q->whereIn('status', ['confirmed', 'in_production', 'pending', 'completed'])
              ->whereNull('archived_at');
            if ($user && $user->isClassScoped()) {
                $q->where('department_id', 4);
            }
            if (!empty($filters['date_from'])) {
                $q->where('created_at', '>=', $filters['date_from'] . ' 00:00:00');
            }
            if (!empty($filters['date_to'])) {
                $q->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
            }
            if (!empty($filters['kanban'])) {
                $q->where('kanban_status', $filters['kanban']);
            }
            if (!empty($filters['search'])) {
                $q->where(function ($sq) use ($filters) {
                    $sq->where('sales_number', 'like', '%' . $filters['search'] . '%')
                       ->orWhere('customer_name', 'like', '%' . $filters['search'] . '%')
                       ->orWhere('sales_agent_name', 'like', '%' . $filters['search'] . '%');
                });
            }
        };

        // ---- KPI: total orders & revenue (same scope as manager list) ----
        $kpiQuery = \App\Models\PrototypeSale::query();
        $deptFilter($kpiQuery);
        $kpiSales = $kpiQuery->get(['id', 'total_amount', 'created_at']);
        $totalOrders = $kpiSales->count();
        $totalRevenue = $kpiSales->sum('total_amount');

        // ---- Kanban status counts ----
        $kanbanOrder = ['new', 'sample_approval', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $kanbanLabels = [
            'new'                => 'New',
            'sample_approval'    => 'Sample/Approval',
            'design'             => 'Design',
            'production'         => 'Production',
            'quality_check'      => 'Quality Check',
            'ready_for_delivery' => 'Ready for Delivery',
            'delivered'          => 'Delivered',
            'completed'          => 'Completed',
        ];
        $kanbanCounts = [];
        foreach ($kanbanOrder as $k) { $kanbanCounts[$k] = 0; }
        $kbQuery = \App\Models\PrototypeSale::query();
        $deptFilter($kbQuery);
        foreach ($kbQuery->get(['kanban_status'])->groupBy('kanban_status') as $k => $grp) {
            if (isset($kanbanCounts[$k])) $kanbanCounts[$k] = $grp->count();
        }
        $kanbanTotal = array_sum($kanbanCounts);

        // ---- Production stages (same map as manager list) ----
        $stageLabels = [
            'FOR SAMPLE'   => 'Sample/Approval',
            'FOR APPROVAL' => 'Sample/Approval',
            'FOR FORMAT'   => 'Design',
            'PRINTING'     => 'Design',
            'PRESSING'     => 'Production',
            'CUTTING'      => 'Production',
            'SEWING'       => 'Production',
            'QA'           => 'Quality Check',
            'HOLD'         => 'Hold',
            'DISPATCH'     => 'Ready for Delivery',
            'UNPAID'       => 'Delivered',
            'DONE'         => 'Completed',
        ];
        $stageCounts = [];
        $stageQuery = \App\Models\PrototypeSale::query();
        $deptFilter($stageQuery);
        foreach ($stageQuery->get(['production_stage'])->groupBy('production_stage') as $st => $grp) {
            $label = $stageLabels[$st] ?? ($st ?: 'No Stage');
            $stageCounts[$label] = ($stageCounts[$label] ?? 0) + $grp->count();
        }
        arsort($stageCounts);

        // ---- Delayed count ----
        $delayedQuery = \App\Models\PrototypeSale::query();
        $deptFilter($delayedQuery);
        $delayedCount = $delayedQuery->where('is_delayed', 1)->count();

        // ---- Priority count ----
        $prioQuery = \App\Models\PrototypeSale::query();
        $deptFilter($prioQuery);
        $prioCount = $prioQuery->whereNotNull('priority')->where('priority', '>', 0)->count();

        // ---- Needed-by / due soon (calendar data) ----
        $dueQuery = \App\Models\PrototypeSale::query();
        $deptFilter($dueQuery);
        $dueSales = $dueQuery->whereNotNull('needed_by')
            ->orderBy('needed_by', 'asc')
            ->get(['id', 'sales_number', 'customer_name', 'needed_by', 'kanban_status', 'priority', 'is_delayed']);
        $upcomingDue = $dueSales->filter(fn ($s) => $s->needed_by >= now()->startOfDay())->take(8);
        $overdueDue = $dueSales->filter(fn ($s) => $s->needed_by < now()->startOfDay());
        $dueCount = $dueSales->count();

        // ---- Open production feedbacks (not yet resolved: open + acknowledged) ----
        $fbQuery = \DB::table('production_feedbacks')->whereIn('status', ['open', 'acknowledged']);
        if ($user && $user->isClassScoped()) {
            $fbQuery->whereIn('sale_id', \DB::table('prototype_sales')->where('department_id', 4)->pluck('id'));
        }
        $openFeedbackCount = $fbQuery->count();

        // ---- Active backjobs (pending comments) ----
        $checklists = \App\Models\ProductionChecklist::where(function ($q) {
            $q->whereNotNull('ga_notes')->where('ga_notes', '!=', '')
              ->orWhereNotNull('additional_comments')->where('additional_comments', '!=', '')
              ->orWhereNotNull('product_comments')->where('product_comments', '!=', '');
        })->get();
        $backjobCount = 0;
        foreach ($checklists as $chk) {
            $sale = \DB::table('prototype_sales')->select('department_id')->find($chk->sale_id);
            if (!$sale) continue;
            if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) continue;
            $active = 0;
            $mainNotes = json_decode($chk->ga_notes ?? '', true) ?: [];
            foreach ((array) $mainNotes as $c) {
                if (is_array($c) && empty($c['deleted']) && empty($c['done'])) { $active++; break; }
            }
            if (!$active) {
                $addMap = json_decode($chk->additional_comments ?? '', true) ?: [];
                foreach ((array) $addMap as $comments) {
                    foreach ((array) $comments as $c) {
                        if (is_array($c) && empty($c['deleted']) && empty($c['done'])) { $active++; break; }
                    }
                    if ($active) break;
                }
            }
            if (!$active) {
                $prodMap = json_decode($chk->product_comments ?? '', true) ?: [];
                foreach ((array) $prodMap as $comments) {
                    foreach ((array) $comments as $c) {
                        if (is_array($c) && empty($c['deleted']) && empty($c['done'])) { $active++; break; }
                    }
                    if ($active) break;
                }
            }
            $backjobCount += $active;
        }

        // ---- Pending change requests & addon requests ----
        $pendingChanges = 0;
        $pendingAddons = 0;
        if ($user && $user->isManager()) {
            $saleIds = \App\Models\PrototypeSale::query();
            $deptFilter($saleIds);
            $saleIds = $saleIds->pluck('id');
            $pendingChanges = \DB::table('prototype_sale_changes')->where('status', 'pending')->whereIn('sale_id', $saleIds)->count();
            $pendingAddons = \DB::table('sale_addon_requests')->where('status', 'pending')->whereIn('sale_id', $saleIds)->count();
        }

        // ---- Recent orders ----
        $recentQuery = \App\Models\PrototypeSale::query();
        $deptFilter($recentQuery);
        $recentSales = $recentQuery->orderBy('created_at', 'desc')
            ->limit(8)
            ->get(['id', 'sales_number', 'customer_name', 'created_at', 'total_amount', 'kanban_status', 'priority', 'is_delayed', 'needed_by']);

        // ---- Chart data: daily orders & revenue trend (last 14 days) ----
        $trendQuery = \App\Models\PrototypeSale::query();
        $deptFilter($trendQuery);
        $trendSales = $trendQuery->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['id', 'total_amount', 'created_at']);
        $trendByDay = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $trendByDay[$day] = ['label' => now()->subDays($i)->format('M d'), 'orders' => 0, 'revenue' => 0];
        }
        foreach ($trendSales as $s) {
            $day = $s->created_at->format('Y-m-d');
            if (isset($trendByDay[$day])) {
                $trendByDay[$day]['orders'] += 1;
                $trendByDay[$day]['revenue'] += (float) $s->total_amount;
            }
        }
        $trendLabels = array_column($trendByDay, 'label');
        $trendOrders = array_column($trendByDay, 'orders');
        $trendRevenue = array_map(fn ($v) => round($v, 2), array_column($trendByDay, 'revenue'));

        // ---- Chart data: kanban distribution (pie) ----
        $pieLabels = [];
        $pieValues = [];
        $pieColors = [];
        $pieColorMap = ['new' => '#94a3b8', 'sample_approval' => '#f43f5e', 'design' => '#8b5cf6', 'production' => '#3b82f6', 'quality_check' => '#f59e0b', 'ready_for_delivery' => '#10b981', 'delivered' => '#14b8a6', 'completed' => '#22c55e'];
        foreach ($kanbanCounts as $key => $cnt) {
            if ($cnt > 0) {
                $pieLabels[] = $kanbanLabels[$key] ?? ucfirst($key);
                $pieValues[] = $cnt;
                $pieColors[] = $pieColorMap[$key] ?? '#94a3b8';
            }
        }

        // ---- Chart data: production stages (bar) ----
        $stageLabels = array_keys($stageCounts);
        $stageValues = array_values($stageCounts);

        $isProdManager = $user && $user->isClassScoped();

        return view('production.tracking', compact(
            'totalOrders', 'totalRevenue', 'kanbanCounts', 'kanbanLabels', 'kanbanTotal',
            'stageCounts', 'delayedCount', 'prioCount', 'dueCount', 'upcomingDue', 'overdueDue',
            'openFeedbackCount', 'backjobCount', 'pendingChanges', 'pendingAddons',
            'recentSales', 'isProdManager', 'filters',
            'trendLabels', 'trendOrders', 'trendRevenue',
            'pieLabels', 'pieValues', 'pieColors',
            'stageLabels', 'stageValues'
        ));
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

        // PO (purchase order) handling: no payment now, only PO reference + PO form photo
        $isPo = ($request->payment_method === 'po');
        $poReference = $isPo ? ($request->po_reference ?: null) : null;
        if ($isPo) {
            $depositPaid = 0;
        }

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
            'payment_method' => $isPo ? 'po' : ($request->payment_method ?? 'cash'),
            'payment_owner' => 'company',
            'payment_account_id' => null,
            'payment_date' => $depositPaid > 0 ? now() : null,
            'reference_number' => null,
            'po_reference' => $poReference,
            'payment_status' => $isPo ? 'po' : ($depositPaid > 0 ? 'pending' : 'unpaid'),
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
        if (!$user || !$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Only managers can request refunds.']);
        }

        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        // Prod manager is Class-only
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Unauthorized access.');
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
        if (!$user || !$user->isManager()) {
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
        if (!$user || !($user->isManager() || $user->isCoo() || $user->isCpo() || $user->isCmo())) {
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
                'users.position as requested_by_position',
                'acceptors.name as accepted_by_name',
                'acceptors.position as accepted_by_position'
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

        // QA / Sales Agent: sariling benta lang — huwag gagalaw sa benta ng iba
        $user = $request->user();
        if ($user && $user->isQa() && (int) $sale->sales_agent_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: you can only manage your own sales.'], 403);
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

        // QA / Sales Agent: sariling benta lang — huwag gagalaw sa benta ng iba
        $user = $request->user();
        if ($user && $user->isQa() && (int) $sale->sales_agent_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: you can only manage your own sales.'], 403);
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
     * Upload an additional mockup image to a sale. The first mockup on a sale
     * automatically becomes the main cover; later uploads default to non-main.
     */
    public function uploadMockup(Request $request, $id)
    {
        $request->validate([
            'mockup_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        // QA / Sales Agent: sariling benta lang — huwag gagalaw sa benta ng iba
        $user = $request->user();
        if ($user && $user->isQa() && (int) $sale->sales_agent_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: you can only manage your own sales.'], 403);
        }

        $file = $request->file('mockup_image');
        $filename = 'mockup_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('uploads/mockups/' . $id, $filename, 'public');
        $url = '/storage/' . $filePath;

        $images = is_array($sale->mockup_images) ? $sale->mockup_images : [];
        $isFirst = empty($images);
        $images[] = [
            'name' => $file->getClientOriginalName(),
            'url' => $url,
            'type' => 'upload',
            'is_main' => $isFirst,
            'uploaded_by' => auth()->user()->name ?? 'Unknown',
            'uploaded_at' => now()->toDateTimeString(),
        ];
        $sale->mockup_images = $images;
        $sale->save();

        return response()->json([
            'success' => true,
            'message' => 'Mockup uploaded successfully.',
            'image' => $images[count($images) - 1],
        ]);
    }

    /**
     * Delete a mockup image. If the deleted mockup was the main cover,
     * the first remaining mockup is promoted to main. Records an audit log.
     */
    public function deleteMockup(Request $request, $id)
    {
        $request->validate([
            'url' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        // QA / Sales Agent: sariling benta lang — huwag gagalaw sa benta ng iba
        $user = $request->user();
        if ($user && $user->isQa() && (int) $sale->sales_agent_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: you can only manage your own sales.'], 403);
        }

        $images = is_array($sale->mockup_images) ? $sale->mockup_images : [];

        // Guard: huwag pwedeng mag-delete kung isa na lang ang mockup
        if (count($images) <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Hindi na pwedeng mag-delete — kailangan ng kahit isang mockup. Mag-upload muna ng bago bago burahin ito.',
            ], 422);
        }

        $targetUrl = $request->url;
        $removed = null;
        $kept = [];
        $wasMain = false;

        foreach ($images as $img) {
            $url = is_array($img) ? ($img['url'] ?? '') : $img;
            if ($url === $targetUrl) {
                $removed = $img;
                $wasMain = is_array($img) && !empty($img['is_main']);
                continue;
            }
            $kept[] = $img;
        }

        if (!$removed) {
            return response()->json(['success' => false, 'message' => 'Mockup not found.'], 404);
        }

        // Promote first remaining mockup if we just deleted the main cover
        if ($wasMain && !empty($kept) && is_array($kept[0])) {
            $kept[0]['is_main'] = true;
        }

        // Remove the physical file (best-effort)
        try {
            $rel = ltrim(str_replace('/storage/', '', $targetUrl), '/');
            if ($rel) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($rel);
            }
        } catch (\Throwable $e) {
            // ignore file deletion errors
        }

        $sale->mockup_images = $kept;
        $sale->save();

        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $sale->id,
            'user_id' => auth()->id() ?? 1,
            'action' => 'mockup_deleted',
            'description' => 'Deleted mockup ("' . ($removed['name'] ?? 'image') . '")' . ($request->reason ? ' Reason: ' . $request->reason : ''),
            'details' => json_encode([
                'url' => $targetUrl,
                'name' => $removed['name'] ?? null,
                'was_main' => $wasMain,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mockup deleted.',
            'promoted' => $wasMain,
        ]);
    }

    /**
     * Set which mockup is used as the main cover on the kanban board,
     * manager order list, and calendar. Other mockups stay as alternates.
     */
    public function setMainMockup(Request $request, $id)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        // QA / Sales Agent: sariling benta lang — huwag gagalaw sa benta ng iba
        $user = $request->user();
        if ($user && $user->isQa() && (int) $sale->sales_agent_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: you can only manage your own sales.'], 403);
        }

        $images = is_array($sale->mockup_images) ? $sale->mockup_images : [];
        $found = false;
        foreach ($images as &$img) {
            if (is_array($img)) {
                $img['is_main'] = ($img['url'] ?? '') === $request->url;
                if (($img['url'] ?? '') === $request->url) {
                    $found = true;
                }
            }
        }
        unset($img);

        if (!$found) {
            return response()->json(['success' => false, 'message' => 'Mockup not found.'], 404);
        }

        $sale->mockup_images = $images;
        $sale->save();

        \DB::table('prototype_sale_audit_logs')->insert([
            'sale_id' => $sale->id,
            'user_id' => auth()->id() ?? 1,
            'action' => 'mockup_main_changed',
            'description' => 'Set mockup as main cover.',
            'details' => json_encode(['url' => $request->url]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Main cover updated.',
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

        // Class Production Manager: only Class department sales (department_id = 4)
        $user = auth()->user();
        if ($user && $user->isClassScoped() && (int) $sale->department_id !== 4) {
            abort(403, 'Class department only.');
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
     * Agent requests payment verification from verifiers (24h cooldown per sale).
     */
    public function notifyVerifier(Request $request, $id)
    {
        $sale = \App\Models\PrototypeSale::find($id);
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $from = auth()->user();

        // Cooldown check: last verification_request for this sale within 24h blocks it
        $lastNotif = \App\Models\SaleNotification::where('sale_id', $sale->id)
            ->where('type', 'verification_request')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastNotif) {
            $minutesSince = (int) $lastNotif->created_at->diffInMinutes(now());
            if ($minutesSince < 1440) {
                $remainingMin = 1440 - $minutesSince;
                $remainingText = $remainingMin < 60 ? $remainingMin . ' min' : round($remainingMin / 60) . 'h';
                $agoText = $minutesSince < 60 ? $minutesSince . ' min' : round($minutesSince / 60) . 'h';
                return response()->json([
                    'success' => false,
                    'cooldown' => true,
                    'message' => "Na-request na ang verification {$agoText} ago. Pwede ulit mag-request pagkatapos ng {$remainingText}.",
                ]);
            }
        }

        // Verifiers = account owner of the tagged payment account (fallback: admin/staff)
        $ownerId = null;
        $pendingPayment = \App\Models\PrototypePayment::where('prototype_sale_id', $sale->id)
            ->where('payment_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
        $accountId = $pendingPayment ? $pendingPayment->payment_account_id : $sale->payment_account_id;
        if ($accountId) {
            $ownerId = \DB::table('payment_accounts')->where('id', $accountId)->value('user_id');
        }

        $verifiers = collect();
        if ($ownerId) {
            $verifiers = \App\Models\User::where('id', $ownerId)->get();
        }
        if ($verifiers->isEmpty()) {
            $verifiers = \App\Models\User::whereIn('role', ['admin', 'staff'])->get();
        }
        if ($verifiers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Walang available na verifier ngayon.']);
        }

        $customer = $sale->customer_name ?: 'customer';
        $title = '🔔 Verification requested: ' . $sale->sales_number;
        $message = 'May pending payment para sa order ni ' . $customer . '. Pakiverify na lang po.';

        $notifCount = \App\Models\SaleNotification::where('sale_id', $sale->id)
            ->where('type', 'verification_request')
            ->count() + 1;

        foreach ($verifiers as $verifier) {
            \App\Models\SaleNotification::create([
                'sale_id' => $sale->id,
                'from_user_id' => $from ? $from->id : null,
                'to_user_id' => $verifier->id,
                'type' => 'verification_request',
                'is_urgent' => false,
                'reminder_count' => $notifCount,
                'title' => $title,
                'message' => $message,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verifier notified! ✅',
            'title' => $title,
            'reminder_count' => $notifCount,
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

    /**
     * Special Price Review List — read-only page listing all orders whose
     * services JSON contains a special price override (sublimation hasSpecialPrice
     * or garment printing isSpecialPrice). Shows the stored reason + project for
     * manager/admin review. Purely additive; no existing feature/logic touched.
     */
    public function specialPriceList()
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isCoo())) {
            abort(403, 'Only the CEO (admin) and COO can view the special price review list.');
        }

        $request = request();
        $q = trim($request->get('q', ''));

        // Candidates: services JSON mentioning either special-price flag (raw LIKE is
        // safest here — the flag can live at item level, sublimationForm, or printing).
        $query = \App\Models\PrototypeSale::with(['payments', 'refunds'])
            ->whereNull('archived_at')
            ->where(function ($sub) {
                $sub->whereRaw("services LIKE '%hasSpecialPrice%'")
                    ->orWhereRaw("services LIKE '%isSpecialPrice%'")
                    ->orWhereRaw("services LIKE '%specialPriceReason%'");
            })
            ->when($user->isClassScoped(), function ($query) {
                $query->where('department_id', 4);
            })
            ->when(filled($q), function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('sales_number', 'like', '%' . $q . '%')
                        ->orWhere('customer_name', 'like', '%' . $q . '%');
                });
            })
            ->orderByDesc('created_at');

        $sales = $query->paginate(100)->withQueryString();

        // Extract special price lines from each sale's services JSON.
        // NOTE: "Additional Order" items store specialPrice as an OBJECT
        // { price, reason } instead of a plain number — unwrap it so the review
        // list shows the real price + reason (not a bogus ₱1.00 from casting).
        $lines = collect();
        foreach ($sales as $sale) {
            $svc = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
            $agent = trim((string) ($sale->sales_agent_name ?? ''));
            foreach ((array) $svc as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $sub = $item['sublimationForm'] ?? [];
                $print = $item['printing'] ?? [];

                $subSpecial = !empty($sub['hasSpecialPrice']) || !empty($item['hasSpecialPrice']);
                $printSpecial = !empty($print['isSpecialPrice']) || !empty($item['isSpecialPrice']);

                if ($subSpecial) {
                    $spRaw = $sub['specialPrice'] ?? $item['specialPrice'] ?? null;
                    if (is_array($spRaw)) {
                        $price = $spRaw['price'] ?? null;
                        $reason = $spRaw['reason'] ?? ($sub['specialPriceReason'] ?? $item['specialPriceReason'] ?? '');
                    } else {
                        $price = $spRaw;
                        $reason = $sub['specialPriceReason'] ?? $item['specialPriceReason'] ?? '';
                    }
                    $lines->push([
                        'sale'      => $sale,
                        'kind'      => 'Sublimation',
                        'itemName'  => $item['name'] ?? (is_array($sub['garment'] ?? null) ? ($sub['garment']['name'] ?? '—') : ($sub['garment'] ?? '—')),
                        'project'   => $sub['projectName'] ?? $item['projectName'] ?? '',
                        'qty'       => (int) ($item['quantity'] ?? $item['totalQty'] ?? 0),
                        'price'     => $price,
                        'reason'    => (string) $reason,
                        'agent'     => $agent,
                        'lineKey'   => (string) ($item['id'] ?? ('n' . crc32(($item['name'] ?? '') . '|Sublimation'))) . '|Sublimation',
                        'reviewed'  => null,
                    ]);
                }
                if ($printSpecial) {
                    $spRaw = $print['specialTotal'] ?? $item['specialPrice'] ?? null;
                    if (is_array($spRaw)) {
                        $price = $spRaw['price'] ?? null;
                        $reason = $spRaw['reason'] ?? ($print['specialReason'] ?? $item['specialReason'] ?? '');
                    } else {
                        $price = $spRaw;
                        $reason = $print['specialReason'] ?? $item['specialReason'] ?? '';
                    }
                    $lines->push([
                        'sale'      => $sale,
                        'kind'      => 'Garment Print',
                        'itemName'  => $item['name'] ?? ($print['printType'] ?? '—'),
                        'project'   => $item['projectName'] ?? '',
                        'qty'       => (int) ($item['totalQty'] ?? $item['quantity'] ?? $print['printQty'] ?? 0),
                        'price'     => $price,
                        'reason'    => (string) $reason,
                        'agent'     => $agent,
                        'lineKey'   => (string) ($item['id'] ?? ('n' . crc32(($item['name'] ?? '') . '|Garment Print'))) . '|Garment Print',
                        'reviewed'  => null,
                    ]);
                }
            }
        }

        // Attach existing CEO/COO review ("checked") state to each line
        if ($lines->isNotEmpty()) {
            $mappedSaleIds = $lines->pluck('sale.id')->unique()->values();
            $reviewRows = \DB::table('prototype_special_price_reviews')
                ->whereIn('sale_id', $mappedSaleIds)
                ->get()
                ->keyBy(fn ($r) => $r->sale_id . '|' . $r->line_key);
            $reviewerIds = $reviewRows->pluck('reviewed_by')->unique()->filter()->values();
            $reviewerNames = \App\Models\User::whereIn('id', $reviewerIds)->get()->pluck('display_label', 'id');
            $lines = $lines->map(function ($line) use ($reviewRows, $reviewerNames) {
                $key = $line['sale']->id . '|' . $line['lineKey'];
                $row = $reviewRows->get($key);
                if ($row) {
                    $line['reviewed'] = [
                        'by' => $reviewerNames[$row->reviewed_by] ?? ('User #' . $row->reviewed_by),
                        'at' => \Carbon\Carbon::parse($row->reviewed_at)->format('M d, g:i A'),
                    ];
                }
                return $line;
            });
        }

        // Unmapped = sales na may TUNAY na special-price flag (hindi lang "false"
        // o "0" na naka-store sa JSON) pero hindi natin na-extract bilang line.
        $hasTruthyFlag = function ($node) use (&$hasTruthyFlag) {
            if (!is_array($node)) {
                return false;
            }
            foreach ($node as $k => $v) {
                if (is_string($k)) {
                    $lk = strtolower($k);
                    if ($lk === 'hasspecialprice' || $lk === 'isspecialprice') {
                        if ($v === true) {
                            return true;
                        }
                        if (is_string($v) && trim($v) !== '' && strtolower(trim($v)) !== 'false' && trim($v) !== '0') {
                            return true;
                        }
                        if (is_numeric($v) && (float) $v > 0) {
                            return true;
                        }
                    }
                }
                if ($hasTruthyFlag($v)) {
                    return true;
                }
            }
            return false;
        };
        $mapped = $lines->pluck('sale.id')->unique()->flip();
        $unmapped = $sales->filter(function ($s) use ($mapped, $hasTruthyFlag) {
            if ($mapped->has($s->id)) {
                return false;
            }
            $svc = is_string($s->services) ? json_decode($s->services, true) : ($s->services ?? []);
            return $hasTruthyFlag($svc);
        });

        $departmentLabels = [1 => 'iPrint', 2 => 'Consol', 3 => 'Cinco', 4 => 'Class', 5 => 'MTO', 6 => 'Other'];

        return view('sales.prototype.special-price-list', compact('sales', 'lines', 'unmapped', 'q', 'departmentLabels'));
    }

    /**
     * Toggle the CEO/COO "checked" review state of one special-price line.
     * Additive only — no other logic is touched.
     */
    public function toggleSpecialPriceReview(Request $request)
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isCoo())) {
            return response()->json(['success' => false, 'message' => 'Only the CEO (admin) and COO can mark special prices as checked.'], 403);
        }

        $data = $request->validate([
            'sale_id'  => 'required|integer',
            'line_key' => 'required|string|max:255',
        ]);

        $existing = \DB::table('prototype_special_price_reviews')
            ->where('sale_id', $data['sale_id'])
            ->where('line_key', $data['line_key'])
            ->first();

        if ($existing) {
            \DB::table('prototype_special_price_reviews')
                ->where('id', $existing->id)
                ->delete();

            return response()->json(['success' => true, 'checked' => false]);
        }

        \DB::table('prototype_special_price_reviews')->insert([
            'sale_id'     => $data['sale_id'],
            'line_key'    => $data['line_key'],
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['success' => true, 'checked' => true]);
    }
}
