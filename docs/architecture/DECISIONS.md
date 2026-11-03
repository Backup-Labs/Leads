# Architecture Decision Records (ADRs) — Leads SaaS

This document logs significant architectural decisions made during the design and implementation of Leads.

---

## ADR-001: Modular Monolith vs. Microservices

### Context
Leads is a B2B SaaS platform that orchestrates inquiries, pipelines, quotes, and revenue recovery. While microservices offer decoupled deployments, they introduce massive distributed transaction costs, data consistency synchronization issues, operational network complexity, and development friction.

### Decision
Adopt a **Modular Monolithic Architecture** in Laravel 11/12 with explicitly defined domain boundaries (`app/Domain/*` and `app/Actions/*`).

### Consequences
- **Positive:** Fast unified testing, simple atomic database transactions across lead updates and activity logging, straightforward Docker deployment.
- **Negative:** Requires rigorous internal discipline to prevent leaky abstractions between modules.

---

## ADR-002: PostgreSQL 17 as Unified Database Engine

### Context
Using SQLite in development and PostgreSQL in production introduces subtle dialect incompatibilities (e.g. JSONB queries, filtering syntax like `FILTER (WHERE ...)`, strict type checking, and timestamp precision).

### Decision
Standardize exclusively on **PostgreSQL 17** across all local development, testing, CI pipelines, and production environments.

### Consequences
- **Positive:** Full compatibility with advanced PostgreSQL capabilities (e.g. JSONB operators, CTEs, window functions for recovery rules, native UUIDs).
- **Negative:** Requires developers to have PostgreSQL running locally or via Docker Compose.

---

## ADR-003: Inertia.js with Vue 3 & TypeScript

### Context
Traditional decoupled SPAs (e.g. separate Vue app consuming REST API) require duplicating models, routing, form validation schemas, and managing dual authentication flows. Traditional server-rendered Blade views lack modern desktop-grade interactivity (Kanban drag-and-drop, real-time modals, fluid transitions).

### Decision
Adopt **Inertia.js** with **Vue 3 (Composition API)** and **TypeScript**.

### Consequences
- **Positive:** True SPA user experience with zero API boilerplate; server returns Vue page components with typed props; automatic CSRF protection; seamless navigation without page reloads.
- **Negative:** Client routing is tied to server controller responses; requires Node build pipeline for Vite.

---

## ADR-004: Shared Database Multi-Tenancy with Scoped Row Isolation

### Context
Multi-tenant architectures can use:
1. Separate database per tenant (complex migrations, high connection pool overhead).
2. Separate schema per tenant (PostgreSQL schema switching complexity).
3. Shared database with shared tables and column tenant isolation.

### Decision
Implement **Shared Database with Column Tenant Isolation** (`organization_id`). Every tenant record has a mandatory `organization_id` column, enforced by Eloquent Global Scope (`TenantScope`), `BelongsToOrganization` trait, and backend Policies.

### Consequences
- **Positive:** Low infrastructure cost, trivial tenant provisioning, simple database-wide aggregations and schema migrations.
- **Negative:** Strict multi-tenant security testing required to guarantee zero IDOR or cross-tenant query leakage.

---

## ADR-005: Session-Based First-Party Authentication

### Context
Single-page apps often jump to JWT tokens stored in `localStorage`, creating severe XSS vulnerability vectors and complicated token refresh choreography.

### Decision
Use Laravel's hardened **Session-based authentication with encrypted HTTP-only, SameSite cookies** for the web application, supplemented by **Laravel Sanctum** tokens strictly for programmatic external API access.

### Consequences
- **Positive:** Complete protection against client-side script token theft; automatic CSRF integration with Axios / Inertia.
- **Negative:** Session state must be stored in Redis in multi-server horizontal load-balancing configurations.

---

## ADR-006: Asynchronous Queues & Deterministic Recovery Engine

### Context
Leads' core value proposition is **Revenue Recovery** (identifying stalled deals, unresponded quotes, and overdue follow-ups). Running complex scans on every HTTP request would degrade user response times.

### Decision
Implement a deterministic rule engine executed by **Laravel Scheduler** and dispatched through background **Queues**. Provide an on-demand scan action scoped strictly to the current tenant for manual refreshes.

### Consequences
- **Positive:** High user interface responsiveness; background notification delivery; audit trail generation without latency.
- **Negative:** Requires background queue worker process (`php artisan queue:work`) running continuously.

