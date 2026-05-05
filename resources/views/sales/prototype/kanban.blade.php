@extends('layouts.app')

@section('title', 'Kanban Board' . ($activeDept === 'all' ? ' — All Departments' : ' — ' . ucfirst($activeDept) . ' Department'))

@push('styles')
<style>
    .kanban-board {
        display: flex;
        gap: 16px;
        overflow-x: auto;
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
        <a href="{{ route('sales.prototype.create') }}" class="btn btn-outline-primary btn-sm">➕ New Order</a>
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
                        <div class="kanban-card" data-id="{{ $sale->id }}" draggable="true" data-department="{{ $sale->department_id }}">
                            @php
                                $svc = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
                                $firstItem = isset($svc[0]) ? (is_string($svc[0]) ? $svc[0] : ($svc[0]['name'] ?? $svc[0]['product_name'] ?? 'Item')) : '';
                                $itemCount = count($svc);
                                $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                                $firstMockup = $mockups[0] ?? null;
                            @endphp

                            @if($firstMockup)
                                <img src="{{ $firstMockup }}" alt="mockup" 
                                     style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-bottom:8px;" 
                                     onerror="this.style.display='none'">
                            @endif
                            
                            <div class="card-title">{{ $sale->customer_name ?: '#' . $sale->sales_number }}</div>
                            
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
                            </div>

                            <div class="card-meta">
                                @if($itemCount > 1)
                                    <span>📦 +{{ $itemCount - 1 }} more items</span>
                                @endif
                                @if($sale->total_amount > 0)
                                    <span>💰 ₱{{ number_format($sale->total_amount, 2) }}</span>
                                @endif
                                @if($sale->deposit_paid > 0)
                                    <span>💳 ₱{{ number_format($sale->deposit_paid, 2) }} paid</span>
                                @endif
                                @if($sale->created_at)
                                    <span>📅 {{ \Carbon\Carbon::parse($sale->created_at)->format('M d') }}</span>
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
            <div class="modal-body" id="modalSaleBody">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- === IMAGE LIGHTBOX === -->
<div class="modal fade" id="imageLightbox" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="lightboxImage" src="" alt="" style="max-width:100%;max-height:80vh;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    const board = document.getElementById('kanbanBoard');
    const tabs = document.querySelectorAll('.department-tab');

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
            body.innerHTML = data.html;
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
    
    // === IMAGE LIGHTBOX ===
    window.openLightbox = function(src) {
        var img = document.getElementById('lightboxImage');
        if (img) {
            img.src = src;
            var lb = new bootstrap.Modal(document.getElementById('imageLightbox'));
            lb.show();
        }
    };
})();
</script>
@endpush
