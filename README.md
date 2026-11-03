# Leads — B2B Lead Management & Revenue Recovery Platform

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2F12-red.svg)](https://laravel.com)
[![Vue Version](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![PostgreSQL Version](https://img.shields.io/badge/PostgreSQL-17-blue.svg)](https://www.postgresql.org)

> **Tagline:** *Turn Every Inquiry Into a Potential Customer.*

---

## 1. Product Introduction & Problem

Small and medium-sized businesses receive customer inquiries constantly across **WhatsApp, Instagram, Facebook, phone calls, web forms, walk-ins, and referrals**. However, most SMBs track these leads in scattered spreadsheets, chat threads, or physical notebooks.

### Consequences:
1. Forgotten follow-ups leading to abandoned deals.
2. Unmonitored quotations expiring with zero follow-through.
3. Lack of pipeline visibility and missed sales revenue.
4. Inability to hold sales representatives accountable.

### The Solution: Leads
**Leads** is a modern B2B SaaS platform engineered to manage the complete prospect lifecycle:
- **Lead Capture & Attribution:** Centralize inquiries from all channels with source tracking and assignment.
- **Visual Sales Pipeline:** Kanban drag-and-drop stages with custom win/loss handling.
- **Accountability & Activity Logging:** Call logs, meeting notes, emails, and stage transition history.
- **Follow-up Engine:** Automated scheduling, overdue reminders, and calendar views.
- **Quotation Engine:** Multi-item quotes with real-time tax/discount computation and PDF generation.
- **Revenue Recovery Engine (Central Differentiator):** Deterministic risk detection identifying stalled opportunities, expiring quotes, and neglected deals before revenue is permanently lost.
- **Executive Analytics:** True won revenue vs. active pipeline value, conversion velocity, and rep performance.

---

## 2. Brand Identity & Design System

The application interface is styled around an intentional, accessible color palette:
- **Dark Indigo (`#280047`):** Primary brand, navigation bars, and structural depth.
- **Red Orange (`#F74D01`):** High-priority actions, conversions, alerts, and call-to-actions.
- **Light Purple (`#C3B6D3`):** Secondary borders, dividers, and card accents.
- **Light Yellow (`#FDFEBA`):** Subtle status badges, warning tags, and attention highlights.
- **Main White (`#FFFFFF`):** High-contrast background canvas and card surfaces.
- **Dark Gray (`#242424`):** Crisp, readable body typography.

---

## 3. Technology Stack

- **Backend:** Laravel 11/12 (PHP 8.2+), Eloquent ORM, Form Requests, Policies, Queues, Scheduler.
- **Frontend:** Vue.js 3, Inertia.js, TypeScript, Tailwind CSS, Lucide Icons, Pinia.
- **Database:** PostgreSQL 17 (Strict row-level multi-tenancy, JSONB, composite indexes).
- **Queues & Cache:** Redis 7 (Local / Production).
- **Testing:** Pest & PHPUnit (Backend), Vitest (Frontend), Playwright (End-to-End).

---

## 4. Multi-Tenant Architecture

Leads uses a **Shared Database with Scoped Row Isolation**:
- Every tenant record contains `organization_id`.
- The Eloquent global scope `TenantScope` transparently enforces `organization_id = :current_tenant_id`.
- Tenant context is dynamically resolved via the authenticated user's session or API headers.
- Multi-tenant boundary tests ensure Organization A can never read, modify, or infer Organization B's records.

---

## 5. Getting Started (Local Development)

### Prerequisites
- PHP 8.2 or 8.3 with `pdo_pgsql`, `pgsql`, `bcmath`, `curl`, `mbstring`.
- Composer 2.8+
- Node.js 20+ & NPM 10+
- PostgreSQL 16 or 17

### Installation

```bash
# 1. Clone repository
git clone https://github.com/your-org/leads.git
cd leads

# 2. Install PHP & Node dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Configure PostgreSQL credentials in .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=leads_dev
# DB_USERNAME=postgres
# DB_PASSWORD=your_password

# 5. Run migrations & seed demo organization data
php artisan migrate:fresh --seed

# 6. Start servers
# Terminal 1: Backend
php artisan serve

# Terminal 2: Frontend
npm run dev

# Terminal 3: Queues
php artisan queue:work
```

---

## 6. Testing

```bash
# Run backend test suite
php artisan test

# Run tenant isolation verification tests
php artisan test --filter=TenantIsolationTest

# Run frontend tests
npm run test:unit
```

---

## 7. Architecture Documentation

Detailed architectural documentation is available in [`docs/`](file:///d:/2026-projects/Leads/docs):
- [System Architecture](file:///d:/2026-projects/Leads/docs/architecture/SYSTEM_ARCHITECTURE.md)
- [Database Design & ERD](file:///d:/2026-projects/Leads/docs/architecture/DATABASE_DESIGN.md)
- [API Documentation](file:///d:/2026-projects/Leads/docs/architecture/API_DOCUMENTATION.md)
- [Security & Threat Model](file:///d:/2026-projects/Leads/docs/architecture/SECURITY.md)
- [Performance & Scalability](file:///d:/2026-projects/Leads/docs/architecture/PERFORMANCE.md)
- [Deployment Guide](file:///d:/2026-projects/Leads/docs/architecture/DEPLOYMENT.md)
- [Architecture Decision Records](file:///d:/2026-projects/Leads/docs/architecture/DECISIONS.md)
- [OpenAPI Specification](file:///d:/2026-projects/Leads/docs/openapi.yaml)
- [Development Guide](file:///d:/2026-projects/Leads/docs/DEVELOPMENT_GUIDE.md)
- [Testing Strategy](file:///d:/2026-projects/Leads/docs/TESTING.md)
- [Product Roadmap](file:///d:/2026-projects/Leads/docs/ROADMAP.md)

