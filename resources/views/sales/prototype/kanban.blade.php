@extends('layouts.app')

@section('title', 'Kanban Board' . ($activeDept === 'all' ? ' — All Departments' : ' — ' . ucfirst($activeDept) . ' Department'))

@push('styles')
<style>
    /* Let the flex chain shrink so the board scrolls internally instead of widening the page */
    .main-content, .content-area { min-width: 0; }
    .kanban-board {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        width: 100%;
        padding: 20px 0;
        min-height: calc(100vh - 160px);
    }
    .kanban-column {
        flex: 1;
        min-width: 260px;
        max-width: 320px;
        background: #f4f5f7;
        border-radius: 12px;
        padding: 12px;
    }
    .kanban-column-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding: 8px 12px;
        border-radius: 8px;
    }
    .kanban-column-header.pending { background: #fff3cd; color: #856404; }
    .kanban-column-header.in_progress { background: #cce5ff; color: #004085; }
    .kanban-column-header.for_review { background: #e2d9f3; color: #6f42c1; }
    .kanban-column-header.completed { background: #d4edda; color: #155724; }
    
    /* Navigation buttons */
    .nav-list-btn {
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 500;
        background: #e8f5e9;
        border-radius: 20px;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .nav-list-btn:hover {
        background: #c8e6c9;
    }
    
    .kanban-card {
        background: white;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        cursor: grab;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .kanban-card:hover {
        box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        transform: translateY(-1px);
    }
    .kanban-card.dragging {
        opacity: 0.5;
        transform: rotate(2deg);
    }
    .kanban-card .card-title {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .kanban-card .card-meta {
        font-size: 11px;
        color: #6c757d;
        display: flex;
        flex-wrap: wrap;
        gap: 4px 8px;
    }
    .kanban-card .card-meta span {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .kanban-card .card-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        display: inline-block;
        margin-bottom: 6px;
    }
    .department-tabs {
        display: flex;
        gap: 8px;
        padding: 16px 0 0;
        overflow-x: auto;
        border-bottom: 1px solid #dee2e6;
    }
    .department-tab {
        padding: 8px 20px;
        border-radius: 8px 8px 0 0;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .department-tab.active {
        background: white;
        border-color: #dee2e6 #dee2e6 white;
        color: #0d6efd;
        font-weight: 600;
    }
    .department-tab:not(.active):hover {
        background: #f0f0f0;
    }
    .card-count {
        font-size: 12px;
        background: rgba(0,0,0,0.08);
        padding: 1px 8px;
        border-radius: 10px;
    }
    .drop-zone {
        min-height: 120px;
        transition: background 0.2s;
        position: relative;
        padding-bottom: 40px;
    }
    .drop-zone.drag-over {
        background: #e8f4fd;
        border-radius: 8px;
        outline: 3px dashed #0d6efd;
        outline-offset: -3px;
    }
    .drop-zone.drag-over::after {
        content: '\25BC Drop here';
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 12px;
        color: #0d6efd;
        font-weight: 600;
        opacity: 0.8;
    }
    .empty-column {
        text-align: center;
        padding: 30px 10px;
        color: #adb5bd;
        font-size: 13px;
    }
    .dept-badge {
        display: inline-block;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        color: white;
        margin-bottom: 6px;
        margin-right: 4px;
    }
    .all-tab {
        font-weight: 700 !important;
        color: #0d6efd !important;
        background: #e7f0ff;
        border-color: #dee2e6 #dee2e6 white !important;
    }
    .all-tab:not(.active) {
        color: #212529 !important;
    }

    /* Sale Details Modal */
    .sale-detail-section {
        margin-bottom: 20px;
    }
    .sale-detail-section h6 {
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 6px;
        margin-bottom: 12px;
    }
    .sale-detail-section .item-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
    }
    .sale-detail-section .item-card .item-label {
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .sale-detail-section .item-card .item-value {
        font-size: 13px;
        font-weight: 500;
    }
    .sale-detail-section .subitem-row {
        display: inline-flex;
        align-items: center;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 4px 10px;
        margin: 2px;
        font-size: 12px;
    }
    .sale-detail-section .print-detail {
        background: #e7f5ff;
        border-radius: 6px;
        padding: 8px 12px;
        margin-top: 8px;
        font-size: 12px;
    }
    .sale-detail-section .ref-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        margin: 3px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .sale-detail-section .ref-image:hover {
        transform: scale(1.1);
    }
    .sale-detail-section .payment-img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    .sale-detail-section .subtotal-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        font-size: 13px;
    }
    .sale-detail-section .total-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-weight: 700;
        font-size: 15px;
        border-top: 2px solid #dee2e6;
        margin-top: 4px;
    }

    /* Production Slip (Interactive) */
    .pslip { font-family: 'Courier New', monospace; font-size: 10pt; color: #000; padding:0 4px; }
    .pslip h1 { font-size: 14pt; margin:0 0 2px; text-align:center; }
    .pslip .divider { border-top:2px solid #000; margin:2px 0; }
    .pslip table { width:100%; border-collapse:collapse; }
    .pslip td, .pslip th { border:1px solid #000; padding:2px 4px; text-align:left; font-size:9pt; vertical-align:top; }
    .pslip .field-label { font-weight:bold; background:#f0f0f0; white-space:nowrap; width:1%; }
    .pslip .mockup-box { border:2px dashed #999; display:flex; align-items:center; justify-content:center; text-align:center; color:#999; font-size:9pt; overflow:hidden; width:100%; aspect-ratio:4/3; max-height:250px; }
    .pslip .mockup-box img { max-width:100%; max-height:100%; object-fit:contain; cursor:pointer; }
    .pslip .section-title { font-weight:bold; font-size:11pt; margin:2px 0; }
    .pslip .chk { width:16px; height:16px; cursor:pointer; accent-color:#198754; margin:0; vertical-align:middle; }
    .pslip .chk-done + td, .pslip .chk-done + td + td { text-decoration:line-through; color:#999; }
    .pslip tr.done td { text-decoration:line-through; color:#999; }
    .pslip .no-border td, .pslip .no-border { border:none; }

    /* QA & GA Checks row */
    .ps-checks { display:flex; gap:6px; margin-top:8px; flex-wrap:wrap; }
    .ps-check-item { flex:1; min-width:160px; }
    .ps-check-item label { display:flex; align-items:flex-start; gap:5px; font-size:10px; cursor:pointer; padding:5px 8px; border:1px solid #dee2e6; border-radius:6px; transition:all 0.2s; }
    .ps-check-item label.checked { border-color:#198754; background:#f0fff4; }
    .ps-check-item input[type=checkbox] { margin-top:1px; }
    /* Comment thread */
    .ps-comment-list { margin-top:6px; max-height:150px; overflow-y:auto; }
    .ps-comment-entry { padding:6px 8px; margin-bottom:4px; background:#f8f9fa; border-radius:4px; font-size:12px; border-left:3px solid #0d6efd; line-height:1.4; }
    .ps-comment-entry .time { font-size:10px; color:#999; }
    .ps-comment-input { display:flex; gap:4px; margin-top:4px; }
    .ps-comment-input input { flex:1; border:1px solid #dee2e6; border-radius:4px; padding:6px 8px; font-size:12px; }
    .ps-comment-input button { padding:6px 12px; font-size:12px; background:#0d6efd; color:white; border:none; border-radius:4px; cursor:pointer; }

    #modalProdSlipBody { max-height:70vh; overflow-y:auto; }
    #modalAddProdSlipBody { max-height:70vh; overflow-y:auto; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Department Tabs (AJAX) -->
    <div class="department-tabs">
        @php
            $deptDisplay = [
                'all'    => 'All Departments',
                'iprint' => 'iPrint',
                'consol' => 'Consol',
                'cinco'  => 'Cinco',
                'class'  => 'Class',
                'mto'    => 'Made to Order',
                'other'  => 'Other',
            ];
        @endphp
        <span class="department-tab all-tab {{ $activeDept === 'all' ? 'active' : '' }}" data-dept="all" style="cursor:pointer;">
            All
        </span>
        @foreach($allowedDepts as $dept)
            <span class="department-tab {{ $activeDept === $dept ? 'active' : '' }}" data-dept="{{ $dept }}" style="cursor:pointer;">
                {{ $deptDisplay[$dept] ?? ucfirst($dept) }}
            </span>
        @endforeach
    </div>

    <!-- Navigation -->
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
        <a href="{{ route('sales.prototype.list') }}" class="nav-list-btn">📋 Manager List</a>
        <button type="button" class="btn btn-warning btn-sm" onclick="openPendingAddons()">
            <i class="fas fa-clock me-1"></i>Pending Add-ons
            <span class="badge bg-dark ms-1" id="pendingAddonsCount" style="font-size:10px;{{ ($pendingAddonCount ?? 0) > 0 ? '' : 'display:none;' }}">{{ $pendingAddonCount ?? 0 }}</span>
        </button>
    </div>

    <!-- Kanban Column Color Map -->
    @php
        $columnColors = [
            'new'                => ['bg' => '#fff3cd', 'text' => '#856404'],
            'design'            => ['bg' => '#cce5ff', 'text' => '#004085'],
            'production'        => ['bg' => '#d4edda', 'text' => '#155724'],
            'quality_check'      => ['bg' => '#e2d9f3', 'text' => '#6f42c1'],
            'ready_for_delivery' => ['bg' => '#d1ecf1', 'text' => '#0c5460'],
            'delivered'         => ['bg' => '#f8d7da', 'text' => '#721c24'],
            'completed'         => ['bg' => '#d4edda', 'text' => '#155724'],
        ];
    @endphp

    <!-- Kanban Board -->
    <div class="kanban-board" id="kanbanBoard">
        @foreach($kanbanOrder as $statusKey)
            @php $colors = $columnColors[$statusKey] ?? ['bg' => '#f4f5f7', 'text' => '#212529']; @endphp
            <div class="kanban-column" data-status="{{ $statusKey }}">
                <div class="kanban-column-header" style="background:{{ $colors['bg'] }};color:{{ $colors['text'] }};">
                    <strong>{{ $kanbanLabels[$statusKey] ?? ucfirst($statusKey) }}</strong>
                    <span class="card-count">{{ count($columns[$statusKey] ?? []) }}</span>
                </div>
                <div class="drop-zone" data-status="{{ $statusKey }}">
                    @forelse(($columns[$statusKey] ?? []) as $sale)
                        @php
                            $svc = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
                            $firstItem = isset($svc[0]) ? (is_array($svc[0]) ? \App\Models\PrototypeSale::itemSpecSummary($svc[0]) : $svc[0]) : '';
                            $itemCount = count($svc);
                            $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                            $firstMockup = $mockups[0] ?? null;
                            $firstMockupUrl = is_string($firstMockup) ? $firstMockup : ($firstMockup['url'] ?? '');
                            $dImgs = is_string($sale->design_images) ? json_decode($sale->design_images, true) : ($sale->design_images ?? []);
                            $hasFileShot = collect($dImgs)->contains('type', 'file_screenshot');
                            $hasColorShot = collect($dImgs)->contains('type', 'sample_color');
                            $allPhotos = $hasFileShot && $hasColorShot;
                        @endphp
                        <div class="kanban-card" data-id="{{ $sale->id }}" draggable="true" data-department="{{ $sale->department_id }}" data-photos="{{ $allPhotos ? 'ok' : 'missing' }}">

                            @if($firstMockupUrl)
                                <img src="{{ $firstMockupUrl }}" alt="mockup" 
                                     style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-bottom:8px;cursor:pointer;" 
                                     onclick="window.openLightbox('{{ $firstMockupUrl }}')"
                                     onerror="this.style.display='none'">
                            @endif
                            
                            <div class="card-title">
                                {{ $sale->customer_name ?: '#' . $sale->sales_number }}
                                @if($sale->group_id)
                                    <span class="badge bg-secondary ms-1" style="font-size:9px;cursor:help;" title="Part of a multi-department transaction">🔄</span>
                                @endif
                            </div>
                            
                            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:6px;">
                                @if($showAll && isset($departmentLabels[$sale->department_id]))
                                    <span class="dept-badge" style="background:{{ $departmentColors[$sale->department_id] ?? '#6c757d' }};">
                                        {{ $departmentLabels[$sale->department_id] ?? 'Unknown' }}
                                    </span>
                                @endif
                                @if($firstItem)
                                    <span class="card-badge" style="background:#e7f0ff;color:#0d6efd;">
                                        {{ $firstItem }}
                                    </span>
                                @endif
                                @if(in_array($sale->id, $pendingAddonSaleIds ?? []))
                                    <span class="card-badge" style="background:#fff3cd;color:#856404;cursor:help;" title="May pending add-on request">🕐 Add-on</span>
                                @endif
                            </div>

                            <div class="card-meta">
                                @if($itemCount > 1)
                                    <span>📦 +{{ $itemCount - 1 }} more items</span>
                                @endif
                                @if(($sale->subtotal ?? 0) > 0)
                                    <span>💰 ₱{{ number_format($sale->subtotal, 2) }}</span>
                                @endif
                                @if($sale->net_paid > 0)
                                    <span>💳 ₱{{ number_format($sale->net_paid, 2) }} paid</span>
                                @endif
                                @if($sale->created_at)
                                    <span>📅 {{ \Carbon\Carbon::parse($sale->created_at)->format('M d') }}</span>
                                @endif
                                @if($sale->payment_status === 'verified')
                                    <span style="display:inline-flex;align-items:center;gap:2px;padding:1px 6px;border-radius:4px;font-size:10px;background:#d1e7dd;color:#0f5132;font-weight:600;">✅ Paid</span>
                                @elseif($sale->payment_status === 'pending' && $sale->payment_account_id)
                                    <span style="display:inline-flex;align-items:center;gap:2px;padding:1px 6px;border-radius:4px;font-size:10px;background:#fff3cd;color:#664d03;font-weight:600;">⏳ Pending</span>
                                @elseif($sale->payment_status === 'rejected')
                                    <span style="display:inline-flex;align-items:center;gap:2px;padding:1px 6px;border-radius:4px;font-size:10px;background:#f8d7da;color:#842029;font-weight:600;">❌ Rejected</span>
                                @endif
                                @if($sale->sales_agent_name)
                                    <span>👤 {{ $sale->sales_agent_name }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-column">No items</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- === SALE DETAILS MODAL === -->
<div class="modal fade" id="saleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSaleTitle">Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Tab bar -->
            <div style="display:flex;border-bottom:2px solid #dee2e6;padding:0 16px;">
                <button class="prod-tab active" data-tab="details" style="background:none;border:none;padding:10px 16px;font-size:14px;font-weight:600;color:#0d6efd;border-bottom:3px solid #0d6efd;cursor:pointer;margin-bottom:-2px;">
                    <i class="fas fa-info-circle me-1"></i>Details
                </button>
                <button class="prod-tab" data-tab="prodSlip" style="background:none;border:none;padding:10px 16px;font-size:14px;font-weight:500;color:#666;border-bottom:3px solid transparent;cursor:pointer;margin-bottom:-2px;">
                    <i class="fas fa-clipboard-list me-1"></i>Production Slip
                </button>
                <button class="prod-tab" data-tab="addProdSlip" style="background:none;border:none;padding:10px 16px;font-size:14px;font-weight:500;color:#666;border-bottom:3px solid transparent;cursor:pointer;margin-bottom:-2px;">
                    <i class="fas fa-plus-circle me-1"></i>Additional Production Slip
                </button>
            </div>
            <div class="modal-body" id="modalSaleBody">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-body" id="modalProdSlipBody" style="display:none;"></div>
            <div class="modal-body" id="modalAddProdSlipBody" style="display:none;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="addonOpenBtn" style="display:none;" onclick="addonOpenProductModal('garment')">
                    <i class="fas fa-plus me-1"></i>+ Add Items
                </button>
                <!-- Edit button removed — will be repurposed for manager approval -->
                <a href="#" id="modalViewFullSaleBtn" class="btn btn-outline-primary" target="_blank" style="display:none;">
                    <i class="fas fa-external-link-alt"></i> View Full Sale
                </a>
                <a href="#" id="modalPrintSlipBtn" class="btn btn-success" target="_blank" style="display:none;">
                    <i class="fas fa-print"></i> View Printable
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- === ADD-ON PRODUCT MODAL (Garment Printing) === -->
<div class="modal fade product-modal" id="addonProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addonProductModalLabel">
                    <i class="fas fa-tshirt"></i> Add Items to Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="addonSaleId" value="">
                
                <ul class="nav nav-tabs nav-fill mb-3" id="addonProductTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="addonGarmentTab" data-bs-toggle="tab" data-bs-target="#addonGarmentBody" type="button" role="tab">
                            <i class="fas fa-tshirt me-1"></i> Garment Printing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="addonGenericTab" data-bs-toggle="tab" data-bs-target="#addonGenericBody" type="button" role="tab">
                            <i class="fas fa-box me-1"></i> Other Products
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="addonProductTabContent">
                                    </div>
                    <div class="tab-pane fade show active" id="addonGarmentBody" role="tabpanel">
                        <div class="text-center py-5">
                            <i class="fas fa-tshirt fa-4x text-primary mb-3" style="opacity:0.6;"></i>
                            <h5 class="fw-bold">Full Garment Printing Form</h5>
                            <p class="text-muted mb-1">Complete garment printing with all features:</p>
                            <div class="d-flex justify-content-center gap-3 mb-3 flex-wrap small">
                                <span class="badge bg-light text-dark p-2"><i class="fas fa-image text-info me-1"></i> Mockup Upload</span>
                                <span class="badge bg-light text-dark p-2"><i class="fas fa-palette text-info me-1"></i> Fabric Selection</span>
                                <span class="badge bg-light text-dark p-2"><i class="fas fa-cogs text-info me-1"></i> Specs (Inner, Pocket, Cuffs, etc.)</span>
                                <span class="badge bg-light text-dark p-2"><i class="fas fa-ruler text-info me-1"></i> Sizes (Qty Mode / Roster / Excel Import)</span>
                                <span class="badge bg-light text-dark p-2"><i class="fas fa-puzzle-piece text-info me-1"></i> Additional Parts</span>
                                <span class="badge bg-light text-dark p-2"><i class="fas fa-tag text-info me-1"></i> Special Pricing</span>
                            </div>
                            <button type="button" class="btn btn-lg btn-primary px-4" onclick="kanbanOpenSubAddProductModal()">
                                <i class="fas fa-external-link-alt me-2"></i> Open Garment Printing Form
                            </button>
                            <p class="text-muted small mt-2 mb-0">Opens the full garment printing form with mockup, specifications, sizes, and pricing.</p>
                        </div>
                    </div>
                                </div>
                    </div>
                    <div class="tab-pane fade" id="addonGenericBody" role="tabpanel">
                                                <div class="row">
                            <div class="col-md-8">
                                <!-- FILTER SECTION -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0"><i class="fas fa-filter me-1"></i> Filter Products</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Brand</label>
                                                <input type="text" class="form-control form-control-sm" id="addonFilterBrand" list="brandOptions" placeholder="Type or select brand...">
                                                <datalist id="addonBrandOptions">
                                                    <!-- Brand options will be loaded dynamically -->
                                                </datalist>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Type</label>
                                                <input type="text" class="form-control form-control-sm" id="addonFilterType" list="typeOptions" placeholder="Type or select type...">
                                                <datalist id="addonTypeOptions">
                                                    <!-- Type options will be loaded dynamically -->
                                                </datalist>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Color</label>
                                                <input type="text" class="form-control form-control-sm" id="addonFilterColor" list="colorOptions" placeholder="Type or select color...">
                                                <datalist id="addonColorOptions">
                                                    <!-- Color options will be loaded dynamically -->
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="applyFilters()">
                                                <i class="fas fa-filter me-1"></i> Apply Filters
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                                                <i class="fas fa-redo me-1"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- END FILTER SECTION -->
                                
                                <!-- MULTIPLE PRODUCT ROWS SECTION -->
                                <div class="mb-3">
                                    <label class="form-label">Products *</label>
                                    <div id="addonProductRowsContainer">
                                        <!-- Product Row Template (Hidden) -->
                                        <div class="product-row-template d-none">
                                            <div class="row g-2 mb-2 align-items-center">
                                                <div class="col-md-6">
                                                    <select class="form-control product-select" name="product_id[]">
                                                        <option value="">Select a product</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" class="form-control product-quantity" name="quantity[]" min="1" value="1" placeholder="Qty">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove this product">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- First Product Row -->
                                        <div class="product-row">
                                            <div class="row g-2 mb-2 align-items-center">
                                                <div class="col-md-6">
                                                    <select class="form-control product-select" required>
                                                        <option value="">Select a product</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" class="form-control product-quantity" name="quantity[]" min="1" value="1" placeholder="Qty" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove this product">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-success" id="addonAddProductRow">
                                            <i class="fas fa-plus-circle me-1"></i> Add Another Product
                                        </button>
                                    </div>
                                    <div class="form-text">Add multiple products in one go. Products loaded from database based on category.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Assign to Department *</label>
                                    <select class="form-control" id="addonDepartmentSelect" required>
                                        <option value="">-- Select Department --</option>
                                        <option value="iprint">iPrint Department</option>
                                        <option value="consol">Consol Department</option>
                                        <option value="cinco">Cinco Department</option>
                                        <option value="class">Class Department</option>
                                        <option value="mto">Made to Order Department</option>
                                        <option value="other">Other Department</option>
                                    </select>
                                    <div class="form-text">Select which department will handle these items</div>
                                </div>
                                
                                <!-- Unit Price Display (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label">Unit Price (₱)</label>
                                    <input type="text" class="form-control" id="productPriceDisplay" readonly value="₱0.00">
                                    <div class="form-text">Price auto-filled when product is selected. Same price applies to all quantities.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Notes (Optional)</label>
                                    <textarea class="form-control" id="addonProductNotes" rows="3" placeholder="Add special instructions, colors, sizes, etc."></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">Order Summary</h6>
                                        
                                        <!-- Products Breakdown -->
                                        <div id="productsBreakdown" class="mb-3">
                                            <div class="text-muted small mb-2">No products selected yet</div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Quantity:</span>
                                            <span id="totalQuantityDisplay">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Unit Price:</span>
                                            <span id="priceDisplay">₱0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Discount:</span>
                                            <span id="discountDisplay" class="text-muted small">No bulk discount applied</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total Amount:</span>
                                            <span id="itemTotalDisplay">₱0.00</span>
                                        </div>
                                        
                                        <button type="button" class="btn btn-primary w-100 mt-4" id="addItemBtn">
                                            <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                        </button>
                                        
                                        <div class="alert alert-info mt-3 small">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Select the department that will handle this item. Managers will be notified automatically.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary w-100" id="addonGeneric_submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Add to Add-on Request (Other)
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 p-3 border rounded bg-light">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-comment me-1"></i> Reason for Add-on</label>
                        <textarea class="form-control" id="addonReason" rows="2" placeholder="Customer requested additional items..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label"><i class="fas fa-user me-1"></i> Requested By</label>
                            <input type="text" class="form-control" id="addonRequestedBy" placeholder="Your name" value="Agent">
                        </div>
                        <div class="col-6">
                            <label class="form-label"><i class="fas fa-tag me-1"></i> Markup Type</label>
                            <select class="form-select" id="addonMarkupType">
                                <option value="standard">Standard Price</option>
                                <option value="discount">Discounted</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- === PENDING ADD-ON REQUESTS MODAL === -->
<div class="modal fade" id="pendingAddonsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clock me-2"></i>Pending Add-on Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pendingAddonsBody">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading pending requests...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="refreshPendingAddons()">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<!-- LIGHTBOX overlay is created dynamically by JS (avoids Bootstrap modal repaint issues) -->

@include('partials.sublimation-show-modal')

@endsection

@push('scripts')
<script>
// Sales with approved additional products (from change requests)
var approvedAdditions = @json(array_keys($approvedAdditions ?? []));

(function() {
    'use strict';
    
    const board = document.getElementById('kanbanBoard');
    const tabs = document.querySelectorAll('.department-tab');

    // Manager/admin can override the photo-completeness lock on card moves
    window.kanbanCanOverride = {{ $canOverride ? 'true' : 'false' }};

    function showKanbanToast(msg) {
        var existing = document.getElementById('kanbanToast');
        if (existing) existing.remove();
        var toast = document.createElement('div');
        toast.id = 'kanbanToast';
        toast.textContent = msg;
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;background:#dc3545;color:#fff;padding:12px 20px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);font-size:14px;font-weight:600;max-width:400px;';
        document.body.appendChild(toast);
        setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(function() { toast.remove(); }, 300); }, 3500);
    }

    // === DRAG & DROP (native HTML5 — most reliable) ===
    let draggedCard = null;

    document.addEventListener('dragstart', function(e) {
        var card = e.target.closest('.kanban-card');
        if (!card || !board.contains(card)) return;
        draggedCard = card;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', card.dataset.id);
    });

    document.addEventListener('dragend', function(e) {
        var card = e.target.closest('.kanban-card');
        if (card) {
            card.classList.remove('dragging');
        }
        document.querySelectorAll('.drop-zone').forEach(function(z) { z.classList.remove('drag-over'); });
        draggedCard = null;
    });

    document.addEventListener('dragover', function(e) {
        e.preventDefault();
        // Check if any ancestor is a drop-zone
        var zone = e.target.closest('.drop-zone');
        if (!zone || !board.contains(zone)) return;
        e.dataTransfer.dropEffect = 'move';
        document.querySelectorAll('.drop-zone.drag-over').forEach(function(z) {
            if (z !== zone) z.classList.remove('drag-over');
        });
        zone.classList.add('drag-over');
    });

    document.addEventListener('dragleave', function(e) {
        var zone = e.target.closest('.drop-zone');
        if (zone) zone.classList.remove('drag-over');
    });

    document.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Clear all highlights
        document.querySelectorAll('.drop-zone.drag-over').forEach(function(z) { z.classList.remove('drag-over'); });
        
        var zone = e.target.closest('.drop-zone');
        if (!zone || !board.contains(zone) || !draggedCard) return;
        
        var newStatus = zone.dataset.status;
        var saleId = draggedCard.dataset.id;
        if (!newStatus || !saleId) return;
        
        // PHOTO LOCK: cannot move to Design and beyond until photos (file screenshot + sample color) are complete
        var currentStatus = draggedCard.closest('.kanban-column') ? draggedCard.closest('.kanban-column').dataset.status : '';
        var photosOk = draggedCard.dataset.photos === 'ok';
        var canOverride = window.kanbanCanOverride === true;
        var lockedStatuses = ['design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        
        if (lockedStatuses.indexOf(newStatus) !== -1 && !photosOk && !canOverride) {
            showKanbanToast('⚠️ Hindi ma-move: kulang pang photos (File Screenshot / Sample Color). Kailangan muna kumpleto bago lumipat sa ' + newStatus + '.');
            document.querySelectorAll('.drop-zone.drag-over').forEach(function(z) { z.classList.remove('drag-over'); });
            return;
        }
        if (lockedStatuses.indexOf(newStatus) !== -1 && !photosOk && canOverride && !confirm('⚠️ Kulang pa ang photos (File Screenshot / Sample Color). I-move pa rin sa ' + newStatus + '?')) {
            document.querySelectorAll('.drop-zone.drag-over').forEach(function(z) { z.classList.remove('drag-over'); });
            return;
        }
        
        // Move card
        var emptyMsg = zone.querySelector('.empty-column');
        if (emptyMsg) emptyMsg.remove();
        zone.appendChild(draggedCard);
        draggedCard.classList.remove('dragging');
        
        // Update counts
        document.querySelectorAll('.kanban-column').forEach(function(col) {
            var count = col.querySelector('.card-count');
            if (count) count.textContent = col.querySelectorAll('.kanban-card').length;
        });
        
        // Empty states
        document.querySelectorAll('.kanban-column').forEach(function(col) {
            var zone2 = col.querySelector('.drop-zone');
            if (zone2 && zone2.querySelectorAll('.kanban-card').length === 0 && !zone2.querySelector('.empty-column')) {
                var empty = document.createElement('div');
                empty.className = 'empty-column';
                empty.textContent = 'No items';
                zone2.appendChild(empty);
            }
        });
        
        // Save
        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf) {
            fetch('/sales/prototype/' + saleId + '/update-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf.content
                },
                body: JSON.stringify({ kanban_status: newStatus })
            }).catch(function(err) {
                console.error('Update error:', err);
            });
        }
        
        draggedCard = null;
    });
// === CARD CLICK - OPEN DETAILS MODAL ===
    document.addEventListener('click', function(e) {
        var card = e.target.closest('.kanban-card:not(.dragging)');
        if (!card || !board.contains(card)) return;
        var saleId = card.dataset.id;
        if (saleId) openSaleDetails(saleId);
    });
    
    // === OPEN SALE DETAILS ===
    window.openSaleDetails = function(saleId) {
        var modal = document.getElementById('saleDetailsModal');
        var body = document.getElementById('modalSaleBody');
        var title = document.getElementById('modalSaleTitle');
        
        if (!modal || !body) return;
        
        // Show loading
        body.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>';
        title.textContent = 'Sale Details';
        
        // Store sale ID immediately (before fetch completes) for Production Slip tab
        title.dataset.saleId = saleId;
        body.dataset.saleId = saleId;
        
        // Toggle Additional Production Slip tab based on approved additions
        var addProdTab = document.querySelector('.prod-tab[data-tab="addProdSlip"]');
        if (addProdTab) {
            if (approvedAdditions && approvedAdditions.indexOf(parseInt(saleId)) !== -1) {
                addProdTab.style.display = '';
            } else {
                addProdTab.style.display = 'none';
            }
        }
        
        // Clear production slip caches so they reload when switching tabs
        var prodBody = document.getElementById('modalProdSlipBody');
        var addProdBody = document.getElementById('modalAddProdSlipBody');
        if (prodBody) prodBody.dataset.loaded = '';
        if (addProdBody) addProdBody.dataset.loaded = '';
        
        // Reset to Details tab on open
        var allTabs = document.querySelectorAll('.prod-tab');
        allTabs.forEach(function(t) {
            t.style.color = '#666';
            t.style.borderBottomColor = 'transparent';
            t.style.fontWeight = '500';
        });
        var detailsTab = document.querySelector('.prod-tab[data-tab="details"]');
        if (detailsTab) {
            detailsTab.style.color = '#0d6efd';
            detailsTab.style.borderBottomColor = '#0d6efd';
            detailsTab.style.fontWeight = '600';
        }
        if (prodBody) prodBody.style.display = 'none';
        if (addProdBody) addProdBody.style.display = 'none';
        body.style.display = '';
        
        // Fetch sale data
        fetch('/sales/prototype/' + saleId + '/details' + window.location.search, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            title.textContent = data.title || 'Sale #' + saleId;
            title.dataset.saleId = saleId;
            body.innerHTML = data.html;
            // Attach lightbox click handlers to dynamically loaded images
            body.querySelectorAll('.ref-image, .payment-img, .mockup-box img').forEach(function(img) {
                img.addEventListener('click', function() {
                    window.openLightbox(this.src);
                });
            });
            // Show addon button only if sale is still active
            if (data.can_addon !== false) {
                document.getElementById('addonOpenBtn').style.display = '';
            } else {
                document.getElementById('addonOpenBtn').style.display = 'none';
            }
            // Store first service name for addon modal
            addon_currentFirstServiceName = data.firstServiceName || '';
            
            // Update Print Slip button link
            var editBtn = document.getElementById('modalEditBtn');
            var printBtn = document.getElementById('modalPrintSlipBtn');
            var fullSaleBtn = document.getElementById('modalViewFullSaleBtn');
            if (editBtn) {
                editBtn.style.display = 'none';
            }
            if (printBtn) {
                printBtn.href = '/sales/prototype/' + saleId + '/print-slip';
                printBtn.style.display = '';
            }
            if (fullSaleBtn) {
                fullSaleBtn.href = '/sales/prototype/' + saleId;
                fullSaleBtn.style.display = '';
            }
            // Body dataset already set before the fetch
        })
        .catch(function(err) {
            body.innerHTML = '<div class="alert alert-danger">Failed to load sale details: ' + err.message + '</div>';
        });
        
        var bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    };
    
    // === DEPARTMENT TAB CLICK (AJAX) ===
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            const dept = this.dataset.dept;
            if (!dept) return;
            
            // Update active tab
            tabs.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');
            
            // Show loading
            board.style.opacity = '0.4';
            board.style.pointerEvents = 'none';
            
            // Build URL
            var url = '{{ route("sales.prototype.kanban") }}';
            if (dept !== 'all') {
                url += '/' + dept;
            }
            
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newBoard = doc.getElementById('kanbanBoard');
                if (newBoard) {
                    board.innerHTML = newBoard.innerHTML;
                }
                // Update page title
                var newTitle = doc.querySelector('title');
                if (newTitle) document.title = newTitle.textContent;
                board.style.opacity = '1';
                board.style.pointerEvents = '';
            })
            .catch(function(err) {
                console.error('Tab switch error:', err);
                board.style.opacity = '1';
                board.style.pointerEvents = '';
            });
        });
    });
    
    // === IMAGE LIGHTBOX (dynamically created — avoids Bootstrap modal repaint issues) ===
    window.openLightbox = function(src) {
        // Remove existing lightbox if any
        var old = document.getElementById('imageLightbox');
        if (old) old.remove();
        
        var overlay = document.createElement('div');
        overlay.id = 'imageLightbox';
        overlay.style.cssText = 'display:flex!important;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;z-index:100000;background:rgba(0,0,0,0.85);cursor:zoom-out;';
        
        var closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = 'position:absolute;top:15px;right:25px;font-size:32px;color:white;background:none;border:none;cursor:pointer;z-index:100001;';
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeLightbox();
        });
        
        var imgContainer = document.createElement('div');
        imgContainer.style.cssText = 'display:flex;align-items:center;justify-content:center;height:100%;padding:40px;';
        
        var img = document.createElement('img');
        img.id = 'lightboxImage';
        img.style.cssText = 'max-width:100%;max-height:90vh;object-fit:contain;border-radius:8px;';
        img.alt = '';
        
        imgContainer.appendChild(img);
        overlay.appendChild(closeBtn);
        overlay.appendChild(imgContainer);
        
        // Close on background click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeLightbox();
            }
        });
        
        // Set src, then show when loaded (or immediately if cached)
        img.src = src;
        if (img.complete) {
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';
        } else {
            img.addEventListener('load', function() {
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden';
            });
            img.addEventListener('error', function() {
                // Still show even if image fails
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden';
            });
            // Timeout fallback (5s)
            setTimeout(function() {
                if (!overlay.parentNode) {
                    document.body.appendChild(overlay);
                    document.body.style.overflow = 'hidden';
                }
            }, 5000);
        }
    };
    window.closeLightbox = function() {
        var overlay = document.getElementById('imageLightbox');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    };
    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var overlay = document.getElementById('imageLightbox');
            if (overlay) {
                closeLightbox();
            }
        }
    });
    
// === ADD-ON GARMENT MODAL SYSTEM (ported from create.blade) ===
let addon_selectedItems = [];
let addon_currentProductType = "";
let addon_currentDepartment = "";
var addon_garment_initialized = false;
var addon_garment_printingData = {};
var addon_garment_selectedPrintSizes = [];
var addon_garment_uploadedReferenceImages = [];
var addon_garment_referenceImageCounter = 0;
var addon_garment_hasSpecialPrice = false;
var addon_currentSaleId = '';
var addon_currentFirstServiceName = '';

window.addonOpenProductModal = function(productType) {
    addon_currentProductType = productType;
    const titleEl = document.getElementById("modalSaleTitle");
    if (titleEl && titleEl.dataset && titleEl.dataset.saleId) {
        addon_currentSaleId = titleEl.dataset.saleId;
    }
    document.getElementById("addonSaleId").value = addon_currentSaleId || "";
    
    const garmentTab = document.getElementById("addonGarmentTab");
    const genericTab = document.getElementById("addonGenericTab");
    if (productType === "garment") {
        if (garmentTab) { garmentTab.style.display = ""; garmentTab.click(); }
        if (genericTab) genericTab.style.display = "none";
        addonGarment_openModal(productType);
    } else {
        if (genericTab) { genericTab.style.display = ""; genericTab.click(); }
        if (garmentTab) garmentTab.style.display = "none";
        addonLoadProductsFromDatabase(productType);
    }
    const modal = new bootstrap.Modal(document.getElementById("addonProductModal"));
    modal.show();
};

window.addonLoadProductsFromDatabase = function(productType) {
    fetch("/api/products-for-box/" + productType)
        .then(r => r.json())
        .then(products => {
            var select = document.getElementById("addonProductsSelect");
            if (select) {
                select.innerHTML = `<option value="">-- Select Product --</option>`;
                products.forEach(function(p) {
                    var opt = document.createElement("option");
                    opt.value = p.id;
                    opt.textContent = p.name + (p.description ? " (" + ((p.description.match(/Size:\\s*([^,]+)/i) || [])[1] || "") + ")" : "");
                    if (p.price) opt.dataset.price = p.price;
                    if (p.brand) opt.dataset.brand = p.brand;
                    if (p.size) opt.dataset.size = p.size;
                    if (p.color) opt.dataset.color = p.color;
                    select.appendChild(opt);
                });
            }
        });
};

window.addonGeneric_addItem = function() {
    alert("Generic product add-on coming soon. Please use Garment Printing tab.");
};

// Garment functions
window.addonGarment_openModal = function(pt) {
    addonGarment_initializeProductRows();
    addonGarment_loadFilterOptions(pt);
    addonGarment_populatePrintTypes();
};

// Full Garment Printing Form (opens sublimation modal from show page, adapted for kanban)
window.kanbanOpenSubAddProductModal = function() {
    var saleId = addon_currentSaleId;
    if (!saleId) {
        alert('No sale selected. Please open a sale first.');
        return;
    }
    
    // Fetch sale data for modal pre-fill
    fetch('/sales/prototype/' + saleId + '/details' + window.location.search, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        // Set up modal data attributes for the shared sublimation modal
        var modalEl = document.getElementById('subAddProductModal');
        if (!modalEl) return;
        
        // Construct the add-product URL for this sale
        modalEl.dataset.addProductUrl = '/sales/prototype/' + saleId + '/add-product';
        modalEl.dataset.orderNumber = data.title ? data.title.replace(/.*\(#/, '').replace(/\)$/, '') : '';
        modalEl.dataset.customerName = data.title ? data.title.replace(/Sale: /, '').replace(/ \(#.*/, '') : '';
        
        // Show sale badge
        var badge = document.getElementById('subSaleBadge');
        if (badge) {
            badge.textContent = 'To Order #' + (data.title ? data.title.replace(/.*\(#/, '').replace(/\)$/, '') : saleId);
            badge.style.display = '';
        }
        
        // Open the modal with sale data
        var saleData = {
            services: [],
            firstServiceName: data.firstServiceName || ''
        };
        window.openSubAddProductModal(saleData);
    })
    .catch(function(err) {
        // Fallback: open modal without pre-fill
        var saleData = { services: [], firstServiceName: '' };
        window.openSubAddProductModal(saleData);
    });
};

window.addonGarment_initializeProductRows = function() {
    const container = document.getElementById("addonGarment_productRowsContainer");
    if (!container) return;
    container.innerHTML = `<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>`;
    fetch("/api/products-for-box/garment")
        .then(r => r.json())
        .then(products => {
            container.innerHTML = "";
            addonGarment_addProductRow();
            addonGarment_setupProductRowEvents();
        });
};

window.addonGarment_setupProductRowEvents = function() {
    const addBtn = document.getElementById("addonGarment_addProductRowBtn");
    if (addBtn) addBtn.onclick = function() { addonGarment_addProductRow(); };
    document.querySelectorAll("#addonGarment_productRowsContainer .product-row").forEach(function(row) {
        const sel = row.querySelector(".product-select");
        if (sel) sel.onchange = function() { addonGarment_updatePrintSummary(); };
        const qty = row.querySelector(".product-qty");
        if (qty) qty.oninput = function() { addonGarment_updatePrintSummary(); };
        const rm = row.querySelector(".remove-row-btn");
        if (rm) rm.onclick = function() { row.remove(); addonGarment_updatePrintSummary(); };
    });
};

window.addonGarment_addProductRow = function() {
    const container = document.getElementById("addonGarment_productRowsContainer");
    if (!container) return;
    fetch("/api/products-for-box/garment")
        .then(r => r.json())
        .then(products => {
            var opts = `<option value="">-- Select --</option>`;
            products.forEach(function(p) {
                var txt = p.name;
                if (p.description) {
                    var m = p.description.match(/Size:\\s*([^,]+)/i);
                    if (m) txt += " (" + m[1].trim() + ")";
                }
                opts += '<option value="' + p.id + '" data-price="' + (p.price || 0) + '" data-brand="' + (p.brand || '') + '" data-size="' + (p.size || '') + '" data-color="' + (p.color || '') + '">' + txt + '</option>';
            });
            var row = document.createElement("div");
            row.className = "product-row mb-2 p-2 border rounded bg-light";
            row.innerHTML = '<div class="d-flex gap-2 align-items-start"><div style="flex:1;"><select class="form-select form-select-sm product-select">' + opts + '</select></div><div style="width:70px;"><input type="number" class="form-control form-control-sm product-qty" value="1" min="1"></div><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">\u2715</button></div>';
            container.appendChild(row);
            addonGarment_setupProductRowEvents();
            addonGarment_updatePrintSummary();
        });
};

window.addonGarment_populatePrintTypes = function() {
    var sel = document.getElementById("addonGarment_printTypeSelect");
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Select Print Type --</option>';
    fetch("/api/printing/options/dtf").then(function(r) { return r.json(); }).then(function(data) {
        var types = data.print_types || [];
        if (types.length) {
            types.forEach(function(t) {
                var o = document.createElement("option");
                o.value = t.toLowerCase();
                var label = t.charAt(0).toUpperCase() + t.slice(1) + ' Print';
                o.textContent = label;
                sel.appendChild(o);
            });
        } else {
            ["DTF","Sublimation","Heat Transfer","Embroidery"].forEach(function(t) {
                var o = document.createElement("option"); o.value = t; o.textContent = t;
                sel.appendChild(o);
            });
        }
    }).catch(function() {
        ["DTF","Sublimation","Heat Transfer","Embroidery"].forEach(function(t) {
            var o = document.createElement("option"); o.value = t; o.textContent = t;
            sel.appendChild(o);
        });
    });
};

window.addonGarment_loadPrintSizes = function(printType) {
    if (!printType) {
        document.getElementById("addonGarment_printSizesContainer").style.display = "none";
        document.getElementById("addonGarment_printQuantitySection").style.display = "none";
        document.getElementById("addonGarment_printSummarySidebar").style.display = "none";
        return;
    }
    fetch("/api/printing/options/" + printType).then(r => r.json()).then(prices => {
        if (prices && prices.length) {
            document.getElementById("addonGarment_printSizesContainer").style.display = "";
            var html = '<div class="row g-1">';
            prices.forEach(function(p) {
                html += '<div class="col-4"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + p.id + '" id="addonPS_" + p.id + "" onchange="addonGarment_togglePrintSize(' + p.id + ', this)"><label class="form-check-label small" for="addonPS_" + p.id + "">' + (p.size_name || p.name) + ' (\u20B1' + (parseFloat(p.price) || 0).toFixed(2) + ')</label></div></div>';
            });
            html += "</div>";
            document.getElementById("addonGarment_printSizesList").innerHTML = html;
        }
        document.getElementById("addonGarment_printQuantitySection").style.display = "";
        addonGarment_updatePrintSummary();
    }).catch(function() {
        document.getElementById("addonGarment_printQuantitySection").style.display = "";
        addonGarment_updatePrintSummary();
    });
};

window.addonGarment_togglePrintSize = function(sizeId, cb) {
    if (cb.checked) { if (!addon_garment_selectedPrintSizes.includes(sizeId)) addon_garment_selectedPrintSizes.push(sizeId); }
    else { addon_garment_selectedPrintSizes = addon_garment_selectedPrintSizes.filter(function(id) { return id !== sizeId; }); }
    addonGarment_updatePrintSummary();
};

window.addonGarment_toggleSpecialPrice = function() {
    addon_garment_hasSpecialPrice = !addon_garment_hasSpecialPrice;
    document.getElementById("addonGarment_specialPriceSection").style.display = addon_garment_hasSpecialPrice ? "" : "none";
    if (!addon_garment_hasSpecialPrice) {
        document.getElementById("addonGarment_specialPrintTotal").value = "";
        document.getElementById("addonGarment_specialPriceReason").value = "";
    }
    addonGarment_updatePrintSummary();
};

window.addonGarment_clearSpecialPrice = function() {
    addon_garment_hasSpecialPrice = false;
    document.getElementById("addonGarment_specialPriceSection").style.display = "none";
    document.getElementById("addonGarment_specialPrintTotal").value = "";
    document.getElementById("addonGarment_specialPriceReason").value = "";
    addonGarment_updatePrintSummary();
};

window.addonGarment_onSpecialPriceChange = function() {
    addonGarment_updatePrintSummary();
};

window.addonGarment_clearPrintSelection = function() {
    document.getElementById("addonGarment_printTypeSelect").value = "";
    document.getElementById("addonGarment_printSizesContainer").style.display = "none";
    document.getElementById("addonGarment_printQuantitySection").style.display = "none";
    document.getElementById("addonGarment_printSummarySidebar").style.display = "none";
    addon_garment_selectedPrintSizes = [];
    addonGarment_updatePrintSummary();
};

window.addonGarment_updatePrintSummary = function() {
    var totalQty = 0, totalAmount = 0;
    var hasSP = addon_garment_hasSpecialPrice || false;
    document.querySelectorAll("#addonGarment_productRowsContainer .product-row").forEach(function(row) {
        var sel = row.querySelector(".product-select"), qtyI = row.querySelector(".product-qty");
        if (!sel || !qtyI) return;
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;
        var qty = parseInt(qtyI.value) || 0;
        if (qty <= 0) return;
        var bp = parseFloat(opt.dataset.price) || 0;
        var up = bp;
        totalQty += qty;
        totalAmount += up * qty;
    });
    document.getElementById("addonGarment_totalQtyDisplay").textContent = totalQty;
    document.getElementById("addonGarment_totalAmountDisplay").textContent = "\u20B1" + totalAmount.toFixed(2);
    
    var printType = document.getElementById("addonGarment_printTypeSelect");
    var printQtyI = document.getElementById("addonGarment_printQuantityInput");
    var pv = printType ? printType.value : "";
    var pq = printQtyI ? parseInt(printQtyI.value) || 0 : 0;
    
    if (pv && pq > 0) {
        document.getElementById("addonGarment_printSummarySidebar").style.display = "";
        fetch("/api/printing/options/" + pv).then(r => r.json()).then(prices => {
            if (!prices || !prices.length) return;
            var ppi = 0;
            var si = [];
            addon_garment_selectedPrintSizes.forEach(function(sid) {
                var sd = prices.find(function(p) { return p.id == sid; });
                if (sd) { si.push(sd); ppi += parseFloat(sd.price) || 0; }
            });
            var ps = ppi * pq;
            var cd = 0;
            var us = new Set(si.map(s => s.size_name || s.name));
            if (us.size >= 3) cd = ps * 0.05;
            var bd = 0;
            if (pq >= 10) bd = ps * 0.10;
            else if (pq >= 5) bd = ps * 0.05;
            var pt = ps - cd - bd;
            if (hasSP) {
                var st = parseFloat(document.getElementById("addonGarment_specialPrintTotal")?.value) || 0;
                if (st > 0) pt = st;
            }
            document.getElementById("addonGarment_printSizesBreakdown").innerHTML = si.map(function(s) { return '<div class="d-flex justify-content-between"><span>' + (s.size_name || s.name) + '</span><span>\u20B1' + (parseFloat(s.price) || 0).toFixed(2) + '</span></div>'; }).join('');
            document.getElementById("addonGarment_printCostPerItemDisplay").textContent = "\u20B1" + ppi.toFixed(2);
            document.getElementById("addonGarment_printQtyDisplay").textContent = pq;
            document.getElementById("addonGarment_printSubtotalDisplay").textContent = "\u20B1" + ps.toFixed(2);
            document.getElementById("addonGarment_comboDiscountRow").style.display = cd > 0 ? "" : "none";
            if (cd > 0) document.getElementById("addonGarment_comboDiscountDisplay").textContent = "-\u20B1" + cd.toFixed(2);
            document.getElementById("addonGarment_bulkDiscountRow").style.display = bd > 0 ? "" : "none";
            if (bd > 0) document.getElementById("addonGarment_bulkDiscountDisplay").textContent = "-\u20B1" + bd.toFixed(2);
            document.getElementById("addonGarment_printTotalDisplay").textContent = "\u20B1" + pt.toFixed(2);
            document.getElementById("addonGarment_grandTotalDisplay").textContent = "\u20B1" + (totalAmount + pt).toFixed(2);
        });
    } else {
        document.getElementById("addonGarment_printSummarySidebar").style.display = "none";
        document.getElementById("addonGarment_grandTotalDisplay").textContent = "\u20B1" + totalAmount.toFixed(2);
    }
};

// Reference image functions
window.addonGarment_triggerReferenceFilePicker = function() { document.getElementById("addonReferenceFilePicker").click(); };
window.addonGarment_onReferencePaste = function(e) {
    for (var i = 0; i < e.clipboardData.items.length; i++) {
        if (e.clipboardData.items[i].type.indexOf("image") !== -1) {
            var f = e.clipboardData.items[i].getAsFile();
            if (f) addonGarment_handleReferenceImage(f);
        }
    }
};
window.addonGarment_onReferenceDragOver = function(e) { e.preventDefault(); e.currentTarget.style.borderColor = "#0d6efd"; };
window.addonGarment_onReferenceDragLeave = function(e) { e.preventDefault(); e.currentTarget.style.borderColor = "#ccc"; };
window.addonGarment_onReferenceDrop = function(e) {
    e.preventDefault(); e.currentTarget.style.borderColor = "#ccc";
    for (var i = 0; i < e.dataTransfer.files.length; i++) addonGarment_handleReferenceImage(e.dataTransfer.files[i]);
};
window.addonGarment_onReferenceFilePickerChange = function(e) {
    for (var i = 0; i < e.target.files.length; i++) addonGarment_handleReferenceImage(e.target.files[i]);
};
window.addonGarment_handleReferenceImage = function(file) {
    if (!file.type.startsWith("image/")) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        addon_garment_uploadedReferenceImages.push({ id: ++addon_garment_referenceImageCounter, file: file, dataUrl: e.target.result, name: file.name });
        addonGarment_renderReferencePreviews();
    };
    reader.readAsDataURL(file);
};
window.addonGarment_renderReferencePreviews = function() {
    var c = document.getElementById("addonGarment_referencePreviews");
    if (!c) return;
    c.innerHTML = '<div class="d-flex flex-wrap gap-2 mt-2">';
    addon_garment_uploadedReferenceImages.forEach(function(img) {
        c.innerHTML += '<div class="position-relative" style="width:80px;height:80px;"><img src="' + img.dataUrl + '" style="width:100%;height:100%;object-fit:cover;border-radius:6px;border:1px solid #ddd;"><button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="font-size:10px;padding:1px 4px;border-radius:50%;" onclick="addonGarment_removeReferenceImage(' + img.id + ')">\u2715</button></div>';
    });
    c.innerHTML += '</div>';
};
window.addonGarment_clearAllReferenceImages = function() { addon_garment_uploadedReferenceImages = []; addonGarment_renderReferencePreviews(); };
window.addonGarment_removeReferenceImage = function(id) { addon_garment_uploadedReferenceImages = addon_garment_uploadedReferenceImages.filter(function(i) { return i.id !== id; }); addonGarment_renderReferencePreviews(); };

// Filter functions
window.addonGarment_loadFilterOptions = function(productType) {
    if (productType !== "garment") return;
    fetch("/api/products-for-box/garment").then(r => r.json()).then(products => {
        ["brand","type","color"].forEach(function(f) {
            var list = document.getElementById("addonGarment_" + f + "Options");
            if (!list) return;
            if (f === "type") f = "size";
            var vals = [...new Set(products.filter(p => p[f]).map(p => p[f]))];
            list.innerHTML = vals.map(function(v) { return '<option value="' + v + '">'; }).join('');
        });
    });
};

window.addonGarment_applyFilters = function() {
    var bv = (document.getElementById("addonGarment_filterBrand")?.value || "").toLowerCase();
    var tv = (document.getElementById("addonGarment_filterType")?.value || "").toLowerCase();
    var cv = (document.getElementById("addonGarment_filterColor")?.value || "").toLowerCase();
    document.querySelectorAll("#addonGarment_productRowsContainer .product-row").forEach(function(row) {
        var sel = row.querySelector(".product-select");
        if (!sel) return;
        for (var i = 1; i < sel.options.length; i++) {
            var o = sel.options[i];
            var ok = true;
            if (bv && !(o.dataset.brand || "").toLowerCase().includes(bv)) ok = false;
            if (tv && !(o.dataset.size || "").toLowerCase().includes(tv)) ok = false;
            if (cv && !(o.dataset.color || "").toLowerCase().includes(cv)) ok = false;
            o.style.display = ok ? "" : "none";
        }
        var vis = false;
        for (var i = 1; i < sel.options.length; i++) { if (sel.options[i].style.display !== "none") { vis = true; break; } }
        row.style.display = vis ? "" : "none";
        if (sel.selectedIndex > 0 && sel.options[sel.selectedIndex].style.display === "none") sel.selectedIndex = 0;
    });
    addonGarment_updatePrintSummary();
};

window.addonGarment_resetFilters = function() {
    ["filterBrand","filterType","filterColor"].forEach(function(id) {
        var el = document.getElementById("addonGarment_" + id);
        if (el) el.value = "";
    });
    document.querySelectorAll("#addonGarment_productRowsContainer .product-row").forEach(function(row) {
        row.style.display = "";
        var sel = row.querySelector(".product-select");
        if (sel) for (var i = 1; i < sel.options.length; i++) sel.options[i].style.display = "";
    });
    addonGarment_updatePrintSummary();
};

// === MAIN SUBMIT: Add to Add-on Request ===
window.addonGarment_submitRequest = function() {
    var saleId = document.getElementById("addonSaleId").value;
    var reason = document.getElementById("addonReason").value;
    var requestedBy = document.getElementById("addonRequestedBy").value;
    if (!saleId) { alert("No sale selected."); return; }
    if (!reason.trim()) { alert("Please provide a reason."); return; }
    
    var items = [], totalQty = 0, totalAmount = 0;
    document.querySelectorAll("#addonGarment_productRowsContainer .product-row").forEach(function(row) {
        var sel = row.querySelector(".product-select"), qtyI = row.querySelector(".product-qty");
        if (!sel || !qtyI) return;
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;
        var qty = parseInt(qtyI.value) || 0;
        if (qty <= 0) return;
        var bp = parseFloat(opt.dataset.price) || 0;
        totalQty += qty; totalAmount += bp * qty;
        var addonItem = {
            name: "Garment Order",
            department: "iprint",
            totalQty: qty,
            totalProductPrice: bp * qty,
            totalPrice: bp * qty,
            totalPricePerUnit: 0,
            subItems: [{
                brand: opt.dataset.brand || "",
                size: opt.dataset.size || "",
                color: opt.dataset.color || "",
                qty: qty,
                price: bp
            }],
            printing: {}
        };
        items.push(addonItem);
    });
    if (items.length === 0) { alert("Add at least one product."); return; }
    
    var printType = document.getElementById("addonGarment_printTypeSelect")?.value || "";
    var printQty = parseInt(document.getElementById("addonGarment_printQuantityInput")?.value) || totalQty;
    if (printType) {
        items.forEach(function(it) {
            it.printing = {
                printType: printType,
                printSizes: addon_garment_selectedPrintSizes,
                printCostPerItem: 0,
                printQty: printQty,
                printSubtotal: 0,
                comboDiscount: 0,
                bulkDiscount: 0,
                isSpecialPrice: addon_garment_hasSpecialPrice,
                specialPriceTotal: addon_garment_hasSpecialPrice ? parseFloat(document.getElementById("addonGarment_specialPrintTotal")?.value) || 0 : 0,
                specialPriceReason: addon_garment_hasSpecialPrice ? document.getElementById("addonGarment_specialPriceReason")?.value || "" : ""
            };
        });
        var gt = document.getElementById("addonGarment_grandTotalDisplay");
        if (gt) totalAmount = parseFloat(gt.textContent.replace(/[\u20B1,]/g, "")) || totalAmount;
    }
    
    var payload = { reason: reason, requested_by: requestedBy || "Agent", requested_items: JSON.stringify(items) };
    
    var btn = document.getElementById("addonGarment_addItemBtn") || document.getElementById("addonGarment_submitBtn");
    if (btn) { btn.disabled = true; btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>Submitting...`; }
    
    fetch("/sales/prototype/" + saleId + "/addon/request", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]")?.content || "", "X-Requested-With": "XMLHttpRequest" },
        body: JSON.stringify(payload)
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            alert("Add-on request submitted! Awaiting approval.");
            var modal = bootstrap.Modal.getInstance(document.getElementById("addonProductModal"));
            if (modal) modal.hide();
            addonGarment_resetAddonForm();
        } else {
            alert("Error: " + (data.message || "Unknown error"));
        }
    }).catch(function(err) {
        alert("Request failed: " + err.message);
    }).finally(function() {
        if (btn) { btn.disabled = false; btn.innerHTML = `<i class="fas fa-cart-plus me-2"></i>Add to Add-on Request`; }
    });
};

window.addonGarment_resetAddonForm = function() {
    document.getElementById("addonGarment_productRowsContainer").innerHTML = "";
    document.getElementById("addonReason").value = "";
    document.getElementById("addonRequestedBy").value = "Agent";
    addonGarment_clearPrintSelection();
    addon_garment_uploadedReferenceImages = [];
    addonGarment_renderReferencePreviews();
    addon_garment_selectedPrintSizes = [];
    addonGarment_updatePrintSummary();
};

// Init on DOM ready
document.addEventListener("DOMContentLoaded", function() {
    var submitBtns = document.querySelectorAll("#addonGarment_submitBtn, #addonGarment_addItemBtn");
    submitBtns.forEach(function(btn) {
        btn.onclick = function(e) {
            e.preventDefault();
            addonGarment_submitRequest();
        };
    });
    var genericBtn = document.getElementById("addonGeneric_submitBtn");
    if (genericBtn) genericBtn.onclick = function() { addonGeneric_addItem(); };
});



    // === PENDING ADD-ONS: Open and show all pending ===
    window.openPendingAddons = function() {
        var modal = new bootstrap.Modal(document.getElementById('pendingAddonsModal'));
        modal.show();
        refreshPendingAddons();
    };
    
    window.refreshPendingAddons = function() {
        var body = document.getElementById('pendingAddonsBody');
        body.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>';
        
        fetch('/sales/prototype/addon/pending', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            refreshPendingAddonsCount();
            if (!data.length) {
                body.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-check-circle fa-3x mb-3" style="color:#28a745;"></i><p>No pending add-on requests.</p></div>';
                return;
            }
            var isManager = window.kanbanCanOverride === true;
            var html = '';
            data.forEach(function(req) {
                var items = req.items || [];
                var itemList = items.map(function(it) {
                    var sub = it.subItems && it.subItems[0];
                    return (sub ? sub.brand + ' ' + sub.size + ' ' + sub.color + ' ×' + it.totalQty : it.name + ' ×' + it.totalQty);
                }).join(', ');
                // Age indicator + stale highlight (>24h)
                var ageH = req.age_hours || 0;
                var ageLabel = ageH < 1 ? '< 1h ago' : (ageH < 24 ? Math.round(ageH) + 'h ago' : Math.round(ageH / 24) + 'd ago');
                var stale = ageH >= 24;
                var borderCls = stale ? 'border-danger' : 'border-warning';
                var ageBadge = stale
                    ? '<span class="badge bg-danger ms-1" title="Waiting ' + ageLabel + '">⏰ ' + ageLabel + '</span>'
                    : '<span class="badge bg-secondary ms-1" title="Waiting ' + ageLabel + '">' + ageLabel + '</span>';
                var actions = '';
                if (isManager) {
                    actions = '<div style="min-width:140px;text-align:right;">' +
                        '<button class="btn btn-success btn-sm me-1" onclick="event.stopPropagation();approveAddon(' + req.id + ',' + req.sale_id + ')">✅ Approve</button>' +
                        '<button class="btn btn-danger btn-sm" onclick="event.stopPropagation();rejectAddon(' + req.id + ',' + req.sale_id + ')">❌ Reject</button>' +
                        '</div>';
                }
                html += '<div class="card mb-3 ' + borderCls + ' pending-addon-card" data-sale-id="' + req.sale_id + '" onclick="openSaleFromAddon(' + req.sale_id + ')" style="cursor:pointer;" title="Click to view sale details">' +
                    '<div class="card-body">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<h6 class="mb-1">' + req.sales_number + ' — ' + (req.customer_name || 'Unknown') + ageBadge + '</h6>' +
                    '<small class="text-muted">' + itemList + '</small><br>' +
                    (req.reason ? '<small class="text-muted">Reason: ' + req.reason + '</small>' : '') +
                    '<br><small class="text-muted">By: ' + (req.requested_by || 'Agent') + ' • ' + new Date(req.created_at).toLocaleString() + '</small>' +
                    '</div>' +
                    actions +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });
            body.innerHTML = html;
        })
        .catch(function(err) {
            body.innerHTML = '<div class="alert alert-danger">Failed to load: ' + err.message + '</div>';
        });
    };
    
    // Open sale details modal from a pending add-on request card
    window.openSaleFromAddon = function(saleId) {
        var pendingModalEl = document.getElementById('pendingAddonsModal');
        var pm = bootstrap.Modal.getInstance(pendingModalEl);
        if (pm) pm.hide();
        setTimeout(function() {
            openSaleDetails(saleId);
        }, 250);
    };
    
    // Refresh the badge count on the Pending Add-ons button (auto-poll every 60s)
    window.refreshPendingAddonsCount = function() {
        fetch('/sales/prototype/addon/pending-count', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var el = document.getElementById('pendingAddonsCount');
            if (el) {
                el.textContent = data.count || 0;
                el.style.display = data.count > 0 ? '' : 'none';
            }
        })
        .catch(function() {});
    };
    setInterval(refreshPendingAddonsCount, 60000);
    
    window.approveAddon = function(requestId, saleId) {
        if (!confirm('Approve this add-on request? Pricing will be recalculated.')) return;
        fetch('/sales/prototype/addon/' + requestId + '/approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ approved_by: 'Manager' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                alert('✅ Approved!\nNew Subtotal: ₱' + data.new_subtotal.toFixed(2) + '\nAdjustment: ₱' + (data.adjustment > 0 ? '+' : '') + data.adjustment.toFixed(2));
                refreshPendingAddons();
                removePendingAddonBadge(saleId);
            } else {
                alert('Error: ' + (data.error || 'Unknown'));
            }
        })
        .catch(function(err) {
            alert('Failed: ' + err.message);
        });
    };
    
    // Remove the 🕐 Add-on badge from the kanban card once resolved
    window.removePendingAddonBadge = function(saleId) {
        document.querySelectorAll('.kanban-card[data-id="' + saleId + '"] .card-badge').forEach(function(b) {
            if (b.textContent.indexOf('🕐') !== -1) b.remove();
        });
    };
    
    window.rejectAddon = function(requestId, saleId) {
        if (!confirm('Reject this add-on request?')) return;
        fetch('/sales/prototype/addon/' + requestId + '/reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ approved_by: 'Manager' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                alert('❌ Request rejected.');
                refreshPendingAddons();
                removePendingAddonBadge(saleId);
            } else {
                alert('Error: ' + (data.error || 'Unknown'));
            }
        })
        .catch(function(err) {
            alert('Failed: ' + err.message);
        });
    };
    
    // Track sale ID in modal title
    document.addEventListener('hidden.bs.modal', function(e) {
        if (e.target.id === 'saleDetailsModal') {
            document.getElementById('addonOpenBtn').style.display = 'none';
        }
    });

    // Payment audit log viewer
    window.showAuditLogs = function(saleId) {
        fetch('/sales/audit-logs/' + saleId)
        .then(function(r) { return r.json(); })
        .then(function(logs) {
            if (!logs || logs.length === 0) {
                alert('No audit logs found for this sale.');
                return;
            }
            var html = '<div style="max-height:400px;overflow-y:auto;padding:10px;">';
            logs.forEach(function(log) {
                var actionBadge = '';
                if (log.action === 'verified') actionBadge = '<span class="badge bg-success">Verified</span>';
                else if (log.action === 'rejected') actionBadge = '<span class="badge bg-danger">Rejected</span>';
                else if (log.action === 're_tagged') actionBadge = '<span class="badge bg-warning text-dark">Re-tagged</span>';
                else if (log.action === 'edited_ref') actionBadge = '<span class="badge bg-info">Edited</span>';
                else if (log.action === 'requested_verify') actionBadge = '<span class="badge bg-primary">Requested</span>';
                else actionBadge = '<span class="badge bg-secondary">' + log.action + '</span>';

                html += '<div style="padding:6px 0;border-bottom:1px solid #eee;">';
                html += '<div style="display:flex;justify-content:space-between;">';
                html += '<div><strong>' + (log.user ? log.user.name : 'System') + '</strong> ' + actionBadge + '</div>';
                html += '<div style="font-size:0.75rem;color:#999;">' + new Date(log.created_at).toLocaleString() + '</div>';
                html += '</div>';
                if (log.payment_account) {
                    html += '<div style="font-size:0.8rem;color:#666;margin-top:2px;">Account: ' + log.payment_account.name + '</div>';
                }
                if (log.old_value && log.new_value) {
                    html += '<div style="font-size:0.8rem;color:#666;"><span style="text-decoration:line-through;">' + log.old_value + '</span> → <strong>' + log.new_value + '</strong></div>';
                }
                if (log.remarks) {
                    html += '<div style="font-size:0.8rem;color:#666;"><em>' + log.remarks + '</em></div>';
                }
                html += '</div>';
            });
            html += '</div>';

            // Show in a Bootstrap modal
            var modalEl = document.getElementById('saleDetailsModal');
            if (modalEl) {
                document.getElementById('saleDetailsModalLabel').innerHTML = '<i class="fas fa-history"></i> Audit Log';
                document.querySelector('#saleDetailsModal .modal-body').innerHTML = html;
            } else {
                // Fallback
                var w = window.open('', '_blank', 'width=600,height=500');
                w.document.write('<html><head><title>Audit Log</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head><body><div class="container mt-3">' + html + '</div></body></html>');
            }
        })
        .catch(function(e) {
            alert('Failed to load audit logs: ' + e);
        });
    };

// ============================================================
// PRODUCTION SLIP (Interactive Print Slip)
// ============================================================

// Tab switching
(function() {
    document.addEventListener('click', function(e) {
        var tab = e.target.closest('.prod-tab');
        if (!tab) return;
        var tabName = tab.dataset.tab;
        var tabs = tab.parentElement.querySelectorAll('.prod-tab');
        tabs.forEach(function(t) {
            t.style.color = '#666';
            t.style.borderBottomColor = 'transparent';
            t.style.fontWeight = '500';
        });
        tab.style.color = '#0d6efd';
        tab.style.borderBottomColor = '#0d6efd';
        tab.style.fontWeight = '600';

        var detailsBody = document.getElementById('modalSaleBody');
        var prodBody = document.getElementById('modalProdSlipBody');
        var addProdBody = document.getElementById('modalAddProdSlipBody');

        if (tabName === 'details') {
            detailsBody.style.display = '';
            prodBody.style.display = 'none';
            addProdBody.style.display = 'none';
        } else if (tabName === 'addProdSlip') {
            detailsBody.style.display = 'none';
            prodBody.style.display = 'none';
            addProdBody.style.display = '';
            // Reload if different sale or not loaded yet
            var needReload = !addProdBody.dataset.loaded || addProdBody.dataset.saleId !== detailsBody.dataset.saleId;
            if (needReload) {
                var saleId = detailsBody.dataset.saleId;
                if (saleId) {
                    addProdBody.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading additional production slip...</p></div>';
                    addProdBody.dataset.loaded = '';
                    loadAdditionalProductionSlip(saleId);
                }
            }
        } else {
            detailsBody.style.display = 'none';
            prodBody.style.display = '';
            addProdBody.style.display = 'none';
            // Reload if different sale or not loaded yet
            var needReload = !prodBody.dataset.loaded || prodBody.dataset.saleId !== detailsBody.dataset.saleId;
            if (needReload) {
                var saleId = detailsBody.dataset.saleId;
                if (saleId) {
                    prodBody.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading production slip...</p></div>';
                    prodBody.dataset.loaded = '';
                    loadProductionSlip(saleId);
                }
            }
        }
    });
})();

function loadProductionSlip(saleId) {
    var prodBody = document.getElementById('modalProdSlipBody');
    if (!saleId || !prodBody) return;

    fetch('/api/production/checklist/' + saleId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) {
                prodBody.innerHTML = '<div class="alert alert-danger">' + escHtml(data.error) + '</div>';
                return;
            }
            renderProductionSlip(data);
        })
        .catch(function(err) {
            prodBody.innerHTML = '<div class="alert alert-danger">Failed to load production slip</div>';
        });
}

function renderProductionSlip(data) {
    var chk = data.checklist || {};
    var slip = data.slip || {};
    var saleId = chk.sale_id || 0;
    var items = chk.items || [];
    var partRows = slip.partRows || [];
    var allRosters = slip.allRosters || [];
    var sizes = slip.sizes || [];
    var hasRoster = slip.hasRoster || false;
    var mockupImages = slip.mockupImages || [];
    var firstMockup = mockupImages.length > 0 ? mockupImages[0] : null;
    var firstMockupUrl = firstMockup ? (typeof firstMockup === 'string' ? firstMockup : (firstMockup.url || null)) : null;

    // Split parts into two columns
    var splitMid = Math.ceil(partRows.length / 2);
    var leftParts = partRows.slice(0, splitMid);
    var rightParts = partRows.slice(splitMid);

    // Helper: find item index by type and label match
    function findItemIdx(type, matchStr) {
        for (var i = 0; i < items.length; i++) {
            if (items[i].type === type && items[i].label.indexOf(matchStr) >= 0) {
                return i;
            }
        }
        return -1;
    }

    // Helper: find nth item of a type
    function findNthItemIdx(type, n) {
        var count = 0;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type === type) {
                if (count === n) return i;
                count++;
            }
        }
        return -1;
    }

    var html = '';
    html += '<div class="pslip" id="pslipContent">';

    // Title
    html += '<h1>CUSTOMER FORM SPECIFICATIONS</h1>';
    if (slip.salesNumber) {
        html += '<div style="text-align:center;font-size:9pt;margin-bottom:2px;">' + escHtml(slip.salesNumber) + '</div>';
    }
    html += '<div class="divider"></div>';

    // Top: 2 columns — Info (33%) | Parts (67%)
    html += '<table><tr>';
    html += '<td style="width:33%;vertical-align:top;" class="no-border">';
    var infoFields = [
        ['PROJECT:', slip.projectName],
        ['DESCRIPTION:', slip.description],
        ['FABRIC:', slip.fabric],
        ['DESIGNER:', slip.designer],
        ['QTY:', slip.totalQty + ' PCS'],
        ['DATE NEEDED:', slip.dateNeeded],
        ['AGENT:', slip.agent],
        ['CUSTOMER:', slip.customer],
    ];
    infoFields.forEach(function(f) {
        html += '<table style="width:100%;"><tr><td class="field-label" style="width:100px;">' + f[0] + '</td><td>' + escHtml(f[1] || '') + '</td></tr></table>';
    });
    html += '</td>';

    // Parts column (67%)
    html += '<td style="width:67%;vertical-align:top;">';
    html += '<div style="width:100%;">';
    // Left parts
    html += '<table style="width:49%;float:left;">';
    html += '<tr><th>Part</th><th>Color/Details</th></tr>';
    leftParts.forEach(function(row) {
        var itemIdx = findItemIdx('part', row.part);
        var done = itemIdx >= 0 && items[itemIdx].status === 'done';
        html += '<tr' + (done ? ' class="done"' : '') + '>';
        html += '<td><input type="checkbox" class="chk" ' + (done ? 'checked' : '') + ' onchange="toggleProdItem(' + saleId + ', ' + itemIdx + ', this.checked)"> ' + escHtml(row.part) + '</td>';
        html += '<td>' + escHtml(row.detail) + '</td></tr>';
    });
    html += '</table>';
    // Right parts
    html += '<table style="width:49%;float:right;">';
    html += '<tr><th>Part</th><th>Color/Details</th></tr>';
    rightParts.forEach(function(row) {
        var itemIdx = findItemIdx('part', row.part);
        var done = itemIdx >= 0 && items[itemIdx].status === 'done';
        html += '<tr' + (done ? ' class="done"' : '') + '>';
        html += '<td><input type="checkbox" class="chk" ' + (done ? 'checked' : '') + ' onchange="toggleProdItem(' + saleId + ', ' + itemIdx + ', this.checked)"> ' + escHtml(row.part) + '</td>';
        html += '<td>' + escHtml(row.detail) + '</td></tr>';
    });
    html += '</table>';
    html += '<div style="clear:both;"></div>';
    html += '</div></td>';
    html += '</tr></table>';

    html += '<div class="divider"></div>';

    // Bottom: Mock-up (30%) | Name List (70%)
    html += '<table><tr>';
    html += '<td style="width:30%;vertical-align:top" class="no-border">';
    html += '<div class="section-title">MOCK UP</div>';
    html += '<div class="mockup-box">';
    if (firstMockupUrl) {
        html += '<img src="' + escHtml(firstMockupUrl) + '" alt="mockup" style="cursor:pointer;" onclick="window.openLightbox(\'' + escHtml(firstMockupUrl) + '\')" onerror="this.style.display=\'none\';this.parentElement.innerHTML=\'<span>MOCK UP HERE</span>\'">';
    } else {
        html += '<span>MOCK UP HERE</span>';
    }
    html += '</div>';
    html += '</td>';
    html += '<td style="width:70%;vertical-align:top" class="no-border">';
    html += '<div class="section-title">NAME LIST</div>';
    // Check if ANY roster entry has Excel columns data
    var hasExcelCols = false;
    var allColHeaders = [];
    var isArrFormat = false;
    allRosters.forEach(function(r) {
        if (r.columns) {
            hasExcelCols = true;
            // Detect format: array of [header,value] pairs vs object
            if (!isArrFormat && Array.isArray(r.columns) && r.columns.length > 0 && Array.isArray(r.columns[0])) {
                isArrFormat = true;
            }
            if (isArrFormat) {
                // Array of pairs: [["BACK NAMES","dfsdf"], ["SIZE","XL"], ...]
                r.columns.forEach(function(pair) {
                    if (allColHeaders.indexOf(pair[0]) < 0) allColHeaders.push(pair[0]);
                });
            } else {
                // Object format (backward compat): {"BACK NAMES":"dfsdf", "SIZE":"XL", ...}
                Object.keys(r.columns).forEach(function(h) {
                    if (allColHeaders.indexOf(h) < 0) allColHeaders.push(h);
                });
            }
        }
    });
    // Helper: get column value from whichever format
    function getColVal(cols, hdr) {
        if (!cols) return '';
        if (isArrFormat && Array.isArray(cols)) {
            for (var ci = 0; ci < cols.length; ci++) {
                if (cols[ci][0] === hdr) return cols[ci][1];
            }
            return '';
        }
        return cols[hdr] || '';
    }
    if (hasRoster && allRosters.length > 0) {
        html += '<table class="roster-table">';
        html += '<thead><tr><th>#</th>';
        if (hasExcelCols) {
            // Excel-imported: use ALL original column headers
            allColHeaders.forEach(function(h) { html += '<th>' + escHtml(h) + '</th>'; });
        } else {
            // No Excel columns: use standard hardcoded headers
            html += '<th>NAME</th><th>SIZE</th><th>QTY</th>';
        }
        html += '<th>GA</th><th>QA1</th><th>QA2</th></tr></thead>';
        html += '<tbody>';
        allRosters.forEach(function(rosterItem, idx) {
            var itemIdx = findNthItemIdx('roster', idx);
            var done = itemIdx >= 0 && items[itemIdx].status === 'done';
            html += '<tr' + (done ? ' class="done"' : '') + '>';
            html += '<td>' + (idx + 1) + '</td>';
            if (hasExcelCols) {
                allColHeaders.forEach(function(h) {
                    html += '<td>' + escHtml(getColVal(rosterItem.columns, h)) + '</td>';
                });
            } else {
                html += '<td>' + escHtml(rosterItem.name || '') + (rosterItem.number ? ' - ' + rosterItem.number : '') + '</td>';
                html += '<td>' + escHtml(rosterItem.size || '') + '</td>';
                html += '<td>' + (rosterItem.qty || 1) + '</td>';
            }
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'ga_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].ga_done) ? 'checked' : '') + '></td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'qa1_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].qa1_done) ? 'checked' : '') + '></td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'qa2_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].qa2_done) ? 'checked' : '') + '></td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
    } else if (sizes.length > 0) {
        html += '<table class="roster-table">';
        html += '<thead><tr><th>SIZE</th><th>QUANTITY</th><th>GA</th><th>QA1</th><th>QA2</th></tr></thead>';
        html += '<tbody>';
        sizes.forEach(function(s, idx) {
            var itemIdx = findNthItemIdx('size', idx);
            var done = itemIdx >= 0 && items[itemIdx].status === 'done';
            html += '<tr' + (done ? ' class="done"' : '') + '>';
            html += '<td>' + escHtml(s.size || '') + '</td>';
            html += '<td>' + (s.quantity || 0) + '</td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'ga_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].ga_done) ? 'checked' : '') + '></td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'qa1_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].qa1_done) ? 'checked' : '') + '></td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'qa2_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].qa2_done) ? 'checked' : '') + '></td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
    } else {
        html += '<div style="text-align:center;padding:8px;font-size:9pt;color:#999;">No items</div>';
    }
    html += '</td>';
    html += '</tr></table>';

    // Notes from sale
    if (slip.notes) {
        html += '<div style="margin-top:6px;font-size:11pt;text-align:left;border-top:1px solid #000;padding:6px 8px;background:#fffbe6;border-left:3px solid #f0ad4e;line-height:1.5;">📝 ' + escHtml(slip.notes) + '</div>';
    }

    html += '<div class="divider"></div>';

    // === COMMENTS (append-only) ===
    html += '<div style="margin-top:10px;"><strong style="font-size:11pt;">Comments</strong></div>';
    html += '<div id="ps-comments-' + saleId + '" class="ps-comment-list">';
    var comments = (chk.ga_notes || '').trim();
    if (comments) {
        try { comments = JSON.parse(comments); } catch(e) { comments = []; }
        if (Array.isArray(comments)) {
            comments.forEach(function(c) {
                html += '<div class="ps-comment-entry">' + escHtml(c.text) + ' <span class="time">' + escHtml(c.at) + '</span></div>';
            });
        }
    }
    html += '</div>';
    html += '<div class="ps-comment-input">';
    html += '<input type="text" id="ps-comment-input-' + saleId + '" placeholder="Add a comment..." onkeydown="if(event.key===\'Enter\')addComment(' + saleId + ')">';
    html += '<button onclick="addComment(' + saleId + ')">Send</button></div>';

    html += '</div>'; // end .pslip

    var prodBody = document.getElementById('modalProdSlipBody');
    prodBody.innerHTML = html;
    prodBody.dataset.loaded = '1';
    prodBody.dataset.saleId = saleId;
}

function loadAdditionalProductionSlip(saleId) {
    var addProdBody = document.getElementById('modalAddProdSlipBody');
    if (!saleId || !addProdBody) return;

    fetch('/api/production/additional/' + saleId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) {
                addProdBody.innerHTML = '<div class="alert alert-danger">' + escHtml(data.error) + '</div>';
                return;
            }
            renderAdditionalProductionSlip(saleId, data);
        })
        .catch(function(err) {
            addProdBody.innerHTML = '<div class="alert alert-danger">Failed to load additional production slip</div>';
        });
}

function renderAdditionalProductionSlip(saleId, data) {
    var html = '';
    
    // Header banner
    html += '<div style="margin-bottom:16px;padding:12px;background:#fff3cd;border:1px solid #ffc107;border-radius:6px;">'
        + '<strong><i class="fas fa-plus-circle me-1"></i> Additional Production Slip</strong><br>'
        + '<span style="font-size:12px;color:#856404;">Products added after the original order via change requests.</span>'
        + '</div>';
    
    if (data.has_additional && data.products && data.products.length > 0) {
        data.products.forEach(function(prod, idx) {
            var partRows = prod.partRows || [];
            var roster = prod.roster || [];
            var sizes = prod.sizes || [];
            var nameList = roster.length > 0 ? roster : sizes;
            var hasRoster = roster.length > 0;
            
            // Card header
            html += '<div class="pslip" style="margin-bottom:16px;">';
            html += '<h1 style="font-size:16pt;">CUSTOMER FORM SPECIFICATIONS</h1>';
            if (data.sales_number) {
                html += '<div style="text-align:center;font-size:9pt;margin-bottom:2px;">' + escHtml(data.sales_number) + '</div>';
            }
            html += '<div class="divider"></div>';
            
            // Info + Parts table
            html += '<table><tr>';
            html += '<td style="width:33%;vertical-align:top;" class="no-border">';
            function fmtDate(d) {
                if (!d) return '';
                var parts = d.split('-');
                if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
                return d;
            }
            var infoFields = [
                ['PROJECT:', prod.name],
                ['DESCRIPTION:', prod.description],
                ['FABRIC:', prod.fabric],
                ['DESIGNER:', prod.designer],
                ['QTY:', (prod.quantity || 0) + ' PCS'],
                ['DATE NEEDED:', fmtDate(prod.dateNeeded)],
                ['AGENT:', data.agent || ''],
                ['CUSTOMER:', data.customer_name || '']
            ];
            infoFields.forEach(function(f) {
                if (f[1]) {
                    html += '<table style="width:100%;"><tr><td class="field-label" style="width:100px;">' + escHtml(f[0]) + '</td><td>' + escHtml(f[1]) + '</td></tr></table>';
                }
            });
            html += '</td>';
            html += '<td style="width:67%;vertical-align:top;">';
            html += '<div style="width:100%;">';
            // Parts table (dynamic from partRows)
            html += '<table style="width:100%;"><tr><th style="width:100px;">Part</th><th>Color/Details</th></tr>';
            partRows.forEach(function(row) {
                html += '<tr><td>' + escHtml(row.part || row[0]) + '</td><td>' + escHtml(row.detail || row[1]) + '</td></tr>';
            });
            html += '</table>';
            html += '<div style="clear:both;"></div></div></td></tr></table>';
            
            html += '<div class="divider"></div>';
            
            // Mockup + Name list
            html += '<table><tr>';
            html += '<td style="width:30%;vertical-align:top" class="no-border">';
            html += '<div class="section-title">MOCK UP</div>';
            html += '<div class="mockup-box">';
            if (prod.hasMockup && prod.mockupUrl) {
                html += '<img src="' + escHtml(prod.mockupUrl) + '" alt="mockup" style="cursor:pointer;max-width:100%;max-height:100%;object-fit:contain;" onclick="window.openLightbox(\'' + escHtml(prod.mockupUrl) + '\')">';
            } else {
                html += '<span style="color:#999;">No mockup</span>';
            }
            html += '</div></td>';
            
            // Name list
            html += '<td style="width:70%;vertical-align:top" class="no-border">';
            html += '<div class="section-title">NAME LIST</div>';
            
            if (hasRoster) {
                // Check for Excel columns in roster
                var hasExcelCols = false;
                var allColHeaders = [];
                var isArrFormat = false;
                var rosterData = roster;
                for (var ri = 0; ri < rosterData.length; ri++) {
                    if (rosterData[ri].columns) {
                        hasExcelCols = true;
                        if (!isArrFormat && Array.isArray(rosterData[ri].columns[0])) {
                            isArrFormat = true;
                        }
                        if (isArrFormat) {
                            for (var cj = 0; cj < rosterData[ri].columns.length; cj++) {
                                if (allColHeaders.indexOf(rosterData[ri].columns[cj][0]) === -1) allColHeaders.push(rosterData[ri].columns[cj][0]);
                            }
                        } else {
                            for (var key in rosterData[ri].columns) {
                                if (allColHeaders.indexOf(key) === -1) allColHeaders.push(key);
                            }
                        }
                    }
                }
                function getColValAddon(item, hdr) {
                    if (!item.columns) return '';
                    if (isArrFormat && Array.isArray(item.columns)) {
                        for (var ci = 0; ci < item.columns.length; ci++) {
                            if (item.columns[ci][0] === hdr) return item.columns[ci][1];
                        }
                        return '';
                    }
                    return item.columns[hdr] || '';
                }
                html += '<table style="width:100%;font-size:9pt;border-collapse:collapse;">';
                html += '<thead><tr><th>#</th>';
                if (hasExcelCols) {
                    allColHeaders.forEach(function(h) { html += '<th>' + escHtml(h) + '</th>'; });
                } else {
                    html += '<th>NAME</th><th>SIZE</th><th>QTY</th>';
                }
                html += '<th>GA</th><th>QA1</th><th>QA2</th></tr></thead>';
                html += '<tbody>';
                rosterData.forEach(function(r, ri) {
                    html += '<tr>';
                    html += '<td style="text-align:center;">' + (ri + 1) + '</td>';
                    if (hasExcelCols) {
                        allColHeaders.forEach(function(h) {
                            html += '<td>' + escHtml(getColValAddon(r, h)) + '</td>';
                        });
                    } else {
                        html += '<td>' + escHtml(r.name || '') + (r.number ? ' - ' + r.number : '') + '</td>';
                        html += '<td>' + escHtml(r.size || '') + '</td>';
                        html += '<td style="text-align:center;">' + (r.qty || 1) + '</td>';
                    }
                    html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + ri + ', \'ga_done\', this.checked)"></td>';
                    html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + ri + ', \'qa1_done\', this.checked)"></td>';
                    html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + ri + ', \'qa2_done\', this.checked)"></td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
            } else if (sizes.length > 0) {
                html += '<table style="width:100%;font-size:9pt;border-collapse:collapse;">';
                html += '<thead><tr><th>SIZE</th><th>QUANTITY</th><th>GA</th><th>QA1</th><th>QA2</th></tr></thead>';
                html += '<tbody>';
                sizes.forEach(function(s, si) {
                    html += '<tr>';
                    html += '<td>' + escHtml(s.size || '') + '</td>';
                    html += '<td style="text-align:center;">' + (s.qty || s.quantity || 0) + '</td>';
                    html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + si + ', \'ga_done\', this.checked)"></td>';
                    html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + si + ', \'qa1_done\', this.checked)"></td>';
                    html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + si + ', \'qa2_done\', this.checked)"></td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
            } else {
                html += '<div style="text-align:center;padding:8px;font-size:9pt;color:#999;">No items</div>';
            }
            html += '</td></tr></table>';
            html += '</div>'; // end .pslip
        });
    } else {
        html += '<div class="text-center text-muted py-5">';
        html += '<i class="fas fa-inbox fa-3x mb-3" style="display:block;color:#ccc;"></i>';
        html += '<p>No additional products found for this sale.</p>';
        html += '<p style="font-size:12px;">Additional products appear here after a change request with new items is approved.</p>';
        html += '</div>';
    }
    
    var addProdBody = document.getElementById('modalAddProdSlipBody');
    addProdBody.innerHTML = html;
    addProdBody.dataset.loaded = '1';
    addProdBody.dataset.saleId = saleId;
}

function renderProductionSlipHtml(data, showProductLabel) {
    // Reuse the same rendering as renderProductionSlip but return HTML
    var chk = data.checklist || {};
    var slip = data.slip || {};
    var saleId = chk.sale_id || 0;
    var items = chk.items || [];
    var partRows = slip.partRows || [];
    var allRosters = slip.allRosters || [];
    var sizes = slip.sizes || [];
    var hasRoster = slip.hasRoster || false;
    var mockupImages = slip.mockupImages || [];
    var firstMockup = mockupImages.length > 0 ? mockupImages[0] : null;
    var firstMockupUrl = firstMockup ? (typeof firstMockup === 'string' ? firstMockup : (firstMockup.url || null)) : null;

    var splitMid = Math.ceil(partRows.length / 2);
    var leftParts = partRows.slice(0, splitMid);
    var rightParts = partRows.slice(splitMid);

    function findNthItemIdx(type, n) {
        var count = 0;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type === type) {
                if (count === n) return i;
                count++;
            }
        }
        return -1;
    }

    function getColVal(item, hdr) {
        if (!item.columns) return '';
        if (Array.isArray(item.columns) && Array.isArray(item.columns[0])) {
            for (var j = 0; j < item.columns.length; j++) {
                if (item.columns[j][0] === hdr) return item.columns[j][1];
            }
            return '';
        }
        return item.columns[hdr] || '';
    }

    var html = '';
    html += '<div class="pslip" id="pslipContent">';

    html += '<h1>CUSTOMER FORM SPECIFICATIONS</h1>';
    if (slip.salesNumber) {
        html += '<div style="text-align:center;font-size:9pt;margin-bottom:2px;">' + escHtml(slip.salesNumber) + '</div>';
    }
    html += '<div class="divider"></div>';

    html += '<table><tr>';
    html += '<td style="width:33%;vertical-align:top;" class="no-border">';
    var infoFields = [
        ['PROJECT:', slip.projectName],
        ['DESCRIPTION:', slip.description],
        ['FABRIC:', slip.fabric],
        ['DESIGNER:', slip.designer],
        ['QTY:', slip.totalQty + ' PCS'],
        ['DATE NEEDED:', slip.dateNeeded],
        ['AGENT:', slip.agent],
        ['CUSTOMER:', slip.customer]
    ];
    infoFields.forEach(function(f) {
        if (f[1]) {
            html += '<table style="width:100%;"><tr><td class="field-label" style="width:100px;">' + escHtml(f[0]) + '</td><td>' + escHtml(f[1]) + '</td></tr></table>';
        }
    });
    html += '</td>';
    html += '<td style="width:67%;vertical-align:top;">';
    html += '<div style="width:100%;">';
    html += '<table style="width:49%;float:left;"><tr><th style="width:100px;">Part</th><th>Color/Details</th></tr>';
    leftParts.forEach(function(row) {
        html += '<tr><td>' + escHtml(row.part || row[0]) + '</td><td>' + escHtml(row.detail || row[1]) + '</td></tr>';
    });
    html += '</table>';
    html += '<table style="width:49%;float:right;"><tr><th style="width:100px;">Part</th><th>Color/Details</th></tr>';
    rightParts.forEach(function(row) {
        html += '<tr><td>' + escHtml(row.part || row[0]) + '</td><td>' + escHtml(row.detail || row[1]) + '</td></tr>';
    });
    html += '</table>';
    html += '<div style="clear:both;"></div></div></td></tr></table>';

    html += '<div class="divider"></div>';

    html += '<table><tr>';
    html += '<td style="width:30%;vertical-align:top" class="no-border">';
    html += '<div class="section-title">MOCK UP</div>';
    html += '<div class="mockup-box">';
    if (firstMockupUrl) {
        html += '<img src="' + escHtml(firstMockupUrl) + '" alt="mockup" style="cursor:pointer;max-width:100%;max-height:100%;object-fit:contain;" onclick="window.openLightbox(\'' + escHtml(firstMockupUrl) + '\')">';
    } else {
        html += '<span>MOCK UP HERE</span>';
    }
    html += '</div></td>';

    html += '<td style="width:70%;vertical-align:top" class="no-border">';
    html += '<div class="section-title">NAME LIST</div>';

    if (allRosters.length > 0) {
        var hasExcelCols = false;
        var allColHeaders = [];
        var isArrFormat = false;
        for (var ri = 0; ri < allRosters.length; ri++) {
            var r = allRosters[ri];
            if (r.columns) {
                hasExcelCols = true;
                if (!isArrFormat && Array.isArray(r.columns[0])) {
                    isArrFormat = true;
                }
                if (isArrFormat) {
                    for (var cj = 0; cj < r.columns.length; cj++) {
                        if (allColHeaders.indexOf(r.columns[cj][0]) === -1) allColHeaders.push(r.columns[cj][0]);
                    }
                } else {
                    for (var key in r.columns) {
                        if (allColHeaders.indexOf(key) === -1) allColHeaders.push(key);
                    }
                }
            }
        }
        html += '<table class="roster-table"><thead><tr><th>#</th>';
        if (hasExcelCols) {
            allColHeaders.forEach(function(h) { html += '<th>' + escHtml(h) + '</th>'; });
        } else {
            html += '<th>NAME</th><th>SIZE</th><th>QTY</th>';
        }
        html += '<th>GA</th><th>QA1</th><th>QA2</th></tr></thead><tbody>';
        allRosters.forEach(function(rosterItem, idx) {
            var itemIdx = findNthItemIdx('roster', idx);
            var done = itemIdx >= 0 && items[itemIdx] && items[itemIdx].status === 'done';
            html += '<tr' + (done ? ' class="done"' : '') + '>';
            html += '<td>' + (idx + 1) + '</td>';
            if (hasExcelCols) {
                allColHeaders.forEach(function(h) {
                    html += '<td>' + escHtml(getColVal(rosterItem, h)) + '</td>';
                });
            } else {
                html += '<td>' + escHtml(rosterItem.name || '') + (rosterItem.number ? ' - ' + rosterItem.number : '') + '</td>';
                html += '<td>' + escHtml(rosterItem.size || '') + '</td>';
                html += '<td>' + (rosterItem.qty || 1) + '</td>';
            }
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'ga_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].ga_done) ? 'checked' : '') + '></td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'qa1_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].qa1_done) ? 'checked' : '') + '></td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'qa2_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].qa2_done) ? 'checked' : '') + '></td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
    } else if (sizes.length > 0) {
        html += '<table class="roster-table"><thead><tr><th>SIZE</th><th>QUANTITY</th><th>GA</th><th>QA1</th><th>QA2</th></tr></thead><tbody>';
        sizes.forEach(function(s, idx) {
            var itemIdx = findNthItemIdx('size', idx);
            var done = itemIdx >= 0 && items[itemIdx] && items[itemIdx].status === 'done';
            html += '<tr' + (done ? ' class="done"' : '') + '>';
            html += '<td>' + escHtml(s.size || '') + '</td>';
            html += '<td>' + (s.quantity || 0) + '</td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'ga_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].ga_done) ? 'checked' : '') + '></td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'qa1_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].qa1_done) ? 'checked' : '') + '></td>';
            html += '<td style="text-align:center;"><input type="checkbox" onchange="toggleProdCheck(' + saleId + ', ' + itemIdx + ', \'qa2_done\', this.checked)" ' + ((items[itemIdx] && items[itemIdx].qa2_done) ? 'checked' : '') + '></td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
    } else {
        html += '<div style="text-align:center;padding:8px;font-size:9pt;color:#999;">No items</div>';
    }
    html += '</td></tr></table>';

    if (slip.notes) {
        html += '<div style="margin-top:6px;font-size:11pt;text-align:left;border-top:1px solid #000;padding:6px 8px;background:#fffbe6;border-left:3px solid #f0ad4e;line-height:1.5;">📝 ' + escHtml(slip.notes) + '</div>';
    }

    html += '<div class="divider"></div>';
    html += '<div style="margin-top:10px;"><strong style="font-size:11pt;">Comments</strong></div>';
    html += '<div id="ps-comments-' + saleId + '" class="ps-comment-list">';
    var comments = (chk.ga_notes || '').trim();
    if (comments) {
        try { comments = JSON.parse(comments); } catch(e) { comments = []; }
        if (Array.isArray(comments)) {
            comments.forEach(function(c) {
                html += '<div class="ps-comment-entry">' + escHtml(c.text) + ' <span class="time">' + escHtml(c.at) + '</span></div>';
            });
        }
    }
    html += '</div>';
    html += '<div class="ps-comment-input">';
    html += '<input type="text" id="ps-comment-input-' + saleId + '" placeholder="Add a comment..." onkeydown="if(event.key===\'Enter\')addComment(' + saleId + ')">';
    html += '<button onclick="addComment(' + saleId + ')">Send</button></div>';
    html += '</div>';

    return html;
}

})();

// Global helper functions (must be outside IIFE for inline event handlers)
function toggleProdItem(saleId, index, checked) {
    if (index < 0) return;
    fetch('/api/production/checklist/' + saleId + '/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
        },
        body: JSON.stringify({
            items: [{index: index, status: checked ? 'done' : 'pending'}]
        })
    }).then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var cb = event && event.target;
            if (cb) {
                var tr = cb.closest('tr');
                if (tr) tr.classList.toggle('done', checked);
            }
        }
    })
    .catch(function(err) {
        console.error('Failed to update item', err);
    });


}

function toggleProdCheck(saleId, index, field, checked) {
    if (index < 0) return;
    var itemUpdate = {index: index};
    itemUpdate[field] = checked;
    fetch('/api/production/checklist/' + saleId + '/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
        },
        body: JSON.stringify({
            items: [itemUpdate]
        })
    }).then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var cb = event && event.target;
            if (cb) {
                var tr = cb.closest('tr');
                if (tr) tr.style.backgroundColor = checked ? '#e8f5e9' : '';
            }
        }
    })
    .catch(function(err) {
        console.error('Failed to update check', err);
    });
}


function toggleQaCheck(saleId, type, checked) {
    var payload = {};
    payload[type + '_done'] = checked;

    fetch('/api/production/checklist/' + saleId + '/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
        },
        body: JSON.stringify(payload)
    }).then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var qaCls = checked ? 'checked' : '';
            var labels = document.querySelectorAll('.ps-check-item label');
            var idx = (type === 'ga') ? 0 : (type === 'qa1') ? 1 : 2;
            if (labels[idx]) labels[idx].className = qaCls;
        }
    }).catch(function(err) {
        console.error('Failed to update check', err);
    });
}

function addComment(saleId) {
    var input = document.getElementById('ps-comment-input-' + saleId);
    if (!input || !input.value.trim()) return;
    var text = input.value.trim();
    input.value = '';
    input.disabled = true;

    // Get current checklist to append to existing comments
    fetch('/api/production/checklist/' + saleId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var existing = [];
        try { existing = JSON.parse(data.checklist.ga_notes || '[]'); } catch(e) {}
        if (!Array.isArray(existing)) existing = [];

        var now = new Date();
        var pad = function(n) { return (n < 10 ? '0' : '') + n; };
        var ts = pad(now.getMonth()+1) + '/' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
        existing.push({text: text, at: ts});

        return fetch('/api/production/checklist/' + saleId + '/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
            },
            body: JSON.stringify({ga_notes: JSON.stringify(existing)})
        });
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            // Add comment to the list without reloading
            var list = document.getElementById('ps-comments-' + saleId);
            if (list) {
                var entry = document.createElement('div');
                entry.className = 'ps-comment-entry';
                var now = new Date();
                var pad = function(n) { return (n < 10 ? '0' : '') + n; };
                var ts = pad(now.getMonth()+1) + '/' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                entry.innerHTML = escHtml(text) + ' <span class="time">' + ts + '</span>';
                list.appendChild(entry);
                list.scrollTop = list.scrollHeight;
            }
        }
    })
    .catch(function(err) {
        console.error('Failed to add comment', err);
    })
    .finally(function() {
        if (input) input.disabled = false;
    });
}


function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
