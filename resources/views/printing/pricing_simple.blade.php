@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Garment Printing Price Calculator
                    </h4>
                    <a href="{{ route('printing.rules', ['type' => $printType]) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-sliders-h me-1"></i> Edit Rules
                    </a>
                </div>
                <div class="card-body">
                    <!-- Print Type Selector -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('printing.pricing', ['type' => 'dtf']) }}" class="btn {{ $printType == 'dtf' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg">
                                    <i class="fas fa-print me-1"></i> DTF Print
                                </a>
                                <a href="{{ route('printing.pricing', ['type' => 'sublimation']) }}" class="btn {{ $printType == 'sublimation' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg">
                                    <i class="fas fa-fire me-1"></i> Sublimation
                                </a>
                                <a href="{{ route('printing.pricing', ['type' => 'silkscreen']) }}" class="btn {{ $printType == 'silkscreen' ? 'btn-primary' : 'btn-outline-primary' }} btn-lg">
                                    <i class="fas fa-palette me-1"></i> Silk Screen
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    @if($userRole === 'sales_agent')
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-user-tie me-1"></i>
                        Logged in as: <strong>Sales Agent</strong>
                        — showing <strong>Agent</strong> prices
                    </div>
                    @endif
                    
                    <!-- Calculator Interface -->
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Select Print Sizes:</h5>
                            <div class="row mb-4">
                                @foreach($prices as $price)
                                @php
                                    $displayPrice = $price->$priceField ?? $price->price;
                                @endphp
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input print-size" 
                                               type="checkbox" 
                                               value="{{ $price->id }}"
                                               id="size_{{ $price->id }}"
                                               data-price="{{ $displayPrice }}"
                                               data-name="{{ $price->name }}">
                                        <label class="form-check-label" for="size_{{ $price->id }}">
                                            <strong>{{ $price->name }}</strong> - ₱{{ number_format($displayPrice, 2) }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Order Summary</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Selected Prints:</td>
                                            <td class="text-end" id="selected-prints">None</td>
                                        </tr>
                                        <tr>
                                            <td>Base Price:</td>
                                            <td class="text-end" id="base-price">₱0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Combo Discount:</td>
                                            <td class="text-end text-success" id="combo-discount">-₱0.00</td>
                                        </tr>
                                        <tr class="table-active">
                                            <td><strong>TOTAL:</strong></td>
                                            <td class="text-end"><strong id="total-price">₱0.00</strong></td>
                                        </tr>
                                    </table>
                                    
                                    <div class="alert alert-info">
                                        <h6>Applied Discounts:</h6>
                                        <div id="applied-discounts">
                                            No discounts applied
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Pricing Rules</h6>
                                </div>
                                <div class="card-body">
                                    <h6>Print Sizes:</h6>
                                    <ul class="list-group mb-3">
                                        @foreach($prices as $price)
                                        @php
                                            $displayPrice = $price->$priceField ?? $price->price;
                                        @endphp
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ $price->name }}</span>
                                            <span>₱{{ number_format($displayPrice, 2) }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                    
                                    <h6>Combo Discounts:</h6>
                                    <ul class="list-group mb-3">
                                        @foreach($comboDiscounts as $combo)
                                        <li class="list-group-item">
                                            {{ $combo->size1->name }} + {{ $combo->size2->name }} = 
                                            <span class="text-success">
                                                -₱{{ number_format($combo->discount_value, 2) }}
                                            </span>
                                        </li>
                                        @endforeach
                                    </ul>
                                    
                                    <h6>Size Upgrades:</h6>
                                    <ul class="list-group mb-3">
                                        @foreach($sizeUpgrades as $upgrade)
                                        <li class="list-group-item">
                                            {{ $upgrade->from_quantity }}x {{ $upgrade->fromSize->name }} → 
                                            1x {{ $upgrade->toSize->name }}
                                        </li>
                                        @endforeach
                                    </ul>
                                    
                                    <h6>Bulk Discounts:</h6>
                                    <ul class="list-group">
                                        @foreach($bulkDiscounts as $bulk)
                                        <li class="list-group-item">
                                            {{ $bulk->min_garments }}-{{ $bulk->max_garments }} garments = 
                                            <span class="text-success">{{ $bulk->discount_percent }}%</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // When print size checkboxes change
    $('.print-size').change(function() {
        calculateTotal();
    });
    
    function calculateTotal() {
        let basePrice = 0;
        let selectedSizes = [];
        let selectedNames = [];
        
        // Calculate base price and collect selected sizes
        $('.print-size:checked').each(function() {
            const price = parseFloat($(this).data('price'));
            const name = $(this).data('name');
            basePrice += price;
            selectedSizes.push($(this).val());
            selectedNames.push(name);
        });
        
        // Update selected prints display
        if (selectedNames.length > 0) {
            $('#selected-prints').text(selectedNames.join(', '));
        } else {
            $('#selected-prints').text('None');
        }
        
        // Calculate combo discount
        let comboDiscount = 0;
        let appliedDiscounts = [];
        
        // Check for Logo + A3 combo
        if (selectedNames.includes('Logo') && selectedNames.includes('A3')) {
            comboDiscount += 30;
            appliedDiscounts.push('Logo + A3: ₱30 discount');
        }
        
        // Check for Logo + A4 combo
        if (selectedNames.includes('Logo') && selectedNames.includes('A4')) {
            comboDiscount += 20;
            appliedDiscounts.push('Logo + A4: ₱20 discount');
        }
        
        // Check for Half A4 + A3 combo
        if (selectedNames.includes('Half A4') && selectedNames.includes('A3')) {
            comboDiscount += 25;
            appliedDiscounts.push('Half A4 + A3: ₱25 discount');
        }
        
        // Calculate total
        const totalPrice = basePrice - comboDiscount;
        
        // Update display
        $('#base-price').text('₱' + basePrice.toFixed(2));
        $('#combo-discount').text('-₱' + comboDiscount.toFixed(2));
        $('#total-price').text('₱' + totalPrice.toFixed(2));
        
        // Update applied discounts
        if (appliedDiscounts.length > 0) {
            $('#applied-discounts').html(
                appliedDiscounts.map(d => `<div>✓ ${d}</div>`).join('')
            );
        } else {
            $('#applied-discounts').text('No discounts applied');
        }
    }
    
    // Initial calculation
    calculateTotal();
});
</script>
@endsection