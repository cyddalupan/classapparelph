<x-app-layout>
    @section('page-title', 'Products')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-tshirt"></i>
                Products
            </h1>
            <p class="page-subtitle">Manage product catalog and inventory</p>
        </div>
    </x-slot>

    <div class="page-content">
        <div class="placeholder-container">
            <div class="placeholder-icon">
                <i class="fas fa-tshirt fa-4x"></i>
            </div>
            <h2 class="placeholder-title">Product Catalog</h2>
            <p class="placeholder-text">
                Manage your t-shirt products, categories, pricing, and inventory.
            </p>
            <div class="placeholder-features">
                <div class="feature-card">
                    <i class="fas fa-boxes"></i>
                    <h3>Product Management</h3>
                    <p>Add, edit, and organize products</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-tags"></i>
                    <h3>Categories & Tags</h3>
                    <p>Organize products with categories</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Pricing</h3>
                    <p>Set prices, discounts, and promotions</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-images"></i>
                    <h3>Product Images</h3>
                    <p>Upload and manage product photos</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>