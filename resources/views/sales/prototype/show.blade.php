@extends('layouts.app')

@section('title', 'Order #' . $sale->sales_number)

@push('styles')
<style>
    .detail-section {
        background: white;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .ref-image:hover, .payment-img:hover {
        opacity: 0.85;
    }
    .detail-title {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #667eea;
    }
    /* Fullsublimation modal styles in partial */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .info-label { color: #6c757d; font-size: 0.85rem; }
    .info-value { font-weight: 500; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Order #{{ $sale->sales_number }}</h1>
            <p class="text-muted mb-0">{{ $sale->customer_name }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($canEdit)
            <button type="button" class="btn btn-success" onclick="openSubAddProductModal()">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
            
            @php
                $canReprocess = !in_array($sale->kanban_status, ['delivered', 'completed', 'cancelled']);
            @endphp
            @if($canEdit && $canReprocess)
            <button type="button" class="btn btn-warning" onclick="openSubReprocessModal()">
                <i class="fas fa-sync-alt"></i> Reprocess Order
            </button>
            @endif
            @endif
            <a href="{{ route('sales.prototype.print-slip', $sale->id) }}" target="_blank" class="btn btn-success">
                <i class="fas fa-print"></i> Print Slip
            </a>
            <button type="button" class="btn btn-primary" onclick="openPdfSelector()">
                <i class="fas fa-file-pdf"></i> Print Order Slip (PDF)
            </button>
            <a href="{{ url('/sales/prototype/kanban/' . ($sale->department_code ?? '')) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

@if(isset($relatedSales) && $relatedSales->count() > 0)
    <div class="alert alert-info mb-3">
        <div class="d-flex align-items-start">
            <i class="fas fa-layer-group me-3 fa-lg mt-1"></i>
            <div class="flex-grow-1">
                <strong>Multi-Department Transaction</strong><br>
                <small>This sale is part of a group with {{ $relatedSales->count() }} other department sale(s):
                @foreach($relatedSales as $rs)
                    <a href="{{ route('sales.prototype.show', $rs->id) }}" class="alert-link me-2">
                        <span class="badge bg-{{ $rs->department_code ?? 'secondary' }}">{{ $rs->department_name }} ({{ $rs->sales_number }})</span>
                    </a>
                @endforeach
                </small>
                <div class="mt-2 p-2 bg-white bg-opacity-25 rounded small">
                    <strong>Overall Transaction Total:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupTotal, 2) }}</span>
                    <span class="mx-2 text-muted">|</span>
                    <strong>Deposit:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupDeposit, 2) }}</span>
                    <span class="mx-2 text-muted">|</span>
                    <strong>Balance Due:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupBalance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
