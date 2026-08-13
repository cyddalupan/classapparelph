@extends('layouts.app')

@section('title', 'Delay List — All Delayed Orders')

@section('content')
<style>
    .dl-header {
        background: linear-gradient(135deg, #2d1b1b 0%, #4a2323 60%, #6b2d2d 100%);
        border-radius: 14px;
        padding: 20px 24px;
        color: #fff;
        box-shadow: 0 6px 18px rgba(220, 53, 69, 0.18);
        position: relative;
        overflow: hidden;
    }
    .dl-header::after {
        content: '⚠️';
        position: absolute;
        right: 18px;
        bottom: -14px;
        font-size: 72px;
        opacity: 0.12;
        transform: rotate(-10deg);
    }
    .dl-header h2 { font-weight: 800; letter-spacing: 0.3px; }
    .dl-stat {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 10px;
        padding: 8px 14px;
        text-align: center;
        backdrop-filter: blur(4px);
    }
    .dl-stat .num { font-size: 1.35rem; font-weight: 800; line-height: 1; }
    .dl-stat .lbl { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; }
    .dl-filter-btn {
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1.5px solid #dee2e6;
        background: #fff;
        color: #495057;
        transition: all .15s ease;
    }
    .dl-filter-btn:hover { border-color: #dc3545; color: #dc3545; }
    .dl-filter-btn.active { background: #dc3545; border-color: #dc3545; color: #fff; box-shadow: 0 3px 10px rgba(220,53,69,0.3); }

    .dl-card {
        border: 1px solid #e9ecef;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform .15s ease, box-shadow .15s ease;
        display: flex;
        flex-direction: column;
    }
    .dl-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(0,0,0,0.10); }
    .dl-card.no-feedback { border-left: 5px solid #adb5bd; }
    .dl-card.has-feedback { border-left: 5px solid #28a745; }

    .dl-card-img-wrap {
        height: 130px;
        background: linear-gradient(160deg, #f8f9fa, #e9ecef);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border-bottom: 1px solid #f1f3f5;
    }
    .dl-card-img-wrap img { max-height: 120px; max-width: 100%; object-fit: contain; }
    .dl-days {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #dc3545;
        color: #fff;
        border-radius: 10px;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 700;
        box-shadow: 0 3px 8px rgba(220,53,69,0.4);
    }
    .dl-days.overdue { background: #212529; }
    .dl-noimg {
        color: #adb5bd;
        font-size: 2rem;
    }
    .dl-feedback-box {
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.82rem;
        line-height: 1.45;
    }
    .dl-feedback-box.ok { background: #f0faf3; border: 1px solid #c9ecd3; color: #1c5e2f; }
    .dl-feedback-box.missing { background: #f8f9fa; border: 1px dashed #ced4da; color: #6c757d; }
    .dl-meta { font-size: 0.78rem; color: #6c757d; }
    .dl-stage-chip {
        display: inline-block;
        border-radius: 6px;
        padding: 2px 8px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #fff;
    }
    .dl-prio-chip {
        display: inline-block;
        border-radius: 6px;
        padding: 2px 8px;
        font-size: 0.7rem;
        font-weight: 700;
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffda6a;
    }
    .dl-balance { font-size: 0.8rem; font-weight: 700; color: #dc3545; }
    .dl-deposit { font-size: 0.8rem; font-weight: 700; color: #28a745; }
</style>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="dl-header mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <a href="{{ route('sales.prototype.list') }}" class="btn btn-sm text-white mb-2" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);"><i class="fas fa-arrow-left me-1"></i> Manager List</a>
                <h2 class="mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Delay List</h2>
                <div class="small" style="opacity:0.85;">Lahat ng delayed orders — may feedback man o wala</div>
            </div>
            <div class="d-flex gap-2">
                <div class="dl-stat">
                    <div class="num">{{ $sales->total() }}</div>
                    <div class="lbl">Delayed</div>
                </div>
                <div class="dl-stat">
                    <div class="num" style="color:#7ef0a3;">{{ $sales->filter(function($s) { return !empty($s->delay_feedback); })->count() }}</div>
                    <div class="lbl">May Feedback</div>
                </div>
                <div class="dl-stat">
                    <div class="num" style="color:#ffd6a0;">{{ $sales->filter(function($s) { return empty($s->delay_feedback); })->count() }}</div>
                    <div class="lbl">Walang Feedback</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter tabs -->
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <button class="dl-filter-btn active" data-filter="all" onclick="filterDelayCards('all', this)">All</button>
        <button class="dl-filter-btn" data-filter="has" onclick="filterDelayCards('has', this)">✔ May Feedback</button>
        <button class="dl-filter-btn" data-filter="none" onclick="filterDelayCards('none', this)">✖ Walang Feedback</button>
    </div>

    @if($sales->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-0 text-muted">Walang delayed orders. 🎉</p>
            </div>
        </div>
    @else
    <div class="row g-3">
        @foreach($sales as $sale)
            @php
                $hasFeedback = !empty($sale->delay_feedback);
                $delayedAt = $sale->delayed_at ? \Carbon\Carbon::parse($sale->delayed_at) : null;
                $daysDelayed = $delayedAt ? (int) $delayedAt->startOfDay()->diffInDays(now()->startOfDay()) : 0;
                $stageLabel = $sale->production_stage ?: 'HOLD';
                $stageColors = [
                    'FOR SAMPLE' => '#6f42c1', 'FOR APPROVAL' => '#fd7e14', 'FOR FORMAT' => '#0d6efd',
                    'PRINTING' => '#198754', 'PRESSING' => '#20c997', 'CUTTING' => '#6c757d',
                    'SEWING' => '#dc3545', 'QA' => '#ffc107', 'HOLD' => '#6c757d',
                    'DISPATCH' => '#0dcaf0', 'UNPAID' => '#d63384', 'DONE' => '#198754',
                ];
                $stageColor = $stageColors[$stageLabel] ?? '#6c757d';
                $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                $mainMockup = null;
                foreach ((array)$mockups as $m) {
                    if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
                }
                if (!$mainMockup && !empty($mockups)) $mainMockup = $mockups[0];
                $mockupUrl = is_string($mainMockup) ? $mainMockup : ($mainMockup['url'] ?? '');
                $balanceDue = $sale->balance_due_computed ?? ($sale->balance_due ?? 0);
            @endphp
            <div class="col-md-6 col-xl-4 d-flex">
                <div class="dl-card w-100 {{ $hasFeedback ? 'has-feedback' : 'no-feedback' }}" data-fb="{{ $hasFeedback ? 'has' : 'none' }}">
                    <!-- Thumbnail -->
                    <div class="dl-card-img-wrap">
                        <span class="dl-days {{ $daysDelayed > 14 ? 'overdue' : '' }}">{{ $daysDelayed }}d delayed</span>
                        @if($mockupUrl)
                            <img src="{{ $mockupUrl }}" alt="mockup" onerror="this.outerHTML='<span class=&quot;dl-noimg&quot;><i class=&quot;fas fa-image&quot;></i></span>'">
                        @else
                            <span class="dl-noimg"><i class="fas fa-tshirt"></i></span>
                        @endif
                        @if($sale->priority)
                            <span class="dl-prio-chip" style="position:absolute;top:10px;right:10px;">🚩 Prio {{ $sale->priority }}</span>
                        @endif
                    </div>

                    <!-- Body -->
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <strong class="fs-6">{{ $sale->sales_number }}</strong>
                                <div class="dl-meta"><i class="fas fa-user me-1"></i>{{ $sale->customer_name ?: '—' }}</div>
                            </div>
                            <span class="dl-stage-chip" style="background:{{ $stageColor }};">{{ $stageLabel }}</span>
                        </div>

                        <div class="dl-meta mb-2">
                            <div><i class="fas fa-user-tie me-1"></i>Agent: {{ $sale->sales_agent_name ?: '—' }}</div>
                            <div><i class="fas fa-building me-1"></i>{{ $sale->department_name ?: '—' }} · <i class="far fa-clock me-1"></i>{{ $delayedAt ? $delayedAt->format('M d, h:i A') : '—' }}</div>
                        </div>

                        <!-- Feedback -->
                        @if($hasFeedback)
                            <div class="dl-feedback-box ok mb-2 flex-grow-1">
                                <i class="fas fa-comment-dots me-1"></i><strong>Feedback:</strong>
                                <div style="white-space:pre-wrap;">{{ \Illuminate\Support\Str::limit($sale->delay_feedback, 120) }}</div>
                            </div>
                        @else
                            <div class="dl-feedback-box missing mb-2 flex-grow-1">
                                <i class="fas fa-exclamation-circle me-1"></i><strong>Walang feedback</strong> — walang dahilan na nakalagay sa delay na ito.
                            </div>
                        @endif

                        <!-- Footer -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="d-flex gap-2">
                                <span class="dl-deposit"><i class="fas fa-hand-holding-usd me-1"></i>{{ number_format($sale->deposit_paid ?? 0) }}</span>
                                <span class="dl-balance"><i class="fas fa-exclamation-circle me-1"></i>{{ number_format($balanceDue) }}</span>
                            </div>
                            <a href="{{ route('sales.prototype.delay-review', $sale->id) }}" class="btn btn-sm btn-danger" style="border-radius:20px;padding:4px 14px;"><i class="fas fa-eye me-1"></i> Review</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $sales->links() }}
    </div>
    @endif
</div>

<script>
function filterDelayCards(filter, btn) {
    document.querySelectorAll('.dl-filter-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.dl-card').forEach(function(card) {
        var show = filter === 'all' || card.getAttribute('data-fb') === filter;
        card.closest('.col-md-6').style.display = show ? '' : 'none';
    });
}
</script>
@endsection
