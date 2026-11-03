<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOrganizationRole
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $member = $this->tenantContext->getMember();

        if (! $member) {
            abort(Response::HTTP_FORBIDDEN, 'No active organization workspace found.');
        }

        if (! empty($roles) && ! in_array($member->role, $roles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to perform this operational action.');
        }

        return $next($request);
    }
}

