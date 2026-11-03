<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuotationStatus;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'lead_id',
        'created_by_user_id',
        'quotation_number',
        'token',
        'customer_name',
        'customer_email',
        'customer_phone',
        'company_name',
        'subtotal',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'currency',
        'status',
        'validity_date',
        'notes',
        'terms_and_conditions',
        'sent_at',
        'viewed_at',
        'responded_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'validity_date' => 'date',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }
}

