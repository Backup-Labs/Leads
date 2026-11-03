<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'pipeline_stage_id',
        'lead_source_id',
        'assigned_to_user_id',
        'customer_name',
        'email',
        'phone',
        'company_name',
        'requested_service',
        'estimated_value',
        'currency',
        'priority',
        'status',
        'description',
        'lost_reason',
        'last_contacted_at',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => LeadPriority::class,
            'status' => LeadStatus::class,
            'estimated_value' => 'decimal:2',
            'last_contacted_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class)->orderBy('scheduled_at');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class)->latest();
    }

    public function recoveryAlerts(): HasMany
    {
        return $this->hasMany(RecoveryAlert::class);
    }
}