@endif

    <div class="row g-3">
        <div class="col-lg-8">
            <!-- Customer Info -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-user me-2"></i>Customer Information</h5>
                <div class="info-grid">
                    <div>
                        <div class="info-label">Customer Name</div>
                        <div class="info-value">{{ $sale->customer_name }}</div>
                    </div>
                    <div>
                        <div class="info-label">Sales Number</div>
                        <div class="info-value">{{ $sale->sales_number }}</div>
                    </div>
                    @if($sale->customer_phone)
                    <div>
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $sale->customer_phone }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="info-label">Sales Agent</div>
                        <div class="info-value">{{ $sale->sales_agent_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Department</div>
                        <div class="info-value">{{ $sale->department_name ?? 'N/A' }}</div>
                    </div>
                    @if(($sale->marketplace ?? false))
                    <div>
                        <div class="info-label">Marketplace</div>
                        <div class="info-value">{{ $sale->marketplace }}</div>
                    </div>
                    @endif
                </div>
                @if($sale->customer_notes)
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="info-label">Customer Notes</div>
                        <p class="mb-0 mt-1">{{ $sale->customer_notes }}</p>
                    </div>
                @endif
                @if($sale->internal_notes)
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="info-label">Internal Notes</div>
                        <p class="mb-0 mt-1">{{ $sale->internal_notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Order Items -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-box me-2"></i>Order Items</h5>
                @if($services && count($services) > 0)
                    @foreach($services as $item)
                        @php
                            $itemTotal = $item['totalPrice'] ?? $item['total_price'] ?? $item['price'] ?? 0;
                            $itemName = $item['name'] ?? $item['product_name'] ?? 'Item #' . ($loop->index + 1);
                            $itemSpec = \App\Models\PrototypeSale::itemSpecSummary($item);
                            $itemNotes = $item['notes'] ?? '';
                            $subItems = $item['subItems'] ?? [];
                            $printing = $item['printing'] ?? null;
                            $refImages = $item['referenceImages'] ?? [];
                        @endphp
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $itemSpec }}</strong>
                                    @if(isset($item['department']))
                                        <span class="badge bg-secondary">{{ $item['department'] }}</span>
                                    @endif
                                    @if($itemSpec !== $itemName && $itemName)
                                        <div class="small text-muted">{{ $itemName }}</div>
                                    @endif
                                </div>
                                <div class="fw-bold text-nowrap">₱{{ number_format($itemTotal, 2) }}</div>
                            </div>

                            @if(!empty($subItems))
                                <div class="mb-2">
                                    @foreach($subItems as $si)
                                        @php
                                            $brand = $si['brand'] ?? $si['product_brand'] ?? '';
                                            $size = $si['size'] ?? $si['type'] ?? $si['product_size'] ?? '';
                                            $color = $si['color'] ?? $si['product_color'] ?? '';
                                            $qty = $si['qty'] ?? $si['quantity'] ?? 1;
                                            $unitPrice = $si['price'] ?? $si['unit_price'] ?? 0;
                                            $parts = [];
                                            if ($brand) $parts[] = $brand;
                                            if ($size) $parts[] = $size;
                                            if ($color) $parts[] = $color;
                                            $parts[] = '×' . $qty;
                                            if ($unitPrice > 0) $parts[] = '₱' . number_format($unitPrice, 2);
                                        @endphp
                                        <div class="small text-muted">{{ implode(' • ', $parts) }}</div>
                                    @endforeach
                                </div>
                            @endif

                            @if($printing)
                                <div class="small mb-2 p-2 bg-white rounded">
                                    <div class="fw-semibold mb-1">🖨️ Print Details</div>
                                    @if(isset($printing['printType']))
                                        <div><span class="text-muted">Type:</span> {{ $printing['printType'] }}</div>
                                    @endif
                                    @if(!empty($printing['printSizes'] ?? []))
                                        <div><span class="text-muted">Sizes:</span> {{ is_array($printing['printSizes']) ? implode(', ', $printing['printSizes']) : $printing['printSizes'] }}</div>
                                    @endif
                                    <div><span class="text-muted">Qty:</span> {{ $printing['printQty'] ?? 'N/A' }}</div>
                                    @if(($printing['printSubtotal'] ?? 0) > 0)
                                        <div><span class="text-muted">Print Subtotal:</span> ₱{{ number_format($printing['printSubtotal'], 2) }}</div>
                                    @endif
                                    @if($printing['isSpecialPrice'] ?? false)
                                        <div class="text-warning">⭐ Special Price: {{ $printing['specialReason'] ?? '' }}</div>
                                    @endif
                                </div>
                            @endif

                            @if($itemNotes)
                                <div class="mt-2 small"><span class="text-muted">📝 Notes:</span> {{ $itemNotes }}</div>
                            @endif

                            @if(!empty($refImages))
                                <div class="mt-2">
                                    <div class="small text-muted mb-1">🖼️ Reference Images ({{ count($refImages) }})</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($refImages as $rimg)
                                            @php $src = $rimg['dataUrl'] ?? $rimg['url'] ?? $rimg['src'] ?? ''; @endphp
                                            @if($src)
                                                <img src="{{ $src }}" alt="{{ $rimg['name'] ?? 'Image' }}" style="max-width:100px;max-height:80px;border-radius:4px;cursor:pointer;" class="border ref-image" onclick="openLightbox(this.src)">
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No items found.</p>
                @endif
            </div>

            <!-- Mockups / Main Cover -->
            <div class="detail-section mt-3">
                <h5 class="detail-title"><i class="fas fa-palette me-2"></i>Mockups <span class="text-muted small fw-normal">(pinili ang isa bilang main cover)</span></h5>

                @php
                    $allMockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                    $mainMockup = null;
                    foreach ($allMockups as $m) {
                        if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
                    }
                    if (!$mainMockup && !empty($allMockups)) $mainMockup = $allMockups[0];
                @endphp

                <div class="d-flex flex-wrap gap-2 mb-2" id="mockupsGallery">
                    @forelse($allMockups as $m)
                        @php
                            $mUrl = is_string($m) ? $m : ($m['url'] ?? '');
                            $mName = is_array($m) ? ($m['name'] ?? 'Mockup') : 'Mockup';
                            $isMain = is_array($m) && !empty($m['is_main']);
                        @endphp
                        @if($mUrl)
                            <div style="position:relative;display:inline-block;" class="mockup-item">
                                <img src="{{ $mUrl }}" alt="{{ $mName }}" class="mockup-thumb" style="width:110px;height:90px;object-fit:cover;border-radius:6px;cursor:pointer;border:{{ $isMain ? '3px solid #10b981' : '1px solid #ddd' }};" onclick="openLightbox('{{ $mUrl }}')">
                                @if($isMain)
                                    <span style="position:absolute;top:-8px;left:-8px;background:#10b981;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.25);">⭐ MAIN</span>
                                @endif
                                <div class="d-flex gap-1 mt-1">
                                    @if(!$isMain)
                                        <button type="button" class="btn btn-sm btn-outline-success" style="padding:1px 8px;font-size:11px;" onclick="setMainMockup('{{ $mUrl }}', this)" title="Gamitin bilang main cover sa kanban, manager list, at calendar">Set as Main</button>
                                    @else
                                        <span class="text-success small fw-semibold" style="font-size:11px;"><i class="fas fa-check-circle"></i> Main cover</span>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="padding:1px 8px;font-size:11px;margin-left:auto;" onclick="confirmDeleteMockup('{{ $mUrl }}','{{ $mName }}')" title="Delete mockup">✕</button>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-muted small">Wala pang mockup. Mag-upload para makapili ng main cover.</div>
                    @endforelse
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('mockupUploadInput').click()">
                    <i class="fas fa-upload me-1"></i>Add Mockup
                </button>
                <input type="file" id="mockupUploadInput" accept="image/*" class="d-none" onchange="uploadMockup(this)">
                <div id="mockupUploadMsg" class="small mt-2"></div>
            </div>

            <!-- Design Files & Sample -->
            <div class="detail-section mt-3">
                <h5 class="detail-title"><i class="fas fa-images me-2"></i>Design Files & Sample</h5>

                @php
                    $designImages = $sale->design_images ?? [];
                    $fileShots = collect($designImages)->where('type', 'file_screenshot')->values();
                    $colorShots = collect($designImages)->where('type', 'sample_color')->values();
                @endphp

                <div class="row g-3">
                    <!-- File Screenshot -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-2">📄 File Screenshot</div>
                            <div class="small text-muted mb-2">Screenshot ng artwork / print file.</div>
                            <div class="d-flex flex-wrap gap-2 mb-2" id="fileShotsGallery">
                                @forelse($fileShots as $img)
                                    <div style="position:relative;display:inline-block;">
                                        <img src="{{ $img['url'] }}" alt="{{ $img['name'] ?? 'File screenshot' }}" class="design-thumb" style="width:90px;height:70px;object-fit:cover;border-radius:4px;cursor:pointer;border:1px solid #ddd;" onclick="openLightbox('{{ $img['url'] }}')">
                                        <button type="button" class="btn btn-sm btn-danger design-del-btn" style="position:absolute;top:-8px;right:-8px;padding:0 6px;font-size:12px;border-radius:50%;z-index:5;" title="Delete this upload" onclick="event.stopPropagation();confirmDeleteDesignImage('{{ $img['url'] }}','file_screenshot','{{ $img['name'] ?? 'image' }}')">✕</button>
                                    </div>
                                @empty
                                    <div class="text-muted small">Wala pang upload.</div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('fileShotInput').click()">
                                <i class="fas fa-upload me-1"></i>Upload File Screenshot
                            </button>
                            <input type="file" id="fileShotInput" accept="image/*" class="d-none" onchange="uploadDesignImage(this, 'file_screenshot')">
                        </div>
                    </div>

                    <!-- Approved Sample Color -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-2">🎨 Approved Sample Color</div>
                            <div class="small text-muted mb-2">Screenshot ng na-approve na sample color.</div>
                            <div class="d-flex flex-wrap gap-2 mb-2" id="colorShotsGallery">
                                @forelse($colorShots as $img)
                                    <div style="position:relative;display:inline-block;">
                                        <img src="{{ $img['url'] }}" alt="{{ $img['name'] ?? 'Sample color' }}" class="design-thumb" style="width:90px;height:70px;object-fit:cover;border-radius:4px;cursor:pointer;border:1px solid #ddd;" onclick="openLightbox('{{ $img['url'] }}')">
                                        <button type="button" class="btn btn-sm btn-danger design-del-btn" style="position:absolute;top:-8px;right:-8px;padding:0 6px;font-size:12px;border-radius:50%;z-index:5;" title="Delete this upload" onclick="event.stopPropagation();confirmDeleteDesignImage('{{ $img['url'] }}','sample_color','{{ $img['name'] ?? 'image' }}')">✕</button>
                                    </div>
                                @empty
                                    <div class="text-muted small">Wala pang upload.</div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="document.getElementById('colorShotInput').click()">
                                <i class="fas fa-upload me-1"></i>Upload Sample Color
                            </button>
                            <input type="file" id="colorShotInput" accept="image/*" class="d-none" onchange="uploadDesignImage(this, 'sample_color')">
                        </div>
                    </div>
                </div>

                <div id="designUploadMsg" class="small mt-2"></div>
            </div>

            <!-- Notes -->
            @if(($sale->reason ?? false))
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-sticky-note me-2"></i>Reason</h5>
                <p>{{ $sale->reason }}</p>
            </div>
            @endif

            <!-- Comment Section -->
            <div class="detail-section mt-3">
                <h5 class="detail-title"><i class="fas fa-comments me-2"></i>Comments</h5>
                
                <div id="commentsContainer">
                    <div class="text-center text-muted py-3" id="commentsLoading">
                        <i class="fas fa-spinner fa-spin"></i> Loading comments...
                    </div>
                </div>
                
                @if($isManager)
                <div class="mt-3">
                    <form id="commentForm" method="POST" action="{{ route('sales.prototype.add-comment', $sale->id) }}">
                        @csrf
                        <div class="mb-2">
                            <textarea name="comment" class="form-control" rows="2" placeholder="Add a comment... (visible to everyone)" required maxlength="1000"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane"></i> Post Comment
                        </button>
                    </form>
                </div>
                @else
                <div class="alert alert-info mt-3 mb-0 py-2">
                    <small><i class="fas fa-info-circle"></i> Only managers can add comments here.</small>
                </div>
                @endif
            </div>

            <!-- Audit History -->
            <div class="detail-section mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="detail-title mb-0" style="border-bottom: none; padding-bottom: 0;">
                        <i class="fas fa-history me-2"></i>Audit History
                    </h5>
                    <button class="btn btn-outline-info btn-sm" onclick="toggleAuditLog()">
                        <i class="fas fa-chevron-down"></i> View History
                    </button>
                </div>
                <div id="auditLogContainer" style="display: none;">
                    <p class="text-muted text-center py-3">Loading...</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status & Progress -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-tasks me-2"></i>Status</h5>
                <div class="mb-3">
                    <div class="info-label">Kanban Status</div>
                    <div class="mt-1">
                        @php $statusLabel = [
                            'new' => 'New',
                            'sample_approval' => 'Sample/Approval',
                            'design' => 'Design',
                            'production' => 'Production',
                            'quality_check' => 'Quality Check',
                            'ready_for_delivery' => 'Ready for Delivery',
                            'delivered' => 'Delivered',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ][$sale->kanban_status ?? 'new'] ?? ucfirst($sale->kanban_status ?? 'New'); @endphp
                        <span class="badge bg-{{ $sale->kanban_status === 'delivered' || $sale->kanban_status === 'completed' ? 'success' : ($sale->kanban_status === 'cancelled' ? 'danger' : 'primary') }} fs-6">
                            {{ $statusLabel }}
                        </span>
                        @if($sale->production_stage)
                            <span class="badge bg-secondary fs-6">{{ $sale->production_stage }}</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progressPercent }}%;"></div>
                    </div>
                    <small class="text-muted">{{ $progressPercent }}% complete</small>
                </div>
                <div>
                    <div class="info-label">Created</div>
                    <div>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y g:i A') }}</div>
                </div>
                @if(($sale->date_needed ?? false))
                <div class="mt-2">
                    <div class="info-label">Date Needed</div>
                    <div>{{ \Carbon\Carbon::parse($sale->date_needed)->format('M d, Y') }}</div>
                </div>
                @endif
                
            </div>

            <!-- Pending Changes -->
            @if(isset($pendingChanges) && $pendingChanges->count() > 0)
            <div class="detail-section border-start border-4 border-warning">
                <h5 class="detail-title">
                    <i class="fas fa-clock text-warning me-2"></i>Pending Changes
                </h5>
                @foreach($pendingChanges as $change)
                <div class="mb-3 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-warning text-dark">Awaiting Approval</span>
                            <small class="text-muted ms-2">{{ \Carbon\Carbon::parse($change->created_at)->diffForHumans() }}</small>
                        </div>
                        <small class="text-muted">by {{ \App\Models\User::find($change->submitted_by)?->name ?? 'Unknown' }}</small>
                    </div>
                    
                    <p class="mb-2">{{ $change->change_summary }}</p>
                    
                    <div class="row text-center g-2 mb-2">
                        <div class="col-4">
                            <small class="text-muted d-block">Current Total</small>
                            <strong>₱{{ number_format($change->total_before, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">New Total</small>
                            <strong>₱{{ number_format($change->total_after, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Difference</small>
                            <strong class="{{ $change->total_after >= $change->total_before ? 'text-success' : 'text-danger' }}">
                                {{ $change->total_after >= $change->total_before ? '+' : '-' }}₱{{ number_format(abs($change->total_after - $change->total_before), 2) }}
                            </strong>
                        </div>
                    </div>
                    
                    @if($isManager)
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" onclick="approveChange({{ $change->id }})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="showRejectModal({{ $change->id }})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                    @endif

                    @php
                        $servicesAfter = is_string($change->services_after) ? json_decode($change->services_after, true) : $change->services_after;
                        $servicesBefore = is_string($change->services_before) ? json_decode($change->services_before, true) : $change->services_before;
                        $servicesAfter = is_array($servicesAfter) ? $servicesAfter : [];
                        $servicesBefore = is_array($servicesBefore) ? $servicesBefore : [];
                    @endphp
                    @if(count($servicesAfter) > 0)
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-info" type="button" onclick="togglePendingDetails({{ $change->id }})">
                            <i class="fas fa-eye"></i> <span id="pendingDetailsLabel-{{ $change->id }}">View Details</span>
                        </button>
                    </div>
                    <div id="pendingDetails-{{ $change->id }}" style="display:none;" class="mt-2 p-2 border rounded bg-white small">
                        @php
                            // Find added items (in services_after but not in services_before by ID)
                            $beforeIds = [];
                            foreach ($servicesBefore as $sb) { $beforeIds[] = $sb['id'] ?? $sb['itemId'] ?? null; }
                            $afterItems = [];
                            foreach ($servicesAfter as $sa) {
                                $saId = $sa['id'] ?? $sa['itemId'] ?? null;
                                if ($saId && in_array($saId, $beforeIds)) continue;
                                $afterItems[] = $sa;
                            }
                            // If no clear diff, show all
                            if (count($afterItems) === 0) $afterItems = $servicesAfter;
                        @endphp
                        @foreach($afterItems as $item)
                            @php $sf = $item['sublimationForm'] ?? $item; @endphp
                            <div class="mb-2 p-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="fw-bold">{{ $item['name'] ?? $sf['projectName'] ?? 'Product' }}</div>
                                <div class="row g-1 mt-1">
                                    <div class="col-6"><span class="text-muted">Fabric:</span> {{ $sf['fabric']['name'] ?? (is_string($sf['fabric'] ?? null) ? $sf['fabric'] : 'N/A') }}</div>
                                    <div class="col-6"><span class="text-muted">Garment:</span> {{ $sf['garment']['name'] ?? $sf['garmentType'] ?? 'N/A' }}</div>
                                    <div class="col-6"><span class="text-muted">Designer:</span> {{ $sf['designer'] ?? 'N/A' }}</div>
                                    <div class="col-6"><span class="text-muted">Qty:</span> {{ $item['quantity'] ?? $sf['totalQty'] ?? '0' }}</div>
                                    @if(!empty($sf['sizes']) && count($sf['sizes']) > 0)
                                    <div class="col-12">
                                        <span class="text-muted">Sizes:</span>
                                        @foreach($sf['sizes'] as $sz)
                                            {{ $sz['size'] ?? $sz['name'] ?? '?' }}{{ isset($sz['qty']) ? ' x'.$sz['qty'] : '' }}{{ !$loop->last ? ',' : '' }}
                                        @endforeach
                                    </div>
                                    @endif
                                    @if(!empty($sf['roster']))
                                    <div class="col-12">
                                        <span class="text-muted">Roster:</span>
                                        @foreach($sf['roster'] as $ro)
                                            {{ $ro['name'] ?? '' }}{{ !$loop->last ? ',' : '' }}
                                        @endforeach
                                    </div>
                                    @endif
                                    @if(!empty($sf['parts']) && count($sf['parts']) > 0)
                                    <div class="col-12">
                                        <span class="text-muted">Parts:</span>
                                        @foreach($sf['parts'] as $pt)
                                            {{ is_array($pt) ? ($pt['name'] ?? $pt['label'] ?? $pt['value']) : $pt }}{{ !$loop->last ? ',' : '' }}
                                        @endforeach
                                    </div>
                                    @endif
                                    @if(!empty($sf['mockupUrl']) || !empty($sf['mockup']))
                                    <div class="col-12 mt-1">
                                        <img src="{{ $sf['mockupUrl'] ?? $sf['mockup'] }}" style="max-height:120px;max-width:100%;object-fit:contain;border:1px solid #ddd;border-radius:4px;" alt="Mockup">
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-1 text-end fw-bold">₱{{ number_format($item['totalPrice'] ?? $item['price'] ?? 0, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            <!-- Payment Info -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-credit-card me-2"></i>Payment</h5>
                <div class="mb-2">
                    <div class="info-label">Total Amount</div>
                    <div class="info-value fw-bold fs-5">₱{{ number_format($sale->total_amount ?? 0, 2) }}</div>
                </div>
                @if(isset($pendingChanges) && $pendingChanges->count() > 0)
                    @php
                        $projectedTotal = $sale->total_amount;
                        foreach ($pendingChanges as $change) {
                            $diff = $change->total_after - $change->total_before;
                            $projectedTotal += $diff;
                        }
                    @endphp
                    @if($projectedTotal > $sale->total_amount)
                    <div class="mb-2 p-2 bg-warning bg-opacity-10 rounded border border-warning">
                        <div class="info-label small text-warning"><i class="fas fa-clock me-1"></i>Projected Total (after pending)</div>
                        <div class="fw-bold" style="color:#856404;">₱{{ number_format($projectedTotal, 2) }}</div>
                    </div>
                    @endif
                @endif
                @if($totalPaid > 0)
                <div class="mb-2">
                    <div class="info-label">Total Paid</div>
                    <div class="info-value text-success">₱{{ number_format($totalPaid, 2) }}</div>
                </div>
                @endif
                @if(($totalRefunded ?? 0) > 0)
                <div class="mb-2">
                    <div class="info-label">Refunded</div>
                    <div class="info-value text-danger">− ₱{{ number_format($totalRefunded, 2) }}</div>
                </div>
                <div class="mb-2">
                    <div class="info-label">Net Paid</div>
                    <div class="info-value text-success">₱{{ number_format($netPaid, 2) }}</div>
                </div>
                @endif
                @php $bal = $balanceDue; @endphp
                @if($bal > 0)
                <div class="mb-2">
                    <div class="info-label">Balance Due</div>
                    <div class="info-value text-danger fw-bold">₱{{ number_format($bal, 2) }}</div>
                </div>
                @endif

                @if($bal > 0)
                <div class="mt-3 mb-3">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#payBalanceModal">
                        <i class="fas fa-credit-card me-2"></i>Pay Balance
                    </button>
                </div>
                @endif

                <hr>
                @if(($sale->overpayment ?? 0) > 0)
                <div class="mb-2 p-2 bg-info bg-opacity-10 rounded border border-info">
                    <div class="info-label small text-info"><i class="fas fa-exclamation-triangle me-1"></i>Overpayment</div>
                    <div class="fw-bold" style="color:#0c5460;">₱{{ number_format($sale->overpayment, 2) }}</div>
                    @if($isManager)
                        <button type="button" class="btn btn-sm btn-outline-info mt-1" onclick="openRefundModal({{ $sale->id }}, {{ $sale->overpayment }})">
                            <i class="fas fa-undo-alt me-1"></i>Request Refund
                        </button>
                    @endif
                </div>
                @endif

                @if(isset($activeRefund))
                <div class="mb-2 p-2 {{ $activeRefund->refund_status === 'approved' ? 'bg-success bg-opacity-10 border-success' : ($activeRefund->refund_status === 'completed' ? 'bg-primary bg-opacity-10 border-primary' : 'bg-warning bg-opacity-10 border-warning') }} rounded border">
                    <div class="info-label small"><i class="fas fa-undo-alt me-1"></i>Refund Status</div>
                    <div>
                        @if($activeRefund->refund_status === 'pending')
                            <span class="badge bg-warning text-dark fs-6">⏳ Pending</span>
                        @elseif($activeRefund->refund_status === 'approved')
                            <span class="badge bg-success fs-6">✅ Approved</span>
                        @elseif($activeRefund->refund_status === 'completed')
                            <span class="badge bg-primary fs-6">✅ Completed</span>
                        @endif
                        <div class="mt-1">
                            <span class="fw-bold">₱{{ number_format($activeRefund->refund_amount, 2) }}</span>
                            <small class="text-muted">via {{ ucfirst($activeRefund->refund_method ?? 'N/A') }}</small>
                        </div>
                        @if($activeRefund->refund_reference)
                            <div class="mt-1 small text-muted">
                                <i class="fas fa-hashtag me-1"></i>Ref. #: {{ $activeRefund->refund_reference }}
                            </div>
                        @endif
                        @if($activeRefund->refund_proof_path)
                            <div class="mt-1">
                                <a href="{{ asset('storage/' . $activeRefund->refund_proof_path) }}" target="_blank" class="small">
                                    <i class="fas fa-image me-1"></i>View Proof Screenshot
                                </a>
                            </div>
                        @endif
                        @if($isManager && $activeRefund->refund_status === 'approved')
                            <button type="button" class="btn btn-sm btn-success mt-1" onclick="openCompleteRefundShow({{ $activeRefund->id }}, {{ $activeRefund->refund_amount }})">
                                <i class="fas fa-check-circle me-1"></i>Mark Completed
                            </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Payment History (individual payments) -->
            @if(isset($payments) && $payments->count() > 0)
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-receipt me-2"></i>Payment History ({{ $payments->count() }})</h5>
                @foreach($payments as $pay)
                    <div class="p-2 mb-2 border rounded {{ $pay->payment_status === 'pending' ? 'border-warning' : 'border-secondary' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                @php
                                    $payLabel = match($pay->payment_type) {
                                        'down_payment' => 'Down Payment',
                                        'additional' => 'Additional Payment',
                                        'fullpayment', 'full_payment' => 'Full Payment',
                                        default => ucwords(str_replace('_', ' ', $pay->payment_type ?? 'Payment'))
                                    };
                                    $payBadge = match($pay->payment_status) {
                                        'verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified' => ['bg-success', '✓ Verified'],
                                        'rejected', 'reject_pending' => ['bg-danger', '✗ Rejected'],
                                        'edit_pending' => ['bg-info', 'Edit Pending'],
                                        default => ['bg-warning text-dark', '⏳ Pending Verification']
                                    };
                                @endphp
                                <span class="badge bg-secondary">{{ $payLabel }}</span>
                                <span class="badge {{ $payBadge[0] }}">{{ $payBadge[1] }}</span>
                                <div class="fw-bold mt-1">₱{{ number_format($pay->amount ?? 0, 2) }}</div>
                                <div class="small text-muted">
                                    {{ ucfirst($pay->payment_method ?? 'N/A') }}
                                    @if($pay->payment_account_id)
                                        @php $payAcct = \App\Models\PaymentAccount::find($pay->payment_account_id); @endphp
                                        @if($payAcct) · {{ $payAcct->name }} @endif
                                    @endif
                                </div>
                                @if($pay->reference_number)
                                    <div class="small text-muted"><i class="fas fa-hashtag me-1"></i>{{ $pay->reference_number }}</div>
                                @endif
                                @if($pay->payment_date)
                                    <div class="small text-muted"><i class="far fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($pay->payment_date)->format('M d, Y') }}</div>
                                @endif
                                @if($pay->verified_by)
                                    <div class="small text-success mt-1">
                                        <i class="fas fa-user-check me-1"></i>{{ \App\Models\User::find($pay->verified_by)?->name ?? 'Unknown' }}
                                        @if($pay->verified_at) · {{ \Carbon\Carbon::parse($pay->verified_at)->format('M d, g:i A') }} @endif
                                    </div>
                                @endif
                            </div>
                            @if($pay->screenshot_path)
                                <div class="ms-2">
                                    <img src="{{ $pay->screenshot_path }}" alt="Payment screenshot" class="rounded" style="width:70px;height:70px;object-fit:cover;cursor:pointer;" onclick="openLightbox('{{ $pay->screenshot_path }}')">
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Refund History (all refunds incl. completed) -->
            @if(isset($refunds) && $refunds->count() > 0)
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-undo-alt me-2"></i>Refund History ({{ $refunds->count() }})</h5>
                @foreach($refunds as $refund)
                    @if($activeRefund && $refund->id === $activeRefund->id) @continue @endif
                    @php
                        $refundStatusBadge = match($refund->refund_status) {
                            'pending' => ['bg-warning text-dark', '⏳ Pending'],
                            'accepted' => ['bg-info text-dark', '✅ Accepted'],
                            'approved' => ['bg-success', '✅ Approved'],
                            'completed' => ['bg-primary', '✅ Completed'],
                            'rejected' => ['bg-danger', '❌ Rejected'],
                            default => ['bg-secondary', ucfirst($refund->refund_status)],
                        };
                        $refundReasonLabel = match($refund->refund_reason) {
                            'reprocess_overpayment' => 'Overpayment Refund',
                            'reprocess' => 'Reprocess',
                            'cancellation' => 'Cancellation',
                            default => ucwords(str_replace('_', ' ', $refund->refund_reason ?? 'Refund')),
                        };
                    @endphp
                    <div class="p-2 mb-2 border rounded {{ in_array($refund->refund_status, ['pending', 'accepted']) ? 'border-warning' : 'border-secondary' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge {{ $refundStatusBadge[0] }}">{{ $refundStatusBadge[1] }}</span>
                                    <span class="badge bg-secondary">{{ $refundReasonLabel }}</span>
                                </div>
                                <div class="fw-bold">₱{{ number_format($refund->refund_amount ?? 0, 2) }}</div>
                                @if($refund->refund_method)
                                    <div class="small text-muted">via {{ ucfirst($refund->refund_method) }}@if($refund->refund_account_name) · {{ $refund->refund_account_name }}@endif</div>
                                @endif
                                @if($refund->refund_reference)
                                    <div class="small text-muted"><i class="fas fa-hashtag me-1"></i>{{ $refund->refund_reference }}</div>
                                @endif
                                @if($refund->reason_details)
                                    <div class="small text-muted"><i class="fas fa-comment me-1"></i>{{ $refund->reason_details }}</div>
                                @endif
                                @if($refund->refund_proof_path)
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <img src="{{ asset('storage/' . $refund->refund_proof_path) }}" alt="Refund proof" class="rounded" style="width:70px;height:70px;object-fit:cover;cursor:pointer;" onclick="openLightbox('{{ asset('storage/' . $refund->refund_proof_path) }}')">
                                        <a href="{{ asset('storage/' . $refund->refund_proof_path) }}" target="_blank" class="small">
                                            <i class="fas fa-external-link-alt me-1"></i>View Full
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="small text-muted text-end">
                                @if($refund->completed_at)
                                    <div><i class="fas fa-check-circle me-1"></i>{{ \Carbon\Carbon::parse($refund->completed_at)->format('M d, g:i A') }}</div>
                                @elseif($refund->approved_at)
                                    <div><i class="fas fa-check me-1"></i>{{ \Carbon\Carbon::parse($refund->approved_at)->format('M d, g:i A') }}</div>
                                @else
                                    <div>{{ \Carbon\Carbon::parse($refund->created_at)->format('M d, g:i A') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Verified By -->
            @if($sale->verified_by)
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-user-check me-2"></i>Verified By</h5>
                <div class="info-value">{{ \App\Models\User::find($sale->verified_by)?->name ?? 'Unknown' }}</div>
                @if($sale->verified_at)
                    <div class="text-muted small">{{ \Carbon\Carbon::parse($sale->verified_at)->format('M d, Y g:i A') }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
<!-- Pay Balance Modal -->
<div class="modal fade" id="payBalanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('sales.prototype.agent.payment.store', $sale->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Pay Balance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php $remaining = $balanceDue; @endphp
                @if(isset($relatedSales) && $relatedSales->count() > 0)
    <div class="alert alert-info mb-3">
        <div class="d-flex align-items-start">
            <i class="fas fa-layer-group me-3 fa-lg mt-1"></i>
            <div class="flex-grow-1">
                <strong>Multi-Department Transaction</strong><br>
                <small>This sale is part of a group with {{ $relatedSales->count() }} other department sale(s):
                @foreach($relatedSales as $rs)
                    <a href="{{ route('sales.prototype.show', $rs->id) }}" class="alert-link me-2">
                        <span class="badge bg-{{ $rs->department_code ?? 'secondary' }}">{{ $rs->department_name }} ({{ $rs->sales_number }})</span>
                    </a>
                @endforeach
                </small>
                <div class="mt-2 p-2 bg-white bg-opacity-25 rounded small">
                    <strong>Overall Transaction Total:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupTotal, 2) }}</span>
                    <span class="mx-2 text-muted">|</span>
                    <strong>Deposit:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupDeposit, 2) }}</span>
                    <span class="mx-2 text-muted">|</span>
                    <strong>Balance Due:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupBalance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
@endif

    <div class="row g-3">
                        <!-- Summary -->
                        <div class="col-12">
                            <div class="bg-light p-3 rounded d-flex justify-content-around text-center">
                                <div>
                                    <small class="text-muted d-block">Total</small>
                                    <strong>₱{{ number_format($sale->total_amount ?? 0, 2) }}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Paid</small>
                                    <strong class="text-success">₱{{ number_format($netPaid, 2) }}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Balance</small>
                                    <strong class="text-danger">₱{{ number_format($remaining, 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <input type="number" name="payment_amount" id="payBalanceAmount" class="form-control" step="0.01" min="0.01" max="{{ $remaining }}" value="{{ $remaining }}" required>
                            <input type="hidden" name="payment_type" id="payBalanceType" value="fullpayment">
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Payment Method — removed; Payment Account already indicates the type -->

                        <!-- Account -->
                        <div class="col-md-6">
                            <label class="form-label">Payment Account <span class="text-danger">*</span></label>
                            <select name="payment_account_id" class="form-select" required>
                                <option value="">Select account...</option>
                                @foreach(\App\Models\PaymentAccount::where('is_active', true)->get() as $acct)
                                    <option value="{{ $acct->id }}">{{ $acct->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Reference -->
                        <div class="col-md-6">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Transaction ref number">
                        </div>

                        <!-- Screenshot -->
                        <div class="col-12">
                            <label class="form-label">Payment Screenshot / Proof</label>
                            <input type="file" name="payment_screenshot" class="form-control" accept="image/*">
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>Submit Payment
                    </button>
                </div>
                @if($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="refundForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-undo-alt me-2"></i>Request Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="refund_amount" id="refundAmount" value="">
                    <input type="hidden" name="refund_reason" value="reprocess_overpayment">
                    
                    <div class="mb-3">
                        <label class="form-label">Refund Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="text" class="form-control" id="refundAmountDisplay" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-muted small">(Overpayment from reprocess)</span></label>
                        <textarea name="reason_details" class="form-control" rows="2" placeholder="Optional details..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Refund Method *</label>
                        <select name="refund_method" class="form-select" required>
                            <option value="gcash">GCash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="paymaya">PayMaya</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name (refund destination)</label>
                        <input type="text" name="refund_account_name" class="form-control" placeholder="e.g. Juan Dela Cruz">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number / Reference</label>
                        <input type="text" name="refund_account_number" class="form-control" placeholder="e.g. GCash #09171234567">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-1"></i>Submit Refund Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.sublimation-show-modal')

<!-- Complete Refund Modal (with proof) -->
<div class="modal fade" id="completeRefundShowModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="completeRefundShowForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="action" value="complete">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-check-circle me-1 text-success"></i>Complete Refund</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Enter the refund details and upload proof of disbursement to confirm the refund.</p>
                    <div class="mb-3">
                        <label class="form-label">Refund Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" name="refund_amount" class="form-control" id="completeShowRefundAmount" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference Number <span class="text-danger">*</span></label>
                        <input type="text" name="refund_reference" class="form-control" required placeholder="e.g. GCash Ref #, Bank Transaction ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proof Screenshot</label>
                        <input type="file" name="refund_proof" class="form-control" accept="image/*">
                        <div class="form-text">Upload screenshot of GCash transfer, bank transaction, etc. (max 5MB)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle me-1"></i>Confirm Refund Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-set payment_type: fullpayment when amount >= remaining, else additional
    (function() {
        var remaining = {{ $remaining }};
        var amountInput = document.getElementById('payBalanceAmount');
        var typeInput = document.getElementById('payBalanceType');
        function updateType() {
            if (!amountInput || !typeInput) return;
            var val = parseFloat(amountInput.value) || 0;
            typeInput.value = (val >= remaining - 0.001) ? 'fullpayment' : 'additional';
        }
        if (amountInput) {
            amountInput.addEventListener('input', updateType);
            updateType();
        }
    })();

    function toggleAuditLog() {
        var container = document.getElementById('auditLogContainer');
        if (container.style.display !== 'none') {
            container.style.display = 'none';
            return;
        }
        container.style.display = 'block';
        
        fetch('{{ route("sales.prototype.audit-history", $sale->id) }}')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var html = '';
                if (data.logs && data.logs.length > 0) {
                    data.logs.forEach(function(log) {
                        var date = new Date(log.created_at);
                        html += '<div class="d-flex gap-3 mb-3 pb-2 border-bottom">';
                        html += '<div class="text-center" style="min-width: 60px;">';
                        html += '<div class="small fw-bold">' + ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth()+1)).slice(-2) + '</div>';
                        html += '<div class="small text-muted">' + ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + '</div>';
                        html += '</div>';
                        html += '<div class="flex-grow-1">';
                        html += '<div><strong>' + (log.user_name || 'System') + '</strong> <span class="badge bg-secondary text-uppercase" style="font-size: 0.65rem;">' + log.action.replace(/_/g, ' ') + '</span></div>';
                        html += '<div class="text-muted small">' + log.description + '</div>';
                        html += '</div></div>';
                    });
                } else {
                    html = '<p class="text-muted text-center py-3">No audit history yet.</p>';
                }
                container.innerHTML = html;
            })
            .catch(function() {
                container.innerHTML = '<p class="text-danger text-center py-3">Failed to load history.</p>';
            });
    }
    
    window.toggleBalanceRefFields = function() {
        var method = document.querySelector('[name="payment_method"]').value;
        var show = method !== '' && method !== 'cash';
        document.getElementById('balanceRefGroup').style.display = show ? 'block' : 'none';
        document.getElementById('balanceScreenshotGroup').style.display = show ? 'block' : 'none';
    };

    function showToast(msg, type) {
        type = type || 'info';
        var existing = document.querySelector('.toast-notification-' + type);
        if (existing) existing.remove();
        
        var toast = document.createElement('div');
        toast.className = 'toast-notification-' + type;
        toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;padding:12px 20px;border-radius:8px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-width:400px;';
        
        var colors = { success: '#d4edda,#155724', danger: '#f8d7da,#721c24', warning: '#fff3cd,#856404', info: '#d1ecf1,#0c5460' };
        var c = colors[type] || colors.info;
        toast.style.background = c.split(',')[0];
        toast.style.color = c.split(',')[1];
        toast.innerHTML = msg;
        
        document.body.appendChild(toast);
        setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(function() { toast.remove(); }, 300); }, 4000);
    }
    
    // ---------- Comment Form Handler ----------
    document.addEventListener('DOMContentLoaded', function() {
        var commentForm = document.getElementById('commentForm');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = this.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        loadComments();
                        commentForm.querySelector('textarea').value = '';
                        showToast('Comment posted.', 'success');
                    } else {
                        showToast(data.message || 'Failed to post.', 'danger');
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Comment';
                })
                .catch(function() {
                    showToast('Error posting comment.', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Comment';
                });
            });
        }
        
        loadComments();
        
        // Check for change_submitted success
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('change_submitted') === '1') {
            showToast('Change request submitted for manager approval.', 'success');
        }
    });
    
    // ---------- Load Comments ----------
    function loadComments() {
        fetch('{{ route("sales.prototype.audit-history", $sale->id) }}')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var html = '';
                if (data.logs && data.logs.length > 0) {
                    var commentLogs = data.logs.filter(function(l) { return l.action === 'comment_added'; });
                    if (commentLogs.length > 0) {
                        commentLogs.forEach(function(log) {
                            var date = new Date(log.created_at);
                            var dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
                            html += '<div class="d-flex gap-3 mb-3 pb-2 border-start border-primary ps-3">';
                            html += '<div class="flex-grow-1">';
                            html += '<div class="d-flex justify-content-between"><strong>' + (log.user_name || 'Manager') + '</strong> <small class="text-muted">' + dateStr + '</small></div>';
                            html += '<div class="mt-1">' + log.description.replace('Manager added a comment: ', '') + '</div>';
                            html += '</div></div>';
                        });
                    } else {
                        html = '<p class="text-muted text-center py-2">No comments yet.</p>';
                    }
                } else {
                    html = '<p class="text-muted text-center py-2">No comments yet.</p>';
                }
                document.getElementById('commentsContainer').innerHTML = html;
            })
            .catch(function() {
                document.getElementById('commentsContainer').innerHTML = '<p class="text-muted text-center py-2">Could not load comments.</p>';
            });
    }
    
    // ---------- Approve / Reject Change ----------
    /* ── Professional Approve Modal ── */
    function togglePendingDetails(changeId) {
        var details = document.getElementById('pendingDetails-' + changeId);
        var label = document.getElementById('pendingDetailsLabel-' + changeId);
        if (details.style.display === 'none') {
            details.style.display = 'block';
            label.textContent = 'Hide Details';
        } else {
            details.style.display = 'none';
            label.textContent = 'View Details';
        }
    }

    function approveChange(changeId) {
        var modal = document.getElementById('approveModal');
        modal.querySelector('.cm-confirm-btn').dataset.changeId = changeId;
        modal.style.display = 'flex';
    }
    function closeApproveModal() {
        document.getElementById('approveModal').style.display = 'none';
    }
    function doApprove(btn) {
        var changeId = btn.dataset.changeId;
        btn.disabled = true;
        btn.innerHTML = '<span class="cm-spinner"></span> Approving...';
        
        fetch('/sales/prototype/change/' + changeId + '/approve', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Change approved! Reloading...', 'success');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                showToast(data.message || 'Failed to approve.', 'danger');
                btn.disabled = false;
                btn.innerHTML = 'Yes, Approve';
            }
        })
        .catch(function() {
            showToast('Error approving change.', 'danger');
            btn.disabled = false;
            btn.innerHTML = 'Yes, Approve';
        });
    }
    
    /* ── Professional Reject Modal ── */
    function showRejectModal(changeId) {
        var modal = document.getElementById('rejectModal');
        modal.querySelector('.cm-confirm-btn').dataset.changeId = changeId;
        modal.querySelector('.cm-reason-input').value = '';
        modal.querySelector('.cm-char-count').textContent = '0';
        modal.style.display = 'flex';
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }
    function updateRejectCount(el) {
        el.closest('.cm-modal-body').querySelector('.cm-char-count').textContent = el.value.length;
    }
    function doReject(btn) {
        var changeId = btn.dataset.changeId;
        var reason = document.getElementById('rejectReasonInput').value.trim();
        if (reason.length < 5) {
            document.getElementById('rejectReasonInput').focus();
            document.getElementById('rejectReasonInput').style.borderColor = '#dc3545';
            return;
        }
        document.getElementById('rejectReasonInput').style.borderColor = '';
        btn.disabled = true;
        btn.innerHTML = '<span class="cm-spinner"></span> Rejecting...';
        
        fetch('/sales/prototype/change/' + changeId + '/reject', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Change rejected. Reloading...', 'warning');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                showToast(data.message || 'Failed to reject.', 'danger');
                btn.disabled = false;
                btn.innerHTML = 'Reject';
            }
        })
        .catch(function() {
            showToast('Error rejecting change.', 'danger');
            btn.disabled = false;
            btn.innerHTML = 'Reject';
        });
    }
    
    window.openLightbox = function(src) {
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
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeLightbox();
            }
        });
        
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
        img.src = src;
    };
    
    /* ── Close modals on Escape key ── */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeApproveModal();
            closeRejectModal();
        }
    });
    
    window.closeLightbox = function() {
        var overlay = document.getElementById('imageLightbox');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    };
