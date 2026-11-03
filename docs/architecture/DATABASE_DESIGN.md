# Database Design & Entity Relationship — Leads SaaS

## 1. Overview & Principles

Leads uses **PostgreSQL 17** as its single, unified relational database engine across development, testing, and production. The schema enforces:
- **Tenant Isolation:** Every operational table contains a foreign key `organization_id` referencing `organizations.id` with `ON DELETE CASCADE`.
- **Financial Precision:** Quotation and monetary figures use `NUMERIC(14, 2)` or integer cents to completely eliminate floating-point rounding errors.
- **Auditing & Traceability:** Soft deletion (`deleted_at`) on critical entities (`leads`, `quotations`, `organizations`), plus dedicated `audit_logs` and `activities` tables.
- **Indexing Strategy:** Composite indexes prefixed with `organization_id` to optimize tenant-scoped queries, sorting, and stage filtering.

---

## 2. Entity Relationship Diagram (Mermaid)

```mermaid
erDiagram
    users ||--o{ organization_members : "belongs to"
    users ||--o{ invitations : "sent/received"
    users ||--o{ activities : "performs"
    users ||--o{ follow_ups : "assigned to"
    users ||--o{ leads : "assigned to"
    
    organizations ||--o{ organization_members : "has"
    organizations ||--o{ invitations : "issues"
    organizations ||--o{ leads : "owns"
    organizations ||--o{ lead_sources : "configures"
    organizations ||--o{ pipeline_stages : "defines"
    organizations ||--o{ activities : "records"
    organizations ||--o{ follow_ups : "schedules"
    organizations ||--o{ quotations : "issues"
    organizations ||--o{ recovery_alerts : "generates"
    organizations ||--o{ audit_logs : "records"
    organizations ||--o{ organization_settings : "maintains"
    organizations ||--o| subscriptions : "holds"

    subscription_plans ||--o{ subscriptions : "subscribed to"

    leads ||--o{ activities : "has"
    leads ||--o{ follow_ups : "receives"
    leads ||--o{ quotations : "generates"
    leads ||--o{ recovery_alerts : "flags"
    pipeline_stages ||--o{ leads : "categorizes"
    lead_sources ||--o{ leads : "attributes"

    quotations ||--|{ quotation_items : "contains"
    quotations ||--o{ recovery_alerts : "monitored by"

    users {
        bigint id PK
        string name
        string email UK
        string password
        timestamp email_verified_at
        string avatar_url
        timestamps created_at_updated_at
    }

    organizations {
        bigint id PK
        string name
        string slug UK
        string logo_url
        string billing_email
        string timezone
        string currency
        timestamps created_at_updated_at
        timestamp deleted_at
    }

    organization_members {
        bigint id PK
        bigint organization_id FK
        bigint user_id FK
        string role "owner, admin, manager, sales"
        timestamp invited_at
        timestamp joined_at
        timestamps created_at_updated_at
    }

    invitations {
        bigint id PK
        bigint organization_id FK
        string email
        string role
        string token UK
        timestamp expires_at
        timestamp accepted_at
        timestamps created_at_updated_at
    }

    pipeline_stages {
        bigint id PK
        bigint organization_id FK
        string name
        string color
        int sort_order
        boolean is_won
        boolean is_lost
        timestamps created_at_updated_at
    }

    lead_sources {
        bigint id PK
        bigint organization_id FK
        string name "WhatsApp, Website, Referral, etc."
        boolean is_active
        timestamps created_at_updated_at
    }

    leads {
        bigint id PK
        bigint organization_id FK
        bigint pipeline_stage_id FK
        bigint lead_source_id FK
        bigint assigned_to_user_id FK
        string customer_name
        string email
        string phone
        string company_name
        string requested_service
        numeric estimated_value "14, 2"
        string currency "RWF, USD, etc."
        string priority "low, medium, high, urgent"
        string status "active, won, lost"
        string lost_reason
        text description
        timestamp converted_at
        timestamp last_contacted_at
        timestamps created_at_updated_at
        timestamp deleted_at
    }

    activities {
        bigint id PK
        bigint organization_id FK
        bigint lead_id FK
        bigint user_id FK
        string type "call, meeting, email, note, stage_change, quote_sent"
        string subject
        text description
        jsonb metadata
        timestamps created_at_updated_at
    }

    follow_ups {
        bigint id PK
        bigint organization_id FK
        bigint lead_id FK
        bigint assigned_to_user_id FK
        text title
        text notes
        timestamp scheduled_at
        timestamp completed_at
        string status "pending, completed, overdue, cancelled"
        timestamps created_at_updated_at
    }

    quotations {
        bigint id PK
        bigint organization_id FK
        bigint lead_id FK
        bigint created_by_user_id FK
        string quotation_number UK "per organization"
        string token UK "for public link"
        string customer_name
        string customer_email
        string customer_phone
        numeric subtotal "14, 2"
        numeric discount_amount "14, 2"
        numeric tax_rate "5, 2"
        numeric tax_amount "14, 2"
        numeric total_amount "14, 2"
        string currency
        string status "draft, sent, accepted, rejected, expired"
        date validity_date
        text notes
        text terms_and_conditions
        timestamp sent_at
        timestamp viewed_at
        timestamp responded_at
        timestamps created_at_updated_at
        timestamp deleted_at
    }

    quotation_items {
        bigint id PK
        bigint quotation_id FK
        string description
        numeric quantity "10, 2"
        numeric unit_price "14, 2"
        numeric total_price "14, 2"
        int sort_order
        timestamps created_at_updated_at
    }

    recovery_alerts {
        bigint id PK
        bigint organization_id FK
        bigint lead_id FK
        bigint quotation_id FK
        string alert_type "overdue_followup, stalled_lead, unanswered_quote, high_value_risk"
        string severity "critical, warning, notice"
        text reason
        text suggested_action
        numeric potential_revenue_at_risk "14, 2"
        boolean is_resolved
        timestamp resolved_at
        timestamps created_at_updated_at
    }

    audit_logs {
        bigint id PK
        bigint organization_id FK
        bigint user_id FK
        string event "lead.created, stage.changed, quote.sent, etc."
        string auditable_type
        bigint auditable_id
        jsonb old_values
        jsonb new_values
        string ip_address
        string user_agent
        timestamps created_at
    }

    subscription_plans {
        bigint id PK
        string name "Starter, Professional, Agency, Business"
        string slug UK
        numeric price_monthly "10, 2"
        int max_leads
        int max_users
        boolean features_quotations
        boolean features_recovery
        boolean is_active
        timestamps created_at_updated_at
    }

    subscriptions {
        bigint id PK
        bigint organization_id FK
        bigint subscription_plan_id FK
        string status "active, past_due, canceled, trialing"
        timestamp trial_ends_at
        timestamp current_period_ends_at
        timestamps created_at_updated_at
    }

    organization_settings {
        bigint id PK
        bigint organization_id FK UK
        string default_currency
        string default_tax_rate
        int stale_lead_threshold_days
        int quote_followup_threshold_days
        jsonb notification_preferences
        timestamps created_at_updated_at
    }
```

