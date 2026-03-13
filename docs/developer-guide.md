# Developer Guide

## 🎯 Development Philosophy

**Core Principle:** Minimal JavaScript, Maximum Laravel
- JavaScript is for UI enhancements ONLY
- Business logic belongs in Laravel
- Forms should work without JavaScript
- Progressive enhancement over complex frontend

## 🏗️ Project Structure

### Directory Overview
```
/var/www/app.classapparelph.com/
├── app/                          # Application core
│   ├── Console/                  # Artisan commands
│   ├── Exceptions/               # Exception handlers
│   ├── Http/                     # HTTP layer
│   │   ├── Controllers/          # Controllers
│   │   ├── Middleware/           # Middleware
│   │   └── Requests/             # Form requests
│   ├── Models/                   # Eloquent models
│   ├── Providers/                # Service providers
│   └── Services/                 # Business logic services
├── bootstrap/                    # Framework bootstrapping
├── config/                       # Configuration files
├── database/                     # Database
│   ├── factories/                # Model factories
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── public/                       # Public assets
├── resources/                    # Application resources
│   ├── css/                      # CSS files
│   ├── js/                       # JavaScript files
│   └── views/                    # Blade templates
│       ├── auth/                 # Authentication views
│       ├── components/           # Blade components
│       ├── layouts/              # Layout templates
│       └── sales/                # Sales system views
├── routes/                       # Route definitions
├── storage/                      # Storage
│   ├── app/                      # Application files
│   ├── framework/                # Framework files
│   └── logs/                     # Log files
└── tests/                        # Automated tests
```

## 🔧 Development Setup

### Prerequisites
- PHP 8.2+
- Composer 2.5+
- Node.js 18+
- MariaDB 10.6+
- Git

### Installation Steps
```bash
# Clone repository
git clone git@github.com:cyddalupan/classapparelph.git
cd classapparelph

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=classapparelph
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

### Development Commands
```bash
# Run tests
php artisan test

# Generate migration
php artisan make:migration create_table_name

# Generate model with migration
php artisan make:model ModelName -m

# Generate controller
php artisan make:controller ControllerName

# Clear caches
php artisan optimize:clear

# View routes
php artisan route:list
```

## 📁 Key Files and Components

### 1. Sales System (`/sales/create-quick`)
**Main File:** `resources/views/sales/create-quick.blade.php`

#### Structure
```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Order Type Selection -->
    <div class="order-type-buttons">
        <button data-order-type="iprint">iPrint</button>
        <button data-order-type="consol">Consol</button>
        <button data-order-type="class">Class</button>
        <button data-order-type="cinco">Cinco</button>
    </div>
    
    <!-- Form Containers -->
    <div id="iprint-form-container" style="display: none;">
        @include('sales.forms.iprint')
    </div>
    
    <!-- Order Items Table -->
    <div class="order-items-table">
        @include('sales.components.order-table')
    </div>
</div>
@endsection

@push('scripts')
<script>
// Minimal JavaScript for UI only
</script>
@endpush
```

### 2. Form Components
**Location:** `resources/views/sales/forms/`

#### DTF Form (`dtf.blade.php`)
```blade
<div id="dtf-form" class="printing-form">
    <form method="POST" action="{{ route('sales.store-dtf') }}">
        @csrf
        
        <!-- Print Type Selection -->
        <div class="form-group">
            <label for="print_type">Print Type</label>
            <select name="print_type" id="print_type" class="form-control" required>
                <option value="">Select Print Type</option>
                <option value="standard">Standard Print</option>
                <option value="shirt">Shirt Print</option>
                <option value="hoodie">Hoodie Print</option>
            </select>
        </div>
        
        <!-- Size and Quantity Table -->
        <div class="size-quantity-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="size-rows">
                    <!-- Rows added dynamically with minimal JS -->
                </tbody>
            </table>
            <button type="button" class="btn btn-secondary" onclick="addSizeRow()">
                Add Size
            </button>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">
            Add to Order
        </button>
    </form>