</script>

<!-- Approve Confirmation Modal -->
<div id="approveModal" class="cm-overlay" onclick="if(event.target===this)closeApproveModal()">
    <div class="cm-modal">
        <div class="cm-header">
            <h4><i class="fas fa-check-circle text-success me-2"></i>Approve Change Request</h4>
            <button onclick="closeApproveModal()" class="cm-close">&times;</button>
        </div>
        <div class="cm-body">
            <p class="cm-body-text">Are you sure you want to approve this change request?</p>
            <div class="cm-info-box">
                <i class="fas fa-info-circle text-primary me-2"></i>
                The approved changes including additional products and price adjustments will be applied to this order immediately.
            </div>
        </div>
        <div class="cm-footer">
            <button onclick="closeApproveModal()" class="cm-cancel-btn">Cancel</button>
            <button onclick="doApprove(this)" class="cm-confirm-btn cm-approve">Yes, Approve</button>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div id="rejectModal" class="cm-overlay" onclick="if(event.target===this)closeRejectModal()">
    <div class="cm-modal">
        <div class="cm-header">
            <h4><i class="fas fa-times-circle text-danger me-2"></i>Reject Change Request</h4>
            <button onclick="closeRejectModal()" class="cm-close">&times;</button>
        </div>
        <div class="cm-body">
            <p class="cm-body-text">Please provide a reason for rejection:</p>
            <div class="cm-reject-input-group">
                <textarea id="rejectReasonInput" class="cm-reason-input" rows="3" placeholder="Enter reason for rejection..." oninput="updateRejectCount(this)"></textarea>
                <div class="cm-char-hint"><span class="cm-char-count">0</span> / 500 characters (min 5)</div>
            </div>
        </div>
        <div class="cm-footer">
            <button onclick="closeRejectModal()" class="cm-cancel-btn">Cancel</button>
            <button onclick="doReject(this)" class="cm-confirm-btn cm-reject">Reject</button>
        </div>
    </div>
