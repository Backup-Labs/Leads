# Security Architecture & Threat Model — Leads SaaS

## 1. Security Architecture Principles

Security is a primary design pillar in Leads. In a multi-tenant B2B SaaS platform handling high-value enterprise sales pipelines, quotes, and customer inquiries, data leakage between tenants is unacceptable.

---

## 2. Multi-Tenant Boundary Enforcement & IDOR Defense

### 2.1 Defense in Depth Layers
1. **Network / Middleware Layer:** The `ResolveTenantContext` middleware verifies the authenticated user has an active membership in the requested organization. If the user does not belong to the tenant, an immediate `403 Forbidden` response is returned.
2. **Eloquent Global Scope (`TenantScope`):** Every tenant model registers a query scope appending `WHERE organization_id = :tenant_id`. Direct lookups like `Lead::find($id)` are automatically constrained to the current tenant.
3. **Domain Action Layer:** Action classes explicitly verify `$lead->organization_id === $tenantContext->id`.
4. **Policy Layer (`LeadPolicy`, `QuotationPolicy`):** Laravel policies verify both tenant equality and role-based permissions before executing any view, update, delete, or export action.

### 2.2 Cross-Tenant Assignment Prevention
When assigning a lead or scheduling a follow-up:
- The assigned `user_id` must be validated against `organization_members` for the **same** `organization_id`.
- Attempting to assign an employee from Organization B to a lead in Organization A triggers a `422 Unprocessable Entity` validation error.

---

## 3. Role-Based Access Control (RBAC) Matrix

| Permission / Action | Owner | Admin | Manager | Sales Rep |
|---|:---:|:---:|:---:|:---:|
| Manage Organization Settings | ✅ | ❌ | ❌ | ❌ |
| Manage Subscriptions & Billing | ✅ | ❌ | ❌ | ❌ |
| Delete Organization | ✅ | ❌ | ❌ | ❌ |
| Invite & Remove Team Members | ✅ | ✅ | ❌ | ❌ |
| Modify Pipeline Stages & Sources | ✅ | ✅ | ❌ | ❌ |
| View Organization-Wide Analytics | ✅ | ✅ | ✅ | ❌ (Own only) |
| Reassign Leads Across Team | ✅ | ✅ | ✅ | ❌ |
| View All Organization Leads | ✅ | ✅ | ✅ | ✅ |
| Create & Edit Assigned Leads | ✅ | ✅ | ✅ | ✅ |
| Create Quotations | ✅ | ✅ | ✅ | ✅ |
| Approve / Send Quotations | ✅ | ✅ | ✅ | ✅ |
| Access Audit Logs | ✅ | ✅ | ❌ | ❌ |

---

## 4. Threat Model (STRIDE)

| Threat Category | Potential SaaS Risk | Leads Architectural Mitigation |
|---|---|---|
| **Spoofing** | Attacker impersonates another user or organization | Strict session auth with HTTP-only, SameSite=Lax cookies, CSRF tokens, signed quotation URLs. |
| **Tampering** | User alters quotation totals or updates leads belonging to another tenant | Server-side price recalculation from raw database line-items; tenant global scopes block cross-tenant updates. |
| **Repudiation** | User denies altering quotation or deleting high-value lead | Immutable `audit_logs` table recording `user_id`, `event`, timestamp, IP address, and JSON diffs of changes. |
| **Information Disclosure** | Competitor discovers client leads via sequential ID guessing | Global scopes prevent IDOR; public quotation URLs use high-entropy random UUID tokens (`token`). |
| **Denial of Service** | Malicious script spamming lead creation or CSV imports | Route rate limiting (`throttle:api`, `throttle:60,1`), file size validation (< 5MB), queued background imports. |
| **Elevation of Privilege** | Sales rep attempts to assign admin role or export all tenant data | Strict Laravel Policies verifying user role in `organization_members`. |

---

## 5. Financial & Data Integrity Rules
- Monetary figures are never stored or computed as floating-point numbers.
- PostgreSQL `NUMERIC(14, 2)` is enforced at the database level.
- Quotation discounts, taxes, and line-item totals are strictly computed on the backend during saving and rendering. Client-supplied totals are discarded and recalculated.

