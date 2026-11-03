<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $organization->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Organization $organization): bool
    {
        $member = app(TenantContext::class)->getMember();
        return $member !== null && $member->role->canManageOrganization();
    }

    public function delete(User $user, Organization $organization): bool
    {
        $member = app(TenantContext::class)->getMember();
        return $member !== null && $member->role->canManageOrganization();
    }

    public function manageTeam(User $user, Organization $organization): bool
    {
        $member = app(TenantContext::class)->getMember();
        return $member !== null && $member->role->canManageTeam();
    }
}

