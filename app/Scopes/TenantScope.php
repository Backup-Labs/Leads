<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantContext = app(TenantContext::class);

        if ($tenantContext->hasOrganization()) {
            $builder->where($model->getTable() . '.organization_id', $tenantContext->getOrganizationId());
        }
    }
}
