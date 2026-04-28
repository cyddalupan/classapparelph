@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Left Side: Calculator -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Garment Printing Price Calculator
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Garment List -->
                    <div id="garment-list">
                        <div class="garment-item card mb-3" data-index="0">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Garment #1</h6>
                                    <button type="button" class="btn btn-sm btn-danger remove-garment" style="display: none;">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Print Size Selection -->
                                <div class="row">
                                    @foreach($prices as $price)
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input print-checkbox" 
                                                   type="checkbox" 
                                                   value="{{ $price->id }}" 
                                                   id="print_{{ $price->id }}_0"
                                                   data-price="{{ $price->price }}"
                                                   data-name="{{ $price->name }}">
                                            <label class="form-check-label" for="print_{{ $price->id }}_0">
                                                <strong>{{ $price->name }}</strong> - ₱{{ number_format($price->price, 2) }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Garment Subtotal -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-info mb-0">
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
                    
                    <!-- Calculation Results -->
                    <div class="card mt-4">
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
                                    <div class="alert alert-warning">
                                        <h6 class="mb-2">Applied Discounts:</h6>
                                        <div id="applied-discounts">
                                            <div class="text-muted">No discounts applied yet</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" id="calculate-btn" class="btn btn-primary">
                                    <i class="fas fa-calculator me-1"></i> Calculate Total
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
        
        <!-- Right Side: Rules Configuration -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Pricing Rules Configuration
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Print Size Prices -->
                    <div class="mb-4">
                        <h5><i class="fas fa-tag me-1"></i> Print Size Prices</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prices as $price)
                                    <tr>
                                        <td>{{ $price->name }}</td>
                                        <td class="text-end">₱{{ number_format($price->price, 2) }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-price" 
                                                    data-id="{{ $price->id }}"
                                                    data-name="{{ $price->name }}"
                                                    data-price="{{ $price->price }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addPriceModal">
                            <i class="fas fa-plus me-1"></i> Add New Size
                        </button>
                    </div>
                    
                    <!-- Combo Discounts -->
                    <div class="mb-4">
                        <h5><i class="fas fa-percentage me-1"></i> Combo Discounts</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Size 1</th>
                                        <th>Size 2</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comboDiscounts as $combo)
                                    <tr>
                                        <td>{{ $combo->size1->name }}</td>
                                        <td>{{ $combo->size2->name }}</td>
                                        <td class="text-end text-success">
                                            @if($combo->discount_type === 'fixed')
                                            -₱{{ number_format($combo->discount_value, 2) }}
                                            @else
                                            -{{ $combo->discount_value }}%
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-combo" data-id="{{ $combo->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addComboModal">
                            <i class="fas fa-plus me-1"></i> Add Combo Rule
                        </button>
                    </div>
                    
                    <!-- Size Upgrades -->
                    <div class="mb-4">
                        <h5><i class="fas fa-arrow-up me-1"></i> Size Upgrades</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>From</th>
                                        <th>To</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sizeUpgrades as $upgrade)
                                    <tr>
                                        <td>{{ $upgrade->from_quantity }}x {{ $upgrade->fromSize->name }}</td>
                                        <td>1x {{ $upgrade->toSize->name }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-upgrade" data-id="{{ $upgrade->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addUpgradeModal">
                            <i class="fas fa-plus me-1"></i> Add Upgrade Rule
                        </button>
                    </div>
                    
                    <!-- Bulk Discounts -->
                    <div class="mb-4">
                        <h5><i class="fas fa-boxes me-1"></i> Bulk Discounts</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Garments</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bulkDiscounts as $bulk)
                                    <tr>
                                        <td>{{ $bulk->min_garments }} - {{ $bulk->max_garments }}</td>
                                        <td class="text-end text-success">{{ $bulk->discount_percent }}%</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-bulk" data-id="{{ $bulk->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addBulkModal">
                            <i class="fas fa-plus me-1"></i> Add Bulk Rule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for adding/editing rules -->
@include('printing.modals.price')
@include('printing.modals.combo')
@include('printing.modals.upgrade')
@include('printing.modals.bulk')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let garmentCount = 1;
    
    // Add new garment
    $('#add-garment').click(function() {
        garmentCount++;
        const newIndex = garmentCount - 1;
        
        const newGarment = $(`.garment-item:first`).clone();
        newGarment.attr('data-index', newIndex);
        newGarment.find('.card-header h6').text('Garment #' + garmentCount);
        newGarment.find('.remove-garment').show();
        
        // Update all IDs and data attributes
        newGarment.find('input[type="checkbox"]').each(function() {
            const oldId = $(this).attr('id');
            const newId = oldId.replace(/_0$/, '_' + newIndex);
            $(this).attr('id', newId);
            $(this).next('label').attr('for', newId);
        });
        
        newGarment.find('#garment-subtotal-0').attr('id', 'garment-subtotal-' + newIndex);
        newGarment.find('#garment-discount-0').attr('id', 'garment-discount-' + newIndex);
        
        $('#garment-list').append(newGarment);
        updateGarmentCount();
    });
    
    // Remove garment
    $(document).on('click', '.remove-garment', function() {
        if (garmentCount > 1) {
            $(this).closest('.garment-item').remove();
            garmentCount--;
            renumberGarments();
            updateGarmentCount();
            calculateTotal();
        }
    });
    
    // Calculate total when checkboxes change
    $(document).on('change', '.print-checkbox', function() {
        calculateGarmentSubtotal($(this).closest('.garment-item'));
        calculateTotal();
    });
    
    // Calculate garment subtotal
    function calculateGarmentSubtotal(garmentElement) {
        const index = garmentElement.data('index');
        let subtotal = 0;
        
        garmentElement.find('.print-checkbox:checked').each(function() {
            subtotal += parseFloat($(this).data('price'));
        });
        
        // Apply combo discount
        const discount = calculateComboDiscount(garmentElement);
        
        $(`#garment-subtotal-${index}`).text('₱' + subtotal.toFixed(2));
        $(`#garment-discount-${index}`).text('-₱' + discount.toFixed(2));
        
        return subtotal - discount;
    }
    
    // Calculate combo discount
    function calculateComboDiscount(garmentElement) {
        const checkedBoxes = garmentElement.find('.print-checkbox:checked');
        let discount = 0;
        const selectedSizes = [];
        
        checkedBoxes.each(function() {
            selectedSizes.push($(this).data('name'));
        });
        
        // Check for Logo + A3 combo
        if (selectedSizes.includes('Logo') && selectedSizes.includes('A3')) {
            discount += 30;
        }
        
        // Check for Logo + A4 combo
        if (selectedSizes.includes('Logo') && selectedSizes.includes('A4')) {
            discount += 20;
        }
        
        // Check for Half A4 + A3 combo
        if (selectedSizes.includes('Half A4') && selectedSizes.includes('A3')) {
            discount += 25;
        }
        
        return discount;
    }
    
    // Calculate total order
    function calculateTotal() {
        let orderSubtotal = 0;
        
        $('.garment-item').each(function() {
            const garmentNet = calculateGarmentSubtotal($(this));
            orderSubtotal += garmentNet;
        });
        
        // Apply bulk discount
        const bulkDiscount = calculateBulkDiscount(orderSubtotal);
        const orderTotal = orderSubtotal - bulkDiscount;
        
        $('#order-subtotal').text('₱' + orderSubtotal.toFixed(2));
        $('#bulk-discount').text('-₱' + bulkDiscount.toFixed(2));
        $('#order-total').text('₱' + orderTotal.toFixed(2));
        
        updateAppliedDiscounts();
    }
    
    // Calculate bulk discount
    function calculateBulkDiscount(subtotal) {
        if (garmentCount >= 50) {
            return subtotal * 0.15;
        } else if (garmentCount >= 25) {
            return subtotal * 0.10;
        } else if (garmentCount >= 10) {
            return subtotal * 0.05;
        }
        return 0;
    }
    
    // Update applied discounts display
    function updateAppliedDiscounts() {
        const discounts = [];
        
        // Check for combo discounts
        $('.garment-item').each(function(index) {
            const discountElement = $(this).find('[id^="garment-discount-"]');
            const discountText = discountElement.text();
            if (discountText && discountText !== '-₱0.00') {
                const garmentDiscount = parseFloat(discountText.replace('-₱', ''));
                if (garmentDiscount > 0) {
                    discounts.push(`Garment ${index + 1}: ₱${garmentDiscount.toFixed(2)} combo discount`);
                }
            }
        });
        
        // Check for bulk discount
        const bulkDiscountText = $('#bulk-discount').text();
        if (bulkDiscountText && bulkDiscountText !== '-₱0.00') {
            const bulkDiscount = parseFloat(bulkDiscountText.replace('-₱', ''));
            if (bulkDiscount > 0) {
                discounts.push(`Bulk order: ₱${bulkDiscount.toFixed(2)} discount (${garmentCount} garments)`);
            }
        }
        
        const discountsHtml = discounts.length > 0 
            ? discounts.map(d => `<div class="mb-1">✓ ${d}</div>`).join('')
            : '<div class="text-muted">No discounts applied yet</div>';
        
        $('#applied-discounts').html(discountsHtml);
    }
    
    // Renumber garments after removal
    function renumberGarments() {
        $('.garment-item').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('.card-header h6').text('Garment #' + (index