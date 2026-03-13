# JavaScript Reference - Minimal Usage Guidelines

## 🚨 IMPORTANT: JavaScript Philosophy

**Use JavaScript ONLY when absolutely necessary.** Prefer Laravel/Blade solutions for all business logic, validation, and data processing.

## 📋 Allowed vs Disallowed JavaScript

### ✅ ALLOWED (UI Enhancements Only)
- Form field visibility toggles
- Modal dialog open/close
- Smooth animations and transitions
- Real-time input feedback (debounced)
- Image previews and upload progress
- Simple form validation (UX only, not security)
- Tab switching and accordions
- Tooltips and popovers

### ❌ DISALLOWED (Do in Laravel Instead)
- Form submission logic
- Data validation (security concern)
- Business calculations (pricing, totals)
- Database operations
- User authentication/authorization
- Complex state management
- Critical business logic

## 🔧 JavaScript Functions Reference

### Core Functions (Minimal Set)

#### 1. `showForm(formId)` - Form Visibility
**Purpose:** Show/hide form sections
**Usage:** Only for UI, no business logic
```javascript
function showForm(formId) {
    // Hide all forms first
    document.querySelectorAll('.form-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Show selected form
    const form = document.getElementById(formId);
    if (form) {
        form.style.display = 'block';
    }
}
```

#### 2. `openModal(modalId)` - Modal Dialogs
**Purpose:** Open modal windows
**Usage:** Simple show/hide only
```javascript
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }
}
```

#### 3. `debounce(func, wait)` - Performance Optimization
**Purpose:** Prevent excessive function calls
**Usage:** For real-time updates only
```javascript
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
```

#### 4. `calculateTotalQuantity()` - UI Updates Only
**Purpose:** Update quantity display (not for calculations)
**Usage:** Display only, actual calculation in Laravel
```javascript
function calculateTotalQuantity() {
    // Find quantity badges in current form instance
    const form = document.querySelector('.active-form');
    if (!form) return 0;
    
    const badges = form.querySelectorAll('[id*="-total-quantity"]');
    let total = 0;
    
    badges.forEach(badge => {
        const quantity = parseInt(badge.textContent) || 0;
        total += quantity;
    });
    
    return total;
}
```

#### 5. `updateTableDetails()` - Real-time UI Sync
**Purpose:** Update table when form changes
**Usage:** Debounced UI updates only
```javascript
const debouncedUpdate = debounce(function() {
    const quantity = calculateTotalQuantity();
    const detailsElement = document.querySelector('#order-details');
    
    if (detailsElement) {
        detailsElement.textContent = `Quantity: ${quantity}`;
    }
}, 300);

// Attach to form inputs
document.querySelectorAll('input, select').forEach(input => {
    input.addEventListener('input', debouncedUpdate);
    input.addEventListener('change', debouncedUpdate);
});
```

#### 6. `setupAutoUpdate()` - Form Event Handling
**Purpose:** Set up form change listeners
**Usage:** Event delegation for performance
```javascript
function setupAutoUpdate() {
    // Use event delegation instead of individual listeners
    document.addEventListener('input', function(e) {
        if (e.target.matches('input, select, textarea')) {
            debouncedUpdate();
        }
    });
}
```

#### 7. `showNotification(message, type)` - User Feedback
**Purpose:** Show success/error messages
**Usage:** UI feedback only
```javascript
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
```

#### 8. `validateForm(formElement)` - UX Validation Only
**Purpose:** Client-side form validation (UX only)
**Usage:** Never for security, always validate server-side
```javascript
function validateForm(formElement) {
    let isValid = true;
    const errors = [];
    
    // Check required fields
    formElement.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            errors.push(`${field.name} is required`);
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    // Show errors if any
    if (!isValid) {
        showNotification(`Please fix: ${errors.join(', ')}`, 'error');
    }
    
    return isValid;
}
```

## 🎯 Form-Specific Functions

### DTF Form Functions
```javascript
// DTF form specific functions
function setupDtfForm() {
    // Only UI setup, no business logic
    const dtfForm = document.getElementById('dtf-form');
    if (!dtfForm) return;
    
    // Show/hide size options based on print type
    const printTypeSelect = dtfForm.querySelector('#print_type');
    if (printTypeSelect) {
        printTypeSelect.addEventListener('change', function() {
            const shirtSizes = dtfForm.querySelector('.shirt-sizes');
            const printSizes = dtfForm.querySelector('.print-sizes');
            
            if (this.value === 'shirt_print') {
                shirtSizes.style.display = 'block';
                printSizes.style.display = 'none';
            } else {
                shirtSizes.style.display = 'none';
                printSizes.style.display = 'block';
            }
        });
    }
}
```

### Order Table Functions
```javascript
// Order items table management
function setupOrderTable() {
    const table = document.getElementById('order-items-table');
    if (!table) return;
    
    // Edit button click
    table.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-item')) {
            const itemId = e.target.dataset.itemId;
            openModal(`edit-modal-${itemId}`);
        }
        
        if (e.target.classList.contains('remove-item')) {
            if (confirm('Remove this item?')) {
                // Submit delete form (server-side)
                const form = e.target.closest('form');
                if (form) form.submit();
            }
        }
    });
}
```

