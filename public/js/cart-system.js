// Cart System JavaScript
// For /sales/prototype/cart-create

// Global variables
let currentStep = 1;
let currentCustomerId = null;
let currentProductType = null;
let cartData = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    setupEventListeners();
    updateStepDisplay();
});

// Setup event listeners
function setupEventListeners() {
    // Customer search
    document.getElementById('searchCustomerBtn')?.addEventListener('click', searchCustomers);
    document.getElementById('customerSearch')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchCustomers();
    });
    
    // Save customer
    document.getElementById('saveCustomerBtn')?.addEventListener('click', saveCustomer);
    document.getElementById('skipCustomerBtn')?.addEventListener('click', skipCustomer);
    document.getElementById('changeCustomerBtn')?.addEventListener('click', changeCustomer);
    
    // Product selection
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            selectProductType(this.dataset.productType);
        });
    });
    
    // Navigation buttons
    document.getElementById('backToStep1Btn')?.addEventListener('click', () => goToStep(1));
    document.getElementById('proceedToReviewBtn')?.addEventListener('click', () => goToStep(3));
    document.getElementById('backToStep2Btn')?.addEventListener('click', () => goToStep(2));
    document.getElementById('proceedToPaymentBtn')?.addEventListener('click', () => goToStep(4));
    document.getElementById('backToStep3Btn')?.addEventListener('click', () => goToStep(3));
    
    // Payment
    document.getElementById('deposit_paid')?.addEventListener('input', updateFinalAmount);
    document.getElementById('submitOrderBtn')?.addEventListener('click', submitOrder);
}

// Load cart data
function loadCart() {
    fetch('/api/cart')
        .then(response => response.json())
        .then(data => {
            cartData = data;
            updateCartDisplay();
        })
        .catch(error => {
            console.error('Error loading cart:', error);
        });
}

