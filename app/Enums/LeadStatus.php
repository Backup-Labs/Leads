<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadStatus: string
{
    case ACTIVE = 'active';
    case WON = 'won';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active Opportunity',
            self::WON => 'Closed Won',
            self::LOST => 'Closed Lost',
        };
    }
}

