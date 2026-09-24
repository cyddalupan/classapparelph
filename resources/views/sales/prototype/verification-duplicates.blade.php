@extends('layouts.app')

@section('title', 'Duplicate Reference Report')

@push('styles')
<style>
    .dup-token { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; font-size: 1rem; color: #3a2d6b; letter-spacing: .04em; }
    .dup-row { transition: background .15s; }
    .dup-row:hover { background: #faf9ff; }
    .dup-rec { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; padding: .25rem 0; border-bottom: 1px dashed #eee; }
    .dup-rec:last-child { border-bottom: none; }
    .src-badge { font-size: .65rem; text-transform: uppercase; letter-spacing: .03em; padding: .15rem .45rem; border-radius: 50px; font-weight: 700; }
    .src-sale { background: #e7f1ff; color: #0d4ea6; }
    .src-payment { background: #eafaf1; color: #157347; }
    .src-layout { background: #f3e8ff; color: #6f42c1; }
    .dup-summary-chip { border-radius: 12px; padding: .6rem .9rem; border: 1px solid #e9ecef; background: #fff; }
    .dup-summary-chip .kpi { font-size: 1.4rem; font-weight: 800; line-height: 1; }
    .flag-pill { font-size: .62rem; padding: .12rem .4rem; border-radius: 50px; font-weight: 700; }
    @media (max-width: 767.98px) {
        .page-content:has(> .container-fluid) { padding-left: 8px !important; padding-right: 8px !important; }
        .dup-table thead { display: none; }
        .dup-table, .dup-table tbody, .dup-table tr, .dup-table td { display: block; width: 100%; }
        .dup-table tr { border: 1px solid #eee; border-radius: 10px; margin-bottom: .6rem; padding: .4rem .6rem; }
        .dup-table td { border: none !important; padding: .25rem 0 !important; }
        .dup-table td::before { content: attr(data-label); display: block; font-size: .65rem; text-transform: uppercase; color: #999; font-weight: 700; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-clone text-danger me-2"></i>Duplicate Reference Report</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">
                Hanapin ang mga ref # na lumalabas nang higit sa isang beses (verified o pending) —
                parehong detection na ginagamit ng Payment Verification page. Read-only.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.verification') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Payment Verification
            </a>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="dup-summary-chip">
                <div class="kpi text-danger">{{ $summary['tokens'] }}</div>
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.03em;">Duplicate refs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dup-summary-chip">
                <div class="kpi" style="color:#6f42c1;">{{ $summary['verified_flags'] }}</div>
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.03em;">Verified records flagged</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dup-summary-chip">
                <div class="kpi text-warning">{{ $summary['pending_flags'] }}</div>
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.03em;">Pending records flagged</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dup-summary-chip">
                <div class="kpi text-secondary">{{ number_format($summary['total_records']) }}</div>
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.03em;">Records scanned</div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('sales.verification.duplicates') }}" class="d-flex gap-2 flex-wrap align-items-center">
                <div class="input-group" style="max-width: 460px;">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ $q }}" class="form-control"
                           placeholder="Ref #, sales #, o customer..." autofocus>
                </div>
                <button class="btn btn-danger"><i class="fas fa-search"></i> Search</button>
                @if($q !== '')
                    <a href="{{ route('sales.verification.duplicates') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
                @if($q !== '')
                    <span class="text-muted" style="font-size:.82rem;">Results para sa "<strong>{{ $q }}</strong>"</span>
                @endif
            </form>
        </div>
    </div>

    {{-- Report --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>
                {{ count($report) }} duplicate reference{{ count($report) == 1 ? '' : 's' }}
            </h5>
        </div>
        <div class="card-body p-0">
            @if(empty($report))
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                    <div>{{ $q !== '' ? 'Walang duplicate na tumugma sa search.' : 'Wala pang duplicate reference na naka-flag.' }}</div>
                </div>
            @else
            <div class="table-responsive">
                <table class="table align-middle mb-0 dup-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:150px;">Ref #</th>
                            <th style="width:90px;"># Sales</th>
                            <th>Records na may parehong ref</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $row)
                        <tr class="dup-row">
                            <td data-label="Ref #">
                                <span class="dup-token">{{ $row['token'] }}</span>
                            </td>
                            <td data-label="# Sales">
                                @if($row['distinct_sales'] >= 2)
                                    <span class="badge bg-danger">{{ $row['distinct_sales'] }} sales</span>
                                @else
                                    <span class="badge bg-warning text-dark">same sale</span>
                                @endif
                            </td>
                            <td data-label="Records">
                                @foreach($row['records'] as $r)
                                    @php
                                        $srcLabel = ['sale' => 'Sale', 'payment' => 'Payment', 'layout' => 'Layout'][$r['source']] ?? $r['source'];
                                        $srcClass = 'src-' . $r['source'];
                                        $statusLabel = str_replace('_', ' ', $r['status']);
                                        $isVerified = $r['bucket'] === 'verified';
                                    @endphp
                                    <div class="dup-rec">
                                        <span class="src-badge {{ $srcClass }}">{{ $srcLabel }}</span>

                                        @if($r['sales_number'])
                                            <a href="{{ route('sales.prototype.show', $r['sale_id']) }}" style="font-weight:600;font-size:.82rem;">
                                                {{ $r['sales_number'] }}
                                            </a>
                                        @elseif($r['job_no'])
                                            <span style="font-weight:600;font-size:.82rem;">{{ $r['job_no'] }}</span>
                                        @else
                                            <span class="text-muted" style="font-size:.82rem;">—</span>
                                        @endif

                                        <span class="text-muted" style="font-size:.8rem;">{{ $r['customer_name'] ?: '' }}</span>

                                        <span class="badge {{ $isVerified ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size:.65rem;">
                                            {{ $statusLabel }}
                                        </span>

                                        <span class="text-muted" style="font-size:.75rem;font-family:ui-monospace,monospace;">{{ $r['ref'] }}</span>

                                        @if($r['flagged'])
                                            @if($r['dup_verified'])
                                                <span class="flag-pill bg-danger text-white" title="May katugmang NABERIFY nang ref">
                                                    ⚠ dup verified ({{ $r['dup_verified'] }})
                                                </span>
                                            @endif
                                            @if($r['dup_pending'])
                                                <span class="flag-pill bg-warning text-dark" title="May katugmang PENDING pa lang na ref">
                                                    ⚠ dup pending ({{ $r['dup_pending'] }})
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <p class="text-muted mt-3 mb-0" style="font-size:.75rem;">
        <i class="fas fa-info-circle"></i>
        Ang duplicate ay base sa parehong 6-digit (o higit pa) na token sa loob ng ref #. Ang sale at payment row ng
        parehong bayad (mirror) ay hindi binibilang na duplicate. Read-only ang page na ito — walang nababago sa data.
    </p>
</div>
@endsection
