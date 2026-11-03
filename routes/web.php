<?php

declare(strict_types=1);

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PublicQuotationController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Public Client-Facing Quotation Token Routes (No Auth Required)
Route::get('/quotes/{token}', [PublicQuotationController::class, 'show'])->name('quotes.public');
Route::post('/quotes/{token}/respond', [PublicQuotationController::class, 'respond'])->name('quotes.public.respond');

/*
|--------------------------------------------------------------------------
| Authenticated & Tenant Workspace Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leads Management
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::patch('/leads/{lead}/stage', [LeadController::class, 'updateStage'])->name('leads.update-stage');
    Route::patch('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('/leads/{lead}/notes', [LeadController::class, 'addNote'])->name('leads.add-note');

    // Visual Sales Pipeline (Kanban)
    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
    Route::post('/pipeline/stage-update', [PipelineController::class, 'updateLeadStage'])->name('pipeline.update-stage');
    Route::post('/pipeline/stages', [PipelineController::class, 'storeStage'])->name('pipeline.store-stage');

    // Follow-ups & Reminders
    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
    Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
    Route::patch('/follow-ups/{followUp}/complete', [FollowUpController::class, 'complete'])->name('follow-ups.complete');

    // Quotations
    Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::get('/quotations/{quotation}/preview', [QuotationController::class, 'preview'])->name('quotations.preview');

    // Revenue Recovery Cockpit
    Route::get('/recovery', [RecoveryController::class, 'index'])->name('recovery.index');
    Route::post('/recovery/scan', [RecoveryController::class, 'scan'])->name('recovery.scan');
    Route::patch('/recovery/{alert}/dismiss', [RecoveryController::class, 'dismiss'])->name('recovery.dismiss');

    // Analytics & Reports
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Team & Invitations
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team/invite', [TeamController::class, 'invite'])->name('team.invite');
    Route::delete('/team/members/{member}', [TeamController::class, 'remove'])->name('team.remove');

    // Workspace & Settings
    Route::post('/organizations/{organization}/switch', [OrganizationController::class, 'switchOrg'])->name('organizations.switch');
    Route::get('/settings', [OrganizationController::class, 'settings'])->name('settings.index');
    Route::patch('/settings', [OrganizationController::class, 'updateSettings'])->name('settings.update');
});
