@extends('layouts.app')

@section('title', 'Product Catalog')

@section('content')
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-list-alt text-primary me-2"></i>
                Product Catalog
            </h1>
            <p class="page-subtitle">Browse available products for sales</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('sales.create-quick') }}" class="btn btn-warning">
                <i class="fas fa-plus-circle me-2"></i> Add Quick Sale
            </a>
        </div>
    </div>

    <!-- Product Catalog -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-tshirt me-2"></i>
                Available Products
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Product 1 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">T-Shirt</h6>
                        </div>
                        <div class="card-body">
                            <div class="product-image mb-3 text-center">
                                <i class="fas fa-tshirt fa-4x text-primary"></i>
                            </div>
                            <h5 class="product-price text-success">₱350.00</h5>
                            <p class="product-description">
                                Basic cotton t-shirt, available in various colors and sizes.
                            </p>
                            <div class="product-specs">
                                <small class="text-muted">
                                    <i class="fas fa-palette me-1"></i> Colors: 12
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-ruler me-1"></i> Sizes: XS-XXL
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-box me-1"></i> Stock: 1,250 pcs
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-primary w-100" onclick="selectProduct('t-shirt', 350)">
                                <i class="fas fa-cart-plus me-1"></i> Select for Sale
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product 2 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">Polo Shirt</h6>
                        </div>
                        <div class="card-body">
                            <div class="product-image mb-3 text-center">
                                <i class="fas fa-user-tie fa-4x text-success"></i>
                            </div>
                            <h5 class="product-price text-success">₱500.00</h5>
                            <p class="product-description">
                                Formal polo shirt, perfect for corporate uniforms.
                            </p>
                            <div class="product-specs">
                                <small class="text-muted">
                                    <i class="fas fa-palette me-1"></i> Colors: 8
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-ruler me-1"></i> Sizes: S-XXXL
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-box me-1"></i> Stock: 850 pcs
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-success w-100" onclick="selectProduct('polo', 500)">
                                <i class="fas fa-cart-plus me-1"></i> Select for Sale
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product 3 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <div class="card-header bg-warning text-white">
                            <h6 class="card-title mb-0">Hoodie</h6>
                        </div>
                        <div class="card-body">
                            <div class="product-image mb-3 text-center">
                                <i class="fas fa-hoodie fa-4x text-warning"></i>
                            </div>
                            <h5 class="product-price text-success">₱800.00</h5>
                            <p class="product-description">
                                Comfortable hoodie with front pocket and drawstring.
                            </p>
                            <div class="product-specs">
                                <small class="text-muted">
                                    <i class="fas fa-palette me-1"></i> Colors: 6
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-ruler me-1"></i> Sizes: M-XXL
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-box me-1"></i> Stock: 420 pcs
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-warning w-100" onclick="selectProduct('hoodie', 800)">
                                <i class="fas fa-cart-plus me-1"></i> Select for Sale
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product 4 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <div class="card-header bg-danger text-white">
                            <h6 class="card-title mb-0">Jacket</h6>
                        </div>
                        <div class="card-body">
                            <div class="product-image mb-3 text-center">
                                <i class="fas fa-vest fa-4x text-danger"></i>
                            </div>
                            <h5 class="product-price text-success">₱1,200.00</h5>
                            <p class="product-description">
                                Premium jacket with waterproof material.
                            </p>
                            <div class="product-specs">
                                <small class="text-muted">
                                    <i class="fas fa-palette me-1"></i> Colors: 4
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-ruler me-1"></i> Sizes: S-XL
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-box me-1"></i> Stock: 180 pcs
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-danger w-100" onclick="selectProduct('jacket', 1200)">
                                <i class="fas fa-cart-plus me-1"></i> Select for Sale
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product 5 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">Pants</h6>
                        </div>
                        <div class="card-body">
                            <div class="product-image mb-3 text-center">
                                <i class="fas fa-vest fa-4x text-info"></i>
                            </div>
                            <h5 class="product-price text-success">₱600.00</h5>
                            <p class="product-description">
                                Comfortable pants for casual and work wear.
                            </p>
                            <div class="product-specs">
                                <small class="text-muted">
                                    <i class="fas fa-palette me-1"></i> Colors: 10
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-ruler me-1"></i> Sizes: 28-42
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-box me-1"></i> Stock: 720 pcs
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-info w-100" onclick="selectProduct('pants', 600)">
                                <i class="fas fa-cart-plus me-1"></i> Select for Sale
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product 6 -->
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="card-title mb-0">Custom Design</h6>
                        </div>
                        <div class="card-body">
                            <div class="product-image mb-3 text-center">
                                <i class="fas fa-paint-brush fa-4x text-secondary"></i>
                            </div>
                            <h5 class="product-price text-success">₱1,000.00</h5>
                            <p class="product-description">
                                Custom-designed apparel with your logo or artwork.
                            </p>
                            <div class="product-specs">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i> Lead Time: 7-10 days
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-tools me-1"></i> Minimum: 50 pcs
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-check-circle me-1"></i> Design Proof Included
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-secondary w-100" onclick="selectProduct('custom', 1000)">
                                <i class="fas fa-cart-plus me-1"></i> Select for Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h6 class="stat-title">Total Products</h6>
                    <h2 class="stat-value text-primary">24</h2>
                    <small class="text-muted">Available for sale</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h6 class="stat-title">Total Stock</h6>
                    <h2 class="stat-value text-success">8,450</h2>
                    <small class="text-muted">Pieces available</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h6 class="stat-title">Low Stock</h6>
                    <h2 class="stat-value text-warning">3</h2>
                    <small class="text-muted">Items below 50 pcs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h6 class="stat-title">Best Seller</h6>
                    <h2 class="stat-value text-info">T-Shirt</h2>
                    <small class="text-muted">Most sold product</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectProduct(productName, price) {
    alert(`Selected: ${productName} (₱${price})\n\nThis product will be added to your quick sale form.`);
    // In a real app, this would redirect to the quick sale form with pre-filled data
    window.location.href = "{{ route('sales.create-quick') }}";
}
</script>
@endpush
@endsection