</div>

<!-- Delete Design Image Modal -->
<div id="deleteDesignModal" class="cm-overlay" onclick="if(event.target===this)closeDeleteDesignModal()">
    <div class="cm-modal">
        <div class="cm-header">
            <h4><i class="fas fa-trash-alt text-danger me-2"></i>Delete Image</h4>
            <button onclick="closeDeleteDesignModal()" class="cm-close">&times;</button>
        </div>
        <div class="cm-body">
            <p class="cm-body-text">Are you sure you want to delete this <strong id="delImgType">image</strong>?</p>
            <div class="cm-info-box">
                <i class="fas fa-info-circle text-warning me-2"></i>
                Wrong upload? You can delete it — this action is logged in the Audit History with your name and reason.
            </div>
            <div class="cm-reject-input-group" style="margin-top:12px;">
                <textarea id="deleteDesignReason" class="cm-reason-input" rows="2" placeholder="Reason (optional) — hal. wrong upload, duplicate..." maxlength="500"></textarea>
            </div>
        </div>
        <div class="cm-footer">
            <button onclick="closeDeleteDesignModal()" class="cm-cancel-btn">Cancel</button>
            <button onclick="doDeleteDesignImage(this)" class="cm-confirm-btn cm-reject"><i class="fas fa-trash-alt me-1"></i>Delete</button>
        </div>
    </div>
