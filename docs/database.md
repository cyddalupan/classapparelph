# Database Schema Documentation

## 📊 Overview
This document describes the database schema for the CLASS Apparel PH Laravel application. The database uses MariaDB/MySQL and follows Laravel Eloquent conventions.

## 🗄️ Database Structure

### Core Tables

#### 1. `users` - User Accounts
```sql
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
);
```

#### 2. `roles` - User Roles and Permissions
```sql
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `permissions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
);
```

#### 3. `products` - Product Catalog
```sql
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `sku` varchar(100) DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_quantity` int(11) NOT NULL DEFAULT '0',
  `min_stock` int(11) NOT NULL DEFAULT '10',
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
);
```

#### 4. `categories` - Product/Expense Categories
```sql
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('product','expense','both') NOT NULL DEFAULT 'product',
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`)
);
```

#### 5. `sales` - Sales Transactions
```sql
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `sale_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('cash','credit_card','bank_transfer','gcash','paymaya') DEFAULT 'cash',
  `payment_status` enum('pending','partial','paid','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_sale_number_unique` (`sale_number`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_user_id_foreign` (`user_id`),
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);
```

#### 6. `sale_items` - Individual Sale Items
```sql
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `item_type` enum('dtf','lanyard','tarpaulin','sublimation','embroidery','other','class','cinco','consol') NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
);
```

#### 7. `expenses` - Business Expenses
```sql
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_number` varchar(50) NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `expense_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('cash','credit_card','bank_transfer','gcash','paymaya') DEFAULT 'cash',
  `receipt_path` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_expense_number_unique` (`expense_number`),
  KEY `expenses_category_id_foreign` (`category_id`),
  KEY `expenses_user_id_foreign` (`user_id`),
  CONSTRAINT `expenses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);
```

#### 8. `customers` - Customer Information
```sql
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text,
  `company` varchar(255) DEFAULT NULL,
  `tax_id` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_email_unique` (`email`)
);
```

#### 9. `inventory_logs` - Inventory Tracking
```sql
CREATE TABLE `inventory_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` enum('purchase','sale','adjustment','return','damage') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_logs_product_id_foreign` (`product_id`),
  KEY `inventory_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `inventory_logs_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `inventory_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);
