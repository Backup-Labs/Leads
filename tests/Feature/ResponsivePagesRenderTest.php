<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Organizations\CreateOrganizationWorkspaceAction;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsivePagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_and_authenticated_pages_render_without_error(): void
    {
        // 1. Public Routes
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();

        // 2. Setup Workspace & Data
        $user = User::factory()->create();
        $org = app(CreateOrganizationWorkspaceAction::class)->execute($user, 'Responsive Systems Ltd');
        $stage = PipelineStage::where('organization_id', $org->id)->firstOrFail();

        $this->actingAs($user)->withSession(['current_organization_id' => $org->id]);

        // Create a sample lead
        $this->post('/leads', [
            'customer_name' => 'Grace Nakato',
            'company_name' => 'Crest Logistics Ltd',
            'email' => 'grace@crestlogistics.ug',
            'phone' => '+256 772 111222',
            'requested_service' => 'Fleet Tracking Deployment',
            'estimated_value' => 12500000,
            'pipeline_stage_id' => $stage->id,
        ])->assertRedirect();

        $lead = Lead::withoutGlobalScopes()->where('customer_name', 'Grace Nakato')->firstOrFail();

        // Create a sample quotation
        $this->post('/quotations', [
            'lead_id' => $lead->id,
            'customer_name' => 'Grace Nakato',
            'customer_email' => 'grace@crestlogistics.ug',
            'customer_phone' => '+256 772 111222',
            'company_name' => 'Crest Logistics Ltd',
            'currency' => 'UGX',
            'tax_rate' => 18,
            'discount_amount' => 0,
            'validity_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'description' => 'Fleet Tracking Deployment',
                    'quantity' => 1,
                    'unit_price' => 12500000,
                ],
            ],
        ])->assertRedirect();

        $quotation = Quotation::withoutGlobalScopes()->where('lead_id', $lead->id)->firstOrFail();

        // 3. Authenticated Application Pages
        $this->get('/dashboard')->assertOk();
        $this->get('/leads')->assertOk();
        $this->get('/leads/create')->assertOk();
        $this->get("/leads/{$lead->id}")->assertOk();
        $this->get('/pipeline')->assertOk();
        $this->get('/follow-ups')->assertOk();
        $this->get('/quotations')->assertOk();
        $this->get('/quotations/create')->assertOk();
        $this->get("/quotations/{$quotation->id}")->assertOk();
        $this->get("/quotations/{$quotation->id}/preview")->assertOk();
        $this->get('/recovery')->assertOk();
        $this->get('/analytics')->assertOk();
        $this->get('/team')->assertOk();
        $this->get('/settings')->assertOk();

        // 4. Public Client Quotation Share Link
        $this->get("/quotes/{$quotation->token}")->assertOk();
    }
}

