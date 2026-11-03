<?php

declare(strict_types=1);

use App\Actions\Leads\CreateLeadAction;
use App\Actions\Leads\TransitionLeadStageAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\Recovery\EvaluateRevenueRecoveryAction;
use App\Enums\QuotationStatus;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\RecoveryAlert;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/login', function (LoginRequest $request): JsonResponse {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    });

    Route::middleware(['auth', 'tenant'])->group(function () {
        Route::get('/auth/me', function (Request $request): JsonResponse {
            $user = $request->user();
            $tenant = app(TenantContext::class)->getOrganization();
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'organization' => $tenant,
                ],
            ]);
        });

        // Leads
        Route::get('/leads', function (Request $request): JsonResponse {
            $leads = Lead::with(['stage', 'source', 'assignedTo'])->paginate(20);
            return response()->json(['success' => true, 'data' => $leads]);
        });

        Route::post('/leads', function (StoreLeadRequest $request, CreateLeadAction $action): JsonResponse {
            $lead = $action->execute($request->validated());
            return response()->json(['success' => true, 'data' => $lead], 201);
        });

        Route::get('/leads/{lead}', function (Lead $lead): JsonResponse {
            return response()->json(['success' => true, 'data' => $lead->load(['stage', 'source', 'activities', 'quotations'])]);
        });

        Route::patch('/leads/{lead}/stage', function (Request $request, Lead $lead, TransitionLeadStageAction $action): JsonResponse {
            $request->validate(['stage_id' => 'required|integer']);
            $updated = $action->execute($lead, (int) $request->stage_id, $request->input('lost_reason'));
            return response()->json(['success' => true, 'data' => $updated]);
        });

        // Pipeline Stages
        Route::get('/pipeline/stages', function (): JsonResponse {
            $orgId = app(TenantContext::class)->getOrganizationId();
            $stages = PipelineStage::where('organization_id', $orgId)->orderBy('sort_order')->get();
            return response()->json(['success' => true, 'data' => $stages]);
        });

        // Quotations
        Route::get('/quotations', function (): JsonResponse {
            $quotes = Quotation::with('items')->latest()->paginate(20);
            return response()->json(['success' => true, 'data' => $quotes]);
        });

        Route::post('/quotations', function (StoreQuotationRequest $request, CreateQuotationAction $action): JsonResponse {
            $lead = Lead::findOrFail($request->lead_id);
            $quotation = $action->execute($lead, $request->validated());
            return response()->json(['success' => true, 'data' => $quotation], 201);
        });

        // Recovery Alerts
        Route::get('/recovery/alerts', function (): JsonResponse {
            $orgId = app(TenantContext::class)->getOrganizationId();
            $alerts = RecoveryAlert::where('organization_id', $orgId)->where('is_resolved', false)->get();
            return response()->json(['success' => true, 'data' => $alerts]);
        });

        // Analytics Overview
        Route::get('/analytics/overview', function (): JsonResponse {
            $orgId = app(TenantContext::class)->getOrganizationId();
            return response()->json([
                'success' => true,
                'data' => [
                    'total_leads' => Lead::where('organization_id', $orgId)->count(),
                    'active_pipeline_value' => (float) Lead::where('organization_id', $orgId)->where('status', 'active')->sum('estimated_value'),
                    'won_revenue' => (float) Lead::where('organization_id', $orgId)->where('status', 'won')->sum('estimated_value'),
                    'overdue_followups' => FollowUp::where('organization_id', $orgId)->where('status', 'pending')->where('scheduled_at', '<', now())->count(),
                ],
            ]);
        });
    });
});
