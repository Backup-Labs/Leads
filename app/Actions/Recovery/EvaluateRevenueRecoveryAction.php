<?php

declare(strict_types=1);

namespace App\Actions\Recovery;

use App\Enums\QuotationStatus;
use App\Enums\RecoveryAlertType;
use App\Enums\RecoverySeverity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Quotation;
use App\Models\RecoveryAlert;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluateRevenueRecoveryAction
{
    /**
     * Evaluates all deterministic revenue recovery rules for a tenant organization.
     * Synchronizes recovery_alerts table.
     *
     * @return int Number of active recovery opportunities identified
     */
    public function execute(Organization $organization): int
    {
        $settings = $organization->settings;
        $staleLeadDays = $settings?->stale_lead_threshold_days ?? 7;
        $quoteDays = $settings?->quote_followup_threshold_days ?? 3;
        $highValueThreshold = (float) ($settings?->high_value_threshold ?? 1000000);

        // Mark previously resolved alerts
        RecoveryAlert::where('organization_id', $organization->id)
            ->where('is_resolved', false)
            ->update(['is_resolved' => true, 'resolved_at' => now()]);

        $activeAlertsCount = 0;

        // 1. Overdue Follow-ups
        $overdueFollowUps = FollowUp::where('organization_id', $organization->id)
            ->where('status', 'pending')
            ->where('scheduled_at', '<', now())
            ->with(['lead', 'assignedTo'])
            ->get();

        foreach ($overdueFollowUps as $followUp) {
            $hoursOverdue = Carbon::parse($followUp->scheduled_at)->diffInHours(now());
            $severity = $hoursOverdue > 48 ? RecoverySeverity::CRITICAL : RecoverySeverity::WARNING;

            $leadName = $followUp->lead?->customer_name ?? 'Prospect';
            $assigneeName = $followUp->assignedTo?->name ?? 'Unassigned';

            RecoveryAlert::create([
                'organization_id' => $organization->id,
                'lead_id' => $followUp->lead_id,
                'alert_type' => RecoveryAlertType::OVERDUE_FOLLOWUP,
                'severity' => $severity,
                'reason' => "Follow-up '{$followUp->title}' for {$leadName} is overdue by {$hoursOverdue} hours (Assigned: {$assigneeName}).",
                'suggested_action' => "Contact {$leadName} immediately to prevent deal neglect.",
                'potential_revenue_at_risk' => (float) ($followUp->lead?->estimated_value ?? 0.00),
                'is_resolved' => false,
            ]);

            $activeAlertsCount++;
        }

        // 2. Unanswered Quotations
        $staleQuotations = Quotation::where('organization_id', $organization->id)
            ->where('status', QuotationStatus::SENT)
            ->where('sent_at', '<', now()->subDays($quoteDays))
            ->with('lead')
            ->get();

        foreach ($staleQuotations as $quote) {
            $daysSinceSent = Carbon::parse($quote->sent_at)->diffInDays(now());

            RecoveryAlert::create([
                'organization_id' => $organization->id,
                'lead_id' => $quote->lead_id,
                'quotation_id' => $quote->id,
                'alert_type' => RecoveryAlertType::UNANSWERED_QUOTE,
                'severity' => RecoverySeverity::CRITICAL,
                'reason' => "Quotation #{$quote->quotation_number} for {$quote->currency} " . number_format((float) $quote->total_amount, 2) . " has had no client response for {$daysSinceSent} days.",
                'suggested_action' => "Send a WhatsApp check-in or phone call to discuss quotation details, answer objections, or adjust terms.",
                'potential_revenue_at_risk' => (float) $quote->total_amount,
                'is_resolved' => false,
            ]);

            $activeAlertsCount++;
        }

        // 3. Stalled Leads (No activity in X days)
        $stalledLeads = Lead::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->where(function ($q) use ($staleLeadDays) {
                $q->whereNull('last_contacted_at')
                  ->orWhere('last_contacted_at', '<', now()->subDays($staleLeadDays));
            })
            ->whereDoesntHave('recoveryAlerts', function ($q) {
                $q->where('is_resolved', false);
            })
            ->get();

        foreach ($stalledLeads as $lead) {
            $days = $lead->last_contacted_at
                ? Carbon::parse($lead->last_contacted_at)->diffInDays(now())
                : $staleLeadDays;

            $isHighValue = (float) $lead->estimated_value >= $highValueThreshold;

            RecoveryAlert::create([
                'organization_id' => $organization->id,
                'lead_id' => $lead->id,
                'alert_type' => $isHighValue ? RecoveryAlertType::HIGH_VALUE_RISK : RecoveryAlertType::STALLED_LEAD,
                'severity' => $isHighValue ? RecoverySeverity::CRITICAL : RecoverySeverity::WARNING,
                'reason' => "No contact recorded with {$lead->customer_name} for {$days} days." . ($isHighValue ? " High-value opportunity at risk." : ""),
                'suggested_action' => "Schedule a discovery check-in or assign to an active rep for immediate contact.",
                'potential_revenue_at_risk' => (float) $lead->estimated_value,
                'is_resolved' => false,
            ]);

            $activeAlertsCount++;
        }

        return $activeAlertsCount;
    }
}

