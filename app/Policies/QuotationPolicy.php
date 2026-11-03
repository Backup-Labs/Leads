<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use App\Services\TenantContext;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return app(TenantContext::class)->hasOrganization();
    }

    public function view(User $user, Quotation $quotation): bool
    {
        $tenantOrgId = app(TenantContext::class)->getOrganizationId();
        return $tenantOrgId !== null && (int) $quotation->organization_id === (int) $tenantOrgId;
    }

    public function create(User $user): bool
    {
        return app(TenantContext::class)->hasOrganization();
    }

    public function update(User $user, Quotation $quotation): bool
    {
        $tenantOrgId = app(TenantContext::class)->getOrganizationId();
        return $tenantOrgId !== null && (int) $quotation->organization_id === (int) $tenantOrgId;
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        $member = app(TenantContext::class)->getMember();
        $tenantOrgId = app(TenantContext::class)->getOrganizationId();

        if (! $member || (int) $quotation->organization_id !== (int) $tenantOrgId) {
            return false;
        }

        return $member->role->canManageTeam();
    }
}
