@extends('layouts.app')

@section('title', 'Production Calendar')

@push('styles')
<style>
/* ========== SALE DETAIL MODAL STYLES (shared with kanban) ========== */
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
/* ========== BASE ========== */
.calendar-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    overflow: hidden;
}
.calendar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.2rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.calendar-header h4 { margin: 0; font-weight: 700; }
.calendar-nav { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
.calendar-nav .btn-outline-light { border-color: rgba(255,255,255,0.3); color: white; }
.calendar-nav .btn-outline-light:hover { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.5); }
.week-label { font-size: 1rem; font-weight: 600; min-width: 200px; text-align: center; }

/* ========== DEPT TABS ========== */
.dept-tabs {
    padding: 0.8rem 2rem;
    border-bottom: 1px solid #eef1ff;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    background: #fafbff;
}
.dept-tab {
    padding: 0.4rem 1rem;
    border-radius: 20px;
    border: 2px solid #e0e0e0;
    background: white;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.dept-tab:hover { border-color: #667eea; background: #f0f2ff; }
.dept-tab.active { background: #667eea; color: white; border-color: #667eea; }
.dept-tab.all-tab.active { background: #6c757d; color: white; border-color: #6c757d; }

/* ========== DATE RANGE PICKER BAR ========== */
.range-bar {
    padding: 0.8rem 2rem;
    background: #f8f9ff;
    border-bottom: 1px solid #eef1ff;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.range-bar label { font-weight: 600; font-size: 0.85rem; color: #555; margin: 0; }
.range-bar input[type="date"] {
    border: 1px solid #d0d5e0;
    border-radius: 6px;
    padding: 0.35rem 0.6rem;
    font-size: 0.85rem;
    color: #333;
}
.range-bar .btn-apply {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 0.35rem 1.2rem;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}
.range-bar .btn-apply:hover { background: #5568d0; }

/* ========== WEEKLY GRID ========== */
.week-container { padding: 1rem 2rem; }

/* Month ribbon — ONE ribbon for the whole visible range */
.month-ribbon {
    text-align: center;
    padding: 0.4rem 0;
    margin-bottom: 0.6rem;
    background: linear-gradient(90deg, transparent, #f0f2ff, transparent);
    font-size: 0.9rem;
    font-weight: 700;
    color: #555;
    letter-spacing: 1px;
}

.week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
    margin-bottom: 1.5rem;
}
.day-header {
    text-align: center;
    font-weight: 700;
    font-size: 0.75rem;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.4rem 0;
    border-bottom: 2px solid #eef1ff;
}

/* Scrollable day cell — PARA MADAMING PROJECTS */
.day-cell {
    min-height: 120px;
    max-height: 280px;
    background: #fafbff;
    border-radius: 8px;
    padding: 0.3rem;
    border: 1px solid #e8ecf5;
    display: flex;
    flex-direction: column;
}
.day-cell .day-number {
    font-size: 0.75rem;
    font-weight: 700;
    color: #666;
    padding: 0.15rem 0.3rem;
    margin-bottom: 0.15rem;
    flex-shrink: 0;
}
.day-cell.today { border-color: #667eea; box-shadow: 0 0 0 2px rgba(102,126,234,0.15); }
.day-cell.drag-over { border-color: #198754; box-shadow: 0 0 0 2.5px rgba(25,135,84,0.4); background: rgba(25,135,84,0.06); }
.day-cell.today .day-number {
    background: #667eea;
    color: white;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}
.day-cell .day-projects-list {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}
/* Thin scrollbar */
.day-cell .day-projects-list::-webkit-scrollbar { width: 3px; }
.day-cell .day-projects-list::-webkit-scrollbar-thumb { background: #d0d5e0; border-radius: 3px; }

.day-project {
    font-size: 0.68rem;
    padding: 0.3rem 0.4rem;
    margin-bottom: 0.2rem;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.1s;
    line-height: 1.25;
    flex-shrink: 0;
}
.day-project:hover { transform: translateX(2px); }
.day-project.moved {
    box-shadow: 0 0 0 1.5px #fd7e14;
    background-image: linear-gradient(135deg, rgba(253,126,20,0.08) 0%, rgba(253,126,20,0.02) 100%);
}
.day-project.dragging {
    opacity: 0.5;
    transform: scale(0.97);
}
.day-project .dp-moved-badge {
    display: inline-block;
    background: #fd7e14;
    color: #fff;
    font-size: 0.5rem;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 8px;
    margin-bottom: 2px;
    letter-spacing: 0.3px;
}
.day-project .dp-prio-badge {
    display: inline-block;
    font-size: 0.5rem;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 8px;
    margin-bottom: 2px;
    letter-spacing: 0.3px;
}
.cal-view-btn {
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 4px 12px;
}
.cal-view-btn.active {
    background: #667eea;
    border-color: #667eea;
    color: #fff;
}
.cal-view-btn.active:hover { color: #fff; }
.day-project .dp-name {
    font-weight: 700;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.day-project .dp-mockup {
    width: 100%;
    height: 68px;
    object-fit: contain;
    object-position: center;
    background: #fff;
    border-radius: 4px;
    margin-bottom: 3px;
    display: block;
    border: 1px solid rgba(0,0,0,0.08);
}
.day-project .dp-qty {
    font-size: 0.6rem;
    font-weight: 800;
    color: #fff;
    background: #667eea;
    border-radius: 8px;
    padding: 0.05rem 0.4rem;
    white-space: nowrap;
}
.day-project .dp-stage {
    display: inline-block;
    font-size: 0.5rem;
    font-weight: 700;
    padding: 0.05rem 0.35rem;
    border-radius: 8px;
    margin-top: 2px;
    white-space: nowrap;
}
.day-project .dp-stage-select {
    width: 100%;
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.1rem 0.2rem;
    border-radius: 4px;
    border: 1px solid rgba(0,0,0,0.12);
    margin-top: 2px;
    cursor: pointer;
    background: #fff;
    color: #495057;
    max-width: 100%;
}
.day-project .dp-meta {
    font-size: 0.6rem;
    opacity: 0.8;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.day-project .dp-dept {
    font-size: 0.55rem;
    padding: 0.05rem 0.35rem;
    border-radius: 6px;
    font-weight: 700;
    display: inline-block;
}
.day-project .dp-amount {
    font-weight: 700;
    white-space: nowrap;
}
.day-project .dp-items {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    margin-top: 3px;
}
.day-project .dp-item {
    font-size: 0.55rem;
    background: rgba(255,255,255,0.7);
    color: #555;
    padding: 0.05rem 0.3rem;
    border-radius: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    border: 1px solid rgba(0,0,0,0.06);
}


/* ========== SIDE SUMMARY ========== */
.summary-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    padding: 1.5rem;
    position: sticky;
    top: 1rem;
}
.summary-panel h6 {
    font-weight: 700;
    color: #667eea;
    border-bottom: 2px solid #667eea;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}
.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.85rem;
}
.summary-item:last-child { border-bottom: none; }
.summary-item-compact { padding: 0.2rem 0; font-size: 0.75rem; }
.summary-item-compact .value { font-size: 0.8rem; }
.summary-item .value { font-weight: 700; }
.summary-stat { text-align: center; padding: 0.4rem 0.5rem; }
.summary-stat .stat-number { font-size: 1.4rem; font-weight: 800; color: #667eea; }
.summary-stat .stat-label { font-size: 0.7rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; }

/* ========== STATUS ========== */
.status-new { background: #e3f2fd; color: #1565c0; }
.status-sample_approval { background: #fef9e7; color: #b7950b; }
.status-design { background: #fff3e0; color: #e65100; }
.status-production { background: #fce4ec; color: #c62828; }
.status-quality_check { background: #f3e5f5; color: #6a1b9a; }
.status-ready_for_delivery { background: #e8f5e9; color: #2e7d32; }
.status-delivered { background: #e0f7fa; color: #00838f; }
.status-completed { background: #e8f5e9; color: #1b5e20; }

.loading-spinner { text-align: center; padding: 3rem; color: #999; }

@media (max-width: 992px) {
    .week-grid { gap: 3px; }
    .day-project { font-size: 0.62rem; padding: 0.2rem 0.3rem; }
}

/* Production Slip (Interactive) */
#modalProdSlipBody { max-height:70vh; overflow-y:auto; }
#modalAddProdSlipBody { max-height:70vh; overflow-y:auto; }
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
.pslip tr.done td { text-decoration:line-through; color:#999; }
.pslip .no-border td, .pslip .no-border { border:none; }
.ps-comment-list { margin-top:6px; max-height:150px; overflow-y:auto; }
.ps-comment-entry { padding:6px 8px; margin-bottom:4px; background:#f8f9fa; border-radius:4px; font-size:12px; border-left:3px solid #0d6efd; line-height:1.4; }
.ps-comment-entry .time { font-size:10px; color:#999; }
.ps-comment-input { display:flex; gap:4px; margin-top:4px; }
.ps-comment-input input { flex:1; border:1px solid #dee2e6; border-radius:4px; padding:6px 8px; font-size:12px; }
.ps-comment-input button { padding:6px 12px; font-size:12px; background:#0d6efd; color:white; border:none; border-radius:4px; cursor:pointer; }
@media (max-width: 768px) {
    .calendar-header { flex-direction: column; padding: 1rem; }
    .dept-tabs, .range-bar { padding: 0.5rem 1rem; }
    .week-container { padding: 0.5rem; }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-9">
            <div class="calendar-wrapper">
                <!-- Header -->
                <div class="calendar-header">
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <h4 style="margin:0;"><i class="fas fa-calendar-alt me-2"></i>Production Calendar</h4>
                        <div class="btn-group btn-group-sm" role="group" style="margin:0;">
                            <button type="button" class="btn btn-light cal-view-btn active" id="viewWeekBtn" onclick="setView('week')"><i class="fas fa-calendar-week"></i> Week</button>
                            <button type="button" class="btn btn-outline-light cal-view-btn" id="viewMonthBtn" onclick="setView('month')"><i class="fas fa-calendar-alt"></i> Month</button>
                        </div>
                    </div>
                    <div class="calendar-nav">
                        <button class="btn btn-sm btn-outline-light" onclick="changeWeek(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="week-label" id="weekLabel">This Week</span>
                        <button class="btn btn-sm btn-outline-light" onclick="changeWeek(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-light ms-2" onclick="goToToday()">
                            <i class="fas fa-calendar-day"></i> Today
                        </button>
                    </div>
                </div>

                <!-- Department Tabs -->
                <div class="dept-tabs" id="deptTabs">
                    <button class="dept-tab all-tab active" data-dept="all" onclick="filterDept('all')">All</button>
                    <button class="dept-tab" data-dept="iPrint" onclick="filterDept('iPrint')" style="border-color:#0d6efd;color:#0d6efd;"><i class="fas fa-print"></i> iPrint</button>
                    <button class="dept-tab" data-dept="Consol" onclick="filterDept('Consol')" style="border-color:#198754;color:#198754;"><i class="fas fa-layer-group"></i> Consol</button>
                    <button class="dept-tab" data-dept="Class" onclick="filterDept('Class')" style="border-color:#6f42c1;color:#6f42c1;"><i class="fas fa-tshirt"></i> Class</button>
                    <button class="dept-tab" data-dept="Cinco" onclick="filterDept('Cinco')" style="border-color:#dc3545;color:#dc3545;"><i class="fas fa-star"></i> Cinco</button>
                    <button class="dept-tab" data-dept="MTO" onclick="filterDept('MTO')" style="border-color:#fd7e14;color:#fd7e14;"><i class="fas fa-ruler-combined"></i> MTO</button>
                    <button class="dept-tab" data-dept="Other" onclick="filterDept('Other')" style="border-color:#6c757d;color:#6c757d;"><i class="fas fa-ellipsis-h"></i> Other</button>
                </div>

                <!-- Date Range Picker -->
                <div class="range-bar" id="rangeBar">
                    <label><i class="fas fa-calendar-range me-1"></i> Show from:</label>
                    <input type="date" id="rangeStart">
                    <label>to:</label>
                    <input type="date" id="rangeEnd">
                    <button class="btn-apply" onclick="applyRange()">Apply</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="resetToWeek()" style="font-size:0.8rem;">This Week</button>
                </div>

                <!-- Weekly Grid -->
                <div class="week-container" id="weekContainer">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                        <p>Loading calendar...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Summary -->
        <div class="col-lg-3">
            <div class="summary-panel" id="summaryPanel">
                <h6><i class="fas fa-chart-pie me-2"></i>Summary</h6>
                <div id="summaryContent">
                    <p class="text-muted small">Select dates to see summary</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Project Detail Modal -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Project Details</h5>
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
            <div class="modal-body" id="projectModalBody">Loading...</div>
            <div class="modal-body" id="modalProdSlipBody" style="display:none;"></div>
            <div class="modal-body" id="modalAddProdSlipBody" style="display:none;"></div>
            <div class="modal-footer">
                <a href="#" id="calendarEditBtn" class="btn btn-primary" target="_blank" style="display:none;">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="#" id="calendarPrintSlipBtn" class="btn btn-success" target="_blank" style="display:none;">
                    <i class="fas fa-print"></i> Print Slip
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Info/Notification Modal (replaces browser alert) -->
<div class="modal fade" id="calInfoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calInfoTitle"><i class="fas fa-info-circle me-2"></i>Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="calInfoBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Reschedule Confirm Modal (replaces browser confirm) -->
<div class="modal fade" id="calConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Confirm Reschedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="calConfirmBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="calConfirmBtn"><i class="fas fa-check"></i> Confirm Move</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ========== CONFIG ==========
const DAYS = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

const dc = {'iPrint':'#0d6efd','Consol':'#198754','Cinco':'#dc3545','Class':'#6f42c1','MTO':'#fd7e14','Other':'#6c757d','iprint':'#0d6efd','consol':'#198754','cinco':'#dc3545','class':'#6f42c1','mto':'#fd7e14','other':'#6c757d'};

// Same production stage map as manager order list (stage → kanban status)
const PROD_STAGE_MAP = @json($prodStageMap);
const STATUS_TO_STAGE = @json($statusToStage);
const STAGE_COLORS = {
    'FOR SAMPLE': '#fd7e14', 'FOR APPROVAL': '#fd7e14',
    'FOR FORMAT': '#0d6efd', 'PRINTING': '#0d6efd',
    'PRESSING': '#6f42c1', 'CUTTING': '#6f42c1', 'SEWING': '#6f42c1',
    'QA': '#20c997', 'HOLD': '#6c757d', 'DISPATCH': '#17a2b8',
    'UNPAID': '#dc3545', 'DONE': '#28a745'
};

let curDate = new Date();
let activeDept = 'all';
let customRange = null; // null = nav mode, {start, end} = range mode
let currentView = 'week'; // 'week' | 'month'

// ========== VIEW TOGGLE ==========
function setView(view) {
    currentView = view;
    document.getElementById('viewWeekBtn').classList.toggle('active', view === 'week');
    document.getElementById('viewWeekBtn').classList.toggle('btn-outline-light', view !== 'week');
    document.getElementById('viewMonthBtn').classList.toggle('active', view === 'month');
    document.getElementById('viewMonthBtn').classList.toggle('btn-outline-light', view !== 'month');
    loadCal();
}

// ========== HELPERS ==========
function getMon(d) {
    const date = new Date(d);
    const day = date.getDay();
    date.setDate(date.getDate() - (day === 0 ? 6 : day - 1));
    date.setHours(0,0,0,0);
    return date;
}
function getSun(m) { const d=new Date(m); d.setDate(d.getDate()+6); return d; }
function fmt(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    return y+'-'+m+'-'+day;
}
function parseD(s) { if (!s) return null; const d=new Date(s); return isNaN(d.getTime())?null:d; }
function curr(n) { return '₱'+parseFloat(n||0).toLocaleString('en-PH',{minimumFractionDigits:2}); }

function changeWeek(delta) {
    customRange = null;
    if (currentView === 'month') {
        curDate.setMonth(curDate.getMonth() + delta);
    } else {
        curDate.setDate(curDate.getDate()+(delta*7));
    }
    loadCal();
}
function goToToday() {
    customRange = null;
    curDate = new Date();
    loadCal();
}
function filterDept(dept) {
    activeDept = dept;
    document.querySelectorAll('.dept-tab').forEach(t=>t.classList.remove('active'));
    document.querySelector(`.dept-tab[data-dept="${dept}"]`).classList.add('active');
    loadCal();
}

// ========== DATE RANGE ==========
function applyRange() {
    const s = document.getElementById('rangeStart').value;
    const e = document.getElementById('rangeEnd').value;
    if (!s || !e) { alert('Pumili ng start at end date.'); return; }
    if (s > e) { alert('End date dapat mas bago sa start date.'); return; }
    customRange = {start: new Date(s+'T00:00:00'), end: new Date(e+'T23:59:59')};
    loadCal();
}
function resetToWeek() {
    customRange = null;
    curDate = new Date();
    document.getElementById('rangeStart').value = '';
    document.getElementById('rangeEnd').value = '';
    loadCal();
}

// ========== RENDER ==========
function renderWeek(monday, projects) {
    const today = fmt(new Date());

    // Build 7 day cells
    let html = '<div class="week-grid">';

    // Day headers
    DAYS.forEach(n => { html+=`<div class="day-header">${n}</div>`; });

    for (let i=0; i<7; i++) {
        const d = new Date(monday);
        d.setDate(d.getDate()+i);
        const dateStr = fmt(d);
        const isToday = dateStr === today;

        // Use effective date: rescheduled_date takes priority, then original
        const dayProjects = projects.filter(p => {
            const pd = parseD(p.rescheduled_date || p.estimated_completion_date || p.created_at);
            return pd && fmt(pd) === dateStr;
        });

        html += `<div class="day-cell ${isToday?'today':''}" data-date="${dateStr}">`;
        html += `<div class="day-number">${d.getDate()}</div>`;
        html += '<div class="day-projects-list">';

        if (dayProjects.length === 0) {
            html += `<div style="font-size:0.55rem;color:#ddd;text-align:center;padding:0.5rem 0;">—</div>`;
        } else {
            // Sort: priority-tagged first (Prio 1 → 2 → 3), then original order
            dayProjects.sort(function(a, b) {
                var pa = a.priority ? parseInt(a.priority) : 99;
                var pb = b.priority ? parseInt(b.priority) : 99;
                if (pa !== pb) return pa - pb;
                return 0;
            });
            dayProjects.forEach(p => {
                const dept = p.department_name || 'other';
                const color = dc[dept] || '#6c757d';
                const name = (p.product_label ? p.product_label + (p.sales_agent_name ? ' - ' + p.sales_agent_name : '') : (p.customer_name || 'Unknown'));
                const amt = parseFloat(p.subtotal || p.total_amount || 0);
                const qty = parseInt(p.total_qty) || 0;
                const stage = p.production_stage || p.kanban_status || '';
                const mockupUrl = p.mockup_url || '';
                const isMoved = !!p.rescheduled_date && p.rescheduled_date !== p.estimated_completion_date;
                const orig = p.estimated_completion_date ? parseD(p.estimated_completion_date) : null;
                
                html += `<div class="day-project ${isMoved?'moved':''}" style="background:${color}15;border-left:3px solid ${isMoved?'#fd7e14':color};"
                    draggable="true" data-id="${p.id}" onclick="showDetail(${p.id})" title="${name} - ${curr(amt)}">`;
                if (isMoved) {
                    html += `<span class="dp-moved-badge" title="Original: ${orig ? orig.toLocaleDateString('en-US',{month:'short',day:'numeric'}) : '—'}">↗ Moved</span>`;
                }
                if (p.priority) {
                    const prio = parseInt(p.priority);
                    const hue = Math.max(0, Math.min(45, (prio - 1) * 5));
                    const pc = `hsl(${hue}, 85%, 45%)`;
                    html += `<span class="dp-prio-badge" style="background:${pc};color:#fff;font-weight:700;">PRIO ${prio}</span>`;
                }
                // Mockup thumbnail (same as manager list)
                if (mockupUrl) {
                    html += `<img src="${mockupUrl}" alt="mockup" class="dp-mockup" loading="lazy" onerror="this.style.display='none'">`;
                }
                html += `<span class="dp-name">${name}</span>`;
                html += `<div class="dp-meta">`;
                html += `<span class="dp-dept" style="background:${color};color:white;">${dept}</span>`;
                // Quantity badge
                if (qty > 0) {
                    html += `<span class="dp-qty">×${qty}</span>`;
                }
                if (p.payment_status === 'verified') {
                    html += `<span class="dp-status" style="color:#198754;font-weight:600;">✅</span>`;
                } else if (p.payment_status === 'pending' && p.payment_account_id) {
                    html += `<span class="dp-status" style="color:#856404;">⏳</span>`;
                } else if (p.payment_status === 'rejected') {
                    html += `<span class="dp-status" style="color:#842029;font-weight:600;">❌</span>`;
                }
                html += `<span class="dp-amount">${curr(amt)}</span>`;
                html += `</div>`;
                // Production stage tagging (same rules as manager order list)
                const curStage = p.production_stage || STATUS_TO_STAGE[p.kanban_status] || 'HOLD';
                const lockedNoPhotos = !p.has_photos && !p.can_override;
                let stageOpts = '';
                Object.keys(PROD_STAGE_MAP).forEach(function(st) {
                    const stStatus = PROD_STAGE_MAP[st];
                    let dis = '';
                    if (stStatus === 'completed' && parseFloat(p.balance_due) > 0) dis = 'disabled';
                    stageOpts += `<option value="${st}" data-status="${stStatus}" ${st === curStage ? 'selected' : ''} ${dis}>${st}</option>`;
                });
                html += `<select class="dp-stage-select" data-sale-id="${p.id}" data-current="${curStage}" ${lockedNoPhotos ? 'disabled' : ''} title="${lockedNoPhotos ? '🔒 Kulang photos (File Screenshot / Sample Color) — i-move sa kanban board' : 'Production status → kanban'}" onclick="event.stopPropagation()" style="${lockedNoPhotos ? 'background:#e9ecef;color:#adb5bd;cursor:not-allowed;' : ''}">${stageOpts}</select>`;
                html += `</div>`;
            });
        }

        html += '</div></div>';
    }

    html += '</div></div></div>';
    return html;
}

// ========== LOAD ==========
function loadCal() {
    let weeks;
    let label = '';
    let dateStart, dateEnd;

    if (currentView === 'month') {
        // MONTH MODE — show the full month grid (weeks from 1st to last day)
        const firstOfMonth = new Date(curDate.getFullYear(), curDate.getMonth(), 1);
        const lastOfMonth = new Date(curDate.getFullYear(), curDate.getMonth() + 1, 0);
        const firstMon = getMon(firstOfMonth);
        const lastSun = getSun(getMon(lastOfMonth));
        weeks = [];
        let w = new Date(firstMon);
        while (w <= lastSun) {
            weeks.push(new Date(w));
            w.setDate(w.getDate()+7);
        }
        dateStart = fmt(firstMon);
        dateEnd = fmt(lastSun);
        label = MONTHS[curDate.getMonth()] + ' ' + curDate.getFullYear();
    } else if (customRange) {
        // RANGE MODE — show all weeks between start and end
        const firstMon = getMon(customRange.start);
        const lastSun = getSun(getMon(customRange.end));
        weeks = [];
        let w = new Date(firstMon);
        while (w <= lastSun) {
            weeks.push(new Date(w));
            w.setDate(w.getDate()+7);
        }
        dateStart = fmt(customRange.start);
        dateEnd = fmt(customRange.end);

        const opts = {month:'short', day:'numeric', year:'numeric'};
        label = customRange.start.toLocaleDateString('en-US',opts) + ' — ' + customRange.end.toLocaleDateString('en-US',opts);
    } else {
        // NAV MODE — show 3 weeks (prev, current, next)
        const monday = getMon(curDate);
        weeks = [];
        for (let i=-1; i<=1; i++) {
            const w = new Date(monday);
            w.setDate(w.getDate()+(i*7));
            weeks.push(w);
        }
        dateStart = fmt(weeks[0]);
        dateEnd = fmt(getSun(weeks[weeks.length-1]));

        label = MONTHS[monday.getMonth()]+' '+monday.getDate()+' — '+
                MONTHS[getSun(monday).getMonth()]+' '+getSun(monday).getDate()+', '+monday.getFullYear();
    }

    document.getElementById('weekLabel').textContent = label;

    const container = document.getElementById('weekContainer');
    container.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p>Loading calendar...</p></div>';

    fetch('/sales/prototype/calendar-data', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'{{ csrf_token() }}'},
        body:JSON.stringify({
            start_date: dateStart,
            end_date: dateEnd,
            department: activeDept
        })
    })
    .then(r=>r.json())
    .then(data => {
        const projects = data.projects || [];

        // Single month ribbon across all weeks
        let html = '';
        const allMonths = new Set();
        weeks.forEach(w => {
            for (let i=0; i<7; i++) {
                const d = new Date(w); d.setDate(d.getDate()+i);
                allMonths.add(MONTHS[d.getMonth()]+' '+d.getFullYear());
            }
        });
        html += `<div class="month-ribbon"><i class="far fa-calendar-alt me-2"></i>${[...allMonths].join(' · ')}</div>`;

        weeks.forEach(w => {
            html += renderWeek(w, projects);
        });

        container.innerHTML = html;
        updateSummary(projects);
    })
    .catch(() => {
        container.innerHTML = '<div class="alert alert-danger">Failed to load calendar. Try refreshing.</div>';
    });
}

// ========== DRAG & DROP RESCHEDULE ==========
let dragSaleId = null;
let dragSaleFromDate = null;

document.addEventListener('dragstart', function(e) {
    const card = e.target.closest('.day-project');
    if (!card) return;
    dragSaleId = card.getAttribute('data-id');
    // Current effective date = the day-cell this card sits in
    const cell = card.closest('.day-cell');
    dragSaleFromDate = cell ? cell.getAttribute('data-date') : null;
    card.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    try { e.dataTransfer.setData('text/plain', dragSaleId); } catch(err) {}
});

document.addEventListener('dragend', function(e) {
    const card = e.target.closest('.day-project');
    if (card) card.classList.remove('dragging');
    document.querySelectorAll('.day-cell.drag-over').forEach(function(c) { c.classList.remove('drag-over'); });
    dragSaleId = null;
    dragSaleFromDate = null;
});

document.addEventListener('dragover', function(e) {
    const cell = e.target.closest('.day-cell');
    if (!cell || !dragSaleId) return;
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    document.querySelectorAll('.day-cell.drag-over').forEach(function(c) { c.classList.remove('drag-over'); });
    cell.classList.add('drag-over');
});

document.addEventListener('drop', function(e) {
    const cell = e.target.closest('.day-cell');
    if (!cell || !dragSaleId) return;
    e.preventDefault();
    document.querySelectorAll('.day-cell.drag-over').forEach(function(c) { c.classList.remove('drag-over'); });

    const newDate = cell.getAttribute('data-date');
    if (!newDate) return;

    const saleId = dragSaleId;
    const fromDate = dragSaleFromDate;
    dragSaleId = null;
    dragSaleFromDate = null;

    // Bawal mag-usog paurong — dapat future date lang palagi (YYYY-MM-DD string compare)
    if (fromDate && newDate < fromDate) {
        document.getElementById('calInfoTitle').innerHTML = '<i class="fas fa-exclamation-triangle text-warning me-2"></i>Hindi Pwedeng I-usog Paurong';
        document.getElementById('calInfoBody').innerHTML = '<p class="mb-1">Hindi pwedeng i-usog <strong>paurong</strong> ang project.</p><p class="mb-0 text-muted">Pumili ng mas <strong>future date</strong> (pagkatapos ng ' + new Date(fromDate + 'T00:00:00').toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) + ').</p>';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('calInfoModal')).show();
        return;
    }

    // Confirm via modal — maghihintay ng confirmation bago mag-fetch
    pendingReschedule = { saleId: saleId, newDate: newDate };
    document.getElementById('calConfirmBody').innerHTML =
        '<p class="mb-1">Ilipat ang project na ito sa <strong>' + new Date(newDate + 'T00:00:00').toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) + '</strong>?</p>' +
        '<p class="mb-0 text-muted small">Original date will be kept.<br>(Drag = reschedule dahil na-delay)</p>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('calConfirmModal')).show();
    return;
});

document.addEventListener('click', function(e) {
    if (e.target.closest('#calConfirmBtn')) {
        doReschedule();
    }
});

let pendingReschedule = null;

function doReschedule() {
    if (!pendingReschedule) return;
    const saleId = pendingReschedule.saleId;
    const newDate = pendingReschedule.newDate;
    pendingReschedule = null;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('calConfirmModal')).hide();

    fetch('/sales/prototype/' + saleId + '/reschedule', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ date: newDate })
    })
    .then(function(r) { return r.json().catch(function() { return {}; }).then(function(d) { return { ok: r.ok, data: d }; }); })
    .then(function(res) {
        if (res.ok && res.data.success) {
            loadCal();
            showCalToast('✅ ' + res.data.message);
        } else {
            showCalInfo('error', res.data.message || 'Failed to reschedule.');
        }
    })
    .catch(function() {
        showCalInfo('error', 'Network error. Please try again.');
    });
}

function showCalInfo(type, msg) {
    document.getElementById('calInfoTitle').innerHTML = (type === 'error' ? '<i class="fas fa-times-circle text-danger me-2"></i>Error' : '<i class="fas fa-info-circle me-2"></i>Notice');
    document.getElementById('calInfoBody').innerHTML = '<p class="mb-0">' + msg + '</p>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('calInfoModal')).show();
}

function showCalToast(msg) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:#198754;color:#fff;padding:12px 18px;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,0.25);font-size:14px;font-weight:600;';
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 4000);
}

// ========== PRODUCTION STAGE TAGGING (same rules as manager order list) ==========
document.addEventListener('change', function(e) {
    const sel = e.target.closest('.dp-stage-select');
    if (!sel) return;
    const saleId = sel.getAttribute('data-sale-id');
    const opt = sel.options[sel.selectedIndex];
    const stage = opt.value;
    const newStatus = opt.getAttribute('data-status');
    const oldStage = sel.getAttribute('data-current');
    sel.disabled = true;
    const csrf = document.querySelector('meta[name="csrf-token"]');
    fetch('/sales/prototype/' + saleId + '/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.content : '{{ csrf_token() }}'
        },
        body: JSON.stringify({ kanban_status: newStatus, production_stage: stage })
    })
    .then(function(r) { return r.json().catch(function() { return {}; }).then(function(d) { return { ok: r.ok, data: d }; }); })
    .then(function(res) {
        if (res.ok && res.data.success) {
            showToast('✅ Tagged ' + stage + ' → kanban: ' + newStatus, 'success');
            loadCal(); // refresh so kanban/list stay in sync
        } else {
            sel.value = oldStage;
            sel.disabled = false;
            showToast('⚠️ ' + (res.data.message || 'Failed to update status.'), 'error');
        }
    })
    .catch(function() {
        sel.value = oldStage;
        sel.disabled = false;
        showToast('❌ Network error. Please try again.', 'error');
    });
});

function showToast(msg, type) {
    const colors = { success: '#198754', error: '#dc3545' };
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:' + (colors[type] || '#0d6efd') + ';color:#fff;padding:12px 18px;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,0.25);font-size:14px;font-weight:600;';
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 4000);
}

// ========== SUMMARY ==========
function updateSummary(projects) {
    const total = projects.length;
    const totalQty = projects.reduce((s,p)=>s+(parseInt(p.total_qty)||0),0);
    const totalAmt = projects.reduce((s,p)=>s+parseFloat(p.subtotal || p.total_amount || 0),0);
    const totalDep = projects.reduce((s,p)=>s+parseFloat(p.deposit_paid||0),0);

    const db = {}, prodb = {}, stb = {};
    projects.forEach(p => {
        const d = p.department_name || 'other';
        db[d] = (db[d] || 0) + 1;
        // By product type (TSHIRT VNECK, POLO, ...) — total pieces
        const pl = p.product_label || p.customer_name || 'Unknown';
        prodb[pl] = (prodb[pl] || 0) + (parseInt(p.total_qty) || 0);
        // By production stage (SEWING, FOR FORMAT, ...) — project count + pieces
        const st = p.production_stage || STATUS_TO_STAGE[p.kanban_status] || 'HOLD';
        if (!stb[st]) stb[st] = { count: 0, qty: 0 };
        stb[st].count++;
        stb[st].qty += (parseInt(p.total_qty) || 0);
    });

    let html = `
        <div class="d-flex justify-content-around mb-3 pb-2 border-bottom">
            <div class="summary-stat"><div class="stat-number">${total}</div><div class="stat-label">Projects</div></div>
            <div class="summary-stat"><div class="stat-number" style="color:#667eea;">${totalQty}</div><div class="stat-label">Pieces</div></div>
            <div class="summary-stat"><div class="stat-number" style="color:#28a745;">${curr(totalAmt)}</div><div class="stat-label">Value</div></div>
        </div>`;

    // By Product (pieces per product type)
    html += `<h6 style="font-size:0.8rem;color:#666;margin-bottom:0.5rem;"><i class="fas fa-tshirt me-1"></i> By Product</h6>`;
    const sortedProds = Object.keys(prodb).sort((a,b) => prodb[b]-prodb[a]);
    sortedProds.forEach(pl => {
        const q = prodb[pl];
        html += `<div class="summary-item summary-item-compact"><span>${pl}</span><span class="value">${q} pc${q>1?'s':''}</span></div>`;
    });

    // By Stage (production flow order)
    html += `<h6 style="font-size:0.8rem;color:#666;margin:0.8rem 0 0.5rem;"><i class="fas fa-industry me-1"></i> By Stage</h6>`;
    const stageOrder = ['FOR SAMPLE','FOR APPROVAL','FOR FORMAT','PRINTING','PRESSING','CUTTING','SEWING','QA','HOLD','DISPATCH','UNPAID','DONE'];
    stageOrder.forEach(st => {
        if (stb[st]) {
            const c = STAGE_COLORS[st] || '#6c757d';
            html += `<div class="summary-item"><span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${c};margin-right:0.4rem;"></span>${st}</span><span class="value">${stb[st].count} proj · ${stb[st].qty} pc${stb[st].qty>1?'s':''}</span></div>`;
        }
    });

    // By Department
    html += `<h6 style="font-size:0.8rem;color:#666;margin:0.8rem 0 0.5rem;"><i class="fas fa-building me-1"></i> By Department</h6>`;
    const dor = ['iPrint','Consol','Cinco','Class','MTO','Other'];
    dor.forEach(d => {
        if (db[d]) {
            const c = dc[d] || '#6c757d';
            html += `<div class="summary-item"><span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${c};margin-right:0.4rem;"></span>${d}</span><span class="value">${db[d]}</span></div>`;
        }
    });

    document.getElementById('summaryContent').innerHTML = html;
}

function showDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('projectModal'));
    const body = document.getElementById('projectModalBody');
    body.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';

    // Store sale ID immediately (before fetch completes) for Production Slip tab
    psSaleId = id;
    body.dataset.saleId = id;

    // Clear production slip caches so they reload when switching tabs
    var prodBody = document.getElementById('modalProdSlipBody');
    var addProdBody = document.getElementById('modalAddProdSlipBody');
    if (prodBody) prodBody.dataset.loaded = '';
    if (addProdBody) addProdBody.dataset.loaded = '';

    // Reset to Details tab on open
    var allTabs = document.querySelectorAll('#projectModal .prod-tab');
    allTabs.forEach(function(t) {
        t.style.color = '#666';
        t.style.borderBottomColor = 'transparent';
        t.style.fontWeight = '500';
    });
    var detailsTab = document.querySelector('#projectModal .prod-tab[data-tab="details"]');
    if (detailsTab) {
        detailsTab.style.color = '#0d6efd';
        detailsTab.style.borderBottomColor = '#0d6efd';
        detailsTab.style.fontWeight = '600';
    }
    body.style.display = '';
    if (prodBody) prodBody.style.display = 'none';
    if (addProdBody) addProdBody.style.display = 'none';

    modal.show();
    fetch(`/sales/prototype/${id}/details`)
        .then(r=>r.json())
        .then(function(data) {
            var mt = document.querySelector('#projectModal .modal-title');
            mt.innerHTML = '<i class="fas fa-info-circle me-2"></i>' + (data.title || 'Sale #' + id);
            body.innerHTML = data.html;
            // Attach lightbox click handlers to dynamically loaded images
            body.querySelectorAll('.ref-image, .payment-img').forEach(function(img) {
                img.addEventListener('click', function() {
                    window.openLightbox(this.src);
                });
            });
            // Update Edit and Print Slip button links
            var editBtn = document.getElementById('calendarEditBtn');
            var printBtn = document.getElementById('calendarPrintSlipBtn');
            if (editBtn) {
                editBtn.href = '/sales/prototype/' + id + '/edit';
                editBtn.style.display = '';
            }
            if (printBtn) {
                printBtn.href = '/sales/prototype/' + id + '/print-slip';
                printBtn.style.display = '';
            }
        })
        .catch(()=>{ body.innerHTML = '<p class="text-danger">Failed to load details.</p>'; });
}

// Lightbox functions (shared with kanban modal)
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
window.closeLightbox = function() {
    var overlay = document.getElementById('imageLightbox');
    if (overlay) {
        overlay.remove();
        document.body.style.overflow = '';
    }
};

document.addEventListener('DOMContentLoaded', loadCal);
// ===== Production Slip Modal (kanban-style tabs in project modal) =====
var psSaleId = null;

// Tab switching (Details / Production Slip / Additional Production Slip)
document.addEventListener('click', function(e) {
    var tab = e.target.closest('.prod-tab');
    if (!tab || !document.getElementById('projectModal')) return;
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

    var detailsBody = document.getElementById('projectModalBody');
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
        var needReload = !addProdBody.dataset.loaded || addProdBody.dataset.saleId !== String(psSaleId);
        if (needReload && psSaleId) {
            addProdBody.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading additional production slip...</p></div>';
            addProdBody.dataset.loaded = '';
            loadAdditionalProductionSlip(psSaleId);
        }
    } else {
        detailsBody.style.display = 'none';
        prodBody.style.display = '';
        addProdBody.style.display = 'none';
        // Reload if different sale or not loaded yet
        var needReload = !prodBody.dataset.loaded || prodBody.dataset.saleId !== String(psSaleId);
        if (needReload && psSaleId) {
            prodBody.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading production slip...</p></div>';
            prodBody.dataset.loaded = '';
            loadProductionSlip(psSaleId);
        }
    }
});

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
    var mainMockup = null;
    for (var mi = 0; mi < mockupImages.length; mi++) {
        if (mockupImages[mi] && typeof mockupImages[mi] === 'object' && mockupImages[mi].is_main) { mainMockup = mockupImages[mi]; break; }
    }
    if (!mainMockup && mockupImages.length > 0) mainMockup = mockupImages[0];
    var firstMockup = mainMockup;
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
    var mainMockup = null;
    for (var mi = 0; mi < mockupImages.length; mi++) {
        if (mockupImages[mi] && typeof mockupImages[mi] === 'object' && mockupImages[mi].is_main) { mainMockup = mockupImages[mi]; break; }
    }
    if (!mainMockup && mockupImages.length > 0) mainMockup = mockupImages[0];
    var firstMockup = mainMockup;
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
