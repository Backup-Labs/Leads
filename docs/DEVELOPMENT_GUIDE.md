# Developer Guide — Leads SaaS

Welcome to the **Leads** developer guide. This document provides setup steps, coding standards, directory conventions, and local workflow instructions.

---

## 1. Prerequisites

Ensure your local development environment has:
- **PHP:** 8.2 or higher (with `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `bcmath`).
- **Composer:** 2.8+
- **Node.js:** 20+ (Node 22 LTS recommended)
- **NPM:** 10+
- **PostgreSQL:** 16+ or 17
- **Docker & Docker Compose:** (Optional, for containerized local services)

---

## 2. Quickstart Setup

```bash
# 1. Clone repository
git clone https://github.com/your-org/leads.git
cd leads

# 2. Install backend dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Copy environment configuration
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure PostgreSQL in .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=leads_dev
# DB_USERNAME=postgres
# DB_PASSWORD=your_password

# 7. Run database migrations & seeders
php artisan migrate:fresh --seed

# 8. Start development servers
# Terminal 1: Laravel Backend
php artisan serve

# Terminal 2: Vite Frontend Dev Server
npm run dev

# Terminal 3: Queue Worker
php artisan queue:work
```

---

## 3. Directory Layout & Conventions

```
app/
├── Actions/                 # Single-responsibility business actions (CreateLead, CalculateQuote, etc.)
├── Domain/                  # Domain-specific logic, contracts & enums
│   ├── Activities/
│   ├── Identity/
│   ├── Leads/
│   ├── Organizations/
│   ├── Pipeline/
│   ├── Quotations/
│   ├── Recovery/
│   └── Subscriptions/
├── Http/
│   ├── Controllers/         # Thin HTTP controllers
│   ├── Middleware/          # TenantContext, RBAC, etc.
│   ├── Requests/            # Form request validation
│   └── Resources/           # API JSON serialization
├── Models/                  # Eloquent models with BelongsToOrganization trait
├── Policies/                # Authorization policies (LeadPolicy, QuotationPolicy, etc.)
└── Scopes/                  # TenantScope global query scope

resources/
├── js/
│   ├── components/          # Reusable Vue components (UI buttons, cards, modals, tables)
│   ├── layouts/             # AppLayout, AuthLayout, GuestLayout
│   ├── pages/               # Inertia page views (Dashboard, Leads, Pipeline, Quotes, Recovery, Settings)
│   ├── composables/         # Vue composables (useTenant, usePermissions, useToast)
│   ├── types/               # TypeScript interfaces & types
│   └── utils/               # Currency, date, formatting helpers
└── css/
    └── app.css              # Tailwind styles and custom design tokens
```

---

## 4. Coding Standards

1. **Strict Types:** Always use `declare(strict_types=1);` in PHP classes.
2. **TypeScript First:** In Vue components, use `<script setup lang="ts">`. Avoid `any`. Define and import models from `@/types`.
3. **Thin Controllers:** Never put more than 15 lines of logic in a controller method. Delegate validation to Form Requests and logic to Domain Actions.
4. **Tenant Isolation:** Never execute raw un-scoped queries like `DB::table('leads')->get()`. Always use `Lead::query()` or ensure `where('organization_id', ...)` is explicitly added.
5. **No Floating Point Money:** Always use `numeric(14, 2)` or integer cents. Use `bcmath` or standard decimal string representation.

