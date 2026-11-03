# Testing Strategy & Test Suite Guide — Leads SaaS

Testing is mandatory in Leads. Our testing strategy ensures multi-tenant security, financial calculation accuracy, business workflow continuity, and regression prevention.

---

## 1. Test Pyramid

1. **Unit Tests (PHPUnit / Pest):**
   - Action classes (`CreateLeadAction`, `CalculateQuotationTotalAction`).
   - Domain enums, value objects, and financial rounding logic.
   - Tenant global scope query manipulation.
2. **Feature Tests (HTTP & Integration):**
   - Authentication, Registration, and Onboarding.
   - Multi-tenant data isolation (cross-tenant access attempts must fail with 403 or 404).
   - Lead creation, pipeline transitions, and activity logging.
   - Quotation issuance, line-item math, and tokenized public viewing.
   - Revenue recovery rules and alert triggering.
3. **Frontend Component Tests (Vitest):**
   - Form components, validation states, and modal dialogs.
   - Dynamic quotation line-item calculations in the browser.
4. **End-to-End Tests (Playwright):**
   - Complete buyer journey: Register -> Create Org -> Invite Member -> Capture Lead -> Move Stage -> Issue Quotation -> Mark Won.

---

## 2. Critical Test Scenarios & Invariants

### 2.1 Multi-Tenant Isolation Test (Mandatory Invariant)
```php
/** @test */
public function user_cannot_view_or_update_leads_from_another_organization(): void
{
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    $userA = User::factory()->create();
    $orgA->members()->attach($userA, ['role' => 'owner']);

    $leadB = Lead::factory()->for($orgB)->create();

    $this->actingAs($userA)
        ->withSession(['current_organization_id' => $orgA->id])
        ->get("/leads/{$leadB->id}")
        ->assertNotFound(); // Scoped query hides lead completely
}
```

### 2.2 Cross-Tenant Member Assignment Prevention
```php
/** @test */
public function lead_cannot_be_assigned_to_user_from_different_organization(): void
{
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $orgA->members()->attach($userA, ['role' => 'owner']);
    $orgB->members()->attach($userB, ['role' => 'sales']);

    $this->actingAs($userA)
        ->withSession(['current_organization_id' => $orgA->id])
        ->post('/leads', [
            'customer_name' => 'Acme Corp',
            'assigned_to_user_id' => $userB->id, // User belonging to Org B
        ])
        ->assertSessionHasErrors(['assigned_to_user_id']);
}
```

### 2.3 Quotation Financial Accuracy
```php
/** @test */
public function quotation_totals_are_calculated_accurately_with_tax_and_discounts(): void
{
    $items = [
        ['description' => 'Website Design', 'quantity' => 1, 'unit_price' => 1000.00],
        ['description' => 'SEO Package', 'quantity' => 2, 'unit_price' => 250.00],
    ]; // Subtotal: 1500.00

    $discount = 100.00; // Subtotal after discount: 1400.00
    $taxRate = 18.00;   // 18% tax on 1400 = 252.00
    // Expected Total: 1652.00

    $quote = app(CreateQuotationAction::class)->execute($org, $payload);

    $this->assertEquals(1500.00, $quote->subtotal);
    $this->assertEquals(100.00, $quote->discount_amount);
    $this->assertEquals(252.00, $quote->tax_amount);
    $this->assertEquals(1652.00, $quote->total_amount);
}
```

---

## 3. Running Test Suites

```bash
# Run all backend feature & unit tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Run specifically tenant isolation tests
php artisan test --filter=TenantIsolationTest

# Run frontend unit tests
npm run test:unit

# Run end-to-end browser tests
npx playwright test
```

