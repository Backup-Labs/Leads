# Performance & Scalability Architecture — Leads SaaS

## 1. High-Performance Design Goals

Leads is engineered to provide sub-100ms dashboard loads and instantaneous Kanban drag-and-drop transitions under active multi-tenant usage.

---

## 2. Query Optimization & Indexing Patterns

### 2.1 Tenant Composite Indexing
Because 99% of all queries filter by the tenant, all major tables use composite indexes prefixed by `organization_id`:
- `(organization_id, pipeline_stage_id, status)` on `leads`
- `(organization_id, scheduled_at, status)` on `follow_ups`
- `(organization_id, status, created_at)` on `quotations`

### 2.2 Eliminating N+1 Query Traps
All Inertia controllers and API resources explicitly eager load required relationships using `with()`:
```php
Lead::query()
    ->with(['stage:id,name,color', 'assignedTo:id,name', 'source:id,name'])
    ->paginate(25);
```

### 2.3 Analytics Aggregations
Dashboard metrics avoid loading Eloquent model instances into memory. High-performance SQL aggregations are executed directly in PostgreSQL:
```php
$metrics = DB::table('leads')
    ->where('organization_id', $orgId)
    ->whereNull('deleted_at')
    ->selectRaw("
        COUNT(*) as total_leads,
        COUNT(*) FILTER (WHERE status = 'active') as active_leads,
        COUNT(*) FILTER (WHERE status = 'won') as won_leads,
        COALESCE(SUM(estimated_value) FILTER (WHERE status = 'won'), 0) as won_revenue,
        COALESCE(SUM(estimated_value) FILTER (WHERE status = 'active'), 0) as pipeline_value
    ")
    ->first();
```

---

## 3. Caching Strategy

- **Tenant Settings & Stages:** Cached in Redis with tags: `cache()->tags(["org_{$orgId}"])->remember('stages', 3600, ...)`
- **Cache Invalidation:** Cache tags are flushed when pipeline stages or organization settings are updated via Model Observers / Domain Actions.
- **Session & Queues:** Backed by Redis in production environments for rapid session deserialization and high-throughput job dispatching.

---

## 4. Asynchronous Background Operations

Heavy and external I/O tasks are offloaded to Laravel Queues:
1. **Quotation PDF Generation:** Compiled asynchronously or streamed on-demand.
2. **Transactional Emails:** Team invitations and follow-up alerts are dispatched via queued Mailable jobs.
3. **Revenue Recovery Evaluations:** Scheduled nightly and hourly evaluation passes across all active tenant leads without degrading interactive user requests.
4. **CSV Bulk Lead Import:** Processed in chunked queued jobs with per-row validation and progress broadcasting.

