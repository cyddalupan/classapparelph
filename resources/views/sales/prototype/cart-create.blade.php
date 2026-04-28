<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Multi-Department Cart System | Class Apparel PH</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .header-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            color: #666;
            transition: var(--transition);
        }
        
        .step.active .step-circle {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-circle {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        
        .step-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            display: none;
        }
        
        .form-card.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 2px solid transparent;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.1);
        }
        
        .product-card.selected {
            border-color: var(--primary-color);
            background: rgba(67, 97, 238, 0.05);
        }
        
        .product-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .cart-item-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .department-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        .badge-iprint { background: #ff6b6b; color: white; }
        .badge-consol { background: #4ecdc4; color: white; }
        .badge-cinco { background: #45b7d1; color: white; }
        .badge-class { background: #96ceb4; color: white; }
        .badge-mto { background: #feca57; color: white; }
        .badge-other { background: #ff9ff3; color: white; }
        
        .customer-card {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: var(--transition);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-success-custom {
            background: linear-gradient(135deg, #4cc9f0, #3a86ff);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: var(--transition);
        }
        
        .totals-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: var(--card-shadow);
        }
        
        .payment-method-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .payment-method-card.selected {
            border-color: var(--primary-color);
            background: rgba(67, 97, 238, 0.05);
        }
        
        .search-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }
        
        .search-modal.active {
            display: flex;
        }
        
        .search-modal-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <!-- Header -->
        <div class="header-card">
            <h1 class="display-5 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Multi-Department Cart System</h1>
            <p class="lead mb-0">Create complex orders across multiple departments with a single payment</p>
        </div>
        
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active" id="step1">
                <div class="step-circle">1</div>
                <div class="step-label">Customer</div>
            </div>
            <div class="step" id="step2">
                <div class="step-circle">2</div>
                <div class="step-label">Add Items</div>
            </div>
            <div class="step" id="step3">
                <div class="step-circle">3</div>
                <div class="step-label">Review</div>
            </div>
            <div class="step" id="step4">
                <div class="step-circle">4</div>
                <div class="step-label">Payment</div>
            </div>
        </div>
        
        <!-- Step 1: Customer Information -->
        <div class="form-card active" id="step1Form">
            <h3 class="mb-4"><i class="fas fa-user me-2"></i>Customer Information</h3>
            
            <!-- Selected Customer Card (hidden by default) -->
            <div class="customer-card mb-4" id="selectedCustomerCard" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1" id="selectedCustomerName">Customer Name</h5>
                        <p class="mb-0" id="selectedCustomerDetails">Phone | Email</p>
                    </div>
                    <button class="btn btn-light btn-sm" onclick="clearCustomer()">
                        <i class="fas fa-times"></i> Change
                    </button>
                </div>
            </div>
            
            <!-- Customer Form -->
            <div id="customerForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="customer_name" placeholder="John Doe" required>
                    </div>
                    <div class="col-md-6">
                        <label for="customer_phone" class="form-label">Phone Number *</label>
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <input type="tel" class="form-control" id="customer_phone" placeholder="9123456789" required maxlength="10">
                        </div>
                        <div class="form-text">Enter 10-digit Philippine mobile number</div>
                    </div>
                    <div class="col-md-6">
                        <label for="customer_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="customer_email" placeholder="john@example.com">
                    </div>
                    <div class="col-md-6">
                        <label for="customer_company" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="customer_company" placeholder="ABC Corporation">
                    </div>
                    <div class="col-12">
                        <label for="customer_address" class="form-label">Address</label>
                        <textarea class="form-control" id="customer_address" rows="2" placeholder="Street, City, Province"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="marketplace" class="form-label">How did you find us?</label>
                        <select class="form-select" id="marketplace">
                            <option value="">Select option</option>
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="referral">Referral</option>
                            <option value="walkin">Walk-in</option>
                            <option value="website">Website</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-primary-custom me-2" onclick="saveCustomer()">
                        <i class="fas fa-save me-1"></i> Save Customer & Continue
                    </button>
                    <button class="btn btn-outline-primary" onclick="searchCustomer()">
                        <i class="fas fa-search me-1"></i> Search Existing Customer
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Step 2: Add Items -->
        <div class="form-card" id="step2Form">
            <h3 class="mb-4"><i class="fas fa-boxes me-2"></i>Add Items to Cart</h3>
            
            <!-- Product Selection -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="product-card" onclick="selectProductType('garment')" id="garmentCard">
                        <div class="product-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <h5>Garment Printing</h5>
                        <p class="text-muted">T-shirts, polos, hoodies with DTF or heat transfer</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-card" onclick="selectProductType('tarpaulin')" id="tarpaulinCard">
                        <div class="product-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <h5>Tarpaulin Printing</h5>
                        <p class="text-muted">Banners, signs, tarpaulins of various sizes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-card" onclick="selectProductType('embroidery')" id="embroideryCard">
                        <div class="product-icon">
                            <i class="fas fa-thread"></i>
                        </div>
                        <h5>Embroidery</h5>
                        <p class="text-muted">Logo stitching on caps, jackets, uniforms</p>
                    </div>
                </div>
            </div>
            
            <!-- Dynamic Form -->
            <div id="productForm" style="display: none;">
                <div class="row g-3" id="garmentForm">
                    <div class="col-md-4">
                        <label for="garment_brand" class="form-label">Brand</label>
                        <select class="form-select" id="garment_brand">
                            <option value="">Select brand</option>
                            <option value="gildan">Gildan</option>
                            <option value="fruit">Fruit of the Loom</option>
                            <option value="hanes">Hanes</option>
                            <option value="american">American Apparel</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="garment_size" class="form-label">Size</label>
                        <select class="form-select" id="garment_size">
                            <option value="">Select size</option>
                            <option value="xs">XS</option>
                            <option value="s">S</option>
                            <option value="m">M</option>
                            <option value="l">L</option>
                            <option value="xl">XL</option>
                            <option value="2xl">2XL</option>
                            <option value="3xl">3XL</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="garment_department" class="form-label">Department</label>
                        <select class="form-select" id="garment_department">
                            <option value="">Auto-assign</option>
                            <option value="iprint">iPrint (IPR)</option>
                            <option value="class">Class (CLS)</option>
                            <option value="mto">Made to Order (MTO)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="garment_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="garment_quantity" min="1" value="1">
                    </div>
                    <div class="col-md-4">
                        <label for="garment_price" class="form-label">Price per piece (₱)</label>
                        <input type="number" class="form-control" id="garment_price" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label for="garment_notes" class="form-label">Notes</label>
                        <input type="text" class="form-control" id="garment_notes" placeholder="Color, design, etc.">
                    </div>
                </div>
                
                <div class="row g-3" id="tarpaulinForm" style="display: none;">
                    <div class="col-md-3">
                        <label for="tarp_width" class="form-label">Width (feet)</label>
                        <input type="number" class="form-control" id="tarp_width" min="1" value="2">
                    </div>
                    <div class="col-md-3">
                        <label for="tarp_height" class="form-label">Height (feet)</label>
                        <input type="number" class="form-control" id="tarp_height" min="1" value="3">
                    </div>
                    <div class="col-md-3">
                        <label for="tarp_department" class="form-label">Department</label>
                        <select class="form-select" id="tarp_department">
                            <option value="">Auto-assign</option>
                            <option value="consol">Consol (CON)</option>
                            <option value="other">Other (OTH)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tarp_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="tarp_quantity" min="1" value="1">
                    </div>
                    <div class="col-md-4">
                        <label for="tarp_price" class="form-label">Price per piece (₱)</label>
                        <input type="number" class="form-control" id="tarp_price" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-8">
                        <label for="tarp_notes" class="form-label">Notes</label>
                        <input type="text" class="form-control" id="tarp_notes" placeholder="Design, grommets, etc.">
                    </div>
                </div>
                
                <div class="row g-3" id="embroideryForm" style="display: none;">
                    <div class="col-md-4">
                        <label for="embroidery_type" class="form-label">Item Type</label>
                        <select class="form-select" id="embroidery_type">
                            <option value="">Select type</option>
                            <option value="cap">Cap</option>
                            <option value="jacket">Jacket</option>
                            <option value="polo">Polo Shirt</option>
                            <option value="uniform">Uniform</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="embroidery_department" class="form-label">Department</label>
                        <select class="form-select" id="embroidery_department">
                            <option value="">Auto-assign</option>
                            <option value="cinco">Cinco (CIN)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="embroidery_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="embroidery_quantity" min="1" value="1">
                    </div>
                    <div class="col-md-4">
                        <label for="embroidery_price" class="form-label">Price per piece (₱)</label>
                        <input type="number" class="form-control" id="embroidery_price" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-8">
                        <label for="embroidery_notes" class="form-label">Notes</label>
                        <input type="text" class="form-control" id="embroidery_notes" placeholder="Thread colors, logo details, etc.">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-primary-custom" onclick="addToCart()">
                        <i class="fas fa-cart-plus me-1"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline-secondary ms-2" onclick="clearProductForm()">
                        <i class="fas fa-times me-1"></i> Clear Form
                    </button>
                </div>
            </div>
            
            <!-- Current Cart Items -->
            <div class="mt-5" id="cartSection" style="display: none;">
                <h4 class="mb-3"><i class="fas fa-shopping-cart me-2"></i>Current Cart Items</h4>
                <div id="cartItems">
                    <!-- Cart items will be dynamically added here -->
                    <div class="text-center text-muted py-4" id="emptyCartMessage">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <p>Your cart is empty. Add items to get started!</p>
                    </div>
                </div>
                
                <!-- Cart Totals -->
                <div class="totals-card">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="cartSubtotal">₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (12%):</span>
                                <span id="cartTax">₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span><strong>Total:</strong></span>
                                <span><strong id="cartTotal">₱0.00</strong></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Department Breakdown</h5>
                            <div id="departmentBreakdown">
                                <p class="text-muted mb-0">No items added yet</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-success-custom" onclick="goToStep(3)">
                        <i class="fas fa-arrow-right me-1"></i> Proceed to Review
                    </button>
                    <button class="btn btn-outline-danger ms-2" onclick="clearCart()">
                        <i class="fas fa-trash me-1"></i> Clear Cart
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Step 3: Review Order -->
        <div class="form-card" id="step3Form">
            <h3 class="mb-4"><i class="fas fa-clipboard-check me-2"></i>Review Order</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="customer-card mb-4">
                        <h5><i class="fas fa-user me-2"></i>Customer</h5>
                        <p class="mb-1" id="reviewCustomerName">-</p>
                        <p class="mb-1" id="reviewCustomerContact">-</p>
                        <p class="mb-0" id="reviewCustomerCompany">-</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="totals-card">
                        <h5><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items:</span>
                            <span id="reviewItemCount">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="reviewSubtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (12%):</span>
                            <span id="reviewTax">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>Total:</strong></span>
                            <span><strong id="reviewTotal">₱0.00</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <h5 class="mt-4 mb-3"><i class="fas fa-boxes me-2"></i>Order Items</h5>
            <div id="reviewItems">
                <p class="text-muted text-center py-4">No items in cart</p>
            </div>
            
            <div class="mt-4">
                <button class="btn btn-primary-custom" onclick="goToStep(2)">
                    <i class="fas fa-arrow-left me-1"></i> Back to Add Items
                </button>
                <button class="btn btn-success-custom ms-2" onclick="goToStep(4)">
                    <i class="fas fa-credit-card me-1"></i> Proceed to Payment
                </button>
            </div>
        </div>
        
        <!-- Step 4: Payment -->
        <div class="form-card" id="step4Form">
            <h3 class="mb-4"><i class="fas fa-credit-card me-2"></i>Payment Information</h3>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Payment Method</h5>
                    <div class="payment-method-card" onclick="selectPaymentMethod('gcash')" id="gcashCard">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">GCash</h6>
                                <p class="mb-0 text-muted">Mobile payment via GCash app</p>
                            </div>
                        </div>
                    </div>
                    <div class="payment-method-card" onclick="selectPaymentMethod('bank_transfer')" id="bankCard">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-university fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Bank Transfer</h6>
                                <p class="mb-0 text-muted">BPI, BDO, Metrobank, etc.</p>
                            </div>
                        </div>
                    </div>
                    <div class="payment-method-card" onclick="selectPaymentMethod('cash')" id="cashCard">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-money-bill-wave fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Cash</h6>
                                <p class="mb-0 text-muted">Physical cash payment</p>
                            </div>
                        </div>
                    </div>
                    <div class="payment-method-card" onclick="selectPaymentMethod('credit_card')" id="creditCard">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-credit-card fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Credit Card</h6>
                                <p class="mb-0 text-muted">Visa, Mastercard, etc.</p>
                            </div>
                        </div>
                    </div>
                    <div class="payment-method-card" onclick="selectPaymentMethod('check')" id="checkCard">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-file-invoice-dollar fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Check</h6>
                                <p class="mb-0 text-muted">Post-dated or manager's check</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5>Payment Details</h5>
                    <div id="paymentDetails">
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">Amount Paid (₱)</label>
                            <input type="number" class="form-control" id="payment_amount" min="0" step="0.01" value="0.00">
                        </div>
                        <div class="mb-3">
                            <label for="payment_reference" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="payment_reference" placeholder="GCash ref, bank ref, etc.">
                        </div>
                        <div class="mb-3">
                            <label for="payment_owner" class="form-label">Payment Owner</label>
                            <select class="form-select" id="payment_owner">
                                <option value="company">Company Account</option>
                                <option value="owner">Owner Personal</option>
                                <option value="sales_agent">Sales Agent</option>
                                <option value="department_head">Department Head</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_screenshot" class="form-label">Payment Screenshot</label>
                            <input type="file" class="form-control" id="payment_screenshot" accept="image/*">
                            <div class="form-text">Upload screenshot for digital payments</div>
                        </div>
                        <div class="mb-3">
                            <label for="payment_notes" class="form-label">Payment Notes</label>
                            <textarea class="form-control" id="payment_notes" rows="2" placeholder="Additional payment details"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button class="btn btn-primary-custom" onclick="goToStep(3)">
                    <i class="fas fa-arrow-left me-1"></i> Back to Review
                </button>
                <button class="btn btn-success-custom ms-2" onclick="submitOrder()">
                    <i class="fas fa-check me-1"></i> Submit Order
                </button>
            </div>
        </div>
    </div>
    
    <!-- Search Customer Modal -->
    <div class="search-modal" id="searchModal">
        <div class="search-modal-content">
            <h4 class="mb-3"><i class="fas fa-search me-2"></i>Search Existing Customer</h4>
            <div class="mb-3">
                <input type="text" class="form-control" id="searchCustomerInput" placeholder="Search by name, phone, or email...">
            </div>
            <div id="searchResults" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                <p class="text-muted text-center py-3">Start typing to search customers</p>
            </div>
            <div class="text-end">
                <button class="btn btn-secondary" onclick="closeSearchModal()">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let currentStep = 1;
        let currentCustomerId = null;
        let currentProductType = null;
        let cartItems = [];
        let selectedPaymentMethod = null;
        
        // Step navigation
        function goToStep(step) {
            // Hide all forms
            document.querySelectorAll('.form-card').forEach(card => {
                card.classList.remove('active');
            });
            
            // Remove active class from all steps
            document.querySelectorAll('.step').forEach(stepEl => {
                stepEl.classList.remove('active', 'completed');
            });
            
            // Show target form
            document.getElementById(`step${step}Form`).classList.add('active');
            
            // Mark previous steps as completed
            for (let i = 1; i < step; i++) {
                document.getElementById(`step${i}`).classList.add('completed');
            }
            
            // Mark current step as active
            document.getElementById(`step${step}`).classList.add('active');
            
            currentStep = step;
            
            // Update review section if going to step 3
            if (step === 3) {
                updateReviewSection();
            }
        }
        
        // Customer functions
        function saveCustomer() {
            const customerData = {
                name: document.getElementById('customer_name').value.trim(),
                phone: '+63' + document.getElementById('customer_phone').value.trim(),
                email: document.getElementById('customer_email').value.trim(),
                marketplace: document.getElementById('marketplace').value,
                address: document.getElementById('customer_address').value.trim(),
                company: document.getElementById('customer_company')?.value?.trim() || ''
            };
            
            if (!customerData.name || !customerData.phone.replace('+63', '')) {
                alert('Please fill in customer name and phone number');
                return;
            }
            
            // In a real implementation, this would call your API
            // For now, we'll simulate success
            simulateCustomerSave(customerData);
        }
        
        function simulateCustomerSave(customerData) {
            // Simulate API call delay
            setTimeout(() => {
                currentCustomerId = Math.floor(Math.random() * 1000) + 1;
                
                // Show selected customer card
                document.getElementById('selectedCustomerName').textContent = customerData.name;
                document.getElementById('selectedCustomerDetails').textContent = `${customerData.phone} | ${customerData.email || 'No email'}`;
                document.getElementById('selectedCustomerCard').style.display = 'block';
                
                // Hide customer form
                document.getElementById('customerForm').style.display = 'none';
                
                // Go to next step
                goToStep(2);
                
                // Show cart section
                document.getElementById('cartSection').style.display = 'block';
                
                alert('Customer saved successfully!');
            }, 500);
        }
        
        function searchCustomer() {
            document.getElementById('searchModal').classList.add('active');
        }
        
        function closeSearchModal() {
            document.getElementById('searchModal').classList.remove('active');
        }
        
        function clearCustomer() {
            currentCustomerId = null;
            document.getElementById('selectedCustomerCard').style.display = 'none';
            document.getElementById('customerForm').style.display = 'block';
            
            // Reset form fields
            document.getElementById('customer_name').value = '';
            document.getElementById('customer_phone').value = '';
            document.getElementById('customer_email').value = '';
            document.getElementById('customer_company').value = '';
            document.getElementById('customer_address').value = '';
            document.getElementById('marketplace').value = '';
        }
        
        // Product selection
        function selectProductType(type) {
            currentProductType = type;
            
            // Remove selected class from all product cards
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            document.getElementById(`${type}Card`).classList.add('selected');
            
            // Show product form
            document.getElementById('productForm').style.display = 'block';
            
            // Hide all product forms
            document.querySelectorAll('#productForm > div').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show selected product form
            document.getElementById(`${type}Form`).style.display = 'block';
        }
        
        function clearProductForm() {
            currentProductType = null;
            
            // Remove selected class from all product cards
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Hide product form
            document.getElementById('productForm').style.display = 'none';
            
            // Reset form fields based on type
            if (currentProductType === 'garment') {
                document.getElementById('garment_brand').value = '';
                document.getElementById('garment_size').value = '';
                document.getElementById('garment_department').value = '';
                document.getElementById('garment_quantity').value = '1';
                document.getElementById('garment_price').value = '';
                document.getElementById('garment_notes').value = '';
            } else if (currentProductType === 'tarpaulin') {
                document.getElementById('tarp_width').value = '2';
                document.getElementById('tarp_height').value = '3';
                document.getElementById('tarp_department').value = '';
                document.getElementById('tarp_quantity').value = '1';
                document.getElementById('tarp_price').value = '';
                document.getElementById('tarp_notes').value = '';
            } else if (currentProductType === 'embroidery') {
                document.getElementById('embroidery_type').value = '';
                document.getElementById('embroidery_department').value = '';
                document.getElementById('embroidery_quantity').value = '1';
                document.getElementById('embroidery_price').value = '';
                document.getElementById('embroidery_notes').value = '';
            }
        }
        
        // Cart functions
        function addToCart() {
            if (!currentProductType) {
                alert('Please select a product type first');
                return;
            }
            
            let item = {
                id: Date.now(), // Unique ID
                type: currentProductType,
                quantity: 1,
                price: 0,
                department: '',
                notes: ''
            };
            
            // Get data based on product type
            if (currentProductType === 'garment') {
                item.brand = document.getElementById('garment_brand').value;
                item.size = document.getElementById('garment_size').value;
                item.department = document.getElementById('garment_department').value || 'iprint';
                item.quantity = parseInt(document.getElementById('garment_quantity').value) || 1;
                item.price = parseFloat(document.getElementById('garment_price').value) || 0;
                item.notes = document.getElementById('garment_notes').value;
                item.description = `${item.brand} ${item.size} T-shirt`;
            } else if (currentProductType === 'tarpaulin') {
                item.width = document.getElementById('tarp_width').value;
                item.height = document.getElementById('tarp_height').value;
                item.department = document.getElementById('tarp_department').value || 'consol';
                item.quantity = parseInt(document.getElementById('tarp_quantity').value) || 1;
                item.price = parseFloat(document.getElementById('tarp_price').value) || 0;
                item.notes = document.getElementById('tarp_notes').value;
                item.description = `${item.width}x${item.height}ft Tarpaulin`;
            } else if (currentProductType === 'embroidery') {
                item.embroideryType = document.getElementById('embroidery_type').value;
                item.department = document.getElementById('embroidery_department').value || 'cinco';
                item.quantity = parseInt(document.getElementById('embroidery_quantity').value) || 1;
                item.price = parseFloat(document.getElementById('embroidery_price').value) || 0;
                item.notes = document.getElementById('embroidery_notes').value;
                item.description = `${item.embroideryType} Embroidery`;
            }
            
            // Auto-assign department if not specified
            if (!item.department) {
                if (currentProductType === 'garment') item.department = 'iprint';
                else if (currentProductType === 'tarpaulin') item.department = 'consol';
                else if (currentProductType === 'embroidery') item.department = 'cinco';
            }
            
            // Add to cart
            cartItems.push(item);
            
            // Update cart display
            updateCartDisplay();
            
            // Clear form
            clearProductForm();
            
            alert('Item added to cart!');
        }
        
        function updateCartDisplay() {
            const cartItemsContainer = document.getElementById('cartItems');
            const emptyCartMessage = document.getElementById('emptyCartMessage');
            
            if (cartItems.length === 0) {
                emptyCartMessage.style.display = 'block';
                cartItemsContainer.innerHTML = '';
                cartItemsContainer.appendChild(emptyCartMessage);
            } else {
                emptyCartMessage.style.display = 'none';
                
                let html = '';
                let subtotal = 0;
                let departmentCount = {};
                
                cartItems.forEach((item, index) => {
                    const itemTotal = item.quantity * item.price;
                    subtotal += itemTotal;
                    
                    // Count items per department
                    departmentCount[item.department] = (departmentCount[item.department] || 0) + 1;
                    
                    // Department badge
                    const deptNames = {
                        'iprint': 'iPrint',
                        'consol': 'Consol', 
                        'cinco': 'Cinco',
                        'class': 'Class',
                        'mto': 'Made to Order',
                        'other': 'Other'
                    };
                    
                    const deptBadge = `<span class="department-badge badge-${item.department}">${deptNames[item.department] || item.department}</span>`;
                    
                    html += `
                    <div class="cart-item-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${item.description}</h6>
                                <p class="mb-1 text-muted">Quantity: ${item.quantity} × ₱${item.price.toFixed(2)} = ₱${itemTotal.toFixed(2)}</p>
                                <p class="mb-1">${deptBadge}</p>
                                ${item.notes ? `<p class="mb-0"><small>Notes: ${item.notes}</small></p>` : ''}
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    `;
                });
                
                cartItemsContainer.innerHTML = html;
                
                // Update totals
                const tax = subtotal * 0.12;
                const total = subtotal + tax;
                
                document.getElementById('cartSubtotal').textContent = `₱${subtotal.toFixed(2)}`;
                document.getElementById('cartTax').textContent = `₱${tax.toFixed(2)}`;
                document.getElementById('cartTotal').textContent = `₱${total.toFixed(2)}`;
                
                // Update department breakdown
                let breakdownHtml = '';
                for (const [dept, count] of Object.entries(departmentCount)) {
                    const deptNames = {
                        'iprint': 'iPrint',
                        'consol': 'Consol', 
                        'cinco': 'Cinco',
                        'class': 'Class',
                        'mto': 'Made to Order',
                        'other': 'Other'
                    };
                    breakdownHtml += `<div class="d-flex justify-content-between mb-1">
                        <span>${deptNames[dept] || dept}:</span>
                        <span>${count} item${count > 1 ? 's' : ''}</span>
                    </div>`;
                }
                document.getElementById('departmentBreakdown').innerHTML = breakdownHtml || '<p class="text-muted mb-0">No items added yet</p>';
            }
        }
        
        function removeFromCart(index) {
            if (confirm('Remove this item from cart?')) {
                cartItems.splice(index, 1);
                updateCartDisplay();
            }
        }
        
        function clearCart() {
            if (cartItems.length === 0) return;
            
            if (confirm('Clear all items from cart?')) {
                cartItems = [];
                updateCartDisplay();
            }
        }
        
        // Review section
        function updateReviewSection() {
            // Update customer info
            const customerName = document.getElementById('customer_name').value || 'Not specified';
            const customerPhone = document.getElementById('customer_phone').value ? '+63' + document.getElementById('customer_phone').value : 'Not specified';
            const customerCompany = document.getElementById('customer_company').value || 'Not specified';
            
            document.getElementById('reviewCustomerName').textContent = customerName;
            document.getElementById('reviewCustomerContact').textContent = customerPhone;
            document.getElementById('reviewCustomerCompany').textContent = `Company: ${customerCompany}`;
            
            // Update order summary
            let subtotal = 0;
            cartItems.forEach(item => {
                subtotal += item.quantity * item.price;
            });
            
            const tax = subtotal * 0.12;
            const total = subtotal + tax;
            
            document.getElementById('reviewItemCount').textContent = cartItems.length;
            document.getElementById('reviewSubtotal').textContent = `₱${subtotal.toFixed(2)}`;
            document.getElementById('reviewTax').textContent = `₱${tax.toFixed(2)}`;
            document.getElementById('reviewTotal').textContent = `₱${total.toFixed(2)}`;
            
            // Update order items
            const reviewItemsContainer = document.getElementById('reviewItems');
            
            if (cartItems.length === 0) {
                reviewItemsContainer.innerHTML = '<p class="text-muted text-center py-4">No items in cart</p>';
            } else {
                let html = '';
                cartItems.forEach(item => {
                    const itemTotal = item.quantity * item.price;
                    
                    // Department badge
                    const deptNames = {
                        'iprint': 'iPrint',
                        'consol': 'Consol', 
                        'cinco': 'Cinco',
                        'class': 'Class',
                        'mto': 'Made to Order',
                        'other': 'Other'
                    };
                    
                    const deptBadge = `<span class="department-badge badge-${item.department}">${deptNames[item.department] || item.department}</span>`;
                    
                    html += `
                    <div class="cart-item-card mb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">${item.description}</h6>
                                <p class="mb-1 text-muted">${item.quantity} × ₱${item.price.toFixed(2)} = ₱${itemTotal.toFixed(2)}</p>
                                <p class="mb-0">${deptBadge}</p>
                            </div>
                            <div>
                                <span class="fw-bold">₱${itemTotal.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                    `;
                });
                
                reviewItemsContainer.innerHTML = html;
            }
        }
        
        // Payment functions
        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            
            // Remove selected class from all payment cards
            document.querySelectorAll('.payment-method-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            document.getElementById(`${method}Card`).classList.add('selected');
        }
        
        function submitOrder() {
            if (!currentCustomerId) {
                alert('Please save customer information first');
                goToStep(1);
                return;
            }
            
            if (cartItems.length === 0) {
                alert('Please add items to cart');
                goToStep(2);
                return;
            }
            
            if (!selectedPaymentMethod) {
                alert('Please select a payment method');
                return;
            }
            
            const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
            if (paymentAmount <= 0) {
                alert('Please enter a valid payment amount');
                return;
            }
            
            // Calculate order total
            let subtotal = 0;
            cartItems.forEach(item => {
                subtotal += item.quantity * item.price;
            });
            const tax = subtotal * 0.12;
            const total = subtotal + tax;
            
            if (paymentAmount < total) {
                if (!confirm(`Payment amount (₱${paymentAmount.toFixed(2)}) is less than total (₱${total.toFixed(2)}). Continue anyway?`)) {
                    return;
                }
            }
            
            // Prepare order data
            const orderData = {
                customer_id: currentCustomerId,
                items: cartItems,
                payment_method: selectedPaymentMethod,
                payment_amount: paymentAmount,
                payment_reference: document.getElementById('payment_reference').value,
                payment_owner: document.getElementById('payment_owner').value,
                payment_notes: document.getElementById('payment_notes').value,
                subtotal: subtotal,
                tax: tax,
                total: total
            };
            
            // In a real implementation, this would call your API
            // For now, we'll simulate success
            simulateOrderSubmit(orderData);
        }
        
        function simulateOrderSubmit(orderData) {
            // Show loading state
            const submitBtn = document.querySelector('button[onclick="submitOrder()"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
            submitBtn.disabled = true;
            
            // Simulate API call delay
            setTimeout(() => {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Show success message
                alert(`✅ Order submitted successfully!\n\nOrder Summary:\n- ${orderData.items.length} items\n- Total: ₱${orderData.total.toFixed(2)}\n- Payment: ${orderData.payment_method}\n- Amount: ₱${orderData.payment_amount.toFixed(2)}\n\nThe order has been split to ${new Set(orderData.items.map(item => item.department)).size} departments.`);
                
                // Reset form for new order
                resetForm();
            }, 2000);
        }
        
        function resetForm() {
            // Reset to step 1
            goToStep(1);
            
            // Clear customer
            clearCustomer();
            
            // Clear cart
            cartItems = [];
            updateCartDisplay();
            
            // Clear payment selection
            selectedPaymentMethod = null;
            document.querySelectorAll('.payment-method-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Reset payment form
            document.getElementById('payment_amount').value = '0.00';
            document.getElementById('payment_reference').value = '';
            document.getElementById('payment_owner').value = 'company';
            document.getElementById('payment_notes').value = '';
            document.getElementById('payment_screenshot').value = '';
            
            // Hide cart section
            document.getElementById('cartSection').style.display = 'none';
            
            // Show product form
            document.getElementById('productForm').style.display = 'none';
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set default payment amount to 0
            document.getElementById('payment_amount').value = '0.00';
            
            // Auto-format phone input
            document.getElementById('customer_phone').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) value = value.substring(0, 10);
                e.target.value = value;
                
                // Auto-check customer if phone is complete
                if (value.length === 10) {
                    checkExistingCustomer(value);
                }
            });
        });
        
        function checkExistingCustomer(phone) {
            // In a real implementation, this would call your API
            // For now, we'll just show a message
            console.log(`Checking customer with phone: +63${phone}`);
            
            // Simulate API call
            setTimeout(() => {
                // For demo, we'll assume no existing customer
                // In real app, you would auto-fill if customer exists
            }, 500);
        }
        
        // Make functions available globally
        window.saveCustomer = saveCustomer;
        window.searchCustomer = searchCustomer;
        window.closeSearchModal = closeSearchModal;
        window.clearCustomer = clearCustomer;
        window.selectProductType = selectProductType;
        window.clearProductForm = clearProductForm;
        window.addToCart = addToCart;
        window.removeFromCart = removeFromCart;
        window.clearCart = clearCart;
        window.goToStep = goToStep;
        window.selectPaymentMethod = selectPaymentMethod;
        window.submitOrder = submitOrder;
        
    </script>
</body>
</html>