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
.day-project .dp-name {
    font-weight: 700;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
                    <h4><i class="fas fa-calendar-alt me-2"></i>Production Calendar</h4>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Project Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="projectModalBody">Loading...</div>
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
@endsection

@push('scripts')
<script>
// ========== CONFIG ==========
const DAYS = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

const dc = {'iPrint':'#0d6efd','Consol':'#198754','Cinco':'#dc3545','Class':'#6f42c1','MTO':'#fd7e14','Other':'#6c757d','iprint':'#0d6efd','consol':'#198754','cinco':'#dc3545','class':'#6f42c1','mto':'#fd7e14','other':'#6c757d'};

let curDate = new Date();
let activeDept = 'all';
let customRange = null; // null = nav mode, {start, end} = range mode

// ========== HELPERS ==========
function getMon(d) {
    const date = new Date(d);
    const day = date.getDay();
    date.setDate(date.getDate() - (day === 0 ? 6 : day - 1));
    date.setHours(0,0,0,0);
    return date;
}
function getSun(m) { const d=new Date(m); d.setDate(d.getDate()+6); return d; }
function fmt(d) { return d.toISOString().split('T')[0]; }
function parseD(s) { if (!s) return null; const d=new Date(s); return isNaN(d.getTime())?null:d; }
function curr(n) { return '₱'+parseFloat(n||0).toLocaleString('en-PH',{minimumFractionDigits:2}); }

function changeWeek(delta) {
    customRange = null;
    curDate.setDate(curDate.getDate()+(delta*7));
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
    const today = new Date().toISOString().split('T')[0];

    // Build 7 day cells
    let html = '<div class="week-grid">';

    // Day headers
    DAYS.forEach(n => { html+=`<div class="day-header">${n}</div>`; });

    for (let i=0; i<7; i++) {
        const d = new Date(monday);
        d.setDate(d.getDate()+i);
        const dateStr = fmt(d);
        const isToday = dateStr === today;

        const dayProjects = projects.filter(p => {
            const pd = parseD(p.date_needed || p.estimated_completion_date || p.created_at);
            return pd && fmt(pd) === dateStr;
        });

        html += `<div class="day-cell ${isToday?'today':''}">`;
        html += `<div class="day-number">${d.getDate()}</div>`;
        html += '<div class="day-projects-list">';

        if (dayProjects.length === 0) {
            html += `<div style="font-size:0.55rem;color:#ddd;text-align:center;padding:0.5rem 0;">—</div>`;
        } else {
            dayProjects.forEach(p => {
                const dept = p.department_name || 'other';
                const color = dc[dept] || '#6c757d';
                const name = p.customer_name || 'Unknown';
                const amt = parseFloat(p.subtotal || p.total_amount || 0);
                
                // Build items summary from services
                let itemsHtml = '';
                if (p.services && p.services.length > 0) {
                    const itemCounts = {};
                    p.services.forEach(s => {
                        const itemName = s.name || (typeof s === 'string' ? s : 'Item');
                        const short = itemName.length > 18 ? itemName.substring(0, 16)+'..' : itemName;
                        const qty = parseInt(s.qty) || 1;
                        itemsHtml += `<span class="dp-item">${qty}× ${short}</span>`;
                    });
                }
                
                html += `<div class="day-project" style="background:${color}15;border-left:3px solid ${color};"
                    onclick="showDetail(${p.id})" title="${name} - ${curr(amt)}">`;
                html += `<span class="dp-name">${name}</span>`;
                html += `<div class="dp-meta">`;
                html += `<span class="dp-dept" style="background:${color};color:white;">${dept}</span>`;
                if (p.payment_status === 'verified') {
                    html += `<span class="dp-status" style="color:#198754;font-weight:600;">✅</span>`;
                } else if (p.payment_status === 'pending' && p.payment_account_id) {
                    html += `<span class="dp-status" style="color:#856404;">⏳</span>`;
                } else if (p.payment_status === 'rejected') {
                    html += `<span class="dp-status" style="color:#842029;font-weight:600;">❌</span>`;
                }
                html += `<span class="dp-amount">${curr(amt)}</span>`;
                html += `</div>`;
                if (itemsHtml) {
                    html += `<div class="dp-items">${itemsHtml}</div>`;
                }
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

    if (customRange) {
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

// ========== SUMMARY ==========
function updateSummary(projects) {
    const total = projects.length;
    const totalAmt = projects.reduce((s,p)=>s+parseFloat(p.subtotal || p.total_amount || 0),0);
    const totalDep = projects.reduce((s,p)=>s+parseFloat(p.deposit_paid||0),0);

    const sb = {}, db = {}, itemb = {};
    projects.forEach(p => {
        const s=p.kanban_status||'new'; sb[s]=(sb[s]||0)+1;
        const d=p.department_name||'other'; db[d]=(db[d]||0)+1;
        // Tally items/services
        if (p.services && p.services.length > 0) {
            p.services.forEach(svc => {
                const name = svc.name || (typeof svc === 'string' ? svc : 'Item');
                const qty = parseInt(svc.qty) || 1;
                itemb[name] = (itemb[name]||0) + qty;
            });
        }
    });

    let html = `
        <div class="d-flex justify-content-around mb-3 pb-2 border-bottom">
            <div class="summary-stat"><div class="stat-number">${total}</div><div class="stat-label">Projects</div></div>
            <div class="summary-stat"><div class="stat-number" style="color:#28a745;">${curr(totalAmt)}</div><div class="stat-label">Value</div></div>
        </div>
        <h6 style="font-size:0.8rem;color:#666;margin-bottom:0.5rem;"><i class="fas fa-boxes me-1"></i> Items Summary</h6>`;

    // Sort items by qty descending
    const sortedItems = Object.keys(itemb).sort((a,b) => itemb[b]-itemb[a]);
    sortedItems.forEach(name => {
        const qty = itemb[name];
        html+=`<div class="summary-item summary-item-compact"><span>${name}</span><span class="value" style="font-weight:700;">${qty}pc${qty>1?'s':''}</span></div>`;
    });

    html+=`<h6 style="font-size:0.8rem;color:#666;margin:0.8rem 0 0.5rem;"><i class="fas fa-tasks me-1"></i> By Status</h6>`;
    const so = ['new','design','production','quality_check','ready_for_delivery','delivered','completed'];
    const sl = {'new':'New','design':'Design','production':'Production','quality_check':'QC','ready_for_delivery':'Ready','delivered':'Delivered','completed':'Completed'};
    so.forEach(s => {
        if (sb[s]) {
            const pct = Math.round((sb[s]/total)*100)||0;
            html+=`<div class="summary-item"><span><span class="status-${s}" style="font-size:0.7rem;padding:0.15rem 0.5rem;border-radius:8px;font-weight:600;">${sl[s]||s}</span></span><span class="value">${sb[s]} <small class="text-muted">(${pct}%)</small></span></div>`;
        }
    });

    html+=`<h6 style="font-size:0.8rem;color:#666;margin:0.8rem 0 0.5rem;"><i class="fas fa-building me-1"></i> By Department</h6>`;
    const dor = ['iPrint','Consol','Cinco','Class','MTO','Other'];
    dor.forEach(d => {
        if (db[d]) {
            const c=dc[d]||'#6c757d';
            html+=`<div class="summary-item"><span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${c};margin-right:0.4rem;"></span>${d}</span><span class="value">${db[d]}</span></div>`;
        }
    });

    html+=`<h6 style="font-size:0.8rem;color:#666;margin:0.8rem 0 0.5rem;"><i class="fas fa-coins me-1"></i> Financials</h6>`;
    html+=`<div class="summary-item"><span>Total Value</span><span class="value" style="color:#28a745;">${curr(totalAmt)}</span></div>`;
    html+=`<div class="summary-item"><span>Deposit Paid</span><span class="value" style="color:#0d6efd;">${curr(totalDep)}</span></div>`;
    html+=`<div class="summary-item"><span>Balance</span><span class="value" style="color:#dc3545;">${curr(totalAmt-totalDep)}</span></div>`;

    document.getElementById('summaryContent').innerHTML = html;
}

function showDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('projectModal'));
    const body = document.getElementById('projectModalBody');
    body.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
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
</script>
@endpush
