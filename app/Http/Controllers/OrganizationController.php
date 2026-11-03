<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Services\AuditLogger;
use App\Services\CurrencyConverter;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected AuditLogger $auditLogger,
        protected CurrencyConverter $currencyConverter
    ) {}

    public function switchOrg(Request $request, Organization $organization): RedirectResponse
    {
        $user = $request->user();

        $isMember = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $isMember) {
            abort(403, 'You do not belong to this organization.');
        }

        $request->session()->put('current_organization_id', $organization->id);

        return redirect()->route('dashboard')->with('success', "Switched workspace to {$organization->name}.");
    }

    public function settings(): Response
    {
        $org = $this->tenantContext->getOrganization();
        $this->authorize('update', $org);

        $org->load('settings');

        return Inertia::render('Settings/Index', [
            'organization' => $org,
            'settings' => $org->settings,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $org = $this->tenantContext->getOrganization();
        $this->authorize('update', $org);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'size:3'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'stale_lead_threshold_days' => ['required', 'integer', 'min:1', 'max:90'],
            'quote_followup_threshold_days' => ['required', 'integer', 'min:1', 'max:30'],
            'high_value_threshold' => ['required', 'numeric', 'min:0'],
            'quotation_default_terms' => ['nullable', 'string'],
        ]);

        $oldCurrency = strtoupper($org->currency ?: 'UGX');
        $newCurrency = strtoupper($validated['currency']);

        if ($oldCurrency !== $newCurrency) {
            $org->load('settings');
            $this->currencyConverter->convertOrganizationRecords($org, $oldCurrency, $newCurrency);
        }

        $org->update([
            'name' => $validated['name'],
            'billing_email' => $validated['billing_email'],
            'phone' => $validated['phone'],
            'currency' => $newCurrency,
        ]);

        $org->settings()->updateOrCreate(
            ['organization_id' => $org->id],
            [
                'default_currency' => $newCurrency,
                'default_tax_rate' => $validated['default_tax_rate'],
                'stale_lead_threshold_days' => (int) $validated['stale_lead_threshold_days'],
                'quote_followup_threshold_days' => (int) $validated['quote_followup_threshold_days'],
                'high_value_threshold' => (int) round((float) $validated['high_value_threshold']),
                'quotation_default_terms' => $validated['quotation_default_terms'],
            ]
        );

        $this->auditLogger->log('organization.settings_updated', $org);

        $msg = $oldCurrency !== $newCurrency
            ? "Settings updated and all amounts converted from {$oldCurrency} to {$newCurrency}."
            : 'Workspace settings updated.';

        return back()->with('success', $msg);
    }

    public function updateCurrency(Request $request): RedirectResponse
    {
        $org = $this->tenantContext->getOrganization();

        $validated = $request->validate([
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $oldCurrency = strtoupper($org->currency ?: 'UGX');
        $newCurrency = strtoupper($validated['currency']);

        if ($oldCurrency !== $newCurrency) {
            $org->load('settings');
            $this->currencyConverter->convertOrganizationRecords($org, $oldCurrency, $newCurrency);
            $org->update(['currency' => $newCurrency]);
            $this->auditLogger->log('organization.currency_converted', $org, ['from' => $oldCurrency, 'to' => $newCurrency]);
        }

        return back()->with('success', "Currency changed to {$newCurrency}. All deal values and quotations have been automatically converted.");
    }
}
