<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'color',
        'sort_order',
        'is_won',
        'is_lost',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}