## 🚫 Anti-Patterns - What NOT to Do

### ❌ BAD: Business logic in JavaScript
```javascript
// DON'T DO THIS - Calculate price in JavaScript
function calculateOrderTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const price = parseFloat(row.dataset.price) || 0;
        const quantity = parseInt(row.querySelector('.quantity').value) || 0;
        total += price * quantity; // BAD! Business logic in JS
    });
    
    document.getElementById('order-total').textContent = `$${total.toFixed(2)}`;
    return total;
}
```

### ✅ GOOD: Server-side calculation with UI update
```blade
<!-- In Blade template -->
<div id="order-total" data-total="{{ $calculatedTotal }}">
    Total: ${{ number_format($calculatedTotal, 2) }}
</div>
```

```javascript
// Only update if server-side value changes
function updateOrderDisplay() {
    const totalElement = document.getElementById('order-total');
    const currentTotal = parseFloat(totalElement.dataset.total) || 0;
    
    // Just display, don't calculate
    totalElement.textContent = `Total: $${currentTotal.toFixed(2)}`;
}
```

### ❌ BAD: JavaScript form submission with business logic
```javascript
// DON'T DO THIS
$('#order-form').submit(function(e) {
    e.preventDefault();
    
    // Collect form data
    const formData = $(this).serialize();
    
    // Make AJAX call
    $.ajax({
        url: '/api/orders',
        method: 'POST',
        data: formData,
        success: function(response) {
            // Process response - BAD!
            if (response.success) {
                updateInventory(response.inventory); // Business logic!
                showInvoice(response.invoice); // Business logic!
            }
        }
    });
});
```

### ✅ GOOD: Standard form submission
```blade
<!-- Standard form with server-side processing -->
<form method="POST" action="{{ route('orders.store') }}">
    @csrf
    <!-- Form fields -->
    <button type="submit">Place Order</button>
</form>
```

```php
// In Laravel controller
public function store(OrderRequest $request)
{
    // All business logic here
    $order = $this->orderService->createOrder($request->validated());
    $this->inventoryService->updateStock($order);
    $invoice = $this->invoiceService->generate($order);
    
    return redirect()->route('orders.show', $order)
        ->with('success', 'Order placed successfully')
        ->with('invoice', $invoice);
}
```

## 🔍 Debugging JavaScript

### Minimal Debugging Approach
```javascript
// Simple debug logging
function debug(message, data = null) {
    if (window.location.hostname === 'localhost' || 
        window.location.hostname === '127.0.0.1') {
        console.log(`[DEBUG] ${message}`, data || '');
    }
}

// Usage
debug('Form loaded', { formId: 'dtf-form', fields: 5 });
```

### Error Handling
```javascript
// Global error handler
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.message, e.filename, e.lineno);
    
    // Don't show technical errors to users
    showNotification('Something went wrong. Please try again.', 'error');
    
    // Report to server (optional)
    if (navigator.sendBeacon) {
        const data = new FormData();
        data.append('error', e.message);
        data.append('url', window.location.href);
        navigator.sendBeacon('/api/js-errors', data);
    }
});
```

## 📊 Performance Guidelines

### 1. Minimize DOM Manipulation
```javascript
// BAD: Multiple DOM updates
function updateFormBad() {
    document.getElementById('field1').value = data.field1;
    document.getElementById('field2').value = data.field2;
    document.getElementById('field3').value = data.field3;
}

// GOOD: Batch updates
function updateFormGood(data) {
    const fragment = document.createDocumentFragment();
    
    Object.keys(data).forEach(key => {
        const element = document.getElementById(key);
        if (element) {
            element.value = data[key];
            fragment.appendChild(element.cloneNode(true));
        }
    });
    
    // Single DOM update
    document.getElementById('form-container').innerHTML = '';
    document.getElementById('form-container').appendChild(fragment);
}
```

### 2. Use Event Delegation
```javascript
// BAD: Individual event listeners
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', handleEdit);
});

// GOOD: Event delegation
document.getElementById('order-table').addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-btn')) {
        handleEdit(e);
    }
});
```

### 3. Lazy Load JavaScript
```html
<!-- Load non-critical JS last -->
<script src="/js/main.js" defer></script>
<script src="/js/forms.js" defer></script>
```

## 🎯 Summary: JavaScript Usage Rules

1. **Rule of Thumb:** If it can be done in Laravel, do it in Laravel
2. **Validation:** Server-side only, JavaScript is for UX hints
3. **Calculations:** All business calculations server-side
4. **State:** Store state in database/session, not JavaScript
5. **Forms:** Use standard HTTP forms, not AJAX
6. **Updates:** Debounce UI updates, batch DOM changes
7. **Errors:** Handle gracefully, don't expose technical details
8. **Performance:** Minimize JavaScript, optimize what remains

---

**Remember:** Every JavaScript function is a liability. Write as little as possible, and make it as simple as possible.