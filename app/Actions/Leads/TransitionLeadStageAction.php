<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Enums\ActivityType;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionLeadStageAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected AuditLogger $auditLogger
    ) {}

    public function execute(Lead $lead, int $newStageId, ?string $lostReason = null): Lead
    {
        $orgId = $this->tenantContext->getOrganizationId();

        $stage = PipelineStage::where('organization_id', $orgId)
            ->where('id', $newStageId)
            ->first();

        if (! $stage) {
            throw ValidationException::withMessages([
                'stage_id' => 'Invalid pipeline stage for this organization.',
            ]);
        }

        $oldStageName = $lead->stage?->name ?? 'Initial';
        $oldValues = $lead->only(['pipeline_stage_id', 'status', 'lost_reason', 'converted_at']);

        return DB::transaction(function () use ($lead, $stage, $oldStageName, $oldValues, $lostReason) {
            $lead->pipeline_stage_id = $stage->id;
            $lead->last_contacted_at = now();

            if ($stage->is_won) {
                $lead->status = LeadStatus::WON;
                $lead->converted_at = now();
                $lead->lost_reason = null;
            } elseif ($stage->is_lost) {
                $lead->status = LeadStatus::LOST;
                $lead->lost_reason = $lostReason ?? 'No reason provided';
            } else {
                $lead->status = LeadStatus::ACTIVE;
                $lead->lost_reason = null;
            }

            $lead->save();

            // Record activity
            $lead->activities()->create([
                'organization_id' => $lead->organization_id,
                'user_id' => auth()->id(),
                'type' => ActivityType::STAGE_CHANGE,
                'subject' => "Pipeline Stage: {$stage->name}",
                'description' => "Moved lead from '{$oldStageName}' to '{$stage->name}'.",
                'metadata' => [
                    'from_stage' => $oldStageName,
                    'to_stage' => $stage->name,
                    'is_won' => $stage->is_won,
                    'is_lost' => $stage->is_lost,
                ],
            ]);

            $this->auditLogger->log('lead.stage_changed', $lead, $oldValues, $lead->toArray());

            return $lead->load(['stage', 'assignedTo', 'source']);
        });
    }
}