</div>
```

### 3. JavaScript Guidelines

#### Allowed JavaScript Patterns
```javascript
// 1. Simple form visibility toggles
function showForm(formId) {
    document.querySelectorAll('.form-container').forEach(container => {
        container.style.display = 'none';
    });
    document.getElementById(formId).style.display = 'block';
}

// 2. Debounced UI updates
const debounce = (func, wait) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
};

// 3. Event delegation for performance
document.getElementById('order-table').addEventListener('click', (e) => {
    if (e.target.classList.contains('edit-btn')) {
        openEditModal(e.target.dataset.itemId);
    }
});

// 4. Simple form validation (UX only)
function validateForm(form) {
    let isValid = true;
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });
    return isValid;
}
```

#### Disallowed JavaScript Patterns
```javascript
// ❌ DON'T: Business logic in JavaScript
function calculateOrderTotal() {
    // BAD: Calculating prices in JavaScript
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const price = parseFloat(row.dataset.price);
        const quantity = parseInt(row.querySelector('.quantity').value);
        total += price * quantity; // Business logic!
    });
    return total;
}

// ❌ DON'T: Complex state management
class OrderState {
    constructor() {
        this.items = [];
        this.customer = null;
        this.payment = {};
        // BAD: Managing business state in JavaScript
    }
}

// ❌ DON'T: AJAX for critical operations
async function submitOrder() {
    // BAD: Submitting order via AJAX instead of standard form
    const response = await fetch('/api/orders', {
        method: 'POST',
        body: JSON.stringify(orderData)
    });
    // Business logic continues in JavaScript...
}
```

## 🎨 Styling Guidelines

### CSS Framework
- **Primary:** Bootstrap 5.3
- **Utilities:** Tailwind CSS (minimal usage)
- **Custom:** Custom CSS in `resources/css/`

### CSS Organization
```css
/* Component-based CSS */
.printing-form {
    /* Form container styles */
}

.order-items-table {
    /* Table styles */
}

/* Utility classes */
.text-currency {
    font-family: monospace;
}

.badge-complete {
    background-color: #28a745;
    color: white;
}

.badge-pending {
    background-color: #ffc107;
    color: black;
}

/* Responsive design */
@media (max-width: 768px) {
    .form-container {
        padding: 1rem;
    }
    
    .order-table {
        font-size: 0.875rem;
    }
}
```

### Color Scheme
```css
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
}
```

## 🗄️ Database Development

### Migration Guidelines
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained();
            $table->enum('item_type', [
                'dtf', 'lanyard', 'tarpaulin', 
                'sublimation', 'embroidery', 'other',
                'class', 'cinco', 'consol'
            ]);
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->json('details')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['sale_id', 'item_type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_items');
    }
};
```

### Model Development
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'customer_id',
        'user_id',
        'sale_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'notes'
    ];
    
    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];
    
    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    
    // Business logic methods
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total_price');
        $this->tax = $this->subtotal * 0.12; // 12% tax
        $this->total = $this->subtotal + $this->tax - $this->discount;
    }
    
    // Scope methods
    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }
    
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }
}
```

## 🔒 Security Implementation

### Form Request Validation
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DtfOrderRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Sale::class);
    }
    
    public function rules()
    {
        return [
            'print_type' => 'required|string|max:255',
            'sizes' => 'required|array|min:1',
            'sizes.*.size' => 'required|string',
            'sizes.*.quantity' => 'required|integer|min:1',
            'sizes.*.price' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'sizes.required' => 'At least one size is required',
            'sizes.*.quantity.min' => 'Quantity must be at least 1',
        ];
    }
}
```

### Middleware Protection
```php
// In app/Http/Kernel.php
protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'role' => \App\Http\Middleware\CheckRole::class,
    'permission' => \App\Http\Middleware\CheckPermission::class,
];

// Role-based middleware
class CheckRole
{
    public function handle($request, Closure $next, $role)
    {
        if (!$request->user()->hasRole($role)) {
            abort(403, 'Unauthorized access');
        }
        return $next($request);
    }
}

// Usage in routes
Route::middleware(['auth', 'role:manager'])
     ->group(function () {
         Route::resource('sales', SaleController::class);
     });
```

