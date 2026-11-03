<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Enums\MemberRole;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteMemberAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function execute(Organization $organization, string $email, MemberRole $role): Invitation
    {
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $isAlreadyMember = OrganizationMember::where('organization_id', $organization->id)
                ->where('user_id', $existingUser->id)
                ->exists();

            if ($isAlreadyMember) {
                throw ValidationException::withMessages([
                    'email' => 'This user is already an active member of your organization workspace.',
                ]);
            }
        }

        // Delete any pending previous invitation for this email in this org
        Invitation::where('organization_id', $organization->id)
            ->where('email', $email)
            ->delete();

        $invitation = Invitation::create([
            'organization_id' => $organization->id,
            'email' => $email,
            'role' => $role,
            'token' => Str::random(48),
            'invited_by_user_id' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        $this->auditLogger->log('member.invited', $invitation, null, [
            'email' => $email,
            'role' => $role->value,
        ]);

        return $invitation;
    }
}
