<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Organizations\CreateOrganizationWorkspaceAction;
use App\Enums\MemberRole;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_or_view_leads_from_another_organization(): void
    {
        $creator = app(CreateOrganizationWorkspaceAction::class);

        $userA = User::factory()->create(['name' => 'User Org A']);
        $orgA = $creator->execute($userA, 'Organization Alpha');

        $userB = User::factory()->create(['name' => 'User Org B']);
        $orgB = $creator->execute($userB, 'Organization Beta');

        // Create lead in Org B
        $stageB = PipelineStage::where('organization_id', $orgB->id)->first();
        $leadB = Lead::create([
            'organization_id' => $orgB->id,
            'customer_name' => 'Confidential Client B',
            'pipeline_stage_id' => $stageB->id,
            'estimated_value' => 5000000,
            'currency' => 'RWF',
        ]);

        // Attempt to view Org B's lead while authenticated as Org A user
        $response = $this->actingAs($userA)
            ->withSession(['current_organization_id' => $orgA->id])
            ->get("/leads/{$leadB->id}");

        // TenantScope hides it, or policy forbids it -> 404 / 403
        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    public function test_lead_cannot_be_assigned_to_user_from_different_organization(): void
    {
        $creator = app(CreateOrganizationWorkspaceAction::class);

        $userA = User::factory()->create(['name' => 'Owner Alpha']);
        $orgA = $creator->execute($userA, 'Organization Alpha');

        $userB = User::factory()->create(['name' => 'Rep Beta']);
        $orgB = $creator->execute($userB, 'Organization Beta');

        $stageA = PipelineStage::where('organization_id', $orgA->id)->first();

        // Attempt to assign userB (who belongs to Org B) to a lead in Org A
        $response = $this->actingAs($userA)
            ->withSession(['current_organization_id' => $orgA->id])
            ->post('/leads', [
                'customer_name' => 'Cross-tenant Test',
                'pipeline_stage_id' => $stageA->id,
                'assigned_to_user_id' => $userB->id, // Belongs to Org B!
            ]);

        $response->assertSessionHasErrors(['assigned_to_user_id']);
    }
}
