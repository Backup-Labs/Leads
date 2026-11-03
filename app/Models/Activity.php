<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityType;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'lead_id',
        'user_id',
        'type',
        'subject',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'metadata' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

