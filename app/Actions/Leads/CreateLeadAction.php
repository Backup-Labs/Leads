<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Enums\ActivityType;
use App\Models\Lead;
use App\Models\OrganizationMember;
use App\Models\PipelineStage;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateLeadAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected AuditLogger $auditLogger
    ) {}

    public function execute(array $data): Lead
    {
        $orgId = $this->tenantContext->getOrganizationId();

        // Cross-tenant protection: Validate assigned user belongs to THIS organization
        if (! empty($data['assigned_to_user_id'])) {
            $isMember = OrganizationMember::where('organization_id', $orgId)
                ->where('user_id', $data['assigned_to_user_id'])
                ->exists();

            if (! $isMember) {
                throw ValidationException::withMessages([
                    'assigned_to_user_id' => 'The assigned employee does not belong to your organization workspace.',
                ]);
            }
        }

        // If pipeline_stage_id not specified, pick default first stage
        if (empty($data['pipeline_stage_id'])) {
            $firstStage = PipelineStage::where('organization_id', $orgId)
                ->orderBy('sort_order')
                ->first();

            $data['pipeline_stage_id'] = $firstStage?->id;
        }

        return DB::transaction(function () use ($data, $orgId) {
            $data['organization_id'] = $orgId;
            $data['currency'] = $data['currency'] ?? ($this->tenantContext->getOrganization()?->currency ?? 'RWF');
            $data['last_contacted_at'] = now();

            $lead = Lead::create($data);

            // Log activity
            $lead->activities()->create([
                'organization_id' => $orgId,
                'user_id' => auth()->id(),
                'type' => ActivityType::NOTE,
                'subject' => 'Lead Created',
                'description' => "Lead captured for {$lead->customer_name} via " . ($lead->source?->name ?? 'Direct Ingestion'),
            ]);

            $this->auditLogger->log('lead.created', $lead, null, $lead->toArray());

            return $lead;
        });
    }
}