</div>

<!-- PDF Item Selector Modal -->
<div id="pdfSelectorModal" class="cm-overlay" onclick="if(event.target===this)closePdfSelector()">
    <div class="cm-modal" style="max-width:500px;">
        <div class="cm-header">
            <h4><i class="fas fa-file-pdf text-primary me-2"></i>Select Print Slip Items</h4>
            <button onclick="closePdfSelector()" class="cm-close">&times;</button>
        </div>
        <div class="cm-body">
            <p class="cm-body-text">Pumili kung aling item ang isasama sa PDF:</p>
            <div id="pdfItemList" style="margin:12px 0;">
            </div>
        </div>
        <div class="cm-footer">
            <button onclick="closePdfSelector()" class="cm-cancel-btn">Cancel</button>
            <button onclick="doDownloadPdf()" class="cm-confirm-btn cm-approve">
                <i class="fas fa-download"></i> Download Selected
            </button>
        </div>
    </div>
</div>

<script>
function openPdfSelector() {
    var items = [];
    @php
        $pdfItems = [];
        if (!empty($services)) {
            foreach ($services as $si => $svc) {
                $sf = $svc['sublimationForm'] ?? [];
                if (!empty($sf)) {
                    $g = $sf['garment']['name'] ?? $sf['garmentType'] ?? '';
                    $f = $sf['fabric']['name'] ?? (is_string($sf['fabric'] ?? null) ? $sf['fabric'] : '');
                    $n = $svc['name'] ?? '';
                    $r = count($sf['roster'] ?? []);
                    $pdfItems[] = ['idx' => $si, 'name' => $n, 'garment' => $g, 'fabric' => $f, 'rosterCount' => $r];
                }
            }
        }
    @endphp
    items = @json($pdfItems);
    
    var container = document.getElementById('pdfItemList');
    container.innerHTML = '';
    
    if (items.length === 0) {
        container.innerHTML = '<p class="text-muted">No sublimation items found.</p>';
        document.querySelector('#pdfSelectorModal .cm-confirm-btn').style.display = 'none';
    } else {
        document.querySelector('#pdfSelectorModal .cm-confirm-btn').style.display = '';
        items.forEach(function(item, i) {
            var label = item.garment ? (item.garment + (item.fabric ? ' — ' + item.fabric : '')) : (item.name || 'Item #' + (item.idx + 1));
            var rosterInfo = item.rosterCount > 0 ? ' (' + item.rosterCount + ' entries)' : '';
            var div = document.createElement('div');
            div.style.cssText = 'padding:8px 12px;margin:4px 0;border:1px solid #ddd;border-radius:8px;display:flex;align-items:center;';
            div.innerHTML = '<input type="checkbox" class="pdf-item-cb" data-idx="' + item.idx + '" checked style="width:18px;height:18px;margin-right:10px;">' +
                '<span>' + label + rosterInfo + '</span>';
            container.appendChild(div);
        });
    }
    
    document.getElementById('pdfSelectorModal').style.display = 'flex';
}

