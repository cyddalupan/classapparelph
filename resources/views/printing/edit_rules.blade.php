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
        .cost-column {
            font-size: 0.9rem;
            color: #64748b;
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
                        <li class="nav-item">
                            <button class="nav-link" id="upgrades-tab" data-bs-toggle="tab" data-bs-target="#upgrades" type="button" role="tab">
                                <i class="fas fa-layer-group me-1"></i> Size Upgrades
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="calc-tab" data-bs-toggle="tab" data-bs-target="#calc" type="button" role="tab">
                                <i class="fas fa-calculator me-1"></i> Test Calculator
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="ruleTabsContent">
                        <!-- ==================== PRINT PRICES TAB ==================== -->
                        <div class="tab-pane fade show active" id="prices" role="tabpanel">
                            <h4 class="section-title">Print Size Prices</h4>
                            <p class="text-muted mb-4">
                                Set the supplier cost, sales team price, and agent cost for each print size.
                            </p>

                            <div class="table-responsive">
                                <table class="table table-hover" id="prices-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Print Size</th>
                                            <th>Supplier Cost</th>
                                            <th>Sales Team Price</th>
                                            <th>Agent Cost</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($prices as $price)
                                        <tr id="price-row-{{ $price->id }}" data-price-id="{{ $price->id }}">
                                            <td><strong>{{ $price->name }}</strong></td>
                                            <td>
                                                <div class="input-group input-group-sm cost-column" style="width: 150px;">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control price-input" id="supplier-cost-{{ $price->id }}"
                                                           data-id="{{ $price->id }}" data-field="supplier_cost"
                                                           value="{{ !is_null($price->supplier_cost) ? number_format($price->supplier_cost, 2, '.', '') : '' }}"
                                                           step="0.01" min="0" placeholder="Set">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm cost-column" style="width: 150px;">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control price-input" id="sales-team-cost-{{ $price->id }}"
                                                           data-id="{{ $price->id }}" data-field="price"
                                                           value="{{ number_format($price->price, 2, '.', '') }}"
                                                           step="0.01" min="0">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm cost-column" style="width: 150px;">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control price-input" id="agent-cost-{{ $price->id }}"
                                                           data-id="{{ $price->id }}" data-field="agent_price"
                                                           value="{{ !is_null($price->agent_price) ? number_format($price->agent_price, 2, '.', '') : '' }}"
                                                           step="0.01" min="0" placeholder="Set">
                                                </div>
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
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Print Size Name</label>
                                        <input type="text" class="form-control" id="newPrintName" placeholder="e.g. A3">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Supplier Cost</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="newSupplierCost" step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Sales Team Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="newSalesPrice" step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Agent Cost</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="newAgentCost" step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-success w-100" onclick="addNewPrintPrice()">
                                            <i class="fas fa-plus me-1"></i> Add Print Size
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    New sizes appear in the calculator immediately.
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

                            <!-- Size gate toggle -->
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body py-3">
                                    <div class="form-check form-switch d-flex align-items-center gap-2 mb-1">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               id="bulk-size-gate" style="width:3em;height:1.5em;"
                                               {{ $bulkSetting->bulk_requires_large_size ? 'checked' : '' }}
                                               onchange="toggleBulkSizeGate(this)">
                                        <label class="form-check-label fw-bold mb-0" for="bulk-size-gate">
                                            Apply bulk discount only for A3 / A4 prints
                                        </label>
                                        <span class="badge {{ $bulkSetting->bulk_requires_large_size ? 'bg-success' : 'bg-secondary' }}"
                                              id="bulk-size-gate-badge">
                                            {{ $bulkSetting->bulk_requires_large_size ? 'ON' : 'OFF' }}
                                        </span>
                                    </div>
                                    <div class="form-text mb-0">
                                        When ON: bulk discount is computed only if the user selected an <strong>A4</strong> or <strong>A3</strong> print.
                                        Logo-only or Half A4-only selections get <strong>no bulk discount</strong>.
                                        When OFF: bulk applies to all sizes (current behaviour).
                                    </div>
                                </div>
                            </div>

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

                        <!-- ==================== SIZE UPGRADES TAB ==================== -->
                        <div class="tab-pane fade" id="upgrades" role="tabpanel">
                            <h4 class="section-title">Size Upgrades</h4>
                            <p class="text-muted mb-4">
                                Auto-combine prints of the same size into the next bigger size. Example:
                                <strong>2× Logo = Half A4</strong>, <strong>2× Half A4 = A4</strong>, <strong>4× Logo = A4 (+ ₱10)</strong>.
                                These apply automatically in the Garment Printing modal.
                            </p>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:32%;">Combine This Size</th>
                                            <th style="width:10%;">Qty</th>
                                            <th style="width:4%;"></th>
                                            <th style="width:32%;">Into This Size</th>
                                            <th style="width:18%;">Surcharge</th>
                                            <th style="width:4%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="upgrades-container">
                                        @forelse($upgrades as $u)
                                        <tr class="upgrade-row">
                                            <td>
                                                <select class="form-select form-select-sm from-size-select">
                                                    @foreach($prices as $price)
                                                    <option value="{{ $price->id }}" {{ $u->from_size_id == $price->id ? 'selected' : '' }}>{{ $price->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm from-quantity" value="{{ $u->from_quantity }}" min="2" step="1">
                                            </td>
                                            <td class="text-center"><i class="fas fa-arrow-right text-muted"></i></td>
                                            <td>
                                                <select class="form-select form-select-sm to-size-select">
                                                    @foreach($prices as $price)
                                                    <option value="{{ $price->id }}" {{ $u->to_size_id == $price->id ? 'selected' : '' }}>{{ $price->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">₱</span>
                                                    <input type="number" class="form-control upgrade-surcharge" value="{{ number_format($u->surcharge ?? 0, 2, '.', '') }}" step="0.01" min="0" placeholder="0.00">
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn-remove" onclick="removeUpgradeRow(this)"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-muted text-center py-3">No size upgrade rules yet. Click "Add Upgrade Rule" below.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-add" onclick="addUpgradeRow()">
                                    <i class="fas fa-plus me-1"></i> Add Upgrade Rule
                                </button>
                            </div>

                            <div class="mt-3 text-end">
                                <button class="btn btn-save" onclick="saveUpgrades()">
                                    <i class="fas fa-save me-2"></i> Save Size Upgrades
                                </button>
                            </div>
                        </div>

                        <!-- ==================== TEST CALCULATOR TAB ==================== -->
                        <div class="tab-pane fade" id="calc" role="tabpanel">
                            <h4 class="section-title">Test Calculator</h4>
                            <p class="text-muted mb-4">
                                Test the current rules <strong>without saving or changing anything</strong>. Uses the same
                                computation as the Garment Printing modal: size upgrades &rarr; best combo &rarr; bulk tier.
                            </p>

                            <div class="row g-3 align-items-end mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Price Tier</label>
                                    <select class="form-select" id="calc-price-tier">
                                        <option value="sales_team">Sales Team price</option>
                                        <option value="agent">Agent price</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Garment Quantity</label>
                                    <input type="number" class="form-control" id="calc-quantity" value="10" min="1" step="1">
                                </div>
                                <div class="col-md-5">
                                    <div class="form-text">Quantity is used for the bulk tier and total.</div>
                                </div>
                            </div>

                            <label class="form-label fw-bold">Prints per garment</label>
                            <p class="text-muted small mb-2">
                                Tick a size and set copies per garment. Example: 2&times; Logo auto-combines into Half A4
                                based on the upgrade rules.
                            </p>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:52px;">Use</th>
                                            <th>Print Size</th>
                                            <th style="width:130px;">Copies / garment</th>
                                            <th class="text-end">Sales Team</th>
                                            <th class="text-end">Agent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($prices as $price)
                                        <tr class="calc-size-row" data-size-id="{{ $price->id }}">
                                            <td><input type="checkbox" class="form-check-input calc-size-checkbox"></td>
                                            <td><strong>{{ $price->name }}</strong></td>
                                            <td><input type="number" class="form-control form-control-sm calc-size-qty" value="1" min="1" step="1"></td>
                                            <td class="text-end">₱{{ number_format($price->price, 2) }}</td>
                                            <td class="text-end">₱{{ number_format($price->agent_price ?? 0, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-muted text-center py-3">No print sizes yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex gap-2 mb-4">
                                <button type="button" class="btn btn-primary" id="calc-run" onclick="runPreview()">
                                    <i class="fas fa-calculator me-2"></i> Compute Preview
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetPreview()">Reset</button>
                            </div>

                            <div id="calc-error" class="alert alert-danger" style="display:none;"></div>

                            <div id="calc-results" style="display:none;">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="text-muted small">Gross (before discount)</div>
                                            <div class="fs-5 fw-bold" id="calc-gross">₱0.00</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="text-muted small">Total discount</div>
                                            <div class="fs-5 fw-bold text-success" id="calc-total-discount">₱0.00</div>
                                            <div class="small text-muted" id="calc-discount-pct">0%</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="text-muted small">Final total</div>
                                            <div class="fs-5 fw-bold text-primary" id="calc-total">₱0.00</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="text-muted small">Per garment</div>
                                            <div class="fs-5 fw-bold" id="calc-per-item">₱0.00</div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="fw-bold">Breakdown</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm">
                                        <tbody id="calc-breakdown"></tbody>
                                    </table>
                                </div>

                                <div id="calc-combo-note" class="alert alert-info py-2" style="display:none;"></div>
                            </div>

                            <hr class="my-4">

                            <h5 class="fw-bold">Rules being applied <span class="text-muted fw-normal small">(read-only)</span></h5>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="fw-bold mb-2"><i class="fas fa-percentage me-1 text-primary"></i> Combo discounts</div>
                                        <div class="small"><strong>Sales Team</strong></div>
                                        <ul class="small mb-2">
                                            @forelse($comboSalesTeam as $combo)
                                            <li>{{ $combo->size1->name ?? '?' }} + {{ $combo->size2->name ?? '?' }} &rarr; ₱{{ number_format($combo->discount_value, 2) }}</li>
                                            @empty
                                            <li class="text-muted">None</li>
                                            @endforelse
                                        </ul>
                                        <div class="small"><strong>Agent</strong></div>
                                        <ul class="small mb-0">
                                            @forelse($comboAgent as $combo)
                                            <li>{{ $combo->size1->name ?? '?' }} + {{ $combo->size2->name ?? '?' }} &rarr; ₱{{ number_format($combo->discount_value, 2) }}</li>
                                            @empty
                                            <li class="text-muted">None</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="fw-bold mb-2"><i class="fas fa-boxes me-1 text-primary"></i> Bulk discount tiers</div>
                                        <div class="small"><strong>Sales Team</strong></div>
                                        <ul class="small mb-2">
                                            @forelse($bulkSalesTeam as $bulk)
                                            <li>
                                                {{ $bulk->min_transactions ?? $bulk->min_garments }}&ndash;{{ $bulk->max_transactions ?? $bulk->max_garments }} txns:
                                                @if(($bulk->discount_type ?? 'percentage') === 'fixed_amount')
                                                    ₱{{ number_format($bulk->discount_amount ?? 0, 2) }} each
                                                @else
                                                    {{ $bulk->discount_percent }}% off
                                                @endif
                                            </li>
                                            @empty
                                            <li class="text-muted">None</li>
                                            @endforelse
                                        </ul>
                                        <div class="small"><strong>Agent</strong></div>
                                        <ul class="small mb-0">
                                            @forelse($bulkAgent as $bulk)
                                            <li>
                                                {{ $bulk->min_transactions ?? $bulk->min_garments }}&ndash;{{ $bulk->max_transactions ?? $bulk->max_garments }} txns:
                                                @if(($bulk->discount_type ?? 'percentage') === 'fixed_amount')
                                                    ₱{{ number_format($bulk->discount_amount ?? 0, 2) }} each
                                                @else
                                                    {{ $bulk->discount_percent }}% off
                                                @endif
                                            </li>
                                            @empty
                                            <li class="text-muted">None</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="border rounded p-3">
                                        <div class="fw-bold mb-2"><i class="fas fa-layer-group me-1 text-primary"></i> Size upgrades</div>
                                        <ul class="small mb-0">
                                            @forelse($upgrades as $upgrade)
                                            <li>
                                                {{ $upgrade->from_quantity }}&times; {{ $upgrade->fromSize->name ?? '?' }}
                                                &rarr; 1&times; {{ $upgrade->toSize->name ?? '?' }}
                                                @if(floatval($upgrade->surcharge ?? 0) > 0)
                                                    (+₱{{ number_format($upgrade->surcharge, 2) }})
                                                @endif
                                            </li>
                                            @empty
                                            <li class="text-muted">None</li>
                                            @endforelse
                                        </ul>
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

    function savePrintPrices() {
        const prices = [];

        $('#prices-table tbody tr').each(function() {
            const id = $(this).data('price-id');
            if (!id) return;
            const supplierCost = $(this).find('[data-field="supplier_cost"]').val();
            const salesPrice = $(this).find('[data-field="price"]').val();
            const agentPrice = $(this).find('[data-field="agent_price"]').val();

            prices.push({
                id: id,
                supplier_cost: supplierCost === '' ? null : parseFloat(supplierCost),
                price: salesPrice === '' ? 0 : parseFloat(salesPrice),
                agent_price: agentPrice === '' ? null : parseFloat(agentPrice)
            });
        });

        if (!prices.length) {
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
        const supplierCost = $('#newSupplierCost').val();
        const salesPrice = $('#newSalesPrice').val();
        const agentCost = $('#newAgentCost').val();

        if (!name) {
            showMessage('Please enter a print size name.', 'error');
            return;
        }

        $.ajax({
            url: '{{ route("printing.add-price") }}',
            method: 'POST',
            data: {
                name: name,
                supplier_cost: supplierCost === '' ? null : parseFloat(supplierCost),
                price: salesPrice === '' ? 0 : parseFloat(salesPrice),
                agent_price: agentCost === '' ? null : parseFloat(agentCost),
                print_type: '{{ $printType }}'
            },
            success: function(res) {
                showMessage(res.message);
                const p = res.price;
                const sup = p.supplier_cost != null ? parseFloat(p.supplier_cost).toFixed(2) : '';
                const salesPrice = parseFloat(p.price).toFixed(2);
                const agentPrice = (p.agent_price != null && p.agent_price !== '') ? parseFloat(p.agent_price).toFixed(2) : '';

                const row = `
                    <tr id="price-row-${p.id}" data-price-id="${p.id}">
                        <td><strong>${p.name}</strong></td>
                        <td>
                            <div class="input-group input-group-sm cost-column" style="width: 150px;">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control price-input" id="supplier-cost-${p.id}"
                                       data-id="${p.id}" data-field="supplier_cost" value="${sup}" step="0.01" min="0" placeholder="Set">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm cost-column" style="width: 150px;">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control price-input" id="sales-team-cost-${p.id}"
                                       data-id="${p.id}" data-field="price" value="${salesPrice}" step="0.01" min="0">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm cost-column" style="width: 150px;">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control price-input" id="agent-cost-${p.id}"
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
                $('#newSupplierCost').val('');
                $('#newSalesPrice').val('');
                $('#newAgentCost').val('');
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
        
        // Saving with ZERO combos is allowed: it clears every combo for this tier.
        // Only block when rows exist but are incomplete (missing size or discount <= 0),
        // so an accidental half-filled row can't wipe the saved rules.
        if (combos.length === 0) {
            const rowCount = container.find('.combo-row').length;
            if (rowCount > 0) {
                showMessage('Some combo rows are incomplete — pick both sizes and a discount greater than 0, or remove the row.', 'info');
                return;
            }
            if (!confirm('No combo discounts for this tier. Save anyway? This will clear the existing combos.')) {
                return;
            }
        }

        $.ajax({
            url: '{{ route("printing.update-combos") }}',
            method: 'POST',
            data: { combos: combos, price_tier: tier, print_type: '{{ $printType }}' },
            success: function(res) {
                showMessage(res.message);
            },
            error: function(xhr) {
                showMessage(xhr.responseJSON?.message || 'Error saving combo discounts', 'error');
            }
        });
    }

    // === SIZE UPGRADES ===

    function addUpgradeRow() {
        const container = $('#upgrades-container');
        const prices = @json($prices->pluck('name', 'id'));
        let options = '';
        for (const [id, name] of Object.entries(prices)) {
            options += `<option value="${id}">${name}</option>`;
        }
        // Remove the empty-state row if present
        container.find('td[colspan]').closest('tr').remove();
        const row = `
            <tr class="upgrade-row">
                <td><select class="form-select form-select-sm from-size-select">${options}</select></td>
                <td><input type="number" class="form-control form-control-sm from-quantity" value="2" min="2" step="1"></td>
                <td class="text-center"><i class="fas fa-arrow-right text-muted"></i></td>
                <td><select class="form-select form-select-sm to-size-select">${options}</select></td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control upgrade-surcharge" value="0.00" step="0.01" min="0" placeholder="0.00">
                    </div>
                </td>
                <td class="text-end"><button class="btn-remove" onclick="removeUpgradeRow(this)"><i class="fas fa-times"></i></button></td>
            </tr>
        `;
        container.append(row);
    }

    function removeUpgradeRow(btn) {
        $(btn).closest('.upgrade-row').remove();
    }

    function saveUpgrades() {
        const upgrades = [];
        $('#upgrades-container .upgrade-row').each(function() {
            const fromSize = $(this).find('.from-size-select').val();
            const qty = parseInt($(this).find('.from-quantity').val()) || 0;
            const toSize = $(this).find('.to-size-select').val();
            const surcharge = parseFloat($(this).find('.upgrade-surcharge').val()) || 0;
            if (fromSize && toSize && qty >= 2) {
                upgrades.push({
                    from_size_id: fromSize,
                    from_quantity: qty,
                    to_size_id: toSize,
                    surcharge: surcharge
                });
            }
        });

        $.ajax({
            url: '{{ route("printing.update-upgrades") }}',
            method: 'POST',
            data: { upgrades: upgrades, print_type: '{{ $printType }}' },
            success: function(res) {
                showMessage(res.message);
            },
            error: function(xhr) {
                showMessage(xhr.responseJSON?.message || 'Error saving size upgrades', 'error');
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

    function toggleBulkSizeGate(el) {
        const enabled = el.checked ? 1 : 0;
        const badge = document.getElementById('bulk-size-gate-badge');
        badge.className = 'badge ' + (enabled ? 'bg-success' : 'bg-secondary');
        badge.textContent = enabled ? 'ON' : 'OFF';

        $.ajax({
            url: '{{ route("printing.update-bulk-settings") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { print_type: '{{ $printType }}', bulk_requires_large_size: enabled },
            success: function(res) {
                showMessage(res.message);
                if (typeof window.syncBulkGateHint === 'function') {
                    window.syncBulkGateHint(enabled);
                }
            },
            error: function(xhr) {
                el.checked = !el.checked;
                badge.className = 'badge ' + (!enabled ? 'bg-success' : 'bg-secondary');
                badge.textContent = !enabled ? 'ON' : 'OFF';
                let msg = 'Could not save the bulk size gate.';
                if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                showMessage(msg, 'error');
            }
        });
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
        
        // Saving with ZERO tiers is allowed: it clears every bulk tier for this tier.
        // Only block when rows exist but are incomplete.
        if (bulk.length === 0) {
            const rowCount = container.find('.bulk-row').length;
            if (rowCount > 0) {
                showMessage('Some bulk rows are incomplete — fill in Min and Max, or remove the row.', 'info');
                return;
            }
            if (!confirm('No bulk discount tiers for this price tier. Save anyway? This will clear the existing tiers.')) {
                return;
            }
        }
        
        $.ajax({
            url: '{{ route("printing.update-bulk") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { bulk: bulk, price_tier: tier, print_type: '{{ $printType }}' },
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

    // ==================== TEST CALCULATOR ====================
    function calcPeso(n) {
        return '\u20B1' + (parseFloat(n) || 0).toFixed(2);
    }

    function resetPreview() {
        $('.calc-size-checkbox').prop('checked', false);
        $('.calc-size-qty').val(1);
        $('#calc-results').hide();
        $('#calc-error').hide();
    }

    function runPreview() {
        var ids = [];
        var tooMany = false;
        $('.calc-size-row').each(function() {
            var row = $(this);
            if (!row.find('.calc-size-checkbox').is(':checked')) {
                return;
            }
            var copies = parseInt(row.find('.calc-size-qty').val()) || 1;
            if (copies < 1) { copies = 1; }
            for (var i = 0; i < copies; i++) {
                ids.push(parseInt(row.data('size-id')));
            }
            if (ids.length > 500) { tooMany = true; return false; }
        });

        var errorBox = $('#calc-error');
        errorBox.hide();

        if (ids.length === 0) {
            errorBox.text('Select at least one print size.').show();
            return;
        }
        if (tooMany) {
            errorBox.text('Too many copies for a preview (max 500). Lower the copies or quantity.').show();
            return;
        }

        var button = $('#calc-run');
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Computing...');

        $.ajax({
            url: '{{ route("printing.rules-preview") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                print_type: '{{ $printType }}',
                price_tier: $('#calc-price-tier').val(),
                quantity: parseInt($('#calc-quantity').val()) || 1,
                print_size_ids: ids
            },
            success: function(res) {
                if (!res || !res.success) {
                    errorBox.text('Preview failed.').show();
                    return;
                }
                renderPreview(res);
            },
            error: function(xhr) {
                var msg = 'Preview failed.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                errorBox.text(msg).show();
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="fas fa-calculator me-2"></i> Compute Preview');
            }
        });
    }

    function renderPreview(res) {
        $('#calc-gross').text(calcPeso(res.gross_before_discount));
        $('#calc-total-discount').text(calcPeso(res.total_discount));
        $('#calc-discount-pct').text(res.effective_discount_percent + '% effective');
        $('#calc-total').text(calcPeso(res.total));
        $('#calc-per-item').text(calcPeso(res.per_item));

        var rows = '';

        // Sizes after upgrades
        if (res.upgrades && res.upgrades.length) {
            var notes = res.upgrades.map(function(u) {
                return u.from_quantity + '\u00D7 ' + u.from + ' \u2192 ' + u.to_quantity + '\u00D7 ' + u.to;
            }).join(', ');
            rows += '<tr><td class="text-muted">Auto-combined</td><td class="text-end text-muted">' + notes + '</td></tr>';
        }

        rows += '<tr><td>Print cost per garment (after upgrades)</td><td class="text-end">';
        rows += (res.sizes || []).map(function(sz) {
            return sz.name + ' ' + calcPeso(sz.price);
        }).join(' + ');
        if (parseFloat(res.upgrade_surcharge) > 0) {
            rows += ' + upgrade ' + calcPeso(res.upgrade_surcharge);
        }
        rows += '</td></tr>';
        rows += '<tr><td>Print cost per garment</td><td class="text-end fw-bold">' + calcPeso(res.print_cost_per_item) + '</td></tr>';

        // Combo
        if (parseFloat(res.combo_discount) > 0) {
            rows += '<tr><td>Combo discount per garment <span class="text-muted small">(' + (res.combo_applied ? res.combo_applied.label : '') + ')</span></td>';
            rows += '<td class="text-end text-success">-' + calcPeso(res.combo_discount) + '</td></tr>';
        } else {
            rows += '<tr><td class="text-muted">Combo discount</td><td class="text-end text-muted">-&#8203;' + calcPeso(0) + '</td></tr>';
        }

        rows += '<tr><td>Print cost per garment after combo</td><td class="text-end">' + calcPeso(res.print_cost_after_combo) + '</td></tr>';
        rows += '<tr><td>Subtotal &times; ' + res.quantity + ' garment(s)</td><td class="text-end">' + calcPeso(res.subtotal) + '</td></tr>';

        // Bulk
        if (res.bulk_tier) {
            rows += '<tr><td>Bulk discount <span class="text-muted small">(' + res.bulk_tier.min + '\u2013' + res.bulk_tier.max + ' txns, ' + res.bulk_tier.label + ')</span></td>';
            rows += '<td class="text-end text-success">-' + calcPeso(res.bulk_discount) + '</td></tr>';
        } else {
            rows += '<tr><td class="text-muted">Bulk discount <span class="small">(no tier for ' + res.quantity + ' txn)</span></td><td class="text-end text-muted">-&#8203;' + calcPeso(0) + '</td></tr>';
        }

        rows += '<tr class="table-light"><td class="fw-bold">Final total</td><td class="text-end fw-bold fs-5">' + calcPeso(res.total) + '</td></tr>';

        $('#calc-breakdown').html(rows);

        // Combo match counter
        var note = $('#calc-combo-note');
        if (res.combo_count > 0) {
            var labels = (res.combos_matched || []).map(function(c) {
                return c.label + ' (' + calcPeso(c.discount) + ')';
            }).join(', ');
            note.html('<strong>' + res.combo_count + ' combo rule(s) matched:</strong> ' + labels +
                '<br><span class="small">Only the best one is applied \u2014 same as the sales modal.</span>').show();
        } else {
            note.hide();
        }

        $('#calc-results').show();
    }
</script>
</body>
</html>