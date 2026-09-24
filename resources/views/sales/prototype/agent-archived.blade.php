@extends('layouts.app')

@section('title', 'My Archived Projects')

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
            <h4>📦 My Archived Projects</h4>
            <div class="archive-sub">
                {{ $sales->total() }} na-archive na order(s) — mga sarili mong order na ni-archive ng CEO/COO
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <form method="GET" action="{{ route('sales.team.archived') }}" style="display:flex;gap:6px;align-items:center;">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Customer o Sales #" value="{{ request('search') }}" style="min-width:180px;">
                <button type="submit" class="btn btn-sm btn-light" style="border-radius:8px;font-weight:600;">Search</button>
            </form>
            <a href="{{ route('sales.team.dashboard') }}" class="btn btn-sm btn-light" style="border-radius:8px;font-weight:600;">← My Dashboard</a>
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
                                    <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px;">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Wala pang archived project sa iyo. Kapag na-archive ng CEO/COO ang completed order mo, lalabas dito.</td>
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