---

## 3. Database Indexes & Constraints

### 3.1 Composite Indexes (Tenant-Optimized)
1. `leads`: `INDEX idx_leads_org_stage (organization_id, pipeline_stage_id, status)`
2. `leads`: `INDEX idx_leads_org_assigned (organization_id, assigned_to_user_id)`
3. `leads`: `INDEX idx_leads_org_last_contact (organization_id, last_contacted_at)`
4. `follow_ups`: `INDEX idx_followups_org_status_date (organization_id, status, scheduled_at)`
5. `quotations`: `INDEX idx_quotations_org_status (organization_id, status)`
6. `activities`: `INDEX idx_activities_org_lead (organization_id, lead_id, created_at)`
7. `recovery_alerts`: `INDEX idx_recovery_org_resolved (organization_id, is_resolved, severity)`

### 3.2 Unique Constraints
- `users`: `UNIQUE (email)`
- `organizations`: `UNIQUE (slug)`
- `organization_members`: `UNIQUE (organization_id, user_id)`
- `quotations`: `UNIQUE (organization_id, quotation_number)`
- `quotations`: `UNIQUE (token)`
- `invitations`: `UNIQUE (token)`
- `invitations`: `UNIQUE (organization_id, email)`
- `pipeline_stages`: `UNIQUE (organization_id, name)`

### 3.3 Foreign Key Cascades
- Deleting an `organization` cascades to its `organization_members`, `pipeline_stages`, `lead_sources`, `leads`, `activities`, `follow_ups`, `quotations`, and `recovery_alerts`.
- Deleting a `lead` cascades to its `activities`, `follow_ups`, and `quotations`.
- Deleting a `quotation` cascades to its `quotation_items`.
- Deleting a `user` sets `assigned_to_user_id` on `leads` and `follow_ups` to `NULL` (`ON DELETE SET NULL`), preserving valuable business lead history.

