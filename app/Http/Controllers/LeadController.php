<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Leads\AssignLeadAction;
use App\Actions\Leads\CreateLeadAction;
use App\Actions\Leads\TransitionLeadStageAction;
use App\Enums\ActivityType;
use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\OrganizationMember;
use App\Models\PipelineStage;
use App\Services\AuditLogger;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected CreateLeadAction $createLeadAction,
        protected TransitionLeadStageAction $transitionStageAction,
        protected AssignLeadAction $assignLeadAction,
        protected AuditLogger $auditLogger
    ) {}

    public function index(Request $request): Response
    {
        $orgId = $this->tenantContext->getOrganizationId();

        $query = Lead::query()
            ->with(['stage:id,name,color,is_won,is_lost', 'source:id,name,color', 'assignedTo:id,name,email'])
            ->latest('id');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'ilike', "%{$search}%")
                  ->orWhere('company_name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        // Filters
        if ($stageId = $request->input('stage_id')) {
            $query->where('pipeline_stage_id', $stageId);
        }

        if ($sourceId = $request->input('source_id')) {
            $query->where('lead_source_id', $sourceId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($assignedTo = $request->input('assigned_to')) {
            $query->where('assigned_to_user_id', $assignedTo);
        }

        $leads = $query->paginate(15)->withQueryString();

        $stages = PipelineStage::where('organization_id', $orgId)->orderBy('sort_order')->get();
        $sources = LeadSource::where('organization_id', $orgId)->where('is_active', true)->get();
        $team = OrganizationMember::where('organization_id', $orgId)->with('user:id,name,email')->get();

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => $request->only(['search', 'stage_id', 'source_id', 'status', 'assigned_to']),
            'stages' => $stages,
            'sources' => $sources,
            'team' => $team,
        ]);
    }

    public function create(): Response
    {
        $orgId = $this->tenantContext->getOrganizationId();

        return Inertia::render('Leads/Create', [
            'stages' => PipelineStage::where('organization_id', $orgId)->orderBy('sort_order')->get(),
            'sources' => LeadSource::where('organization_id', $orgId)->where('is_active', true)->get(),
            'team' => OrganizationMember::where('organization_id', $orgId)->with('user:id,name,email')->get(),
            'priorities' => array_map(fn ($p) => ['value' => $p->value, 'label' => $p->label()], LeadPriority::cases()),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $lead = $this->createLeadAction->execute($request->validated());

        return redirect()->route('leads.show', $lead->id)
            ->with('success', "Lead '{$lead->customer_name}' captured successfully.");
    }

    public function show(Lead $lead): Response
    {
        Gate::authorize('view', $lead);

        $lead->load([
            'stage',
            'source',
            'assignedTo',
            'activities.user',
            'followUps.assignedTo',
            'quotations.items',
            'recoveryAlerts',
        ]);

        $orgId = $this->tenantContext->getOrganizationId();

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
            'stages' => PipelineStage::where('organization_id', $orgId)->orderBy('sort_order')->get(),
            'team' => OrganizationMember::where('organization_id', $orgId)->with('user:id,name,email')->get(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'requested_service' => ['nullable', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $old = $lead->toArray();
        $lead->update($validated);
        $this->auditLogger->log('lead.updated', $lead, $old, $lead->toArray());

        return back()->with('success', 'Lead details updated.');
    }

    public function updateStage(Request $request, Lead $lead): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $request->validate([
            'pipeline_stage_id' => ['required', 'integer'],
            'lost_reason' => ['nullable', 'string'],
        ]);

        $this->transitionStageAction->execute(
            $lead,
            (int) $request->pipeline_stage_id,
            $request->lost_reason
        );

        return back()->with('success', 'Lead moved to new pipeline stage.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $request->validate([
            'assigned_to_user_id' => ['nullable', 'integer'],
        ]);

        $this->assignLeadAction->execute(
            $lead,
            $request->assigned_to_user_id ? (int) $request->assigned_to_user_id : null
        );

        return back()->with('success', 'Lead assignment updated.');
    }

    public function addNote(Request $request, Lead $lead): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $request->validate([
            'note' => ['required', 'string'],
        ]);

        $lead->activities()->create([
            'organization_id' => $lead->organization_id,
            'user_id' => auth()->id(),
            'type' => ActivityType::NOTE,
            'subject' => 'Note Added',
            'description' => $request->note,
        ]);

        $lead->update(['last_contacted_at' => now()]);

        return back()->with('success', 'Activity note recorded.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        Gate::authorize('delete', $lead);

        $name = $lead->customer_name;
        $lead->delete();
        $this->auditLogger->log('lead.deleted', $lead);

        return redirect()->route('leads.index')->with('success', "Lead '{$name}' archived.");
    }

    public function export(): StreamedResponse
    {
        $org = $this->tenantContext->getOrganization();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads-export-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Customer Name', 'Email', 'Phone', 'Company', 'Service', 'Estimated Value', 'Currency', 'Priority', 'Status', 'Stage', 'Assigned To', 'Created At']);

            Lead::with(['stage', 'assignedTo'])
                ->chunk(100, function ($leads) use ($handle) {
                    foreach ($leads as $lead) {
                        fputcsv($handle, [
                            $lead->id,
                            $lead->customer_name,
                            $lead->email,
                            $lead->phone,
                            $lead->company_name,
                            $lead->requested_service,
                            $lead->estimated_value,
                            $lead->currency,
                            $lead->priority->value,
                            $lead->status->value,
                            $lead->stage?->name,
                            $lead->assignedTo?->name,
                            $lead->created_at->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
