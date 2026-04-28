<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Pricing Rules - CLASS Apparel PH</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .rule-editor-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
        }
        .header h1 { color: #2563eb; font-weight: 700; }
        .header p { color: #64748b; font-size: 1.1rem; }
        .section-title {
            color: #1e40af;
            border-left: 4px solid #2563eb;
            padding-left: 15px;
            margin: 30px 0 20px 0;
            font-weight: 600;
        }
        .price-input {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: right;
            width: 140px;
            transition: all 0.3s;
        }
        .price-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        .combo-row, .bulk-row {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .btn-add {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-add:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        .btn-remove {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .btn-remove:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        .btn-save {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }
        .alert-success {
            background: #d1fae5;
            border: 2px solid #10b981;
            border-radius: 10px;
            color: #065f46;
        }
        .alert-error {
            background: #fee2e2;
            border: 2px solid #ef4444;
            border-radius: 10px;
            color: #7f1d1d;
        }
        .nav-tabs { border-bottom: 2px solid #e2e8f0; }
        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 600;
            border: none;
            padding: 12px 25px;
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
        }
        .nav-tabs .nav-link.active {
            color: #2563eb;
            background: white;
            border-bottom: 3px solid #2563eb;
        }
        .tab-content {
            background: white;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 10px 10px;
            padding: 25px;
        }
        .linked-price-badge {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 10px;
        }
        .cost-column {
            font-size: 0.9rem;
            color: #64748b;
        }
        .master-item-select {
            min-width: 200px;
        }
        .sub-tab-link {
            font-size: 0.95rem;
            padding: 8px 16px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="rule-editor-card">
                    <!-- Header -->
                    <div class="header">
                        <h1><i class="fas fa-sliders-h me-2"></i> Pricing Rule Editor</h1>
                        <p>Edit print prices, combo discounts, and bulk pricing rules</p>
                        <div class="mt-3">
                            <a href="{{ route('printing.pricing') }}" class="btn btn-outline-primary me-2">
                                <i class="fas fa-calculator me-1"></i> Back to Calculator
                            </a>
                            <a href="{{ route('printing.public') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-external-link-alt me-1"></i> View Public Calculator
                            </a>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div id="message-container" class="mb-4" style="display: none;">
                        <div class="alert" id="message-alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <span id="message-text"></span>
                        </div>
                    </div>

                    <!-- Print Type Selector -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="fw-bold mb-2"><i class="fas fa-print me-1"></i> Printing Type:</label>
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('printing.rules', ['type' => 'dtf']) }}" class="btn {{ $printType == 'dtf' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg">
                                    <i class="fas fa-print me-1"></i> DTF Print
                                </a>
                                <a href="{{ route('printing.rules', ['type' => 'sublimation']) }}" class="btn {{ $printType == 'sublimation' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg">
                                    <i class="fas fa-fire me-1"></i> Sublimation
                                </a>
                                <a href="{{ route('printing.rules', ['type' => 'silkscreen']) }}" class="btn {{ $printType == 'silkscreen' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg">
                                    <i class="fas fa-palette me-1"></i> Silk Screen
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Main Tabs -->
                    <ul class="nav nav-tabs" id="ruleTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="prices-tab" data-bs-toggle="tab" data-bs-target="#prices" type="button" role="tab">
                                <i class="fas fa-tag me-1"></i> Print Prices
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="combos-tab" data-bs-toggle="tab" data-bs-target="#combos" type="button" role="tab">
                                <i class="fas fa-percentage me-1"></i> Combo Discounts
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab">
                                <i class="fas fa-boxes me-1"></i> Bulk Discounts
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="ruleTabsContent">
                        <!-- ==================== PRINT PRICES TAB ==================== -->
                        <div class="tab-pane fade show active" id="prices" role="tabpanel">
                            <h4 class="section-title">Print Size Prices</h4>
                            <p class="text-muted mb-4">
                                Link each print size to a Product Pricing item. Prices auto-fill from the linked pricing tiers.
                            </p>

                            <div class="table-responsive">
                                <table class="table table-hover" id="prices-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Print Size</th>
                                            <th>Linked Product</th>
                                            <th>Supplier Cost</th>
                                            <th>Sales Team Price</th>
                                            <th>Agent Cost</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($prices as $price)
                                        @php
                                            $linked = $price->masterItem;
                                            $rowPricings = $linked ? $linked->productPricings->keyBy('price_tier') : collect();
                                        @endphp
                                        <tr id="price-row-{{ $price->id }}">
                                            <td><strong>{{ $price->name }}</strong></td>
                                            <td>
                                                <select class="form-select form-select-sm master-item-select" 
                                                        data-price-id="{{ $price->id }}"
                                                        onchange="onMasterItemChange({{ $price->id }}, this)">
                                                    <option value="">-- None --</option>
                                                    @foreach($productPricingOptions as $opt)
                                                        @php
                                                            $pricings = $opt->productPricings->keyBy('price_tier');
                                                            $supplier = isset($pricings['supplier_cost']) && !is_null($pricings['supplier_cost']->final_price) ? number_format($pricings['supplier_cost']->final_price, 2) : '—';
                                                            $sales = isset($pricings['sales_team']) && !is_null($pricings['sales_team']->final_price) ? number_format($pricings['sales_team']->final_price, 2) : '—';
                                                            $agent = isset($pricings['agent_cost']) && !is_null($pricings['agent_cost']->final_price) ? number_format($pricings['agent_cost']->final_price, 2) : '—';
                                                        @endphp
                                                        <option value="{{ $opt->id }}" 
                                                            {{ $price->master_item_id == $opt->id ? 'selected' : '' }}
                                                            data-supplier="{{ $supplier }}"
                                                            data-sales="{{ $sales }}"
                                                            data-agent="{{ $agent }}">
                                                            {{ $opt->description }} (S:₱{{ $supplier }}, ST:₱{{ $sales }}, A:₱{{ $agent }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="cost-column" id="supplier-cost-{{ $price->id }}">
                                                @if(isset($rowPricings['supplier_cost']) && !is_null($rowPricings['supplier_cost']->final_price))
                                                    ₱{{ number_format($rowPricings['supplier_cost']->final_price, 2) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="cost-column" id="sales-team-cost-{{ $price->id }}">
                                                @if(isset($rowPricings['sales_team']) && !is_null($rowPricings['sales_team']->final_price))
                                                    ₱{{ number_format($rowPricings['sales_team']->final_price, 2) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="cost-column" id="agent-cost-{{ $price->id }}">
                                                @if(isset($rowPricings['agent_cost']) && !is_null($rowPricings['agent_cost']->final_price))
                                                    ₱{{ number_format($rowPricings['agent_cost']->final_price, 2) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePrintPrice({{ $price->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Add New Price -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <h5 class="mb-3"><i class="fas fa-plus-circle me-2 text-success"></i> Add New Print Size</h5>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Print Size Name</label>
                                        <input type="text" class="form-control" id="newPrintName" placeholder="e.g. A3">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold">Link to Product Pricing (optional)</label>
                                        <select class="form-select" id="newMasterItemId">
                                            <option value="">-- None (manual entry) --</option>
                                            @foreach($productPricingOptions as $opt)
                                                <option value="{{ $opt->id }}">{{ $opt->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-success w-100" onclick="addNewPrintPrice()">
                                            <i class="fas fa-plus me-1"></i> Add Print Size
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Prices auto-fill from linked product pricing. New sizes appear in the calculator immediately.
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="mt-4 text-end">
                                <button class="btn btn-save" onclick="savePrintPrices()">
                                    <i class="fas fa-save me-2"></i> Save Price Changes
                                </button>
                            </div>
                        </div>

                        <!-- ==================== COMBO DISCOUNTS TAB ==================== -->
                        <div class="tab-pane fade" id="combos" role="tabpanel">
                            <h4 class="section-title">Combo Discounts</h4>
                            <p class="text-muted mb-4">Set fixed discounts when specific print sizes are ordered together on one garment.</p>

                            <!-- Sales Team / Agent sub-tabs -->
                            <ul class="nav nav-pills mb-3" id="comboTierTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link sub-tab-link active" id="combo-sales-tab" data-bs-toggle="pill" 
                                            data-bs-target="#combo-sales" type="button" role="tab">
                                        <i class="fas fa-users me-1"></i> Sales Team
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link sub-tab-link" id="combo-agent-tab" data-bs-toggle="pill" 
                                            data-bs-target="#combo-agent" type="button" role="tab">
                                        <i class="fas fa-user-tie me-1"></i> Agent
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="comboTierContent">
                                <!-- Sales Team Combos -->
                                <div class="tab-pane fade show active" id="combo-sales" role="tabpanel">
                                    <div id="combo-container-sales">
                                        @forelse($comboSalesTeam as $combo)
                                        <div class="combo-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <select class="form-select size1-select">
                                                        @foreach($prices as $price)
                                                        <option value="{{ $price->id }}" {{ $combo->size1_id == $price->id ? 'selected' : '' }}>
                                                            {{ $price->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-1 text-center"><span class="fw-bold">+</span></div>
                                                <div class="col-md-4">
                                                    <select class="form-select size2-select">
                                                        @foreach($prices as $price)
                                                        <option value="{{ $price->id }}" {{ $combo->size2_id == $price->id ? 'selected' : '' }}>
                                                            {{ $price->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="input-group">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control discount-value" 
                                                               value="{{ $combo->discount_value }}" step="0.01" min="0" placeholder="Discount">
                                                    </div>
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <button class="btn-remove" onclick="removeComboRow(this)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted text-center py-3">No Sales Team combo discounts yet. Click "Add Combo Discount" below.</p>
                                        @endforelse
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-add" onclick="addComboRow('sales')">
                                            <i class="fas fa-plus me-1"></i> Add Combo Discount
                                        </button>
                                    </div>
                                    <div class="mt-3 text-end">
                                        <button class="btn btn-save" onclick="saveCombos('sales_team')">
                                            <i class="fas fa-save me-2"></i> Save Sales Team Combos
                                        </button>
                                    </div>
                                </div>

                                <!-- Agent Combos -->
                                <div class="tab-pane fade" id="combo-agent" role="tabpanel">
                                    <div id="combo-container-agent">
                                        @forelse($comboAgent as $combo)
                                        <div class="combo-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <select class="form-select size1-select">
                                                        @foreach($prices as $price)
                                                        <option value="{{ $price->id }}" {{ $combo->size1_id == $price->id ? 'selected' : '' }}>
                                                            {{ $price->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-1 text-center"><span class="fw-bold">+</span></div>
                                                <div class="col-md-4">
                                                    <select class="form-select size2-select">
                                                        @foreach($prices as $price)
                                                        <option value="{{ $price->id }}" {{ $combo->size2_id == $price->id ? 'selected' : '' }}>
                                                            {{ $price->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="input-group">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control discount-value" 
                                                               value="{{ $combo->discount_value }}" step="0.01" min="0" placeholder="Discount">
                                                    </div>
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <button class="btn-remove" onclick="removeComboRow(this)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted text-center py-3">No Agent combo discounts yet. Click "Add Combo Discount" below.</p>
                                        @endforelse
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-add" onclick="addComboRow('agent')">
                                            <i class="fas fa-plus me-1"></i> Add Combo Discount
                                        </button>
                                    </div>
                                    <div class="mt-3 text-end">
                                        <button class="btn btn-save" onclick="saveCombos('agent')">
                                            <i class="fas fa-save me-2"></i> Save Agent Combos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== BULK DISCOUNTS TAB ==================== -->
                        <div class="tab-pane fade" id="bulk" role="tabpanel">
                            <h4 class="section-title">Bulk Discounts</h4>
                            <p class="text-muted mb-4">Set discounts based on transaction count (not garment count). Combo items count as 1 transaction.</p>

                            <!-- Sales Team / Agent sub-tabs -->
                            <ul class="nav nav-pills mb-3" id="bulkTierTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link sub-tab-link active" id="bulk-sales-tab" data-bs-toggle="pill" 
                                            data-bs-target="#bulk-sales" type="button" role="tab">
                                        <i class="fas fa-users me-1"></i> Sales Team
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link sub-tab-link" id="bulk-agent-tab" data-bs-toggle="pill" 
                                            data-bs-target="#bulk-agent" type="button" role="tab">
                                        <i class="fas fa-user-tie me-1"></i> Agent
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="bulkTierContent">
                                <!-- Sales Team Bulk -->
                                <div class="tab-pane fade show active" id="bulk-sales" role="tabpanel">
                                    <div id="bulk-container-sales">
                                        @forelse($bulkSalesTeam as $bulk)
                                        <div class="bulk-row">
                                            <div class="row align-items-center">
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control min-transactions" 
                                                               value="{{ $bulk->min_transactions ?? $bulk->min_garments }}" min="1" placeholder="Min"
                                                               style="background-color: white; color: black;">
                                                        <span class="input-group-text">to</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control max-transactions" 
                                                               value="{{ $bulk->max_transactions ?? $bulk->max_garments }}" min="1" placeholder="Max"
                                                               style="background-color: white; color: black;">
                                                        <span class="input-group-text">transactions</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <select class="form-control discount-type">
                                                        <option value="percentage" {{ ($bulk->discount_type ?? 'percentage') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                        <option value="fixed_amount" {{ ($bulk->discount_type ?? 'percentage') == 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <div class="input-group percentage-field" style="{{ ($bulk->discount_type ?? 'percentage') == 'fixed_amount' ? 'display:none;' : '' }}">
                                                        <input type="number" class="form-control discount-percent" 
                                                               value="{{ $bulk->discount_percent }}" step="0.01" min="0" max="100" placeholder="%">
                                                        <span class="input-group-text">% off</span>
                                                    </div>
                                                    <div class="input-group amount-field" style="{{ ($bulk->discount_type ?? 'percentage') == 'percentage' ? 'display:none;' : '' }}">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control discount-amount" 
                                                               value="{{ $bulk->discount_amount ?? 0 }}" step="0.01" min="0" placeholder="Amount">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input count-combo-as-one" 
                                                               {{ ($bulk->count_combo_as_one ?? true) ? 'checked' : '' }}>
                                                        <label class="form-check-label small">Combo = 1 transaction</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2 text-end">
                                                    <button class="btn-remove" onclick="removeBulkRow(this)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted text-center py-3">No Sales Team bulk discounts yet. Click "Add Bulk Discount" below.</p>
                                        @endforelse
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-add" onclick="addBulkRow('sales')">
                                            <i class="fas fa-plus me-1"></i> Add Bulk Discount
                                        </button>
                                    </div>
                                    <div class="mt-3 text-end">
                                        <button class="btn btn-save" onclick="saveBulk('sales_team')">
                                            <i class="fas fa-save me-2"></i> Save Sales Team Bulk
                                        </button>
                                    </div>
                                </div>

                                <!-- Agent Bulk -->
                                <div class="tab-pane fade" id="bulk-agent" role="tabpanel">
                                    <div id="bulk-container-agent">
                                        @forelse($bulkAgent as $bulk)
                                        <div class="bulk-row">
                                            <div class="row align-items-center">
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control min-transactions" 
                                                               value="{{ $bulk->min_transactions ?? $bulk->min_garments }}" min="1" placeholder="Min"
                                                               style="background-color: white; color: black;">
                                                        <span class="input-group-text">to</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <div class="input-group">
                                                        <input type="number" class="form-control max-transactions" 
                                                               value="{{ $bulk->max_transactions ?? $bulk->max_garments }}" min="1" placeholder="Max"
                                                               style="background-color: white; color: black;">
                                                        <span class="input-group-text">transactions</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <select class="form-control discount-type">
                                                        <option value="percentage" {{ ($bulk->discount_type ?? 'percentage') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                        <option value="fixed_amount" {{ ($bulk->discount_type ?? 'percentage') == 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <div class="input-group percentage-field" style="{{ ($bulk->discount_type ?? 'percentage') == 'fixed_amount' ? 'display:none;' : '' }}">
                                                        <input type="number" class="form-control discount-percent" 
                                                               value="{{ $bulk->discount_percent }}" step="0.01" min="0" max="100" placeholder="%">
                                                        <span class="input-group-text">% off</span>
                                                    </div>
                                                    <div class="input-group amount-field" style="{{ ($bulk->discount_type ?? 'percentage') == 'percentage' ? 'display:none;' : '' }}">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control discount-amount" 
                                                               value="{{ $bulk->discount_amount ?? 0 }}" step="0.01" min="0" placeholder="Amount">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input count-combo-as-one" 
                                                               {{ ($bulk->count_combo_as_one ?? true) ? 'checked' : '' }}>
                                                        <label class="form-check-label small">Combo = 1 transaction</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2 text-end">
                                                    <button class="btn-remove" onclick="removeBulkRow(this)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted text-center py-3">No Agent bulk discounts yet. Click "Add Bulk Discount" below.</p>
                                        @endforelse
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-add" onclick="addBulkRow('agent')">
                                            <i class="fas fa-plus me-1"></i> Add Bulk Discount
                                        </button>
                                    </div>
                                    <div class="mt-3 text-end">
                                        <button class="btn btn-save" onclick="saveBulk('agent')">
                                            <i class="fas fa-save me-2"></i> Save Agent Bulk
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    function showMessage(text, type = 'success') {
        const container = $('#message-container');
        const alert = $('#message-alert');
        alert.removeClass('alert-success alert-error');
        alert.addClass(type === 'success' ? 'alert-success' : 'alert-error');
        alert.find('i').removeClass().addClass('fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' me-2');
        $('#message-text').text(text);
        container.fadeIn();
        setTimeout(() => container.fadeOut(), 5000);
    }

    // === PRINT PRICES ===

    function onMasterItemChange(priceId, select) {
        const selectedOption = select.options[select.selectedIndex];
        
        if (!selectedOption.value) {
            $(`#supplier-cost-${priceId}`).html('<span class="text-muted">—</span>');
            $(`#sales-team-cost-${priceId}`).html('<span class="text-muted">—</span>');
            $(`#agent-cost-${priceId}`).html('<span class="text-muted">—</span>');
            return;
        }
        
        // Get prices from data attributes
        const supplierPrice = selectedOption.getAttribute('data-supplier');
        const salesPrice = selectedOption.getAttribute('data-sales');
        const agentPrice = selectedOption.getAttribute('data-agent');
        
        // Update display
        $(`#supplier-cost-${priceId}`).html(supplierPrice === '—' ? '<span class="text-muted">—</span>' : '₱' + supplierPrice);
        $(`#sales-team-cost-${priceId}`).html(salesPrice === '—' ? '<span class="text-muted">—</span>' : '₱' + salesPrice);
        $(`#agent-cost-${priceId}`).html(agentPrice === '—' ? '<span class="text-muted">—</span>' : '₱' + agentPrice);
        
        // Also update the printing price record via AJAX
        $.ajax({
            url: '{{ route("printing.update-prices") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                prices: [{
                    id: priceId,
                    master_item_id: selectedOption.value
                }]
            },
            success: function(res) {
                console.log('Master item linked successfully');
            },
            error: function(xhr) {
                console.error('Error linking master item:', xhr.responseText);
            }
        });
    }

    function savePrintPrices() {
        const prices = [];
        let hasChanges = false;

        $('#prices-table tbody tr').each(function() {
            const id = $(this).find('.master-item-select').data('price-id');
            const masterItemId = $(this).find('.master-item-select').val() || null;
            const salesPrice = parseFloat($(this).find('[data-field="price"]').val()) || 0;
            const agentPrice = $(this).find('[data-field="agent_price"]').val();
            const agentVal = agentPrice === '' ? null : parseFloat(agentPrice);

            prices.push({
                id: id,
                master_item_id: masterItemId,
                price: salesPrice,
                agent_price: agentVal
            });
            hasChanges = true;
        });

        if (!hasChanges) {
            showMessage('No prices to save.', 'info');
            return;
        }

        $.ajax({
            url: '{{ route("printing.update-prices") }}',
            method: 'POST',
            data: { prices: prices },
            success: function(res) {
                showMessage(res.message);
            },
            error: function(xhr) {
                showMessage(xhr.responseJSON?.message || 'Error saving prices', 'error');
            }
        });
    }

    function addNewPrintPrice() {
        const name = $('#newPrintName').val().trim();
        const masterItemId = $('#newMasterItemId').val();

        if (!name) {
            showMessage('Please enter a print size name.', 'error');
            return;
        }

        $.ajax({
            url: '{{ route("printing.add-price") }}',
            method: 'POST',
            data: {
                name: name,
                master_item_id: masterItemId || null,
                print_type: '{{ $printType }}'
            },
            success: function(res) {
                showMessage(res.message);
                const p = res.price;
                const salesPrice = parseFloat(p.price).toFixed(2);
                const agentPrice = p.agent_price ? parseFloat(p.agent_price).toFixed(2) : '';
                const linkedText = p.master_item_id ? ($('#newMasterItemId option:selected').text()) : '';

                const row = `
                    <tr id="price-row-${p.id}">
                        <td><strong>${p.name}</strong></td>
                        <td>
                            <select class="form-select form-select-sm master-item-select" data-price-id="${p.id}" onchange="onMasterItemChange(${p.id}, this)">
                                <option value="">-- None --</option>
                                @foreach($productPricingOptions as $opt)
                                <option value="{{ $opt->id }}" ${p.master_item_id == {{ $opt->id }} ? 'selected' : ''}>{{ $opt->description }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="cost-column" id="supplier-cost-${p.id}">
                            <span class="text-muted">—</span>
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control price-input" id="price-sales-${p.id}"
                                       data-id="${p.id}" data-field="price" value="${salesPrice}" step="0.01" min="0">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control price-input" id="price-agent-${p.id}"
                                       data-id="${p.id}" data-field="agent_price" value="${agentPrice}" step="0.01" min="0" placeholder="Set">
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePrintPrice(${p.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#prices-table tbody').append(row);
                $('#newPrintName').val('');
                $('#newMasterItemId').val('');
            },
            error: function(xhr) {
                showMessage(xhr.responseJSON?.message || 'Error adding print price', 'error');
            }
        });
    }

    function deletePrintPrice(id) {
        if (!confirm('Delete this print size? This cannot be undone.')) return;
        $.ajax({
            url: '{{ route("printing.delete-price", "__ID__") }}'.replace('__ID__', id),
            method: 'DELETE',
            success: function(res) {
                showMessage(res.message);
                $('#price-row-' + id).remove();
            },
            error: function(xhr) {
                showMessage(xhr.responseJSON?.message || 'Error deleting', 'error');
            }
        });
    }

    // === COMBO DISCOUNTS ===

    function addComboRow(tier) {
        const container = $(`#combo-container-${tier}`);
        const prices = @json($prices->pluck('name', 'id'));
        let options = '';
        for (const [id, name] of Object.entries(prices)) {
            options += `<option value="${id}">${name}</option>`;
        }

        const row = `
            <div class="combo-row">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <select class="form-select size1-select">${options}</select>
                    </div>
                    <div class="col-md-1 text-center"><span class="fw-bold">+</span></div>
                    <div class="col-md-4">
                        <select class="form-select size2-select">${options}</select>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control discount-value" value="0" step="0.01" min="0" placeholder="Discount">
                        </div>
                    </div>
                    <div class="col-md-1 text-end">
                        <button class="btn-remove" onclick="removeComboRow(this)"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>
        `;
        container.append(row);
    }

    function removeComboRow(btn) {
        $(btn).closest('.combo-row').remove();
    }

    function saveCombos(tier) {
        const combos = [];
        const container = tier === 'agent' ? $('#combo-container-agent') : $('#combo-container-sales');
        
        container.find('.combo-row').each(function() {
            const size1 = $(this).find('.size1-select').val();
            const size2 = $(this).find('.size2-select').val();
            const discount = parseFloat($(this).find('.discount-value').val()) || 0;
            
            if (size1 && size2 && discount > 0) {
                combos.push({
                    size1_id: size1,
                    size2_id: size2,
                    discount_value: discount,
                    price_tier: tier
                });
            }
        });
        
        if (combos.length === 0) {
            showMessage('Please add at least one combo discount.', 'info');
            return;
        }
        
        $.ajax({
            url: '{{ route("printing.update-combos") }}',
            method: 'POST',
            data: { combos: combos, print_type: '{{ $printType }}' },
            success: function(res) {
                showMessage(res.message);
            },
            error: function(xhr) {
                showMessage(xhr.responseJSON?.message || 'Error saving combo discounts', 'error');
            }
        });
    }

    // === BULK DISCOUNTS ===

    function addBulkRow(tier) {
        const container = $(`#bulk-container-${tier}`);
        const row = `
            <div class="bulk-row">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                        <div class="input-group">
                            <input type="number" class="form-control min-transactions" value="10" min="1" placeholder="Min"
                                   style="background-color: white; color: black;">
                            <span class="input-group-text">to</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                        <div class="input-group">
                            <input type="number" class="form-control max-transactions" value="24" min="1" placeholder="Max"
                                   style="background-color: white; color: black;">
                            <span class="input-group-text">transactions</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                        <select class="form-control discount-type">
                            <option value="percentage">Percentage</option>
                            <option value="fixed_amount">Fixed Amount</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                        <div class="input-group percentage-field">
                            <input type="number" class="form-control discount-percent" value="5" step="0.01" min="0" max="100" placeholder="%">
                            <span class="input-group-text">% off</span>
                        </div>
                        <div class="input-group amount-field" style="display:none;">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control discount-amount" value="0" step="0.01" min="0" placeholder="Amount">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input count-combo-as-one" checked>
                            <label class="form-check-label small">Combo = 1 transaction</label>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-2 text-end">
                        <button class="btn-remove" onclick="removeBulkRow(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.append(row);
        
        // Add event listener for discount type change
        $(row).find('.discount-type').on('change', function() {
            const isPercentage = $(this).val() === 'percentage';
            $(this).closest('.bulk-row').find('.percentage-field').toggle(isPercentage);
            $(this).closest('.bulk-row').find('.amount-field').toggle(!isPercentage);
        });
    }

    function removeBulkRow(btn) {
        $(btn).closest('.bulk-row').remove();
    }

    function saveBulk(tier) {
        const bulk = [];
        const container = tier === 'agent' ? $('#bulk-container-agent') : $('#bulk-container-sales');
        
        container.find('.bulk-row').each(function() {
            const min = parseInt($(this).find('.min-transactions').val());
            const max = parseInt($(this).find('.max-transactions').val());
            const discountType = $(this).find('.discount-type').val();
            const percent = parseFloat($(this).find('.discount-percent').val()) || 0;
            const amount = parseFloat($(this).find('.discount-amount').val()) || 0;
            const countComboAsOne = $(this).find('.count-combo-as-one').is(':checked') ? 1 : 0;
            
            if (min && max) {
                bulk.push({
                    min_transactions: min,
                    max_transactions: max,
                    discount_type: discountType,
                    discount_percent: discountType === 'percentage' ? percent : 0,
                    discount_amount: discountType === 'fixed_amount' ? amount : 0,
                    count_combo_as_one: countComboAsOne,
                    price_tier: tier
                });
            }
        });
        
        if (bulk.length === 0) {
            showMessage('Please add at least one bulk discount tier.', 'info');
            return;
        }
        
        $.ajax({
            url: '{{ route("printing.update-bulk") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { bulk: bulk, print_type: '{{ $printType }}' },
            success: function(res) {
                showMessage(res.message);
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                let msg = 'Error saving bulk discounts';
                if (errors) {
                    msg = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                showMessage(msg, 'error');
            }
        });
    }
</script>
</body>
</html>