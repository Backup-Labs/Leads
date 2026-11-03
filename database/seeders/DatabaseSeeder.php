<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Organizations\CreateOrganizationWorkspaceAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\Recovery\EvaluateRevenueRecoveryAction;
use App\Enums\ActivityType;
use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Enums\MemberRole;
use App\Enums\QuotationStatus;
use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Subscription Plans
        $starterPlan = SubscriptionPlan::firstOrCreate(['slug' => 'starter'], [
            'name' => 'Starter',
            'price_monthly' => 19.00,
            'currency' => 'USD',
            'max_leads' => 150,
            'max_users' => 3,
            'has_quotations' => true,
            'has_revenue_recovery' => true,
        ]);

        SubscriptionPlan::firstOrCreate(['slug' => 'professional'], [
            'name' => 'Professional',
            'price_monthly' => 49.00,
            'currency' => 'USD',
            'max_leads' => 500,
            'max_users' => 10,
            'has_quotations' => true,
            'has_revenue_recovery' => true,
        ]);

        SubscriptionPlan::firstOrCreate(['slug' => 'agency'], [
            'name' => 'Agency',
            'price_monthly' => 99.00,
            'currency' => 'USD',
            'max_leads' => 2000,
            'max_users' => 25,
            'has_quotations' => true,
            'has_revenue_recovery' => true,
        ]);

        SubscriptionPlan::firstOrCreate(['slug' => 'business'], [
            'name' => 'Business',
            'price_monthly' => 199.00,
            'currency' => 'USD',
            'max_leads' => 10000,
            'max_users' => 100,
            'has_quotations' => true,
            'has_revenue_recovery' => true,
        ]);

        // 2. Demo Users
        $ownerUser = User::firstOrCreate(['email' => 'admin@leads.test'], [
            'name' => 'Jean Claude Nshimiyimana',
            'phone' => '+250 788 111 222',
            'password' => Hash::make('password'),
        ]);

        $managerUser = User::firstOrCreate(['email' => 'diane@leads.test'], [
            'name' => 'Diane Mugisha',
            'phone' => '+250 788 333 444',
            'password' => Hash::make('password'),
        ]);

        $repUser = User::firstOrCreate(['email' => 'eric@leads.test'], [
            'name' => 'Eric Ndoli',
            'phone' => '+250 788 555 666',
            'password' => Hash::make('password'),
        ]);

        // 3. Workspace Creation
        $createWorkspaceAction = app(CreateOrganizationWorkspaceAction::class);
        $organization = Organization::where('slug', 'kigali-premier-solutions')->first();

        if (! $organization) {
            $organization = $createWorkspaceAction->execute($ownerUser, 'Kigali Premier Solutions', 'RWF');
        }

        // Attach Manager and Sales Rep
        OrganizationMember::firstOrCreate(
            ['organization_id' => $organization->id, 'user_id' => $managerUser->id],
            ['role' => MemberRole::MANAGER, 'joined_at' => now()]
        );

        OrganizationMember::firstOrCreate(
            ['organization_id' => $organization->id, 'user_id' => $repUser->id],
            ['role' => MemberRole::SALES, 'joined_at' => now()]
        );

        // Set tenant context for scoped creation
        app(TenantContext::class)->setOrganization($organization);

        $stages = PipelineStage::where('organization_id', $organization->id)->orderBy('sort_order')->get()->keyBy('slug');
        $sources = LeadSource::where('organization_id', $organization->id)->get()->keyBy('slug');

        $stageInquiry = $stages['new-inquiry'] ?? $stages->first();
        $stageContacted = $stages['contacted'] ?? $stages->first();
        $stageQualified = $stages['qualified'] ?? $stages->first();
        $stageNegotiation = $stages['negotiation'] ?? $stages->first();
        $stageWon = $stages['closed-won'] ?? $stages->first();
        $stageLost = $stages['closed-lost'] ?? $stages->first();

        $sourceWhatsApp = $sources['whatsapp'] ?? $sources->first();
        $sourceInstagram = $sources['instagram'] ?? $sources->first();
        $sourceWebsite = $sources['website'] ?? $sources->first();
        $sourcePhone = $sources['phone'] ?? $sources->first();
        $sourceReferral = $sources['referral'] ?? $sources->first();

        // 4. Seed Diverse Sample Leads
        $leadsData = [
            [
                'customer_name' => 'Aline Umutoni',
                'company_name' => 'Kigali Heights Commercial Suites',
                'email' => 'aline@kigaliheights.rw',
                'phone' => '+250 788 123 456',
                'requested_service' => 'Commercial Deep Cleaning Contract',
                'estimated_value' => 3800000.00,
                'stage_id' => $stageNegotiation->id,
                'source_id' => $sourceWhatsApp->id,
                'assigned_to' => $repUser->id,
                'priority' => LeadPriority::URGENT,
                'status' => LeadStatus::ACTIVE,
                'last_contacted_at' => now()->subDays(4), // Trigger stale quote
            ],
            [
                'customer_name' => 'Patrick Habimana',
                'company_name' => 'Silverback Logistics',
                'email' => 'patrick@silverback.rw',
                'phone' => '+250 788 654 321',
                'requested_service' => 'Fleet Fleet Management Software',
                'estimated_value' => 5500000.00,
                'stage_id' => $stageQualified->id,
                'source_id' => $sourceWebsite->id,
                'assigned_to' => $managerUser->id,
                'priority' => LeadPriority::HIGH,
                'status' => LeadStatus::ACTIVE,
                'last_contacted_at' => now()->subDays(1),
            ],
            [
                'customer_name' => 'Chantal Gasana',
                'company_name' => 'Inzora Rooftop Cafe',
                'email' => 'chantal@inzoracafe.com',
                'phone' => '+250 788 987 654',
                'requested_service' => 'Brand Identity & Menu Redesign',
                'estimated_value' => 1200000.00,
                'stage_id' => $stageWon->id,
                'source_id' => $sourceInstagram->id,
                'assigned_to' => $repUser->id,
                'priority' => LeadPriority::MEDIUM,
                'status' => LeadStatus::WON,
                'converted_at' => now()->subDays(5),
                'last_contacted_at' => now()->subDays(5),
            ],
            [
                'customer_name' => 'Emmanuel Kayitare',
                'company_name' => 'Gorilla Trek Safaris',
                'email' => 'emmanuel@gorillasafaris.rw',
                'phone' => '+250 788 246 810',
                'requested_service' => 'Digital Marketing & Meta Ads Campaign',
                'estimated_value' => 2400000.00,
                'stage_id' => $stageContacted->id,
                'source_id' => $sourceReferral->id,
                'assigned_to' => $ownerUser->id,
                'priority' => LeadPriority::HIGH,
                'status' => LeadStatus::ACTIVE,
                'last_contacted_at' => now()->subDays(9), // Trigger stale lead alert!
            ],
            [
                'customer_name' => 'Divine Ingabire',
                'company_name' => 'Solace Medical Center',
                'email' => 'divine@solacemed.rw',
                'phone' => '+250 788 135 790',
                'requested_service' => 'IT Infrastructure & Networking',
                'estimated_value' => 4200000.00,
                'stage_id' => $stageWon->id,
                'source_id' => $sourcePhone->id,
                'assigned_to' => $managerUser->id,
                'priority' => LeadPriority::URGENT,
                'status' => LeadStatus::WON,
                'converted_at' => now()->subDays(12),
                'last_contacted_at' => now()->subDays(12),
            ],
            [
                'customer_name' => 'Olivier Nkurunziza',
                'company_name' => 'Kigali Woodcraft',
                'email' => 'olivier@kigaliwood.rw',
                'phone' => '+250 788 369 123',
                'requested_service' => 'E-Commerce Website & Inventory POS',
                'estimated_value' => 1800000.00,
                'stage_id' => $stageInquiry->id,
                'source_id' => $sourceWhatsApp->id,
                'assigned_to' => $repUser->id,
                'priority' => LeadPriority::MEDIUM,
                'status' => LeadStatus::ACTIVE,
                'last_contacted_at' => now()->subHours(6),
            ],
        ];

        foreach ($leadsData as $item) {
            $lead = Lead::create([
                'organization_id' => $organization->id,
                'pipeline_stage_id' => $item['stage_id'],
                'lead_source_id' => $item['source_id'],
                'assigned_to_user_id' => $item['assigned_to'],
                'customer_name' => $item['customer_name'],
                'company_name' => $item['company_name'],
                'email' => $item['email'],
                'phone' => $item['phone'],
                'requested_service' => $item['requested_service'],
                'estimated_value' => $item['estimated_value'],
                'currency' => 'RWF',
                'priority' => $item['priority'],
                'status' => $item['status'],
                'last_contacted_at' => $item['last_contacted_at'],
                'converted_at' => $item['converted_at'] ?? null,
            ]);

            // Add initial activity
            $lead->activities()->create([
                'organization_id' => $organization->id,
                'user_id' => $item['assigned_to'],
                'type' => ActivityType::NOTE,
                'subject' => 'Inquiry Logged',
                'description' => "Initial customer contact logged for {$lead->requested_service}.",
            ]);

            // For Aline: create quotation and overdue follow-up
            if ($item['customer_name'] === 'Aline Umutoni') {
                $quotationAction = app(CreateQuotationAction::class);
                $quote = $quotationAction->execute($lead, [
                    'customer_name' => $lead->customer_name,
                    'company_name' => $lead->company_name,
                    'customer_email' => $lead->email,
                    'customer_phone' => $lead->phone,
                    'discount_amount' => 100000.00,
                    'tax_rate' => 18.00,
                    'items' => [
                        ['description' => 'Commercial Office Deep Cleaning (Monthly)', 'quantity' => 1, 'unit_price' => 2500000.00],
                        ['description' => 'High-Rise Exterior Window Cleaning', 'quantity' => 1, 'unit_price' => 800000.00],
                    ],
                ]);

                // Mark sent 4 days ago to trigger recovery alert
                $quote->update([
                    'status' => QuotationStatus::SENT,
                    'sent_at' => now()->subDays(4),
                ]);

                // Overdue Follow-up
                FollowUp::create([
                    'organization_id' => $organization->id,
                    'lead_id' => $lead->id,
                    'assigned_to_user_id' => $repUser->id,
                    'title' => 'Follow up on sent commercial proposal',
                    'notes' => 'Call Aline to answer questions regarding cleaning supplies and staffing breakdown.',
                    'scheduled_at' => now()->subHours(26), // 26 hours overdue!
                    'status' => 'pending',
                ]);
            }

            // For Patrick: create upcoming follow-up for tomorrow
            if ($item['customer_name'] === 'Patrick Habimana') {
                FollowUp::create([
                    'organization_id' => $organization->id,
                    'lead_id' => $lead->id,
                    'assigned_to_user_id' => $managerUser->id,
                    'title' => 'Live Software Architecture Demo',
                    'notes' => 'Present demo on vehicle GPS telemetry and dispatch dashboard.',
                    'scheduled_at' => now()->addHours(18),
                    'status' => 'pending',
                ]);
            }
        }

        // 5. Run Revenue Recovery Evaluation to populate initial cockpit alerts
        app(EvaluateRevenueRecoveryAction::class)->execute($organization);
    }
}
