<?php

declare(strict_types=1);

namespace App\Actions\FollowUps;

use App\Enums\ActivityType;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\OrganizationMember;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleFollowUpAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected AuditLogger $auditLogger
    ) {}

    public function execute(Lead $lead, array $data): FollowUp
    {
        $orgId = $this->tenantContext->getOrganizationId();

        if (! empty($data['assigned_to_user_id'])) {
            $isMember = OrganizationMember::where('organization_id', $orgId)
                ->where('user_id', $data['assigned_to_user_id'])
                ->exists();

            if (! $isMember) {
                throw ValidationException::withMessages([
                    'assigned_to_user_id' => 'Assigned employee is not a member of this organization workspace.',
                ]);
            }
        } else {
            $data['assigned_to_user_id'] = auth()->id();
        }

        return DB::transaction(function () use ($lead, $data, $orgId) {
            $followUp = FollowUp::create([
                'organization_id' => $orgId,
                'lead_id' => $lead->id,
                'assigned_to_user_id' => $data['assigned_to_user_id'],
                'title' => $data['title'],
                'notes' => $data['notes'] ?? null,
                'scheduled_at' => Carbon::parse($data['scheduled_at']),
                'status' => 'pending',
            ]);

            // Update lead last contacted/touched
            $lead->update(['last_contacted_at' => now()]);

            // Log activity
            $lead->activities()->create([
                'organization_id' => $orgId,
                'user_id' => auth()->id(),
                'type' => ActivityType::NOTE,
                'subject' => "Follow-up Scheduled: {$followUp->title}",
                'description' => "Scheduled for " . $followUp->scheduled_at->format('M d, Y H:i'),
                'metadata' => [
                    'follow_up_id' => $followUp->id,
                    'scheduled_at' => $followUp->scheduled_at->toISOString(),
                ],
            ]);

            $this->auditLogger->log('followup.scheduled', $followUp, null, $followUp->toArray());

            return $followUp;
        });
    }
}