function closePdfSelector() {
    document.getElementById('pdfSelectorModal').style.display = 'none';
}

function doDownloadPdf() {
    var checkboxes = document.querySelectorAll('.pdf-item-cb:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one item.');
        return;
    }
    var indices = [];
    checkboxes.forEach(function(cb) {
        indices.push(cb.dataset.idx);
    });
    closePdfSelector();
    var url = "{{ route('sales.prototype.print-slip.pdf', $sale->id) }}?items=" + indices.join(',');
    window.open(url, '_blank');
}

/* Refund Modal */
function openRefundModal(saleId, amount) {
    document.getElementById('refundAmount').value = amount;
    document.getElementById('refundAmountDisplay').value = Number(amount).toFixed(2);
    var form = document.getElementById('refundForm');
    form.action = '{{ url("sales/prototype/refund") }}/' + saleId;
    var modal = new bootstrap.Modal(document.getElementById('refundModal'));
    modal.show();
}

/* Refund form submission via AJAX */
document.addEventListener('DOMContentLoaded', function() {
    var refundForm = document.getElementById('refundForm');
    if (refundForm) {
        refundForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error submitting refund request.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Refund Request';
                }
            })
            .catch(function() {
                alert('Network error. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Refund Request';
            });
        });
    }

    /* Complete Refund Modal */
    var completeShowForm = document.getElementById('completeRefundShowForm');
    if (completeShowForm) {
        completeShowForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var modalEl = document.getElementById('completeRefundShowModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    location.reload();
                } else {
                    alert(data.message || 'Error completing refund.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Confirm Refund Completed';
                }
            })
            .catch(function() {
                alert('Network error.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Confirm Refund Completed';
            });
        });
    }
});

