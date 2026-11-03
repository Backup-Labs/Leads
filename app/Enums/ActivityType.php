<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityType: string
{
    case CALL = 'call';
    case MEETING = 'meeting';
    case EMAIL = 'email';
    case MESSAGE = 'message';
    case NOTE = 'note';
    case STAGE_CHANGE = 'stage_change';
    case QUOTATION_CREATED = 'quotation_created';
    case QUOTATION_SENT = 'quotation_sent';
    case QUOTATION_ACCEPTED = 'quotation_accepted';
    case QUOTATION_REJECTED = 'quotation_rejected';
    case ASSIGNED = 'assigned';

    public function label(): string
    {
        return match ($this) {
            self::CALL => 'Phone Call',
            self::MEETING => 'Client Meeting',
            self::EMAIL => 'Email Sent',
            self::MESSAGE => 'WhatsApp / SMS Message',
            self::NOTE => 'Internal Note',
            self::STAGE_CHANGE => 'Stage Changed',
            self::QUOTATION_CREATED => 'Quotation Drafted',
            self::QUOTATION_SENT => 'Quotation Sent',
            self::QUOTATION_ACCEPTED => 'Quotation Accepted',
            self::QUOTATION_REJECTED => 'Quotation Rejected',
            self::ASSIGNED => 'Lead Assigned',
        };
    }
}

