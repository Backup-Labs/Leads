<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

use App\Enums\ActivityType;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class SendQuotationAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function execute(Quotation $quotation): Quotation
    {
        return DB::transaction(function () use ($quotation) {
            $quotation->update([
                'status' => QuotationStatus::SENT,
                'sent_at' => now(),
            ]);

            $lead = $quotation->lead;
            if ($lead) {
                $lead->update(['last_contacted_at' => now()]);

                $lead->activities()->create([
                    'organization_id' => $quotation->organization_id,
                    'user_id' => auth()->id(),
                    'type' => ActivityType::QUOTATION_SENT,
                    'subject' => "Quotation #{$quotation->quotation_number} Sent to Client",
                    'description' => "Quotation total: {$quotation->currency} " . number_format((float) $quotation->total_amount, 2),
                    'metadata' => [
                        'quotation_id' => $quotation->id,
                        'token' => $quotation->token,
                    ],
                ]);
            }

            $this->auditLogger->log('quotation.sent', $quotation);

            return $quotation;
        });
    }
}

