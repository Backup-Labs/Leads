<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Quotations\CalculateQuotationTotalAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\Quotations\SendQuotationAction;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\Lead;
use App\Models\Quotation;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected CreateQuotationAction $createQuotationAction,
        protected SendQuotationAction $sendQuotationAction,
        protected CalculateQuotationTotalAction $calculateAction
    ) {}

    public function index(Request $request): Response
    {
        $orgId = $this->tenantContext->getOrganizationId();

        $query = Quotation::where('organization_id', $orgId)
            ->with(['lead:id,customer_name', 'createdBy:id,name'])
            ->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'ilike', "%{$search}%")
                  ->orWhere('customer_name', 'ilike', "%{$search}%")
                  ->orWhere('company_name', 'ilike', "%{$search}%");
            });
        }

        $quotations = $query->paginate(15)->withQueryString();

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $lead = null;
        if ($leadId = $request->input('lead_id')) {
            $lead = Lead::find($leadId);
            if ($lead) {
                Gate::authorize('view', $lead);
            }
        }

        $org = $this->tenantContext->getOrganization();

        return Inertia::render('Quotations/Create', [
            'lead' => $lead,
            'defaultTaxRate' => (float) ($org->settings?->default_tax_rate ?? 18.00),
            'defaultTerms' => $org->settings?->quotation_default_terms,
            'currency' => $org->currency,
        ]);
    }

    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        $lead = Lead::findOrFail($request->lead_id);
        Gate::authorize('update', $lead);

        $quotation = $this->createQuotationAction->execute($lead, $request->validated());

        return redirect()->route('quotations.show', $quotation->id)
            ->with('success', "Quotation #{$quotation->quotation_number} generated successfully.");
    }

    public function show(Quotation $quotation): Response
    {
        Gate::authorize('view', $quotation);

        $quotation->load(['lead', 'items', 'createdBy', 'organization']);

        return Inertia::render('Quotations/Show', [
            'quotation' => $quotation,
            'publicUrl' => route('quotes.public', $quotation->token),
        ]);
    }

    public function send(Quotation $quotation): RedirectResponse
    {
        Gate::authorize('update', $quotation);

        $this->sendQuotationAction->execute($quotation);

        return back()->with('success', "Quotation #{$quotation->quotation_number} marked as sent to customer.");
    }

    public function preview(Quotation $quotation)
    {
        Gate::authorize('view', $quotation);

        $quotation->load(['items', 'organization']);

        return view('quotations.print', [
            'quotation' => $quotation,
            'organization' => $this->tenantContext->getOrganization(),
        ]);
    }
}
