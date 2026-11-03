<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Organization;
use App\Scopes\TenantScope;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model): void {
            $tenantContext = app(TenantContext::class);

            if (empty($model->organization_id) && $tenantContext->hasOrganization()) {
                $model->organization_id = $tenantContext->getOrganizationId();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

