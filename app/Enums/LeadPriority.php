<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low Priority',
            self::MEDIUM => 'Medium Priority',
            self::HIGH => 'High Priority',
            self::URGENT => 'Urgent Priority',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => '#6B7280',
            self::MEDIUM => '#3B82F6',
            self::HIGH => '#F59E0B',
            self::URGENT => '#F74D01',
        };
    }
}
