<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Organizations\InviteMemberAction;
use App\Enums\MemberRole;
use App\Http\Requests\InviteMemberRequest;
use App\Models\Invitation;
use App\Models\OrganizationMember;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected InviteMemberAction $inviteAction,
        protected AuditLogger $auditLogger
    ) {}

    public function index(): Response
    {
        $org = $this->tenantContext->getOrganization();

        $members = OrganizationMember::where('organization_id', $org->id)
            ->with('user:id,name,email,avatar_url')
            ->get();

        $invitations = Invitation::where('organization_id', $org->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        return Inertia::render('Team/Index', [
            'members' => $members,
            'invitations' => $invitations,
            'roles' => array_map(fn ($r) => ['value' => $r->value, 'label' => $r->label()], MemberRole::cases()),
        ]);
    }

    public function invite(InviteMemberRequest $request): RedirectResponse
    {
        $org = $this->tenantContext->getOrganization();
        $this->authorize('manageTeam', $org);

        $invitation = $this->inviteAction->execute(
            $org,
            $request->email,
            MemberRole::from($request->role)
        );

        return back()->with('success', "Invitation sent to {$invitation->email}.");
    }

    public function remove(OrganizationMember $member): RedirectResponse
    {
        $org = $this->tenantContext->getOrganization();
        $this->authorize('manageTeam', $org);

        if ((int) $member->organization_id !== (int) $org->id) {
            abort(403);
        }

        // Prevent removing the last owner
        if ($member->role === MemberRole::OWNER) {
            $ownerCount = OrganizationMember::where('organization_id', $org->id)
                ->where('role', MemberRole::OWNER)
                ->count();

            if ($ownerCount <= 1) {
                return back()->with('error', 'Cannot remove the primary organization owner.');
            }
        }

        $userEmail = $member->user?->email;
        $member->delete();

        $this->auditLogger->log('member.removed', null, ['email' => $userEmail]);

        return back()->with('success', 'Team member removed from workspace.');
    }
}

