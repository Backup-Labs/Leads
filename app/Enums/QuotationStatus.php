<?php

declare(strict_types=1);

namespace App\Enums;

enum QuotationStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent to Client',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::EXPIRED => 'Expired',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-700',
            self::SENT => 'bg-blue-50 text-blue-700 border border-blue-200',
            self::ACCEPTED => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            self::REJECTED => 'bg-rose-50 text-rose-700 border border-rose-200',
            self::EXPIRED => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
    }
}

