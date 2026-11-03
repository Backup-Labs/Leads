<?php

declare(strict_types=1);

namespace App\Enums;

enum RecoverySeverity: string
{
    case CRITICAL = 'critical';
    case WARNING = 'warning';
    case NOTICE = 'notice';

    public function label(): string
    {
        return match ($this) {
            self::CRITICAL => 'Critical Action Needed',
            self::WARNING => 'Attention Required',
            self::NOTICE => 'Opportunity Notice',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CRITICAL => 'bg-red-50 text-red-700 border border-red-200',
            self::WARNING => 'bg-amber-50 text-amber-800 border border-amber-200',
            self::NOTICE => 'bg-blue-50 text-blue-700 border border-blue-200',
        };
    }
}

