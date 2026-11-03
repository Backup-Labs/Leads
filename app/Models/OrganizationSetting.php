<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationSetting extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'default_currency',
        'default_tax_rate',
        'stale_lead_threshold_days',
        'quote_followup_threshold_days',
        'high_value_threshold',
        'notification_preferences',
        'quotation_default_terms',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:2',
            'stale_lead_threshold_days' => 'integer',
            'quote_followup_threshold_days' => 'integer',
            'high_value_threshold' => 'integer',
            'notification_preferences' => 'array',
        ];
    }
}

