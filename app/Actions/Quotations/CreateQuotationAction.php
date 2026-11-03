<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

use App\Enums\ActivityType;
use App\Enums\QuotationStatus;
use App\Models\Lead;
use App\Models\Quotation;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateQuotationAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected CalculateQuotationTotalAction $calculateAction,
        protected AuditLogger $auditLogger
    ) {}

    public function execute(Lead $lead, array $data): Quotation
    {
        $orgId = $this->tenantContext->getOrganizationId();
        $org = $this->tenantContext->getOrganization();

        $calc = $this->calculateAction->execute(
            $data['items'] ?? [],
            $data['discount_amount'] ?? 0.00,
            $data['tax_rate'] ?? ($org->settings?->default_tax_rate ?? 18.00)
        );

        return DB::transaction(function () use ($lead, $data, $calc, $orgId, $org) {
            // Generate sequence quotation number
            $year = now()->format('Y');
            $latestCount = Quotation::where('organization_id', $orgId)
                ->whereYear('created_at', $year)
                ->count() + 1;
            $quotationNumber = sprintf('QUO-%s-%04d', $year, $latestCount);

            $quotation = Quotation::create([
                'organization_id' => $orgId,
                'lead_id' => $lead->id,
                'created_by_user_id' => auth()->id(),
                'quotation_number' => $quotationNumber,
                'token' => Str::random(40),
                'customer_name' => $data['customer_name'] ?? $lead->customer_name,
                'customer_email' => $data['customer_email'] ?? $lead->email,
                'customer_phone' => $data['customer_phone'] ?? $lead->phone,
                'company_name' => $data['company_name'] ?? $lead->company_name,
                'subtotal' => $calc['subtotal'],
                'discount_amount' => $calc['discount_amount'],
                'tax_rate' => $calc['tax_rate'],
                'tax_amount' => $calc['tax_amount'],
                'total_amount' => $calc['total_amount'],
                'currency' => $data['currency'] ?? $lead->currency,
                'status' => QuotationStatus::DRAFT,
                'validity_date' => ! empty($data['validity_date']) ? Carbon::parse($data['validity_date']) : now()->addDays(14),
                'notes' => $data['notes'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? ($org->settings?->quotation_default_terms ?? null),
            ]);

            foreach ($calc['items'] as $item) {
                $quotation->items()->create($item);
            }

            // Sync lead's estimated value if quotation is higher or lead had 0
            if ((float) $lead->estimated_value <= 0 || (float) $calc['total_amount'] > (float) $lead->estimated_value) {
                $lead->update(['estimated_value' => $calc['total_amount']]);
            }

            // Log activity
            $lead->activities()->create([
                'organization_id' => $orgId,
                'user_id' => auth()->id(),
                'type' => ActivityType::QUOTATION_CREATED,
                'subject' => "Quotation #{$quotation->quotation_number} Drafted",
                'description' => "Drafted quotation for {$quotation->currency} " . number_format((float) $quotation->total_amount, 2),
                'metadata' => [
                    'quotation_id' => $quotation->id,
                    'total_amount' => $quotation->total_amount,
                ],
            ]);

            $this->auditLogger->log('quotation.created', $quotation);

            return $quotation->load('items');
        });
    }
}
