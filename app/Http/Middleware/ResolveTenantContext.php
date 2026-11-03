<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Determine requested organization ID from header, session, or route
        $orgId = $request->header('X-Organization-Id')
            ?? $request->session()->get('current_organization_id');

        $member = null;

        if ($orgId) {
            $member = OrganizationMember::where('organization_id', $orgId)
                ->where('user_id', $user->id)
                ->with('organization')
                ->first();
        }

        // Fallback to user's first available organization if not selected or invalid
        if (! $member) {
            $member = OrganizationMember::where('user_id', $user->id)
                ->with('organization')
                ->first();

            if ($member && $request->hasSession()) {
                $request->session()->put('current_organization_id', $member->organization_id);
            }
        }

        if ($member && $member->organization) {
            $this->tenantContext->setOrganization($member->organization, $member);
        }

        return $next($request);
    }
}