// Update cart display
function updateCartDisplay() {
    if (!cartData) return;
    
    // Update cart header
    const cartTotal = document.getElementById('cartTotal');
    const cartItemCount = document.getElementById('cartItemCount');
    
    if (cartTotal) {
        cartTotal.textContent = '₱' + parseFloat(cartData.totals?.total || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
    }
    
    if (cartItemCount) {
        const itemCount = cartData.items?.length || 0;
        cartItemCount.textContent = itemCount + ' item' + (itemCount !== 1 ? 's' : '');
    }
    
    // Update cart items in step 2
    updateCartItemsList();
    
    // Update totals in step 2
    updateCartTotals();
    
    // Update final amount in step 4
    updateFinalAmount();
}

// Update cart items list
function updateCartItemsList() {
    const container = document.getElementById('cartItemsContainer');
    if (!container) return;
    
    if (!cartData?.items || cartData.items.length === 0) {
        container.innerHTML = `
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <h4>Your cart is empty</h4>
                <p>Add items to get started</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    cartData.items.forEach(item => {
        const departmentClass = `department-${item.department?.code?.toLowerCase() || 'other'}`;
        html += `
            <div class="cart-item" data-item-id="${item.id}">
                <div class="item-details">
                    <div class="d-flex align-items-center mb-1">
                        <span class="item-department ${departmentClass}">${item.department?.code || 'DEPT'}</span>
                        <strong>${item.product_type?.toUpperCase() || 'ITEM'}</strong>
                    </div>
                    <div class="small text-muted mb-2">${getItemDescription(item)}</div>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="fw-semibold">₱${parseFloat(item.unit_price || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span> each
                        </div>
                        <div class="input-group input-group-sm item-quantity">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                            <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                        <div class="ms-3 fw-bold">
                            ₱${parseFloat(item.total_price || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})}
                        </div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-danger ms-3" onclick="removeItem(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Update cart totals
function updateCartTotals() {
    if (!cartData) return;
    
    const subtotal = document.getElementById('cartSubtotal');
    const tax = document.getElementById('cartTax');
    const grandTotal = document.getElementById('cartGrandTotal');
    
    if (subtotal) subtotal.textContent = '₱' + parseFloat(cartData.totals?.subtotal || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
    if (tax) tax.textContent = '₱' + parseFloat(cartData.totals?.tax || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
    if (grandTotal) grandTotal.textContent = '₱' + parseFloat(cartData.totals?.total || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
}

// Get item description
function getItemDescription(item) {
    const details = item.product_details || {};
    switch(item.product_type) {
        case 'garment':
            return `${details.brand || 'Unknown'} - Size: ${details.size || 'N/A'}, Print: ${details.print_area || 'N/A'}`;
        case 'tarpaulin':
            return `${details.material || 'Material'} - ${details.width || '0'}x${details.height || '0'} ${details.unit || 'units'}`;
        case 'embroidery':
            return `${details.thread_colors || '0'} colors, ${details.stitch_count || '0'} stitches`;
        default:
            return item.product_type || 'Item';
    }
}

// Search customers
function searchCustomers() {
    const searchTerm = document.getElementById('customerSearch').value.trim();
    if (searchTerm.length < 3) {
        alert('Please enter at least 3 characters to search');
        return;
    }
    
    fetch(`/api/customers/search?q=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('customerSearchResults');
            if (data.customers && data.customers.length > 0) {
                let html = '<div class="list-group">';
                data.customers.forEach(customer => {
                    const tierIcon = customer.customer_tier === 'gold' ? '🥇' : 
                                    customer.customer_tier === 'silver' ? '🥈' : 
                                    customer.customer_tier === 'platinum' ? '💎' : '🥉';
                    html += `
                        <a href="#" class="list-group-item list-group-item-action" data-customer-id="${customer.id}" onclick="selectCustomer(${customer.id}, '${customer.name.replace(/'/g, "\\'")}', '${customer.phone}', '${customer.email || ''}')">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${customer.name} ${tierIcon}</h6>
                                <small>${customer.total_orders || 0} orders</small>
                            </div>
                            <p class="mb-1">${customer.phone} | ${customer.email || 'No email'}</p>
                            <small>Total spent: ₱${parseFloat(customer.total_spent || 0).toLocaleString('en-PH')}</small>
                        </a>
                    `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
            } else {
                resultsDiv.innerHTML = '<div class="alert alert-warning">No customers found</div>';
            }
        })
        .catch(error => {
            console.error('Error searching customers:', error);
        });
}

// Select customer from search results
function selectCustomer(id, name, phone, email) {
    currentCustomerId = id;
    
    // Fill form fields
    document.getElementById('customer_name').value = name;
    document.getElementById('customer_phone').value = phone.replace('+63', '');
    document.getElementById('customer_email').value = email;
    
    // Show selected customer card
    document.getElementById('selectedCustomerName').textContent = name;
    document.getElementById('selectedCustomerDetails').textContent = `${phone} | ${email || 'No email'}`;
    document.getElementById('selectedCustomerCard').style.display = 'block';
    
    // Hide search results
    document.getElementById('customerSearchResults').innerHTML = '';
    document.getElementById('customerSearch').value = '';
    
    // Show skip button
    document.getElementById('skipCustomerBtn').style.display = 'block';
}

// Save customer
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
    
    fetch('/api/customers/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(customerData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentCustomerId = data.customer.id;
            
            // Set customer for cart
            return fetch('/api/cart/customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ customer_id: data.customer.id })
            });
        } else {
            throw new Error(data.message || 'Failed to save customer');
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show selected customer card
            document.getElementById('selectedCustomerName').textContent = customerData.name;
            document.getElementById('selectedCustomerDetails').textContent = `${customerData.phone} | ${customerData.email || 'No email'}`;
            document.getElementById('selectedCustomerCard').style.display = 'block';
            
            // Hide customer form
            document.getElementById('customerForm').style.display = 'none';
            
            // Show skip button
            document.getElementById('skipCustomerBtn').style.display = 'block';
            
            // Proceed to step 2
            goToStep(2);
        }
    })
    .catch(error => {
        console.error('Error saving customer:', error);
        alert('Error saving customer: ' + error.message);
    });
}

// Skip customer (for walk-in/quick sales)
function skipCustomer() {
    currentCustomerId = null;
    goToStep(2);
}

// Change customer
function changeCustomer() {
    currentCustomerId = null;
    document.getElementById('selectedCustomerCard').style.display = 'none';
    document.getElementById('customerForm').style.display = 'block';
    document.getElementById('skipCustomerBtn').style.display = 'none';
}

// Select product type
function selectProductType(productType) {
    currentProductType = productType;
    
    // Update UI
    document.querySelectorAll('.product-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    
    // Load product configuration
    loadProductConfiguration(productType);
}

// Load product configuration
function loadProductConfiguration(productType) {
    const configDiv = document.getElementById('productConfiguration');
    if (!configDiv) return;
    
    // Show configuration section
    configDiv.style.display = 'block';
    
    // Load departments for this product type
    fetch(`/api/cart/departments/${productType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '';
                
                if (productType === 'garment') {
                    html = getGarmentConfiguration(data.departments);
                } else if (productType === 'tarpaulin') {
                    html = getTarpaulinConfiguration(data.departments);
                } else if (productType === 'embroidery') {
                    html = getEmbroideryConfiguration(data.departments);
                }
                
                configDiv.innerHTML = html;
                
                // Add event listener to add to cart button
                const addToCartBtn = document.getElementById('addToCartBtn');
                if (addToCartBtn) {
                    addToCartBtn.addEventListener('click', addItemToCart);
                }
            }
        })
        .catch(error => {
            console.error('Error loading departments:', error);
        });
}

// Garment configuration form
function getGarmentConfiguration(departments) {
    return `
        <div class="row g-3">
            <div class="col-md-4">
                <label for="garment_brand" class="form-label">Brand *</label>
                <select class="form-control" id="garment_brand" required>
                    <option value="">Select brand</option>
                    <option value="gildan">Gildan</option>
                    <option value="fruit_of_the_loom">Fruit of the Loom</option>
                    <option value="hanes">Hanes</option>
                    <option value="champion">Champion</option>
                    <option value="bella_canvas">Bella + Canvas</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="garment_size" class="form-label">Size *</label>
                <select class="form-control" id="garment_size" required>
                    <option value="">Select size</option>
                    <option value="s">Small (S)</option>
                    <option value="m">Medium (M)</option>
                    <option value="l">Large (L)</option>
                    <option value="xl">Extra Large (XL)</option>
                    <option value="xxl">2XL</option>
                    <option value="xxxl">3XL</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="garment_department" class="form-label">Department *</label>
                <select class="form-control" id="garment_department" required>
                    <option value="">Select department</option>
                    ${departments.map(dept => `<option value="${dept.id}">${dept.name} (${dept.code})</option>`).join('')}
                </select>
            </div>
        </div>
        
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label for="print_area" class="form-label">Print Area</label>
                <select class="form-control" id="print_area">
                    <option value="front">Front</option>
                    <option value="back">Back</option>
                    <option value="sleeve">Sleeve</option>
                    <option value="pocket">Pocket</option>
                    <option value="front_back">Front & Back</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="print_width" class="form-label">Print Width (inches)</label>
                <input type="number" class="form-control" id="print_width" min="1" max="24" value="12">
            </div>
            <div class="col-md-4">
                <label for="print_height" class="form-label">Print Height (inches)</label>
                <input type="number" class="form-control" id="print_height" min="1" max="24" value="12">
            </div>
        </div>
        
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label for="garment_color" class="form-label">Garment Color</label>
                <input type="text" class="form-control" id="garment_color" placeholder="White, Black, etc.">
            </div>
            <div class="col-md-4">
                <label for="garment_quantity" class="form-label">Quantity *</label>
                <input type="number" class="form-control" id="garment_quantity" min="1" value="1" required>
            </div>
            <div class="col-md-4">
                <label for="garment_price" class="form-label">Price Each *</label>
                <div