<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Recovery\EvaluateRevenueRecoveryAction;
use App\Enums\QuotationStatus;
use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\RecoveryAlert;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected EvaluateRevenueRecoveryAction $evaluateRecoveryAction
    ) {}

    public function index(Request $request): Response
    {
        $org = $this->tenantContext->getOrganization();

        if (! $org) {
            abort(403, 'No organization workspace found.');
        }

        // Run recovery evaluation pass if requested or periodic
        if ($request->boolean('refresh_recovery')) {
            $this->evaluateRecoveryAction->execute($org);
        }

        $leadsCount = Lead::count();
        $activeLeadsCount = Lead::where('status', 'active')->count();
        $wonLeadsCount = Lead::where('status', 'won')->count();
        $lostLeadsCount = Lead::where('status', 'lost')->count();

        $closedCount = $wonLeadsCount + $lostLeadsCount;
        $conversionRate = $closedCount > 0 ? round(($wonLeadsCount / $closedCount) * 100, 1) : 0.0;

        $pipelineValue = (float) Lead::where('status', 'active')->sum('estimated_value');
        $wonRevenue = (float) Lead::where('status', 'won')->sum('estimated_value');

        $overdueFollowUpsCount = FollowUp::where('status', 'pending')
            ->where('scheduled_at', '<', now())
            ->count();

        $pendingQuotesCount = Quotation::where('status', QuotationStatus::SENT)->count();
        $pendingQuotesValue = (float) Quotation::where('status', QuotationStatus::SENT)->sum('total_amount');

        // Pipeline stages with lead counts and accumulated value
        $stages = PipelineStage::withCount('leads')
            ->withSum(['leads as total_value' => fn ($q) => $q->whereNull('deleted_at')], 'estimated_value')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'leads_count' => $stage->leads_count,
                'total_value' => (float) ($stage->total_value ?? 0.00),
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
            ]);

        // Recent timeline activities
        $recentActivities = Activity::with(['user:id,name,avatar_url', 'lead:id,customer_name'])
            ->latest()
            ->take(8)
            ->get();

        // Top urgent revenue recovery alerts
        $recoveryAlerts = RecoveryAlert::where('is_resolved', false)
            ->with(['lead:id,customer_name,estimated_value', 'quotation:id,quotation_number,total_amount'])
            ->orderByRaw("CASE WHEN severity = 'critical' THEN 1 WHEN severity = 'warning' THEN 2 ELSE 3 END")
            ->take(5)
            ->get();

        // Upcoming follow-ups for today & tomorrow
        $upcomingFollowUps = FollowUp::where('status', 'pending')
            ->where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<=', now()->addDays(2))
            ->with(['lead:id,customer_name,phone', 'assignedTo:id,name'])
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'metrics' => [
                'total_leads' => $leadsCount,
                'active_leads' => $activeLeadsCount,
                'won_leads' => $wonLeadsCount,
                'lost_leads' => $lostLeadsCount,
                'conversion_rate' => $conversionRate,
                'pipeline_value' => $pipelineValue,
                'won_revenue' => $wonRevenue,
                'overdue_followups' => $overdueFollowUpsCount,
                'pending_quotes_count' => $pendingQuotesCount,
                'pending_quotes_value' => $pendingQuotesValue,
                'currency' => $org->currency,
            ],
            'stages' => $stages,
            'recentActivities' => $recentActivities,
            'recoveryAlerts' => $recoveryAlerts,
            'upcomingFollowUps' => $upcomingFollowUps,
        ]);
    }
}
