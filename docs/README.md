# CLASS Apparel PH - Laravel Application Documentation

## 📋 Overview
This is the main Laravel application for CLASS Apparel PH, a printing and apparel business. The application handles sales, expenses, order management, and customer relationships.

**Live URL:** https://app.classapparelph.com

## 🎯 Core Philosophy
**Minimal JavaScript, Maximum Laravel/Blade**
- Use JavaScript ONLY for visual effects and UI enhancements
- Core functionality MUST be implemented in Laravel/Blade
- Server-side validation and processing preferred
- Keep frontend simple and maintainable

## 🏗️ Architecture

### Directory Structure
```
app.classapparelph.com/
├── app/                    # Laravel application core
│   ├── Http/              # Controllers
│   ├── Models/            # Database models
│   └── View/              # View components
├── database/              # Migrations, seeders
├── public/               # Public assets
├── resources/            # Views, CSS, JS
│   └── views/
│       └── sales/        # Sales system views
├── routes/               # Application routes
└── storage/              # Uploads, logs, cache
```

### Key Components
1. **Sales System** - Order management and processing
2. **Expense Tracking** - Business expense management
3. **Product Catalog** - Product inventory and management
4. **User Management** - Role-based access control
5. **Reporting** - Sales and expense reports

## 📊 Database Schema

### Core Tables
- **users** - User accounts and authentication
- **products** - Product catalog
- **sales** - Sales transactions
- **sale_items** - Individual items in sales
- **expenses** - Business expenses
- **categories** - Product/expense categories
- **roles** - User roles and permissions

### Relationships
- Users → Sales (One-to-Many)
- Sales → Sale Items (One-to-Many)
- Products → Sale Items (One-to-Many)
- Users → Roles (Many-to-Many)

## 🛒 Sales System

### Order Forms
The application features multiple order form types:

#### 1. iPrint Form
**Purpose:** Standard printing services
**Sub-forms:**
- DTF (Direct-to-Film) Printing
- Lanyard Printing
- Tarpaulin Printing
- Sublimation Printing
- Embroidery
- Other Items

#### 2. Consol Form
**Purpose:** Consolidated printing services
**Implementation:** Similar to iPrint but with different pricing/options

#### 3. Class Form
**Purpose:** Training/class services
**Fields:**
- Class Type
- Participant Count
- Duration
- Date & Time
- Requirements

#### 4. Cinco Form
**Purpose:** Package deals/services
**Fields:**
- Package Type
- Service Category
- Quantity
- Duration
- Priority
- Dates
- Details

### Form Implementation Principles
1. **Server-side form handling** - Use Laravel form requests
2. **Blade components** - Reusable form components
3. **Minimal JavaScript** - Only for UI enhancements
4. **Real-time updates via Livewire** (if needed, but prefer page reloads)

## 🔧 Development Guidelines

### JavaScript Usage Policy
**✅ ALLOWED:**
- Form validation feedback
- UI animations and transitions
- Modal dialogs and popups
- Real-time form field updates (debounced)
- Image previews and upload progress

**❌ AVOID:**
- Core form submission logic
- Data validation (do this server-side)
- Business logic in frontend
- Complex state management
- AJAX for critical operations

### Code Standards
1. **Controllers** - Keep thin, delegate to services
2. **Models** - Use Eloquent relationships
3. **Views** - Use Blade components and layouts
4. **CSS** - Use Tailwind CSS utility classes
5. **JavaScript** - Vanilla JS or Alpine.js (no heavy frameworks)

### Security Practices
1. **CSRF Protection** - All forms include CSRF tokens
2. **Input Validation** - Laravel form requests
3. **SQL Injection Prevention** - Eloquent ORM
4. **XSS Protection** - Blade escaping
5. **Authentication** - Laravel Breeze with role-based access

## 🚀 Deployment

### Server Configuration
- **Web Server:** Apache 2.4
- **PHP Version:** 8.2+
- **Database:** MariaDB 10.6+
- **SSL:** Let's Encrypt certificates
- **Domain:** app.classapparelph.com

### Environment Variables
Key environment variables in `.env`:
```
APP_URL=https://app.classapparelph.com
DB_DATABASE=classapparelph
DB_USERNAME=classapparelph_user
DB_PASSWORD=********
```

### Deployment Steps
1. `git pull origin main`
2. `composer install --no-dev`
3. `npm install && npm run build`
4. `php artisan migrate --force`
5. `php artisan config:cache`
6. `php artisan route:cache`
7. `php artisan view:cache`

## 📝 User Roles

### Role Hierarchy
1. **Administrator** - Full system access
2. **Developer** - Technical implementation
3. **Manager** - Business operations
4. **Employee** - Task execution
5. **Customer** - View-only access

### Permissions
- **Sales Creation:** Manager, Employee
- **Expense Management:** Manager
- **Product Management:** Manager, Developer
- **User Management:** Administrator
- **Reporting:** Manager, Administrator

## 🔍 Troubleshooting

### Common Issues
1. **Form not submitting** - Check CSRF token, JavaScript errors
2. **Database errors** - Check migrations, connection
3. **Permission issues** - Verify user roles
4. **Slow performance** - Check cache, database indexes

### Debug Mode
Enable debug mode in `.env`:
```
APP_DEBUG=true
```

### Logs
Check application logs:
```
tail -f storage/logs/laravel.log
```

## 📚 Additional Documentation

- [Form System Guide](forms.md) - Detailed form documentation
- [API Reference](api.md) - REST API endpoints
- [Database Schema](database.md) - Complete database documentation
- [User Guide](user-guide.md) - End-user documentation
- [Developer Guide](developer-guide.md) - Technical implementation details

---

**Last Updated:** March 13, 2026  
**Maintainer:** Andrew (Developer)  
**Version:** 2.0