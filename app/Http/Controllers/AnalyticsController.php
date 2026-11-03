<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\OrganizationMember;
use App\Models\Quotation;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): Response
    {
        $org = $this->tenantContext->getOrganization();
        $days = (int) $request->input('period', 30);
        $startDate = now()->subDays($days);

        // Core aggregate figures
        $totalLeads = Lead::where('created_at', '>=', $startDate)->count();
        $wonLeads = Lead::where('status', 'won')->where('converted_at', '>=', $startDate)->count();
        $lostLeads = Lead::where('status', 'lost')->where('updated_at', '>=', $startDate)->count();
        $closedLeads = $wonLeads + $lostLeads;

        $conversionRate = $closedLeads > 0 ? round(($wonLeads / $closedLeads) * 100, 1) : 0.0;
        $wonRevenue = (float) Lead::where('status', 'won')->where('converted_at', '>=', $startDate)->sum('estimated_value');
        $pipelineValue = (float) Lead::where('status', 'active')->sum('estimated_value');
        $avgDealValue = $wonLeads > 0 ? round($wonRevenue / $wonLeads, 2) : 0.00;

        // Source performance
        $sourceStats = LeadSource::where('organization_id', $org->id)
            ->withCount(['leads as total_leads' => fn ($q) => $q->where('created_at', '>=', $startDate)])
            ->withCount(['leads as won_leads' => fn ($q) => $q->where('status', 'won')->where('converted_at', '>=', $startDate)])
            ->withSum(['leads as won_revenue' => fn ($q) => $q->where('status', 'won')->where('converted_at', '>=', $startDate)], 'estimated_value')
            ->get()
            ->map(fn ($source) => [
                'name' => $source->name,
                'color' => $source->color ?? '#6B7280',
                'total_leads' => $source->total_leads,
                'won_leads' => $source->won_leads,
                'won_revenue' => (float) ($source->won_revenue ?? 0.00),
                'conversion_rate' => $source->total_leads > 0 ? round(($source->won_leads / $source->total_leads) * 100, 1) : 0.0,
            ]);

        // Rep / Team performance
        $teamStats = OrganizationMember::where('organization_id', $org->id)
            ->with('user:id,name,email')
            ->get()
            ->map(function ($member) use ($startDate) {
                $user = $member->user;
                $assignedCount = Lead::where('assigned_to_user_id', $user->id)->where('created_at', '>=', $startDate)->count();
                $wonCount = Lead::where('assigned_to_user_id', $user->id)->where('status', 'won')->where('converted_at', '>=', $startDate)->count();
                $wonSum = (float) Lead::where('assigned_to_user_id', $user->id)->where('status', 'won')->where('converted_at', '>=', $startDate)->sum('estimated_value');

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $member->role->label(),
                    'assigned_leads' => $assignedCount,
                    'won_leads' => $wonCount,
                    'won_revenue' => $wonSum,
                    'win_rate' => $assignedCount > 0 ? round(($wonCount / $assignedCount) * 100, 1) : 0.0,
                ];
            });

        // Quotations metrics
        $quoteCount = Quotation::where('organization_id', $org->id)->where('created_at', '>=', $startDate)->count();
        $acceptedQuoteCount = Quotation::where('organization_id', $org->id)->where('status', 'accepted')->where('responded_at', '>=', $startDate)->count();
        $quoteAcceptanceRate = $quoteCount > 0 ? round(($acceptedQuoteCount / $quoteCount) * 100, 1) : 0.0;

        return Inertia::render('Analytics/Index', [
            'metrics' => [
                'total_leads' => $totalLeads,
                'won_leads' => $wonLeads,
                'lost_leads' => $lostLeads,
                'conversion_rate' => $conversionRate,
                'won_revenue' => $wonRevenue,
                'pipeline_value' => $pipelineValue,
                'avg_deal_value' => $avgDealValue,
                'quote_acceptance_rate' => $quoteAcceptanceRate,
                'currency' => $org->currency,
            ],
            'sourceStats' => $sourceStats,
            'teamStats' => $teamStats,
            'currentPeriod' => $days,
        ]);
    }
}

