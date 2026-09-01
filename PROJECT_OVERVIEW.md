# UltimatePOS - Project Overview

## What is this?

**Ultimate POS v6.12** is a commercial Point of Sale (POS) application built with **Laravel 9 (PHP 8.0+)**. It is sold via Codecanyon by **Ultimate Fosters** (brand of The Web Fosters).

The current deployment is customized in Spanish as **"Tu caja"**, targeting Latin America (timezone `America/La_Paz`).

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0+, Laravel 9.51 |
| Frontend | AdminLTE, Bootstrap, jQuery, Blade templates |
| Database | MySQL (`ultimate_pos`) |
| Auth | Laravel Passport (OAuth2), spatie/laravel-permission (RBAC) |
| Real-time | Pusher |
| PDF | DomPDF, mPDF |
| Charts | consoletvs/charts |
| Excel | maatwebsite/excel |
| Modules | nwidart/laravel-modules v9 |
| Payments | Stripe, PayPal, Razorpay, Paystack, Pesapal, MyFatoorah |

---

## Architecture

The app follows a **monolithic-modular** architecture:

- **Core** (`app/`): Standard Laravel structure with ~40 Eloquent models, 61 controllers, 17 middleware, 12 utility classes.
- **Modules** (`Modules/` + vendor): 23 modules managed via `nwidart/laravel-modules`. 4 physically in `/Modules`, others likely packaged in vendor.

### Active Modules (23 total)

| Module | Purpose |
|--------|---------|
| Superadmin | Multi-business SaaS, subscriptions, payment gateways |
| Accounting | Double-entry bookkeeping, trial balance, cash flow |
| ProductCatalogue | Product catalogue & menu management |
| Ticket | Support/ticket system |
| Essentials | Core POS essentials |
| AssetManagement | Asset tracking |
| Cms | Content management |
| Connector | External integrations |
| Crm | Customer relationship management |
| Ecommerce | Online store |
| FieldForce | Field sales/operations |
| Manufacturing | Production/manufacturing |
| Project | Project management |
| Repair | Repair shop management |
| Spreadsheet | Spreadsheet imports/exports |
| Woocommerce | WooCommerce sync |
| AiAssistance | AI-powered features (OpenAI) |
| Hms | Hotel management system |
| Gym | Gym management |
| InboxReport | Email inbox reporting |
| CustomDashboard | Custom dashboards |
| ZatcaIntegrationKsa | Saudi Arabia ZATCA e-invoicing |

---

## Core Features

- **Point of Sale (POS)**: Cash register, quick sales, barcode scanning
- **Sales & Purchases**: Full transaction lifecycle with returns
- **Inventory Management**: Products with variations, stock adjustments, stock transfers
- **Contacts**: Customers, suppliers, customer groups
- **Expense Management**: Recurring expenses, expense categories
- **Reports**: Sales, purchases, tax, profit/loss, stock reports
- **Restaurant Module**: Table management, kitchen orders, bookings
- **Barcode Generation**: Product and label barcodes
- **Multi-Business**: SaaS-ready with Superadmin module
- **Multi-Language**: English + Spanish (29 translation files)
- **Multi-Currency**: Configurable currencies
- **Tax Management**: Tax rates, tax groups, GST support
- **Discount System**: Per-product, per-transaction discounts
- **Payment Gateways**: 6+ gateways supported
- **Backup System**: spatie/laravel-backup
- **Activity Log**: Full audit trail via spatie/laravel-activitylog

---

## Project Structure

```
UltimatePOS/
├── app/                   # Core application code
│   ├── Http/Controllers/  # 61 controllers (SellPos, Purchase, Product, Contact, etc.)
│   ├── Http/Middleware/   # 17 middleware
│   ├── Models/            # ~40 Eloquent models
│   ├── Utils/             # 12 utility classes
│   └── Providers/         # 6 service providers
├── config/                # 34 configuration files
├── database/migrations/   # ~300 migration files
├── Modules/               # Physical module directories (4 present)
├── resources/views/       # 65+ Blade template directories
├── resources/plugins/     # 25 frontend libraries
├── routes/web.php         # 530 lines of web routes
├── public/                # Web root
└── lang/es/               # 29 Spanish translation files
```

---

## Key Configuration

- **APP_NAME**: "Tu caja"
- **APP_LOCALE**: Spanish (`es`)
- **APP_TIMEZONE**: `America/La_Paz`
- **Database**: `ultimate_pos` on MySQL
- **Mail**: `mail.tucaja.store` (SMTP, port 465)

---

## Dependencies to Know

- `laravel/passport` - API authentication (OAuth2)
- `spatie/laravel-permission` - Role-based access control
- `spatie/laravel-activitylog` - Audit trail
- `spatie/laravel-backup` - Application backups
- `yajra/laravel-datatables` - Server-side DataTables
- `maatwebsite/excel` - Excel import/export
- `barryvdh/laravel-dompdf` + `mpdf/mpdf` - PDF generation
- `milon/barcode` - Barcode generation (Code128, EAN, UPC, etc.)
- `nwidart/laravel-modules` - Modular architecture
- `openai-php/laravel` - AI Assistant integration
- `guzzlehttp/guzzle` - HTTP client

---

## Environment

- **Current deployment**: Development/local (`APP_DEBUG=true`)
- **OS**: macOS (darwin)
- **Git**: Tracked in git repository
