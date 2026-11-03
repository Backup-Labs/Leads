<?php

declare(strict_types=1);

namespace App\Enums;

enum MemberRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case SALES = 'sales';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Sales Manager',
            self::SALES => 'Sales Representative',
        };
    }

    public function canManageOrganization(): bool
    {
        return $this === self::OWNER;
    }

    public function canManageTeam(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN], true);
    }

    public function canViewAnalytics(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN, self::MANAGER], true);
    }

    public function canReassignLeads(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN, self::MANAGER], true);
    }
}

