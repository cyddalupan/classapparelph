@extends('layouts.app')

@section('title', 'Archived Projects')

@push('styles')
<style>
    .archive-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
        background: linear-gradient(135deg, #343a40 0%, #495057 60%, #6c757d 100%);
        border-radius: 14px;
        padding: 18px 24px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    }
    .archive-header h4 {
        margin: 0;
        color: #fff;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .archive-header .archive-sub {
        color: rgba(255,255,255,0.75);
        font-size: 13px;
        margin-top: 3px;
    }
    .archive-table { font-size: 13px; }
    .archive-table th { white-space: nowrap; background: #f8f9fa; }
    .archive-table td { vertical-align: middle; }
    .archived-badge {
        background: #e2e3e5;
        color: #383d41;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="archive-header">
        <div>
            <h4>📦 Archived Projects</h4>
            <div class="archive-sub">
                {{ $sales->total() }} archived project(s) — naka-store dito ang mga na-archive na completed orders
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <form method="GET" action="{{ route('sales.prototype.archived') }}" style="display:flex;gap:6px;align-items:center;">
                <select name="department" class="form-select form-select-sm" style="min-width:140px;" onchange="this.form.submit()">
                    <option value="all">All Departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('sales.prototype.kanban') }}" class="btn btn-sm btn-light" style="border-radius:8px;font-weight:600;">← Kanban Board</a>
        </div>
    </div>

    <!-- Archive Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover archive-table mb-0">
                    <thead>
                        <tr>
                            <th>Sales #</th>
                            <th>Customer</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Archived At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            @php
                                $svc = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
                                $itemCount = count($svc);
                                $deptName = $sale->department_name ?: '—';
                                $archivedLabel = $sale->archived_at ? \Carbon\Carbon::parse($sale->archived_at)->format('M d, Y h:i A') : '—';
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $sale->sales_number ?: '#' . $sale->id }}</strong>
                                    <div style="font-size:11px;color:#6c757d;">{{ $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') : '' }}</div>
                                </td>
                                <td>{{ $sale->customer_name ?: '—' }}</td>
                                <td><span class="badge bg-secondary">{{ $deptName }}</span></td>
                                <td>{{ $itemCount ? $itemCount . ' item(s)' : '—' }}</td>
                                <td>₱{{ number_format($sale->total_amount ?? $sale->subtotal ?? 0, 2) }}</td>
                                <td>
                                    @if($sale->net_paid > 0)
                                        <span style="color:#198754;font-weight:600;">₱{{ number_format($sale->net_paid, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><span class="archived-badge">📦 {{ $archivedLabel }}</span></td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px;">View</a>
                                        <button type="button" class="btn btn-sm btn-outline-success restore-btn" data-sale-id="{{ $sale->id }}" style="font-size:11px;padding:2px 8px;" onclick="restoreSale(this)">↩ Restore</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Walang archived projects — i-archive ang completed projects mula sa kanban board.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $sales->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function restoreSale(btn) {
    var saleId = btn.getAttribute('data-sale-id');
    if (!saleId) return;
    if (!confirm('↩ I-restore ang project na ito pabalik sa kanban board (Completed column)?')) return;
    btn.disabled = true;
    btn.textContent = '⏳...';
    fetch('/sales/prototype/' + saleId + '/restore', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        }
    })
    .then(function(r) { return r.json().catch(function() { return {}; }).then(function(d) { return { ok: r.ok, data: d }; }); })
    .then(function(res) {
        if (res.ok && res.data.success) {
            var row = btn.closest('tr');
            if (row) row.remove();
            var toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:#198754;color:#fff;padding:12px 18px;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,0.25);font-size:14px;font-weight:600;';
            toast.textContent = res.data.message;
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 4000);
        } else {
            btn.disabled = false;
            btn.textContent = '↩ Restore';
            alert('⚠️ ' + (res.data.message || 'Failed to restore.'));
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = '↩ Restore';
        alert('❌ Network error. Please try again.');
    });
}
</script>
@endpush
