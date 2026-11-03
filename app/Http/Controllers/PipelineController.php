<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Leads\TransitionLeadStageAction;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PipelineController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected TransitionLeadStageAction $transitionStageAction
    ) {}

    public function index(): Response
    {
        $orgId = $this->tenantContext->getOrganizationId();

        $stages = PipelineStage::where('organization_id', $orgId)
            ->with(['leads' => function ($q) {
                $q->with(['assignedTo:id,name,avatar_url', 'source:id,name,color'])
                  ->latest('updated_at');
            }])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Pipeline/Index', [
            'stages' => $stages,
        ]);
    }

    public function updateLeadStage(Request $request): JsonResponse
    {
        $request->validate([
            'lead_id' => ['required', 'integer'],
            'stage_id' => ['required', 'integer'],
            'lost_reason' => ['nullable', 'string'],
        ]);

        $lead = Lead::findOrFail($request->lead_id);
        $this->authorize('update', $lead);

        $updatedLead = $this->transitionStageAction->execute(
            $lead,
            (int) $request->stage_id,
            $request->lost_reason
        );

        return response()->json([
            'success' => true,
            'message' => 'Lead pipeline stage updated.',
            'lead' => $updatedLead,
        ]);
    }

    public function storeStage(Request $request): RedirectResponse
    {
        $orgId = $this->tenantContext->getOrganizationId();

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $maxOrder = PipelineStage::where('organization_id', $orgId)->max('sort_order') ?? 0;

        PipelineStage::create([
            'organization_id' => $orgId,
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'color' => $request->color ?? '#280047',
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Pipeline stage created.');
    }
}
