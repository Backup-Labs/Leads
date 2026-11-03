<?php

declare(strict_types=1);

namespace App\Enums;

enum RecoveryAlertType: string
{
    case OVERDUE_FOLLOWUP = 'overdue_followup';
    case STALLED_LEAD = 'stalled_lead';
    case UNANSWERED_QUOTE = 'unanswered_quote';
    case HIGH_VALUE_RISK = 'high_value_risk';

    public function label(): string
    {
        return match ($this) {
            self::OVERDUE_FOLLOWUP => 'Overdue Follow-up',
            self::STALLED_LEAD => 'Stalled Lead (No Activity)',
            self::UNANSWERED_QUOTE => 'Unanswered Quotation',
            self::HIGH_VALUE_RISK => 'High-Value Opportunity Stalled',
        };
    }
}

