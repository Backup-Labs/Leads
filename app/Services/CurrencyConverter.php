<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Models\Organization;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RecoveryAlert;
use Illuminate\Support\Facades\DB;

class CurrencyConverter
{
    /**
     * Exchange rates relative to 1 USD.
     *
     * @var array<string, float>
     */
    public const RATES_TO_USD = [
        'USD' => 1.0,
        'EUR' => 0.92,
        'GBP' => 0.79,
        'UGX' => 3750.0,
        'KES' => 129.0,
        'TZS' => 2620.0,
        'RWF' => 1380.0,
    ];

    /**
     * Convert an amount from one currency to another.
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        $from = strtoupper($fromCurrency);
        $to = strtoupper($toCurrency);

        if ($from === $to || $amount == 0.0) {
            return $amount;
        }

        $fromRate = self::RATES_TO_USD[$from] ?? 1.0;
        $toRate = self::RATES_TO_USD[$to] ?? 1.0;

        $amountInUsd = $amount / $fromRate;
        $converted = $amountInUsd * $toRate;

        // Zero-decimal currencies (UGX, TZS, RWF, KES) round to whole numbers for clean pricing
        if (in_array($to, ['UGX', 'TZS', 'RWF'], true)) {
            return round($converted, 0);
        }

        return round($converted, 2);
    }

    /**
     * Get the multiplier ratio from $fromCurrency to $toCurrency.
     */
    public function getRatio(string $fromCurrency, string $toCurrency): float
    {
        $from = strtoupper($fromCurrency);
        $to = strtoupper($toCurrency);

        if ($from === $to) {
            return 1.0;
        }

        $fromRate = self::RATES_TO_USD[$from] ?? 1.0;
        $toRate = self::RATES_TO_USD[$to] ?? 1.0;

        return $toRate / $fromRate;
    }

    /**
     * Convert all monetary records belonging to an organization from its old currency to the new currency.
     */
    public function convertOrganizationRecords(Organization $org, string $oldCurrency, string $newCurrency): void
    {
        $from = strtoupper($oldCurrency);
        $to = strtoupper($newCurrency);

        if ($from === $to) {
            return;
        }

        $decimals = in_array($to, ['UGX', 'TZS', 'RWF'], true) ? 0 : 2;

        DB::transaction(function () use ($org, $from, $to, $decimals) {
            // 1. Convert all Leads in this organization
            $leads = Lead::withoutGlobalScopes()
                ->where('organization_id', $org->id)
                ->get();

            foreach ($leads as $lead) {
                $sourceCurr = $lead->currency ?: $from;
                $newVal = $this->convert((float) $lead->estimated_value, $sourceCurr, $to);
                $lead->update([
                    'estimated_value' => round($newVal, $decimals),
                    'currency' => $to,
                ]);
            }

            // 2. Convert all Quotations and Quotation Items in this organization
            $quotations = Quotation::withoutGlobalScopes()
                ->where('organization_id', $org->id)
                ->with('items')
                ->get();

            foreach ($quotations as $quotation) {
                $sourceCurr = $quotation->currency ?: $from;
                $subtotal = 0.0;

                foreach ($quotation->items as $item) {
                    $newUnitPrice = round($this->convert((float) $item->unit_price, $sourceCurr, $to), $decimals);
                    $newTotalPrice = round($newUnitPrice * (float) $item->quantity, $decimals);

                    QuotationItem::where('id', $item->id)->update([
                        'unit_price' => $newUnitPrice,
                        'total_price' => $newTotalPrice,
                    ]);

                    $subtotal += $newTotalPrice;
                }

                $newDiscount = round($this->convert((float) $quotation->discount_amount, $sourceCurr, $to), $decimals);
                $taxable = max(0.0, $subtotal - $newDiscount);
                $newTaxAmount = round($taxable * ((float) $quotation->tax_rate / 100.0), $decimals);
                $newTotal = round($taxable + $newTaxAmount, $decimals);

                $quotation->update([
                    'subtotal' => $subtotal,
                    'discount_amount' => $newDiscount,
                    'tax_amount' => $newTaxAmount,
                    'total_amount' => $newTotal,
                    'currency' => $to,
                ]);
            }

            // 3. Convert active Recovery Alerts
            $alerts = RecoveryAlert::where('organization_id', $org->id)->get();
            foreach ($alerts as $alert) {
                $newRisk = round($this->convert((float) $alert->potential_revenue_at_risk, $from, $to), $decimals);
                $alert->update([
                    'potential_revenue_at_risk' => $newRisk,
                ]);
            }

            // 4. Convert Organization Settings high_value_threshold if present (integer column in PostgreSQL)
            if ($org->settings) {
                $newThreshold = (int) round($this->convert((float) $org->settings->high_value_threshold, $from, $to));
                $org->settings->update([
                    'default_currency' => $to,
                    'high_value_threshold' => max(1, $newThreshold),
                ]);
            }
        });
    }
}

