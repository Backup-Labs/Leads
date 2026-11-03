<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Recovery\EvaluateRevenueRecoveryAction;
use App\Models\RecoveryAlert;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecoveryController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected EvaluateRevenueRecoveryAction $evaluateRecoveryAction
    ) {}

    public function index(Request $request): Response
    {
        $org = $this->tenantContext->getOrganization();

        $query = RecoveryAlert::where('organization_id', $org->id)
            ->where('is_resolved', false)
            ->with(['lead.assignedTo', 'lead.stage', 'quotation'])
            ->orderByRaw("CASE WHEN severity = 'critical' THEN 1 WHEN severity = 'warning' THEN 2 ELSE 3 END")
            ->latest('id');

        if ($type = $request->input('type')) {
            $query->where('alert_type', $type);
        }

        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        $alerts = $query->paginate(20)->withQueryString();

        $totalRevenueAtRisk = (float) RecoveryAlert::where('organization_id', $org->id)
            ->where('is_resolved', false)
            ->sum('potential_revenue_at_risk');

        $counts = [
            'critical' => RecoveryAlert::where('organization_id', $org->id)->where('is_resolved', false)->where('severity', 'critical')->count(),
            'warning' => RecoveryAlert::where('organization_id', $org->id)->where('is_resolved', false)->where('severity', 'warning')->count(),
            'total' => RecoveryAlert::where('organization_id', $org->id)->where('is_resolved', false)->count(),
        ];

        return Inertia::render('Recovery/Index', [
            'alerts' => $alerts,
            'totalRevenueAtRisk' => $totalRevenueAtRisk,
            'counts' => $counts,
            'currency' => $org->currency,
            'filters' => $request->only(['type', 'severity']),
        ]);
    }

    public function scan(): RedirectResponse
    {
        $org = $this->tenantContext->getOrganization();
        $count = $this->evaluateRecoveryAction->execute($org);

        return back()->with('success', "Revenue recovery scan complete. Identified {$count} potential revenue opportunities.");
    }

    public function dismiss(RecoveryAlert $alert): RedirectResponse
    {
        if ((int) $alert->organization_id !== (int) $this->tenantContext->getOrganizationId()) {
            abort(403);
        }

        $alert->update(['is_resolved' => true, 'resolved_at' => now()]);

        return back()->with('success', 'Recovery alert dismissed.');
    }
}