function openCompleteRefundShow(refundId, amount) {
    var form = document.getElementById('completeRefundShowForm');
    form.action = '{{ url("sales/prototype/refund") }}/' + refundId + '/process';
    document.getElementById('completeShowRefundAmount').value = amount;
    var modal = new bootstrap.Modal(document.getElementById('completeRefundShowModal'));
    modal.show();
}

// Design Files & Sample upload
function uploadDesignImage(input, type) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    var msg = document.getElementById('designUploadMsg');
    var galleryId = type === 'sample_color' ? 'colorShotsGallery' : 'fileShotsGallery';
    var gallery = document.getElementById(galleryId);
    msg.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Uploading...</span>';

    var fd = new FormData();
    fd.append('design_image', file);
    fd.append('type', type);

    fetch('{{ route("sales.prototype.upload-design-image", $sale->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            if (gallery.querySelector('.text-muted.small')) {
                gallery.innerHTML = '';
            }
            var wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;display:inline-block;';
            var img = document.createElement('img');
            img.src = data.image.url;
            img.alt = data.image.name || 'Upload';
            img.style.cssText = 'width:90px;height:70px;object-fit:cover;border-radius:4px;cursor:pointer;border:1px solid #ddd;';
            img.onclick = function() { openLightbox(data.image.url); };
            var delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'btn btn-sm btn-danger design-del-btn';
            delBtn.style.cssText = 'position:absolute;top:-8px;right:-8px;padding:0 6px;font-size:12px;border-radius:50%;z-index:5;';
            delBtn.title = 'Delete this upload';
            delBtn.innerHTML = '✕';
            delBtn.onclick = function(e) {
                e.stopPropagation();
                confirmDeleteDesignImage(data.image.url, type, data.image.name || 'image');
            };
            wrap.appendChild(img);
            wrap.appendChild(delBtn);
            gallery.appendChild(wrap);
            msg.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + data.message + '</span>';
        } else {
            msg.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>' + (data.message || 'Upload failed.') + '</span>';
        }
    })
    .catch(function() {
        msg.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Network error. Try again.</span>';
    })
    .finally(function() {
        input.value = '';
    });
}