## 🧪 Testing Strategy

### Unit Tests
```php
<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\User;
use Tests\TestCase;

class SaleTest extends TestCase
{
    public function test_sale_totals_calculation()
    {
        $sale = Sale::factory()->create();
        $sale->items()->createMany([
            ['unit_price' => 100, 'quantity' => 2, 'total_price' => 200],
            ['unit_price' => 50, 'quantity' => 3, 'total_price' => 150],
        ]);
        
        $sale->calculateTotals();
        
        $this->assertEquals(350, $sale->subtotal);
        $this->assertEquals(42, $sale->tax); // 12% of 350
        $this->assertEquals(392, $sale->total); // 350 + 42
    }
    
    public function test_sale_requires_customer()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        $response = $this->post('/sales', [
            // Missing required customer_id
        ]);
    }
}
```

### Feature Tests
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_manager_can_create_sale()
    {
        $user = User::factory()->manager()->create();
        
        $response = $this->actingAs($user)
            ->post('/sales', [
                'customer_id' => 1,
                'items' => [
                    ['product_id' => 1, 'quantity' => 2]
                ]
            ]);
        
        $response->assertRedirect('/sales')
            ->assertSessionHas('success');
        
        $this->assertDatabaseHas('sales', ['user_id' => $user->id]);
    }
    
    public function test_employee_cannot_delete_sale()
    {
        $user = User::factory()->employee()->create();
        $sale = Sale::factory()->create();
        
        $response = $this->actingAs($user)
            ->delete("/sales/{$sale->id}");
        
        $response->assertStatus(403); // Forbidden
    }
}
```

### Browser Tests (Minimal)
```php
<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SalesFormTest extends DuskTestCase
{
    public function test_dtf_form_visibility()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/sales/create-quick')
                ->click('@iprint-button')
                ->click('@dtf-button')
                ->assertVisible('#dtf-form');
        });
    }
    
    public function test_form_submission()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/sales/create-quick')
                ->type('customer_name', 'John Doe')
                ->press('Submit Order')
                ->assertPathIs('/sales')
                ->assertSee('Order created successfully');
        });
    }
}
```

## 🚀 Deployment

### Production Deployment Checklist
1. **Pre-deployment:**
   - Run all tests: `php artisan test`
   - Check migrations: `php artisan migrate:status`
   - Build assets: `npm run production`
   - Clear caches: `php artisan optimize:clear`

2. **Deployment:**
   - Put in maintenance: `php artisan down`
   - Pull latest code: `git pull origin main`
   - Install dependencies: `composer install --no-dev`
   - Run migrations: `php artisan migrate --force`
   - Clear caches: `php artisan optimize`
   - Take out of maintenance: `php artisan up`

3. **Post-deployment:**
   - Verify site is up
   - Test critical functionality
   - Check error logs
   - Monitor performance

### Environment Configuration
```env
# Production .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.classapparelph.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=classapparelph
DB_USERNAME=classapparelph_user
DB_PASSWORD=secure_password_here

# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Security
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
```

## 🔍 Debugging and Monitoring

### Laravel Telescope (Development Only)
```bash
# Install Telescope
composer require laravel/telescope
php artisan telescope:install
php artisan migrate

# Access at /telescope
```

### Error Tracking
```php
// Log errors properly
try {
    // Code that might fail
} catch (\Exception $e) {
    \Log::error('Sale creation failed', [
        'error' => $e->getMessage(),
        'user_id' => auth()->id(),
        'data' => $request->all()
    ]);
    
    return back()->with('error', 'Something went wrong');
}
```

### Performance Monitoring
```php
// Use Laravel Debugbar for development
composer require barryvdh/laravel-debugbar --dev

