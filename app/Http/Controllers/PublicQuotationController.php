<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Quotations\RespondQuotationAction;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicQuotationController extends Controller
{
    public function __construct(
        protected RespondQuotationAction $respondAction
    ) {}

    public function show(string $token): Response
    {
        $quotation = Quotation::where('token', $token)
            ->with(['items', 'organization:id,name,logo_url,billing_email,phone,currency'])
            ->firstOrFail();

        // Mark viewed timestamp
        if (! $quotation->viewed_at) {
            $quotation->update(['viewed_at' => now()]);
        }

        return Inertia::render('PublicQuote/Show', [
            'quotation' => $quotation,
        ]);
    }

    public function respond(Request $request, string $token): RedirectResponse
    {
        $quotation = Quotation::where('token', $token)->firstOrFail();

        $request->validate([
            'action' => ['required', 'string', 'in:accept,reject'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $isAccepted = $request->input('action') === 'accept';
        $this->respondAction->execute($quotation, $isAccepted, $request->input('reason'));

        $message = $isAccepted
            ? 'Thank you! You have accepted this quotation.'
            : 'Thank you for your response. The quotation has been declined.';

        return back()->with('success', $message);
    }
}