// --- Delete design image (file screenshot / sample color) ---
var pendingDeleteImage = null;
function confirmDeleteDesignImage(url, type, name) {
    pendingDeleteImage = { url: url, type: type, name: name };
    document.getElementById('delImgType').textContent = type === 'sample_color' ? 'Approved Sample Color' : 'File Screenshot';
    document.getElementById('deleteDesignReason').value = '';
    document.getElementById('deleteDesignModal').style.display = 'flex';
}
function closeDeleteDesignModal() {
    document.getElementById('deleteDesignModal').style.display = 'none';
    pendingDeleteImage = null;
}
function doDeleteDesignImage(btn) {
    if (!pendingDeleteImage) return;
    var delType = pendingDeleteImage.type;
    var delUrl = pendingDeleteImage.url;
    var reason = document.getElementById('deleteDesignReason').value.trim();
    var fd = new FormData();
    fd.append('type', delType);
    fd.append('url', delUrl);
    fd.append('reason', reason);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

    fetch('{{ route("sales.prototype.delete-design-image", $sale->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Delete';
        closeDeleteDesignModal();
        if (data.success) {
            var galleryId = delType === 'sample_color' ? 'colorShotsGallery' : 'fileShotsGallery';
            var gallery = document.getElementById(galleryId);
            if (gallery) {
                var wraps = gallery.querySelectorAll('div[style*="position:relative"]');
                wraps.forEach(function(w) {
                    var img = w.querySelector('img');
                    if (img && img.src === delUrl) w.remove();
                });
                var thumbs = gallery.querySelectorAll('img.design-thumb');
                thumbs.forEach(function(im) {
                    if (im.src === delUrl) im.parentElement.remove();
                });
                if (!gallery.querySelector('img')) {
                    gallery.innerHTML = '<div class="text-muted small">Wala pang upload.</div>';
                }
            }
            var msg = document.getElementById('designUploadMsg');
            if (msg) msg.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + data.message + '</span>';
        } else {
            alert(data.message || 'Delete failed.');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Delete';
        alert('Network error. Try again.');
    });
}

// === MOCKUP MANAGEMENT (multiple mockups + main cover) ===
function uploadMockup(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    var msg = document.getElementById('mockupUploadMsg');
    var gallery = document.getElementById('mockupsGallery');
    msg.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Uploading...</span>';

    var fd = new FormData();
    fd.append('mockup_image', file);

    fetch('{{ route("sales.prototype.upload-mockup", $sale->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            if (gallery.querySelector('.text-muted.small')) gallery.innerHTML = '';
            var wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;display:inline-block;';
            wrap.className = 'mockup-item';
            var img = document.createElement('img');
            img.src = data.image.url;
            img.alt = data.image.name || 'Mockup';
            img.className = 'mockup-thumb';
            img.style.cssText = 'width:110px;height:90px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid #ddd;';
            img.onclick = function() { openLightbox(data.image.url); };
            var actions = document.createElement('div');
            actions.className = 'd-flex gap-1 mt-1';
            if (data.image.is_main) {
                actions.innerHTML = '<span class="text-success small fw-semibold" style="font-size:11px;"><i class="fas fa-check-circle"></i> Main cover</span>';
            } else {
                var btnMain = document.createElement('button');
                btnMain.type = 'button';
                btnMain.className = 'btn btn-sm btn-outline-success';
                btnMain.style.cssText = 'padding:1px 8px;font-size:11px;';
                btnMain.textContent = 'Set as Main';
                btnMain.onclick = function() { setMainMockup(data.image.url, btnMain); };
                actions.appendChild(btnMain);
            }
            var delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'btn btn-sm btn-outline-danger';
            delBtn.style.cssText = 'padding:1px 8px;font-size:11px;margin-left:auto;';
            delBtn.textContent = '✕';
            delBtn.title = 'Delete mockup';
            delBtn.onclick = function(e) {
                e.stopPropagation();
                confirmDeleteMockup(data.image.url, data.image.name || 'Mockup');
            };
            actions.appendChild(delBtn);
            wrap.appendChild(img);
            wrap.appendChild(actions);
            gallery.appendChild(wrap);
            msg.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + data.message + '</span>';
        } else {
            msg.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>' + (data.message || 'Upload failed.') + '</span>';
        }
    })
    .catch(function() {
        msg.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Network error. Try again.</span>';
    })
    .finally(function() {
        input.value = '';
    });
}

function setMainMockup(url, btn) {
    var fd = new FormData();
    fd.append('url', url);

    fetch('{{ route("sales.prototype.set-main-mockup", $sale->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update main cover.');
        }
    })
    .catch(function() {
        alert('Network error. Try again.');
    });
}

var pendingDeleteMockup = null;
function confirmDeleteMockup(url, name) {
    pendingDeleteMockup = { url: url, name: name };
    document.getElementById('delImgType').textContent = 'mockup';
    document.getElementById('deleteDesignReason').value = '';
    document.getElementById('deleteDesignModal').style.display = 'flex';
}
function doDeleteMockup(btn) {
    if (!pendingDeleteMockup) return;
    var delUrl = pendingDeleteMockup.url;
    var reason = document.getElementById('deleteDesignReason').value.trim();
    var fd = new FormData();
    fd.append('url', delUrl);
    fd.append('reason', reason);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

    fetch('{{ route("sales.prototype.delete-mockup", $sale->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Delete';
        closeDeleteDesignModal();
        if (data.success) {
            var gallery = document.getElementById('mockupsGallery');
            if (gallery) {
                gallery.querySelectorAll('.mockup-item').forEach(function(w) {
                    var img = w.querySelector('img');
                    if (img && img.src === delUrl) w.remove();
                });
                if (!gallery.querySelector('img')) {
                    gallery.innerHTML = '<div class="text-muted small">Wala pang mockup. Mag-upload para makapili ng main cover.</div>';
                }
            }
            var msg = document.getElementById('mockupUploadMsg');
            if (msg) msg.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + data.message + '</span>';
        } else {
            alert(data.message || 'Delete failed.');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Delete';
        alert('Network error. Try again.');
    });
}
</script>

<style>
/* Confirmation Modal Styles */
.cm-overlay {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(3px);
    align-items: center;
    justify-content: center;
    animation: cmFadeIn 0.2s;
}
@keyframes cmFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.cm-modal {
    background: #fff;
    max-width: 480px;
    width: 90%;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
    animation: cmSlideUp 0.25s ease-out;
}
@keyframes cmSlideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.cm-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #eee;
}
.cm-header h4 {
    margin: 0;
    font-size: 17px;
    font-weight: 600;
}
.cm-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
    line-height: 1;
    padding: 0 4px;
}
.cm-close:hover { color: #333; }
.cm-body {
    padding: 24px;
}
.cm-body-text {
    margin: 0 0 16px;
    font-size: 15px;
    color: #333;
    line-height: 1.5;
}
.cm-info-box {
    background: #f0f7ff;
    border-left: 4px solid #0d6efd;
    padding: 12px 14px;
    border-radius: 8px;
    font-size: 13px;
    color: #555;
    line-height: 1.4;
}
.cm-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    border-top: 1px solid #eee;
    background: #fafafa;
}
.cm-cancel-btn {
    padding: 8px 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    color: #555;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.15s;
}
.cm-cancel-btn:hover {
    background: #f5f5f5;
    border-color: #ccc;
}
.cm-confirm-btn {
    padding: 8px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    color: #fff;
    transition: all 0.15s;
    min-width: 130px;
}
.cm-confirm-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.cm-approve {
    background: #198754;
}
.cm-approve:hover:not(:disabled) {
    background: #157347;
}
.cm-reject {
    background: #dc3545;
}
.cm-reject:hover:not(:disabled) {
    background: #bb2d3b;
}
.cm-reject-input-group {
    margin-top: 8px;
}
.cm-reason-input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    font-size: 14px;
    resize: vertical;
    transition: border-color 0.2s;
    box-sizing: border-box;
    font-family: inherit;
}
.cm-reason-input:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
}
.cm-char-hint {
    font-size: 12px;
    color: #999;
    margin-top: 6px;
    text-align: right;
}
.cm-char-count {
    font-weight: 600;
}
.cm-spinner {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: cmSpin 0.6s linear infinite;
    vertical-align: middle;
    margin-right: 6px;
}
@keyframes cmSpin {
    to { transform: rotate(360deg); }
}
</style>
@endpush
