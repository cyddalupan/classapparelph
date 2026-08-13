@extends('layouts.app')

@section('title', 'Delay List — All Delayed Orders')

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <a href="{{ route('sales.prototype.list') }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="fas fa-arrow-left me-1"></i> Back to Manager List</a>
            <h2 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Delay List</h2>
            <div class="text-muted small mt-1">Lahat ng delayed orders — may feedback man o wala</div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-danger" style="font-size:0.9rem;">{{ $sales->total() }} delayed</span>
            <span class="badge bg-success" style="font-size:0.9rem;">
                {{ $sales->filter(function($s) { return !empty($s->delay_feedback); })->count() }} may feedback
            </span>
            <span class="badge bg-secondary" style="font-size:0.9rem;">
                {{ $sales->filter(function($s) { return empty($s->delay_feedback); })->count() }} walang feedback
            </span>
        </div>
    </div>

    @if($sales->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-0 text-muted">Walang delayed orders. 🎉</p>
            </div>
        </div>
    @else
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th>Sales #</th>
                        <th>Customer</th>
                        <th>Agent</th>
                        <th>Department</th>
                        <th>Marked Delayed</th>
                        <th>Feedback</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr style="{{ !empty($sale->delay_feedback) ? 'background:#fff9f9;' : '' }}">
                        <td>
                            <span class="badge bg-danger">⚠️ DELAYED</span>
                        </td>
                        <td>
                            <strong>{{ $sale->sales_number }}</strong>
                            <div class="text-muted small">{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}</div>
                        </td>
                        <td>{{ $sale->customer_name ?: '—' }}</td>
                        <td>{{ $sale->sales_agent_name ?: '—' }}</td>
                        <td>{{ $sale->department_name ?: '—' }}</td>
                        <td>{{ $sale->delayed_at ? \Carbon\Carbon::parse($sale->delayed_at)->format('M d, Y h:i A') : '—' }}</td>
                        <td style="max-width:280px;">
                            @if(!empty($sale->delay_feedback))
                                <span class="badge bg-success mb-1">✔ May feedback</span>
                                <div class="text-muted small" style="white-space:normal;word-break:break-word;">{{ \Illuminate\Support\Str::limit($sale->delay_feedback, 80) }}</div>
                            @else
                                <span class="badge bg-secondary">✖ Walang feedback</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('sales.prototype.delay-review', $sale->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i> Review</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $sales->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