// Monitor slow queries
DB::enableQueryLog();
// Run queries
$queries = DB::getQueryLog();
```

## 📚 Code Standards

### PHP Standards
- Follow PSR-12 coding standard
- Use type hints and return types
- Document complex methods with PHPDoc
- Keep methods under 20 lines when possible

### Blade Standards
- Use `@include` for reusable components
- Keep logic in controllers, not views
- Use `@forelse` instead of `@if` with loops
- Escape output with `{{ }}` or `{!! !!}` carefully

### JavaScript Standards
- Use vanilla JavaScript when possible
- Keep functions under 15 lines
- Comment complex logic
- Avoid global variables

## 🆘 Common Issues and Solutions

### 1. Form Submission Issues
**Problem:** Form not submitting or redirecting incorrectly
**Solution:**
```php
// Check CSRF token
<form method="POST">
    @csrf
    <!-- Form fields -->
</form>

// Verify route names
Route::post('/sales', [SaleController::class, 'store'])
     ->name('sales.store');

// Check validation errors
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### 2. JavaScript Errors
**Problem:** JavaScript not working or throwing errors
**Solution:**
```javascript
// Add error handling
try {
    // JavaScript code
} catch (error) {
    console.error('JavaScript Error:', error);
    // Fallback to server-side functionality
}

// Check console for errors
// Use browser developer tools
// Test without JavaScript enabled
```

### 3. Performance Issues
**Problem:** Slow page loads or queries
**Solution:**
```php
// Optimize database queries
// BAD: N+1 query problem
foreach ($sales as $sale) {
    echo $sale->customer->name; // Queries customer for each sale
}

// GOOD: Eager loading
$sales = Sale::with('customer')->get();
foreach ($sales as $sale) {
    echo $sale->customer->name; // No additional queries
}

// Cache expensive operations
$sales = Cache::remember('today_sales', 3600, function () {
    return Sale::today()->with('customer')->get();
});
```

### 4. Deployment Issues
**Problem:** Application not working after deployment
**Solution:**
```bash
# Check common issues
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Check file permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Check error logs
tail -f storage/logs/laravel.log
```

## 🎯 Development Workflow

### 1. Feature Development
```bash
# Create feature branch
git checkout -b feature/new-form-type

# Make changes
# Write tests
# Update documentation

# Commit changes
git add .
git commit -m "Add new form type with validation"

# Push to repository
git push origin feature/new-form-type

# Create pull request
# Code review
# Merge to main
```

### 2. Bug Fix Workflow
```bash
# Reproduce bug
# Write failing test
# Fix bug
# Verify test passes
# Update documentation if needed
# Commit fix
```

### 3. Code Review Checklist
- [ ] Follows coding standards
- [ ] Includes tests
- [ ] Updates documentation
- [ ] No security issues
- [ ] Performance considered
- [ ] Backward compatible
- [ ] Error handling implemented

## 📞 Getting Help

### Internal Resources
- This documentation
- Code comments
- Test cases
- Memory files in workspace

### External Resources
- [Laravel Documentation](https://laravel.com/docs)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)

### Team Communication
- Discuss complex changes before implementing
- Document decisions in memory files
- Share knowledge through code reviews
- Ask for help when stuck

## 🔄 Continuous Improvement

### Regular Tasks
1. **Weekly:** Review error logs, optimize slow queries
2. **Monthly:** Update dependencies, review security
3. **Quarterly:** Refactor technical debt, update documentation
4. **Yearly:** Major version updates, architecture review

### Feedback Loop
```
User Feedback → Issue Tracking → Development → Testing → Deployment → Monitoring
      ↓              ↓              ↓           ↓          ↓           ↓
  Bug Reports    Prioritization  Code Changes  QA Tests  Live Update  Performance
```

### Learning Resources
- Laravel News newsletter
- PHP conferences and meetups
- Online courses (Laracasts, Udemy)
- Open source contributions

---

**Remember:** The goal is to build a maintainable, secure, and efficient application that serves the business needs while being easy to work with for future developers.

**Last Updated:** March 13, 2026  
**Maintainer:** Andrew (Developer)  
**Next Review:** June 13, 2026