@extends('layouts.app')

@section('title', 'Product Pricing')

@section('content')
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-tags text-success me-2"></i>
                Product Pricing
            </h1>
            <p class="page-subtitle">Complete price list for all products</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('sales.create-quick') }}" class="btn btn-warning">
                <i class="fas fa-plus-circle me-2"></i> Add Quick Sale
            </a>
        </div>
    </div>

    <!-- Pricing Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-table me-2"></i>
                Price List
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Wholesale (50+)</th>
                            <th>Wholesale (100+)</th>
                            <th>Corporate (500+)</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <i class="fas fa-tshirt text-primary me-2"></i>
                                <strong>T-Shirt</strong>
                            </td>
                            <td>Basic Apparel</td>
                            <td><span class="badge bg-primary">₱350.00</span></td>
                            <td><span class="badge bg-success">₱300.00</span></td>
                            <td><span class="badge bg-success">₱280.00</span></td>
                            <td><span class="badge bg-success">₱250.00</span></td>
                            <td>1,250 pcs</td>
                            <td><span class="badge bg-success">In Stock</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>
                                <i class="fas fa-user-tie text-success me-2"></i>
                                <strong>Polo Shirt</strong>
                            </td>
                            <td>Formal Wear</td>
                            <td><span class="badge bg-primary">₱500.00</span></td>
                            <td><span class="badge bg-success">₱450.00</span></td>
                            <td><span class="badge bg-success">₱420.00</span></td>
                            <td><span class="badge bg-success">₱380.00</span></td>
                            <td>850 pcs</td>
                            <td><span class="badge bg-success">In Stock</span></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>
                                <i class="fas fa-hoodie text-warning me-2"></i>
                                <strong>Hoodie</strong>
                            </td>
                            <td>Outerwear</td>
                            <td><span class="badge bg-primary">₱800.00</span></td>
                            <td><span class="badge bg-success">₱720.00</span></td>
                            <td><span class="badge bg-success">₱680.00</span></td>
                            <td><span class="badge bg-success">₱600.00</span></td>
                            <td>420 pcs</td>
                            <td><span class="badge bg-success">In Stock</span></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>
                                <i class="fas fa-vest text-danger me-2"></i>
                                <strong>Jacket</strong>
                            </td>
                            <td>Outerwear</td>
                            <td><span class="badge bg-primary">₱1,200.00</span></td>
                            <td><span class="badge bg-success">₱1,080.00</span></td>
                            <td><span class="badge bg-success">₱1,020.00</span></td>
                            <td><span class="badge bg-success">₱900.00</span></td>
                            <td>180 pcs</td>
                            <td><span class="badge bg-success">In Stock</span></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>
                                <i class="fas fa-vest text-info me-2"></i>
                                <strong>Pants</strong>
                            </td>
                            <td>Bottom Wear</td>
                            <td><span class="badge bg-primary">₱600.00</span></td>
                            <td><span class="badge bg-success">₱540.00</span></td>
                            <td><span class="badge bg-success">₱510.00</span></td>
                            <td><span class="badge bg-success">₱450.00</span></td>
                            <td>720 pcs</td>
                            <td><span class="badge bg-success">In Stock</span></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>
                                <i class="fas fa-vest-patches text-secondary me-2"></i>
                                <strong>Shorts</strong>
                            </td>
                            <td>Bottom Wear</td>
                            <td><span class="badge bg-primary">₱400.00</span></td>
                            <td><span class="badge bg-success">₱360.00</span></td>
                            <td><span class="badge bg-success">₱340.00</span></td>
                            <td><span class="badge bg-success">₱300.00</span></td>
                            <td>560 pcs</td>
                            <td><span class="badge bg-success">In Stock</span></td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>
                                <i class="fas fa-hat-cowboy text-warning me-2"></i>
                                <strong>Cap</strong>
                            </td>
                            <td>Accessories</td>
                            <td><span class="badge bg-primary">₱250.00</span></td>
                            <td><span class="badge bg-success">₱225.00</span></td>
                            <td><span class="badge bg-success">₱212.50</span></td>
                            <td><span class="badge bg-success">₱187.50</span></td>
                            <td>890 pcs</td>
                            <td><span class="badge bg-success">In Stock</span></td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>
                                <i class="fas fa-shopping-bag text-info me-2"></i>
                                <strong>Bag</strong>
                            </td>
                            <td>Accessories</td>
                            <td><span class="badge bg-primary">₱450.00</span></td>
                            <td><span class="badge bg-success">₱405.00</span></td>
                            <td><span class="badge bg-success">₱382.50</span></td>
                            <td><span class="badge bg-success">₱337.50</span></td>
                            <td>320 pcs</td>
                            <td><span class="badge bg-success">In Stock</span></td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>
                                <i class="fas fa-paint-brush text-secondary me-2"></i>
                                <strong>Custom Design</strong>
                            </td>
                            <td>Custom</td>
                            <td><span class="badge bg-primary">₱1,000.00</span></td>
                            <td><span class="badge bg-success">₱900.00</span></td>
                            <td><span class="badge bg-success">₱850.00</span></td>
                            <td><span class="badge bg-success">₱750.00</span></td>
                            <td>N/A</td>
                            <td><span class="badge bg-info">Made to Order</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pricing Notes -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Pricing Notes
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>Unit Price:</strong> Price for single item purchase</li>
                        <li><strong>Wholesale (50+):</strong> 10-15% discount for 50+ pieces</li>
                        <li><strong>Wholesale (100+):</strong> 15-20% discount for 100+ pieces</li>
                        <li><strong>Corporate (500+):</strong> 25-30% discount for bulk orders</li>
                        <li>All prices are in Philippine Peso (₱)</li>
                        <li>Prices are exclusive of VAT (12%)</li>
                        <li>Custom designs require 50% down payment</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-percentage me-2"></i>
                        Commission Rates
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sales Type</th>
                                    <th>Commission Rate</th>
                                    <th>Example (₱10,000 sale)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Retail Sales</td>
                                    <td><span class="badge bg-primary">5%</span></td>
                                    <td>₱500</td>
                                </tr>
                                <tr>
                                    <td>Wholesale (50+)</td>
                                    <td><span class="badge bg-success">7%</span></td>
                                    <td>₱700</td>
                                </tr>
                                <tr>
                                    <td>Wholesale (100+)</td>
                                    <td><span class="badge bg-success">8%</span></td>
                                    <td>₱800</td>
                                </tr>
                                <tr>
                                    <td>Corporate (500+)</td>
                                    <td><span class="badge bg-success">10%</span></td>
                                    <td>₱1,000</td>
                                </tr>
                                <tr>
                                    <td>Custom Designs</td>
                                    <td><span class="badge bg-warning">12%</span></td>
                                    <td>₱1,200</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">* Commission paid monthly based on collected payments</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="fas fa-bolt text-warning me-2"></i>
                Quick Actions
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <a href="{{ route('sales.create-quick') }}" class="btn btn-warning w-100">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create Quick Sale
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="{{ route('sales.products') }}" class="btn btn-primary w-100">
                        <i class="fas fa-list-alt me-2"></i>
                        View Product Catalog
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <button class="btn btn-success w-100" onclick="printPriceList()">
                        <i class="fas fa-print me-2"></i>
                        Print Price List
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function printPriceList() {
    alert('Printing price list...');
    // In a real app, this would trigger the print dialog
    window.print();
}
</script>
@endpush
@endsection