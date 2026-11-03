<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RecoveryAlertType;
use App\Enums\RecoverySeverity;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecoveryAlert extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'lead_id',
        'quotation_id',
        'alert_type',
        'severity',
        'reason',
        'suggested_action',
        'potential_revenue_at_risk',
        'is_resolved',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'alert_type' => RecoveryAlertType::class,
            'severity' => RecoverySeverity::class,
            'potential_revenue_at_risk' => 'decimal:2',
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}

