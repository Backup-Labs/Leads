<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price_monthly',
        'currency',
        'max_leads',
        'max_users',
        'has_quotations',
        'has_revenue_recovery',
        'has_custom_stages',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'max_leads' => 'integer',
            'max_users' => 'integer',
            'has_quotations' => 'boolean',
            'has_revenue_recovery' => 'boolean',
            'has_custom_stages' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}

