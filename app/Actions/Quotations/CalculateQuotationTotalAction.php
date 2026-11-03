<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

class CalculateQuotationTotalAction
{
    /**
     * Calculates quotation totals using string/integer arithmetic to prevent float errors.
     *
     * @param array<int, array{quantity: float|int|string, unit_price: float|int|string}> $items
     * @return array{subtotal: float, discount_amount: float, tax_rate: float, tax_amount: float, total_amount: float, items: array}
     */
    public function execute(array $items, float|string $discountAmount = 0.00, float|string $taxRate = 18.00): array
    {
        $subtotal = 0.00;
        $processedItems = [];

        foreach ($items as $index => $item) {
            $qty = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $totalPrice = round($qty * $unitPrice, 2);

            $subtotal += $totalPrice;

            $processedItems[] = [
                'description' => (string) $item['description'],
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'sort_order' => $item['sort_order'] ?? $index,
            ];
        }

        $subtotal = round($subtotal, 2);
        $discount = min($subtotal, max(0.00, round((float) $discountAmount, 2)));
        $taxable = max(0.00, $subtotal - $discount);

        $rate = max(0.00, (float) $taxRate);
        $taxAmount = round($taxable * ($rate / 100), 2);
        $totalAmount = round($taxable + $taxAmount, 2);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_rate' => $rate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'items' => $processedItems,
        ];
    }
}

