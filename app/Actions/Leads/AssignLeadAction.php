<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Enums\ActivityType;
use App\Models\Lead;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignLeadAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected AuditLogger $auditLogger
    ) {}

    public function execute(Lead $lead, ?int $userId): Lead
    {
        $orgId = $this->tenantContext->getOrganizationId();

        $assignee = null;
        if ($userId) {
            $isMember = OrganizationMember::where('organization_id', $orgId)
                ->where('user_id', $userId)
                ->exists();

            if (! $isMember) {
                throw ValidationException::withMessages([
                    'assigned_to_user_id' => 'The user is not an active member of this organization workspace.',
                ]);
            }

            $assignee = User::find($userId);
        }

        return DB::transaction(function () use ($lead, $assignee, $userId) {
            $oldAssignee = $lead->assignedTo?->name ?? 'Unassigned';
            $lead->assigned_to_user_id = $userId;
            $lead->save();

            $newAssignee = $assignee?->name ?? 'Unassigned';

            $lead->activities()->create([
                'organization_id' => $lead->organization_id,
                'user_id' => auth()->id(),
                'type' => ActivityType::ASSIGNED,
                'subject' => "Lead Reassigned to {$newAssignee}",
                'description' => "Ownership transferred from {$oldAssignee} to {$newAssignee}.",
            ]);

            $this->auditLogger->log('lead.assigned', $lead, ['assigned_to' => $oldAssignee], ['assigned_to' => $newAssignee]);

            return $lead->load('assignedTo');
        });
    }
}

