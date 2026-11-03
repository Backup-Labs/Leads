<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Quotations\CalculateQuotationTotalAction;
use Tests\TestCase;

class QuotationCalculationTest extends TestCase
{
    public function test_quotation_totals_calculate_accurately_with_discounts_and_statutory_vat(): void
    {
        $action = new CalculateQuotationTotalAction();

        $items = [
            ['description' => 'Commercial Deep Cleaning', 'quantity' => 2, 'unit_price' => 1250000.00], // 2,500,000
            ['description' => 'Window Cleaning Service', 'quantity' => 1, 'unit_price' => 500000.00],   // 500,000
        ]; // Subtotal: 3,000,000

        $discount = 200000.00; // Taxable: 2,800,000
        $taxRate = 18.00;      // 18% of 2,800,000 = 504,000
        // Grand Total: 3,304,000

        $calc = $action->execute($items, $discount, $taxRate);

        $this->assertEquals(3000000.00, $calc['subtotal']);
        $this->assertEquals(200000.00, $calc['discount_amount']);
        $this->assertEquals(504000.00, $calc['tax_amount']);
        $this->assertEquals(3304000.00, $calc['total_amount']);
    }

    public function test_discount_cannot_exceed_subtotal(): void
    {
        $action = new CalculateQuotationTotalAction();

        $items = [
            ['description' => 'Single Service', 'quantity' => 1, 'unit_price' => 1000.00],
        ];

        $calc = $action->execute($items, 5000.00, 18.00);

        $this->assertEquals(1000.00, $calc['subtotal']);
        $this->assertEquals(1000.00, $calc['discount_amount']);
        $this->assertEquals(0.00, $calc['tax_amount']);
        $this->assertEquals(0.00, $calc['total_amount']);
    }
}
