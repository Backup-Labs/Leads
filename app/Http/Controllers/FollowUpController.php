<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FollowUps\CompleteFollowUpAction;
use App\Actions\FollowUps\ScheduleFollowUpAction;
use App\Http\Requests\StoreFollowUpRequest;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\OrganizationMember;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowUpController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected ScheduleFollowUpAction $scheduleAction,
        protected CompleteFollowUpAction $completeAction
    ) {}

    public function index(Request $request): Response
    {
        $orgId = $this->tenantContext->getOrganizationId();
        $status = $request->input('status', 'upcoming');

        $query = FollowUp::where('organization_id', $orgId)
            ->with(['lead:id,customer_name,company_name,phone,email,estimated_value', 'assignedTo:id,name']);

        if ($status === 'overdue') {
            $query->where('status', 'pending')
                  ->where('scheduled_at', '<', now())
                  ->orderBy('scheduled_at', 'asc');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed')
                  ->orderBy('completed_at', 'desc');
        } else { // upcoming
            $query->where('status', 'pending')
                  ->where('scheduled_at', '>=', now())
                  ->orderBy('scheduled_at', 'asc');
        }

        $followUps = $query->paginate(20)->withQueryString();

        $counts = [
            'upcoming' => FollowUp::where('organization_id', $orgId)->where('status', 'pending')->where('scheduled_at', '>=', now())->count(),
            'overdue' => FollowUp::where('organization_id', $orgId)->where('status', 'pending')->where('scheduled_at', '<', now())->count(),
            'completed' => FollowUp::where('organization_id', $orgId)->where('status', 'completed')->count(),
        ];

        $team = OrganizationMember::where('organization_id', $orgId)->with('user:id,name,email')->get();

        return Inertia::render('FollowUps/Index', [
            'followUps' => $followUps,
            'currentStatus' => $status,
            'counts' => $counts,
            'team' => $team,
        ]);
    }

    public function store(StoreFollowUpRequest $request): RedirectResponse
    {
        $lead = Lead::findOrFail($request->lead_id);
        $this->authorize('update', $lead);

        $this->scheduleAction->execute($lead, $request->validated());

        return back()->with('success', 'Follow-up reminder scheduled successfully.');
    }

    public function complete(Request $request, FollowUp $followUp): RedirectResponse
    {
        if ((int) $followUp->organization_id !== (int) $this->tenantContext->getOrganizationId()) {
            abort(403);
        }

        $request->validate([
            'completion_notes' => ['nullable', 'string'],
        ]);

        $this->completeAction->execute($followUp, $request->completion_notes);

        return back()->with('success', 'Follow-up marked completed.');
    }
}

