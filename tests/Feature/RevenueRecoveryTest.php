<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Organizations\CreateOrganizationWorkspaceAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\Recovery\EvaluateRevenueRecoveryAction;
use App\Enums\QuotationStatus;
use App\Enums\RecoveryAlertType;
use App\Enums\RecoverySeverity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\RecoveryAlert;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluates_overdue_followups_and_stalled_quotations(): void
    {
        $user = User::factory()->create();
        $org = app(CreateOrganizationWorkspaceAction::class)->execute($user, 'Recovery Test Org');
        app(TenantContext::class)->setOrganization($org);

        $stage = PipelineStage::where('organization_id', $org->id)->first();

        // 1. Create lead with overdue follow-up
        $leadA = Lead::create([
            'organization_id' => $org->id,
            'customer_name' => 'Stalled Client A',
            'pipeline_stage_id' => $stage->id,
            'estimated_value' => 2000000.00,
            'currency' => 'RWF',
            'status' => 'active',
            'last_contacted_at' => now()->subDays(5),
        ]);

        FollowUp::create([
            'organization_id' => $org->id,
            'lead_id' => $leadA->id,
            'title' => 'Important contract review call',
            'scheduled_at' => now()->subHours(50), // >48h overdue
            'status' => 'pending',
        ]);

        // 2. Create lead with sent quotation awaiting response for 6 days
        $leadB = Lead::create([
            'organization_id' => $org->id,
            'customer_name' => 'Pending Quote Client B',
            'pipeline_stage_id' => $stage->id,
            'estimated_value' => 4500000.00,
            'currency' => 'RWF',
            'status' => 'active',
            'last_contacted_at' => now()->subDays(6),
        ]);

        $quote = app(CreateQuotationAction::class)->execute($leadB, [
            'customer_name' => $leadB->customer_name,
            'discount_amount' => 0,
            'tax_rate' => 18.00,
            'items' => [
                ['description' => 'Software Implementation', 'quantity' => 1, 'unit_price' => 4500000.00],
            ],
        ]);

        $quote->update([
            'status' => QuotationStatus::SENT,
            'sent_at' => now()->subDays(6),
        ]);

        // 3. Execute recovery evaluation
        $action = app(EvaluateRevenueRecoveryAction::class);
        $count = $action->execute($org);

        $this->assertGreaterThanOrEqual(2, $count);

        // Verify overdue follow-up alert
        $this->assertDatabaseHas('recovery_alerts', [
            'organization_id' => $org->id,
            'lead_id' => $leadA->id,
            'alert_type' => RecoveryAlertType::OVERDUE_FOLLOWUP->value,
            'severity' => RecoverySeverity::CRITICAL->value,
            'is_resolved' => false,
        ]);

        // Verify unanswered quote alert
        $this->assertDatabaseHas('recovery_alerts', [
            'organization_id' => $org->id,
            'quotation_id' => $quote->id,
            'alert_type' => RecoveryAlertType::UNANSWERED_QUOTE->value,
            'severity' => RecoverySeverity::CRITICAL->value,
            'is_resolved' => false,
        ]);
    }
}
