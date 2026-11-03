<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Organizations\CreateOrganizationWorkspaceAction;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_lifecycle_from_capture_to_stage_transition_and_win(): void
    {
        $user = User::factory()->create();
        $org = app(CreateOrganizationWorkspaceAction::class)->execute($user, 'Apex Digital');

        $stages = PipelineStage::where('organization_id', $org->id)->get()->keyBy('slug');
        $initialStage = $stages['new-inquiry'];
        $wonStage = $stages['closed-won'];

        // 1. Capture Lead
        $response = $this->actingAs($user)
            ->withSession(['current_organization_id' => $org->id])
            ->post('/leads', [
                'customer_name' => 'Jean Pierre',
                'company_name' => 'Kigali Logistics Ltd',
                'email' => 'jp@kigalilogistics.rw',
                'phone' => '+250 788 123 999',
                'requested_service' => 'ERP Deployment',
                'estimated_value' => 7500000.00,
                'pipeline_stage_id' => $initialStage->id,
            ]);

        $response->assertSessionHasNoErrors();
        $lead = Lead::withoutGlobalScopes()->where('customer_name', 'Jean Pierre')->first();
        $this->assertNotNull($lead);
        $this->assertEquals(LeadStatus::ACTIVE, $lead->status);

        // 2. Transition to Won Stage
        $stageResponse = $this->actingAs($user)
            ->withSession(['current_organization_id' => $org->id])
            ->patch("/leads/{$lead->id}/stage", [
                'pipeline_stage_id' => $wonStage->id,
            ]);

        $stageResponse->assertSessionHasNoErrors();

        $lead->refresh();
        $this->assertEquals($wonStage->id, $lead->pipeline_stage_id);
        $this->assertEquals(LeadStatus::WON, $lead->status);
        $this->assertNotNull($lead->converted_at);

        // Verify activity logged
        $this->assertDatabaseHas('activities', [
            'lead_id' => $lead->id,
            'type' => 'stage_change',
        ]);
    }
}
