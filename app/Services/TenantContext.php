<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

class TenantContext
{
    protected ?Organization $organization = null;
    protected ?OrganizationMember $currentMember = null;

    public function setOrganization(?Organization $organization, ?OrganizationMember $member = null): void
    {
        $this->organization = $organization;
        $this->currentMember = $member;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function getOrganizationId(): ?int
    {
        return $this->organization?->id;
    }

    public function getMember(): ?OrganizationMember
    {
        return $this->currentMember;
    }

    public function hasOrganization(): bool
    {
        return $this->organization !== null;
    }

    public function checkUserAccess(User $user, Organization $organization): ?OrganizationMember
    {
        return OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();
    }
}

