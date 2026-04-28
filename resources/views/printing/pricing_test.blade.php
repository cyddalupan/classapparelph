@extends('layouts.app')

@section('title', 'Garment Calculator Test - CLASS Apparel PH')

@section('content')
<style>
.test-badge { font-size: 0.7rem; vertical-align: middle; }
.garment-header { cursor: pointer; }
.garment-header:hover { background: #f0f0f0; }
</style>

<div class="container-fluid">
    <!-- Print Type & Tier Selector -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <select id="test-print-type" class="form-select form-select-sm">
                        <option value="dtf">DTF Print</option>
                        <option value="sublimation">Sublimation</option>
                        <option value="silkscreen">Silk Screen</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="test-price-tier" class="form-select form-select-sm">
                        <option value="sales_team">🏢 Sales Team Pricing</option>
                        <option value="agent">👤 Agent Pricing</option>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-warning text-dark me-2">🧪 TEST MODE</span>
                    <a href="{{ route('printing.rules') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-sliders-h"></i> Edit Rules
                    </a>
                    <a href="{{ route('printing.pricing') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calculator"></i> Live Calculator
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- LEFT: Calculator -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-flask me-2"></i>
                        Garment Printing Price Calculator (TEST)
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Garment List -->
                    <div id="test-garment-list">
                        <div class="garment-item card mb-3" data-index="0">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Garment #1</h6>
                                <button type="button" class="btn btn-sm btn-danger remove-garment" style="display: none;">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row print-size-row">
                                    @foreach($prices as $price)
                                    @php
                                        $displayPrice = $price->$priceField ?? $price->price;
                                    @endphp
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input print-checkbox" 
                                                   type="checkbox" 
                                                   value="{{ $price->id }}" 
                                                   id="print_{{ $price->id }}_0"
                                                   data-price="{{ $displayPrice }}"
                                                   data-name="{{ $price->name }}">
                                            <label class="form-check-label small" for="print_{{ $price->id }}_0">
                                                {{ $price->name }}
                                                <span class="text-primary">₱{{ number_format($displayPrice, 2) }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="alert alert-info mb-0 py-2 px-3">
                                            <div class="d-flex justify-content-between">
                                                <span>Garment Subtotal:</span>
                                                <strong id="garment-subtotal-0">₱0.00</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span>Combo Discount:</span>
                                                <strong id="garment-discount-0" class="text-success">₱0.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add Garment Button -->
                    <div class="mb-3">
                        <button type="button" id="add-garment" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Add Another Garment
                        </button>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Number of Garments:</td>
                                            <td class="text-end"><strong id="garment-count">1</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Subtotal:</td>
                                            <td class="text-end"><strong id="order-subtotal">₱0.00</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Bulk Discount:</td>
                                            <td class="text-end text-success"><strong id="bulk-discount">₱0.00</strong></td>
                                        </tr>
                                        <tr class="table-active">
                                            <td><h5 class="mb-0">TOTAL:</h5></td>
                                            <td class="text-end"><h5 class="mb-0 text-primary" id="order-total">₱0.00</h5></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-warning mb-0">
                                        <h6 class="mb-2">Applied Discounts:</h6>
                                        <div id="applied-discounts">
                                            <div class="text-muted">No discounts applied yet</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" id="calculate-btn" class="btn btn-primary">
                                    <i class="fas fa-calculator me-1"></i> Calculate Total (Server)
                                </button>
                                <button type="button" id="reset-btn" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </button>
                                <button type="button" id="copy-total" class="btn btn-outline-success">
                                    <i class="fas fa-copy me-1"></i> Copy Total
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- RIGHT: Rules Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Active Pricing Rules
                    </h4>
                </div>
                <div class="card-body" id="rules-summary">
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-spinner fa-spin me-1"></i> Loading rules...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let garmentIndex = 0;
    
    // Load rules summary
    loadRulesSummary();
    
    $('#test-print-type, #test-price-tier').change(function() {
        loadRulesSummary();
    });
    
    function loadRulesSummary() {
        const printType = $('#test-print-type').val();
        
        $('#rules-summary').html('<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-1"></i> Loading rules...</div>');
        
        $.ajax({
            url: '{{ route("printing.get-product-pricing") }}',
            method: 'GET',
            data: { print_type: printType },
            success: function(res) {
                const data = res.data || res;
                
                let html = '';
                
                // Combo Discounts
                const combos = data.combo_discounts || [];
                html += '<h6 class="border-bottom pb-1"><i class="fas fa-link me-1"></i> Combo Discounts</h6>';
                if (combos.length === 0) {
                    html += '<p class="small text-muted">None set</p>';
                } else {
                    html += '<ul class="list-unstyled small mb-3">';
                    combos.forEach(function(c) {
                        const s1 = c.size1?.name || '?';
                        const s2 = c.size2?.name || '?';
                        const val = c.discount_type === 'fixed' ? '₱' + parseFloat(c.discount_value).toFixed(2) : c.discount_value + '%';
                        html += `<li class="mb-1">✅ ${s1} + ${s2} <span class="text-success">(-${val})</span></li>`;
                    });
                    html += '</ul>';
                }
                
                // Size Upgrades
                const upgrades = data.size_upgrades || [];
                html += '<h6 class="border-bottom pb-1"><i class="fas fa-arrow-up me-1"></i> Size Upgrades</h6>';
                if (upgrades.length === 0) {
                    html += '<p class="small text-muted">None set</p>';
                } else {
                    html += '<ul class="list-unstyled small mb-3">';
                    upgrades.forEach(function(u) {
                        const from = u.from_size_name || u.fromSize?.name || '?';
                        const to = u.to_size_name || u.toSize?.name || '?';
                        html += `<li class="mb-1">⬆️ ${u.from_quantity}x ${from} → 1x ${to}</li>`;
                    });
                    html += '</ul>';
                }
                
                // Bulk Discounts
                const bulks = data.bulk_discounts || [];
                html += '<h6 class="border-bottom pb-1"><i class="fas fa-boxes me-1"></i> Bulk Discounts</h6>';
                const tier = $('#test-price-tier').val();
                const filtered = bulks.filter(function(b) { return b.price_tier === tier; });
                if (filtered.length === 0) {
                    html += '<p class="small text-muted">None for this tier</p>';
                } else {
                    html += '<ul class="list-unstyled small mb-0">';
                    filtered.forEach(function(b) {
                        const disc = b.discount_type === 'percentage' ? b.discount_percent + '%' : '₱' + parseFloat(b.discount_amount).toFixed(2);
                        html += `<li class="mb-1">📦 ${b.min_transactions}-${b.max_transactions} garments → <span class="text-success">${disc}</span></li>`;
                    });
                    html += '</ul>';
                }
                
                $('#rules-summary').html(html);
            },
            error: function() {
                $('#rules-summary').html('<div class="text-danger">❌ Failed to load rules</div>');
            }
        });
    }
    
    // Add Garment
    $('#add-garment').click(function() {
        garmentIndex++;
        const newIndex = garmentIndex;
        
        const template = `
        <div class="garment-item card mb-3" data-index="${newIndex}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Garment #${newIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger remove-garment">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
            <div class="card-body">
                <div class="row print-size-row">
                    @foreach($prices as $price)
                    @php
                        $displayPrice = $price->$priceField ?? $price->price;
                    @endphp
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input print-checkbox" 
                                   type="checkbox" 
                                   value="{{ $price->id }}" 
                                   id="print_{{ $price->id }}_${newIndex}"
                                   data-price="{{ $displayPrice }}"
                                   data-name="{{ $price->name }}">
                            <label class="form-check-label small" for="print_{{ $price->id }}_${newIndex}">
                                {{ $price->name }}
                                <span class="text-primary">₱{{ number_format($displayPrice, 2) }}</span>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="alert alert-info mb-0 py-2 px-3">
                            <div class="d-flex justify-content-between">
                                <span>Garment Subtotal:</span>
                                <strong id="garment-subtotal-${newIndex}">₱0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span>Combo Discount:</span>
                                <strong id="garment-discount-${newIndex}" class="text-success">₱0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        
        $('#test-garment-list').append(template);
        $('.remove-garment').show();
        $('#garment-count').text($('.garment-item').length);
    });
    
    // Remove Garment
    $(document).on('click', '.remove-garment', function() {
        $(this).closest('.garment-item').remove();
        renumberGarments();
    });
    
    function renumberGarments() {
        $('.garment-item').each(function(idx) {
            $(this).attr('data-index', idx);
            $(this).find('.card-header h6').text('Garment #' + (idx + 1));
        });
        if ($('.garment-item').length <= 1) {
            $('.remove-garment').hide();
        }
        $('#garment-count').text($('.garment-item').length);
    }
    
    // Reset
    $('#reset-btn').click(function() {
        $('.garment-item:not(:first)').remove();
        $('.garment-item:first').find('.print-checkbox').prop('checked', false);
        renumberGarments();
        $('#order-subtotal').text('₱0.00');
        $('#bulk-discount').text('₱0.00');
        $('#order-total').text('₱0.00');
        $('#applied-discounts').html('<div class="text-muted">No discounts applied yet</div>');
        $('[id^="garment-subtotal-"]').text('₱0.00');
        $('[id^="garment-discount-"]').text('₱0.00');
    });
    
    // Calculate via Server
    $('#calculate-btn').click(function() {
        const printType = $('#test-print-type').val();
        const garments = [];
        
        $('.garment-item').each(function() {
            const prints = [];
            $(this).find('.print-checkbox:checked').each(function() {
                prints.push(parseInt($(this).val()));
            });
            if (prints.length > 0) {
                garments.push({ prints: prints });
            }
        });
        
        if (garments.length === 0) {
            alert('Please select at least one print size on at least one garment.');
            return;
        }
        
        $('#calculate-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Calculating...');
        
        $.ajax({
            url: '{{ route("printing.calculate") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                garments: garments,
                type: printType
            },
            success: function(res) {
                displayResults(res);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || xhr.responseText || 'Server error';
                alert('Calculation error: ' + msg);
            },
            complete: function() {
                $('#calculate-btn').prop('disabled', false).html('<i class="fas fa-calculator me-1"></i> Calculate Total (Server)');
            }
        });
    });
    
    function displayResults(data) {
        const breakdown = data.breakdown || [];
        const total = parseFloat(data.total || 0);
        const bulkDiscount = parseFloat(data.bulk_discount || 0);
        const garmentCount = data.garment_count || breakdown.length;
        
        // Update each garment's subtotal and combo discount
        breakdown.forEach(function(g) {
            const idx = g.garment_number - 1;
            $(`#garment-subtotal-${idx}`).text('₱' + parseFloat(g.subtotal).toFixed(2));
            $(`#garment-discount-${idx}`).text('-₱' + parseFloat(g.combo_discount).toFixed(2));
            
            // Show upgraded prints if any
            if ((g.upgraded_prints || []).length > 0) {
                g.upgraded_prints.forEach(function(up) {
                    console.log('Upgrade on garment #' + g.garment_number + ': ' + up.from_quantity + 'x ' + up.from + ' -> ' + up.to);
                });
            }
        });
        
        // Update order summary
        const subtotalWithoutBulk = total + bulkDiscount;
        $('#garment-count').text(garmentCount);
        $('#order-subtotal').text('₱' + subtotalWithoutBulk.toFixed(2));
        $('#bulk-discount').text('-₱' + bulkDiscount.toFixed(2));
        $('#order-total').text('₱' + total.toFixed(2));
        
        // Build applied discounts list
        let discounts = [];
        
        // Check which garments got combo discounts
        breakdown.forEach(function(g) {
            if (parseFloat(g.combo_discount) > 0) {
                discounts.push('Garment #' + g.garment_number + ': Combo discount -₱' + parseFloat(g.combo_discount).toFixed(2));
            }
        });
        
        if (bulkDiscount > 0) {
            discounts.push('Bulk order: ₱' + bulkDiscount.toFixed(2) + ' discount (' + garmentCount + ' garments)');
        }
        
        // Size upgrades
        breakdown.forEach(function(g) {
            const upgrades = g.upgraded_prints || [];
            if (upgrades.length > 0) {
                upgrades.forEach(function(u) {
                    discounts.push('Garment #' + g.garment_number + ': ' + u.from_quantity + 'x ' + u.from + ' → 1x ' + u.to);
                });
            }
        });
        
        const html = discounts.length > 0 
            ? discounts.map(function(d) { return '<div class="mb-1">✓ ' + d + '</div>'; }).join('')
            : '<div class="text-muted">No discounts applied yet</div>';
        
        $('#applied-discounts').html(html);
    }
    
    // Copy total
    $('#copy-total').click(function() {
        const total = $('#order-total').text();
        navigator.clipboard.writeText(total).then(function() {
            alert('Total copied: ' + total);
        }).catch(function() {
            alert('Failed to copy. Total: ' + total);
        });
    });
});
</script>
@endsection
