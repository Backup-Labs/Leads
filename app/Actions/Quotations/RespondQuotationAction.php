<?php

declare(strict_types=1);

namespace App\Actions\Quotations;

use App\Actions\Leads\TransitionLeadStageAction;
use App\Enums\ActivityType;
use App\Enums\QuotationStatus;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class RespondQuotationAction
{
    public function __construct(
        protected AuditLogger $auditLogger,
        protected TransitionLeadStageAction $transitionAction
    ) {}

    public function execute(Quotation $quotation, bool $isAccepted, ?string $rejectionReason = null): Quotation
    {
        return DB::transaction(function () use ($quotation, $isAccepted, $rejectionReason) {
            $newStatus = $isAccepted ? QuotationStatus::ACCEPTED : QuotationStatus::REJECTED;

            $quotation->update([
                'status' => $newStatus,
                'responded_at' => now(),
                'rejection_reason' => $isAccepted ? null : $rejectionReason,
            ]);

            $lead = $quotation->lead;
            if ($lead) {
                $lead->update(['last_contacted_at' => now()]);

                $lead->activities()->create([
                    'organization_id' => $quotation->organization_id,
                    'user_id' => auth()->id(),
                    'type' => $isAccepted ? ActivityType::QUOTATION_ACCEPTED : ActivityType::QUOTATION_REJECTED,
                    'subject' => $isAccepted
                        ? "Quotation #{$quotation->quotation_number} Accepted!"
                        : "Quotation #{$quotation->quotation_number} Rejected",
                    'description' => $isAccepted
                        ? "Customer accepted proposal for {$quotation->currency} " . number_format((float) $quotation->total_amount, 2)
                        : "Reason: " . ($rejectionReason ?? 'No reason provided'),
                    'metadata' => [
                        'quotation_id' => $quotation->id,
                        'is_accepted' => $isAccepted,
                    ],
                ]);

                // If accepted, auto-transition lead to Won stage if one exists
                if ($isAccepted) {
                    $wonStage = PipelineStage::where('organization_id', $quotation->organization_id)
                        ->where('is_won', true)
                        ->first();

                    if ($wonStage) {
                        $this->transitionAction->execute($lead, $wonStage->id);
                    }
                }
            }

            $this->auditLogger->log('quotation.responded', $quotation);

            return $quotation;
        });
    }
}

