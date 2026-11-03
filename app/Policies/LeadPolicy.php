<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Services\TenantContext;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return app(TenantContext::class)->hasOrganization();
    }

    public function view(User $user, Lead $lead): bool
    {
        $tenantOrgId = app(TenantContext::class)->getOrganizationId();
        return $tenantOrgId !== null && (int) $lead->organization_id === (int) $tenantOrgId;
    }

    public function create(User $user): bool
    {
        return app(TenantContext::class)->hasOrganization();
    }

    public function update(User $user, Lead $lead): bool
    {
        $tenantOrgId = app(TenantContext::class)->getOrganizationId();
        return $tenantOrgId !== null && (int) $lead->organization_id === (int) $tenantOrgId;
    }

    public function delete(User $user, Lead $lead): bool
    {
        $member = app(TenantContext::class)->getMember();
        $tenantOrgId = app(TenantContext::class)->getOrganizationId();

        if (! $member || (int) $lead->organization_id !== (int) $tenantOrgId) {
            return false;
        }

        return $member->role->canManageTeam();
    }
}