```

## 🔗 Relationships

### 1. User Relationships
```php
// User model relationships
class User extends Authenticatable
{
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
```

### 2. Sale Relationships
```php
// Sale model relationships
class Sale extends Model
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
```

### 3. Product Relationships
```php
// Product model relationships
class Product extends Model
{
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
    
    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
```

### 4. Category Relationships
```php
// Category model relationships
class Category extends Model
{
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
```

## 📈 Indexes for Performance

### Critical Indexes
```sql
-- Sales performance indexes
CREATE INDEX idx_sales_date ON sales(sale_date);
CREATE INDEX idx_sales_status ON sales(payment_status);
CREATE INDEX idx_sales_user ON sales(user_id);

-- Sale items indexes
CREATE INDEX idx_sale_items_sale ON sale_items(sale_id);
CREATE INDEX idx_sale_items_type ON sale_items(item_type);

-- Products indexes
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_products_stock ON products(stock_quantity);

-- Expenses indexes
CREATE INDEX idx_expenses_date ON expenses(expense_date);
CREATE INDEX idx_expenses_category ON expenses(category_id);

-- Inventory logs indexes
CREATE INDEX idx_inventory_product ON inventory_logs(product_id);
CREATE INDEX idx_inventory_date ON inventory_logs(created_at);
```

## 🔄 Data Flow

### 1. Sale Creation Flow
```
User → Creates Sale → Adds Sale Items → Updates Inventory → Generates Invoice
   ↓        ↓              ↓                  ↓                  ↓
Customer  Sale Record  SaleItem Records  Inventory Logs    PDF/Email Invoice
```

### 2. Inventory Update Flow
```
Sale/Expense → Triggers → Inventory Log → Updates → Product Stock
     ↓              ↓           ↓              ↓           ↓
Transaction    Quantity     Log Entry      Calculation  New Quantity
```

### 3. Reporting Flow
```
Database → Aggregates → Calculates → Formats → Displays
   ↓           ↓           ↓           ↓         ↓
Raw Data   Totals by    Metrics    Charts/    Dashboard
           Date/Type              Tables
```

## 🗃️ JSON Fields Usage

### `sale_items.details` Field Structure
```json
{
  "form_type": "dtf",
  "print_type": "standard",
  "sizes": [
    {
      "size": "8x10",
      "quantity": 5,
      "price_per_unit": 50.00
    },
    {
      "size": "12x18", 
      "quantity": 3,
      "price_per_unit": 80.00
    }
  ],
  "colors": ["white", "black"],
  "notes": "Rush order, need by Friday"
}
```

### `roles.permissions` Field Structure
```json
{
  "sales": {
    "create": true,
    "edit": true,
    "delete": false,
    "view_all": true
  },
  "expenses": {
    "create": true,
    "edit": false,
    "delete": false,
    "view_own": true
  },
  "products": {
    "create": false,
    "edit": false,
    "delete": false,
    "view": true
  },
  "reports": {
    "view_sales": true,
    "view_expenses": false,
    "view_profit": false
  }
}
```

## 🧹 Data Maintenance

### 1. Regular Cleanup Tasks
```sql
-- Archive old sales (older than 2 years)
INSERT INTO sales_archive SELECT * FROM sales WHERE sale_date < DATE_SUB(NOW(), INTERVAL 2 YEAR);
DELETE FROM sales WHERE sale_date < DATE_SUB(NOW(), INTERVAL 2 YEAR);

-- Clean orphaned records
DELETE FROM sale_items WHERE sale_id NOT IN (SELECT id FROM sales);
DELETE FROM inventory_logs WHERE product_id NOT IN (SELECT id FROM products);

-- Update product stock from inventory logs
UPDATE products p
SET stock_quantity = (
    SELECT new_quantity 
    FROM inventory_logs il 
    WHERE il.product_id = p.id 
    ORDER BY created_at DESC 
    LIMIT 1
)
WHERE EXISTS (
    SELECT 1 FROM inventory_logs WHERE product_id = p.id
);
```

### 2. Backup Strategy
```bash
# Daily backup script
mysqldump -u username -p database_name > /backups/database_$(date +%Y%m%d).sql
# Keep 7 daily backups, 4 weekly backups, 12 monthly backups
```

## 🔍 Query Optimization Examples

### 1. Sales Report Query
```sql
SELECT 
    DATE(s.sale_date) as sale_day,
    COUNT(DISTINCT s.id) as total_sales,
    COUNT(si.id) as total_items,
    SUM(s.total) as total_revenue,
    AVG(s.total) as average_sale
FROM sales s
LEFT JOIN sale_items si ON s.id = si.sale_id
WHERE s.sale_date BETWEEN ? AND ?
    AND s.payment_status = 'paid'
GROUP BY DATE(s.sale_date)
ORDER BY sale_day DESC;
```

### 2. Inventory Alert Query
```sql
SELECT 
    p.name,
    p.sku,
    p.stock_quantity,
    p.min_stock,
    c.name as category,
    CASE 
        WHEN p.stock_quantity <= 0 THEN 'Out of Stock'
        WHEN p.stock_quantity <= p.min_stock THEN 'Low Stock'
        ELSE 'In Stock'
    END as stock_status
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.is_active = 1
    AND (p.stock_quantity <= p.min_stock OR p.stock_quantity <= 0)
ORDER BY p.stock_quantity ASC;
```

### 3. Customer Purchase History
```sql
SELECT 
    c.name,
    c.email,
    COUNT(DISTINCT s.id) as total_purchases,
    SUM(s.total) as total_spent,
    MAX(s.sale_date) as last_purchase,
    DATEDIFF(NOW(), MAX(s.sale_date)) as days_since_last_purchase
FROM customers c
LEFT JOIN sales s ON c.id = s.customer_id
WHERE s.payment_status = 'paid'
GROUP BY c.id, c.name, c.email
HAVING total_purchases > 0
ORDER BY total_spent DESC;
```

## 🚀 Migration Strategy

### 1. New Feature Migrations
When adding new features:
```php
// Create migration
php artisan make:migration add_form_type_to_sale_items

// In migration file
public function up()
{
    Schema::table('sale_items', function (Blueprint $table) {
        $table->enum('form_type', ['iprint', 'consol', 'class', 'cinco'])
              ->default('iprint')
              ->after('item_type');
    });
}
```

### 2. Data Migration
When migrating existing data:
```php
public function up()
{
    // Add new column
    Schema::table('sale_items', function (Blueprint $table) {
        $table->json('details')->nullable()->after('total_price');
    });
    
    // Migrate existing data
    DB::statement("
        UPDATE sale_items 
        SET details = JSON_OBJECT(
            'form_type', 'iprint',
            'migrated', true
        )
        WHERE details IS NULL
    ");
}
```

## 📝 Best Practices

### 1. Naming Conventions
- Table names: plural (e.g., `users`, `products`, `sales`)
- Column names: snake_case (e.g., `created_at`, `stock_quantity`)
- Foreign keys: `table_name_id` (e.g., `user_id`, `category_id`)
- Primary keys: `id` (auto-incrementing bigint)

### 2. Data Types
- Use appropriate data types for each column
- Decimal for money: `DECIMAL(10,2)` (10 total digits, 2 decimal places)
- Text for long content, varchar for short content
- Use ENUM for fixed sets of values
- Use JSON for flexible, structured data

### 3. Indexing Strategy
- Index foreign key columns
- Index frequently searched columns
- Index columns used in WHERE, ORDER BY, JOIN clauses
- Avoid over-indexing (slows down writes)

### 4. Data Integrity
- Use foreign key constraints
- Set appropriate default values
- Use NOT NULL where appropriate
- Implement cascading deletes carefully

### 5. Performance
- Normalize data to 3rd normal form
- Denormalize for performance when needed
- Use appropriate data types to reduce storage
- Archive old data regularly

## 🔒 Security Considerations

### 1. Data Protection
- Never store passwords in plain text (use Laravel hashing)
- Encrypt sensitive customer information
- Implement proper access controls
- Audit sensitive operations

### 2. SQL Injection Prevention
- Use Laravel Eloquent or Query Builder
- Never concatenate user input into SQL
- Use parameterized queries
- Validate and sanitize all inputs

### 3. Backup Strategy
- Daily automated backups
- Test backup restoration regularly
- Off-site backup storage
- Versioned backups for point-in-time recovery

## 🚀 Deployment Checklist

### Before Migration
1. Backup current database
2. Test migrations on staging environment
3. Verify data integrity
4. Check for breaking changes

### During Migration
1. Put application in maintenance mode
2. Run migrations sequentially
3. Verify each migration success
4. Run seeders if needed

### After Migration
1. Test critical functionality
2. Verify data consistency
3. Update application cache
4. Monitor performance

## 📊 Monitoring and Maintenance

### Regular Tasks
1. **Daily:** Check error logs, monitor slow queries
2. **Weekly:** Review performance, optimize indexes
3. **Monthly:** Clean up old data, update statistics
4. **Quarterly:** Review schema, plan improvements

### Performance Monitoring
```sql
-- Slow queries
SHOW PROCESSLIST;
SHOW FULL PROCESSLIST;

-- Table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = DATABASE()
ORDER BY size_mb DESC;

-- Index usage
SELECT 
    table_name,
    index_name,
    rows_read,
    rows_changed
FROM information_schema.table_statistics
WHERE table_schema = DATABASE();
```

## 🆘 Troubleshooting

### Common Issues

#### 1. Slow Queries
```sql
-- Identify slow queries
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Check slow query log
SHOW VARIABLES LIKE 'slow_query_log%';
```

#### 2. Deadlocks
```sql
-- Check for deadlocks
SHOW ENGINE INNODB STATUS;

-- Prevent deadlocks
-- Use consistent transaction order
-- Keep transactions short
-- Use appropriate isolation levels
```

#### 3. Data Corruption
```sql
-- Check table health
CHECK TABLE table_name;
REPAIR TABLE table_name;

-- Optimize tables
OPTIMIZE TABLE table_name;
```

#### 4. Connection Issues
```sql
-- Check connection limits
SHOW VARIABLES LIKE 'max_connections';
SHOW STATUS LIKE 'Threads_connected';

-- Increase if needed
SET GLOBAL max_connections = 500;
```

## 📚 Additional Resources

### Laravel Documentation
- [Database: Migrations](https://laravel.com/docs/migrations)
- [Database: Seeding](https://laravel.com/docs/seeding)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Query Builder](https://laravel.com/docs/queries)

### MySQL/MariaDB Documentation
- [MySQL Official Documentation](https://dev.mysql.com/doc/)
- [MariaDB Knowledge Base](https://mariadb.com/kb/en/)
- [Performance Schema](https://dev.mysql.com/doc/refman/8.0/en/performance-schema.html)

### Tools
- **phpMyAdmin:** Web-based database management
- **MySQL Workbench:** Desktop database design tool
- **Laravel Telescope:** Debugging and monitoring
- **Laravel Debugbar:** Development toolbar

---

**Last Updated:** March 13, 2026  
**Database Version:** 2.0  
**Maintainer:** Andrew (Developer)  
**Next Review:** June 13, 2026