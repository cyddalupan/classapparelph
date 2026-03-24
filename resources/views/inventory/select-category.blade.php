<x-app-layout>
    @section('page-title', 'Inventory Action')
    
    {{-- Cache control headers to prevent browser caching issues --}}
    @php
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    @endphp
    
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div>
                <h1 class="page-title mb-1">
                    <i class="fas fa-boxes me-2"></i>
                    Inventory Action
                </h1>
                <p class="page-subtitle text-muted mb-0">Select a category for your inventory action</p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn btn-primary" id="add-new-item-btn">
                    <i class="fas fa-plus-circle me-2"></i>Add New Item
                </button>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <i class="fas fa-layer-group text-primary me-2"></i>
                            Select Category
                        </h5>
                    </div>
                    
                    <p class="text-muted mb-4">Click on one of the boxes below to select a category</p>
                    
                    <!-- 4 BOXES DESIGN - SIDE BY SIDE! -->
                    <div class="row gx-4 justify-content-center">
                        <!-- Box 1: Shirt Products -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="category-box card border-3 mx-2" data-category="Shirt Products" id="box-shirt">
                                <div class="card-body text-center">
                                    <div class="category-icon mb-2">
                                        <i class="fas fa-tshirt fa-2x text-primary"></i>
                                    </div>
                                    <h6 class="card-title mb-1">Shirt Products</h6>
                                    <p class="card-text text-muted small mb-0">T-shirts, polo shirts, hoodies</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Box 2: Other Items -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="category-box card border-3 mx-2" data-category="Other Items" id="box-other">
                                <div class="card-body text-center">
                                    <div class="category-icon mb-2">
                                        <i class="fas fa-box fa-2x text-info"></i>
                                    </div>
                                    <h6 class="card-title mb-1">Other Items</h6>
                                    <p class="card-text text-muted small mb-0">Miscellaneous items</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Box 3: Office Supplies -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="category-box card border-3 mx-2" data-category="Office Supplies" id="box-office">
                                <div class="card-body text-center">
                                    <div class="category-icon mb-2">
                                        <i class="fas fa-clipboard-list fa-2x text-warning"></i>
                                    </div>
                                    <h6 class="card-title mb-1">Office Supplies</h6>
                                    <p class="card-text text-muted small mb-0">Paper, pens, folders</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Box 4: Machine and Equipments -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="category-box card border-3 mx-2" data-category="Machine and Equipments" id="box-machine">
                                <div class="card-body text-center">
                                    <div class="category-icon mb-2">
                                        <i class="fas fa-tools fa-2x text-danger"></i>
                                    </div>
                                    <h6 class="card-title mb-1">Machine and Equipments</h6>
                                    <p class="card-text text-muted small mb-0">Printers, computers, tools</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden field for selected category -->
                    <input type="hidden" name="category" id="selected-category" value="">
                    
                    <!-- SHIRT PRODUCTS SPECIFIC BUTTONS (HIDDEN BY DEFAULT) -->
                    <div class="mt-4 d-flex justify-content-center gap-3" id="shirt-products-buttons" style="display: none;">
                        <button type="button" class="btn" id="shirt-add-item-btn">
                            <i class="fas fa-plus-circle me-2"></i>Add Shirt Product
                        </button>
                        <button type="button" class="btn" id="shirt-deduct-item-btn">
                            <i class="fas fa-minus-circle me-2"></i>Deduct Shirt Product
                        </button>
                    </div>
                    
                    <!-- CONTINUE BUTTON (HIDDEN BY DEFAULT) -->
                    <div class="mt-4 text-center" id="continue-button" style="display: none;">
                        <button type="button" class="btn btn-primary btn-lg" id="continue-btn">
                            Continue <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="alert alert-info mt-4">
            <h5 class="alert-heading">
                <i class="fas fa-info-circle me-2"></i> How to Use Inventory Action
            </h5>
            <ul class="mb-0">
                <li><strong>Click any category box</strong> to select it</li>
                <li>For <strong>Shirt Products</strong>, use "Add Shirt Product" or "Deduct Shirt Product" buttons</li>
                <li>For other categories, click the "Continue" button</li>
                <li>You'll be taken to the appropriate form for data entry</li>
            </ul>
        </div>
    </div>

    @push('styles')
    <style>
        .page-title {
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.8rem;
        }
        
        .page-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .page-actions {
            flex-shrink: 0;
        }
        
        /* 4 BOXES DESIGN - SIDE BY SIDE! - USING FLEX FOR 4 COLUMNS! */
        .category-box {
            cursor: pointer;
            transition: all 0.3s ease;
            border-color: #dee2e6 !important;
            margin: 0.5rem;
            /* Make it square */
            aspect-ratio: 1 / 1; /* Perfect square */
            display: flex;
            align-items: center;
            justify-content: center;
            /* Make boxes MALAKI NA! - 120px × 120px (NO MORE CRAMPED!) */
            height: 120px !important; /* MALAKI NA! */
            min-height: 120px !important; /* MALAKI NA! */
            max-height: 120px !important; /* Don't grow! */
            /* Ensure square on all screens */
            width: 100% !important;
            /* KUNTING RADIUS SA MGA KANTO - AS REQUESTED BY USER! */
            border-radius: 10px !important; /* SOFT ROUNDED CORNERS! */
        }
        
        .category-box:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: #0d6efd !important;
            border-radius: 10px !important; /* MAINTAIN ROUNDED CORNERS ON HOVER */
        }
        
        .category-box.selected {
            border-color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.05);
            box-shadow: 0 5px 20px rgba(13, 110, 253, 0.2);
            transform: scale(1.02);
            border-radius: 10px !important; /* MAINTAIN ROUNDED CORNERS WHEN SELECTED */
        }
        
        /* FLEX CONTAINER FOR 4 COLUMNS - AS REQUESTED BY USER! */
        .row.gx-4.justify-content-center {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            gap: 1rem !important;
        }
        
        /* MAKE SURE EACH BOX TAKES 25% WIDTH FOR 4 COLUMNS */
        .row.gx-4.justify-content-center > div {
            flex: 0 0 calc(25% - 1rem) !important; /* 4 columns with gap */
            max-width: calc(25% - 1rem) !important;
        }
        
        .category-icon {
            height: 40px !important; /* MAS MALAKI NA ICON HEIGHT! */
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem !important; /* MAS MALAKI NA SPACING! */
        }
        
        /* Shirt Products specific buttons - BELOW CATEGORY BOXES */
        #shirt-products-buttons {
            margin-top: 2rem;
            padding: 1rem 0;
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }
        
        #add-new-item-btn, #shirt-add-item-btn, #shirt-deduct-item-btn {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            min-width: 180px;
            font-size: 1rem;
        }
        
        #add-new-item-btn:hover, #shirt-add-item-btn:hover, #shirt-deduct-item-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Shirt buttons specific hover - LIGHT GRAY HOVER WITH BLUE TEXT */
        #shirt-add-item-btn:hover, #shirt-deduct-item-btn:hover {
            background: #e9ecef !important; /* SLIGHTLY DARKER GRAY ON HOVER */
            color: #0a58ca !important; /* DARKER BLUE TEXT ON HOVER */
        }
        
        #add-new-item-btn:active, #shirt-add-item-btn:active, #shirt-deduct-item-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* Shirt buttons specific active - EVEN DARKER GRAY */
        #shirt-add-item-btn:active, #shirt-deduct-item-btn:active {
            background: #dee2e6 !important; /* EVEN DARKER GRAY ON ACTIVE */
            color: #084298 !important; /* EVEN DARKER BLUE TEXT ON ACTIVE */
        }
        
        #shirt-add-item-btn {
            background: #f8f9fa !important; /* LIGHT GRAY BACKGROUND */
            color: #0d6efd !important; /* BLUE TEXT */
            border: 1px solid #dee2e6 !important; /* LIGHT BORDER */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important; /* SUBTLE SHADOW */
        }
        
        #shirt-deduct-item-btn {
            background: #f8f9fa !important; /* SAME LIGHT GRAY BACKGROUND */
            color: #0d6efd !important; /* BLUE TEXT */
            border: 1px solid #dee2e6 !important; /* SAME LIGHT BORDER */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important; /* SAME SUBTLE SHADOW */
        }
        
        /* Continue button */
        #continue-btn {
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        /* Responsive button adjustments */
        @media (max-width: 768px) {
            /* Shirt Products buttons - stack vertically on mobile */
            #shirt-products-buttons {
                flex-direction: column; /* STACK VERTICALLY ON MOBILE */
                gap: 1.5rem !important; /* MAS MALAKI PA NA SPACING ON MOBILE */
            }
            
            /* All buttons - larger on mobile */
            #add-new-item-btn, #shirt-add-item-btn, #shirt-deduct-item-btn {
                min-width: 200px !important; /* MAS MALAKI PA ON MOBILE */
                width: 100% !important; /* FULL WIDTH ON MOBILE */
                max-width: 250px !important; /* BUT NOT TOO WIDE */
            }
            
            /* Category boxes - 2 columns on mobile */
            .row.gx-4.justify-content-center > div {
                flex: 0 0 calc(50% - 1rem) !important; /* 2 columns on mobile */
                max-width: calc(50% - 1rem) !important;
            }
        }
        
        @media (max-width: 576px) {
            #add-new-item-btn, #shirt-add-item-btn, #shirt-deduct-item-btn {
                min-width: 100% !important; /* FULL WIDTH ON SMALL MOBILE */
                font-size: 1rem !important; /* SLIGHTLY SMALLER TEXT */
                padding: 0.65rem 1.25rem !important;
            }
            
            /* Shirt Products buttons - adjust spacing on small mobile */
            #shirt-products-buttons {
                margin-top: 1.5rem !important;
                padding: 0.5rem 0;
            }
            
            /* Category boxes - 1 column on very small mobile */
            .row.gx-4.justify-content-center > div {
                flex: 0 0 100% !important; /* 1 column on very small mobile */
                max-width: 100% !important;
            }
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inventory Action page loaded successfully!');
            
            // Category selection
            const categoryBoxes = document.querySelectorAll('.category-box');
            const selectedCategoryField = document.getElementById('selected-category');
            
            // Button containers
            const shirtProductsButtons = document.getElementById('shirt-products-buttons');
            const continueButton = document.getElementById('continue-button');
            const addNewItemBtn = document.getElementById('add-new-item-btn');
            const shirtAddItemBtn = document.getElementById('shirt-add-item-btn');
            const shirtDeductItemBtn = document.getElementById('shirt-deduct-item-btn');
            const continueBtn = document.getElementById('continue-btn');
            
            let selectedCategory = '';
            
            // Category selection handler
            categoryBoxes.forEach(box => {
                box.addEventListener('click', function() {
                    // Remove selection from all boxes
                    categoryBoxes.forEach(b => {
                        b.classList.remove('selected');
                    });
                    
                    // Add selection to clicked box
                    this.classList.add('selected');
                    
                    // Set category value
                    selectedCategory = this.getAttribute('data-category');
                    selectedCategoryField.value = selectedCategory;
                    
                    // SPECIAL HANDLING FOR SHIRT PRODUCTS
                    if (selectedCategory === 'Shirt Products') {
                        // Show shirt products buttons
                        shirtProductsButtons.style.display = 'flex';
                        continueButton.style.display = 'none';
                    } else {
                        // Hide shirt products buttons, show continue button
                        shirtProductsButtons.style.display = 'none';
                        continueButton.style.display = 'block';
                    }
                    
                    console.log('Selected category:', selectedCategory);
                });
            });
            
            // Add New Item button (top right) - goes to create page
            if (addNewItemBtn) {
                addNewItemBtn.addEventListener('click', function() {
                    // If no category selected, show alert
                    if (!selectedCategory) {
                        alert('Please select a category first.');
                        return;
                    }
                    
                    // Map category to URL parameter
                    const categoryMap = {
                        'Shirt Products': 'shirt',
                        'Other Items': 'other',
                        'Office Supplies': 'office',
                        'Machine and Equipments': 'machine'
                    };
                    
                    const urlCategory = categoryMap[selectedCategory];
                    if (urlCategory) {
                        window.location.href = '/inventory/create?category=' + urlCategory;
                    }
                });
            }
            
            // Add Shirt Product button
            if (shirtAddItemBtn) {
                shirtAddItemBtn.addEventListener('click', function() {
                    if (selectedCategory === 'Shirt Products') {
                        window.location.href = '/inventory/create?category=shirt&action=add';
                    }
                });
            }
            
            // Deduct Shirt Product button
            if (shirtDeductItemBtn) {
                shirtDeductItemBtn.addEventListener('click', function() {
                    if (selectedCategory === 'Shirt Products') {
                        alert('Deduct Shirt Product feature will be implemented soon.');
                    }
                });
            }
            
            // Continue button (for non-shirt categories)
            if (continueBtn) {
                continueBtn.addEventListener('click', function() {
                    if (!selectedCategory) {
                        alert('Please select a category first.');
                        return;
                    }
                    
                    // Map category to URL parameter
                    const categoryMap = {
                        'Shirt Products': 'shirt',
                        'Other Items': 'other',
                        'Office Supplies': 'office',
                        'Machine and Equipments': 'machine'
                    };
                    
                    const urlCategory = categoryMap[selectedCategory];
                    if (urlCategory) {
                        window.location.href = '/inventory/create?category=' + urlCategory;
                    }
                });
            }
            
            // Debug logging
            console.log('Elements found:', {
                categoryBoxes: categoryBoxes.length,
                addNewItemBtn: !!addNewItemBtn,
                shirtAddItemBtn: !!shirtAddItemBtn,
                shirtDeductItemBtn: !!shirtDeductItemBtn,
                continueBtn: !!continueBtn
            });
        });
    </script>
    @endpush
</x-app-layout>