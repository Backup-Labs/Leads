<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Enums\MemberRole;
use App\Models\LeadSource;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\OrganizationSetting;
use App\Models\PipelineStage;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrganizationWorkspaceAction
{
    public function execute(User $owner, string $organizationName, ?string $currency = 'RWF'): Organization
    {
        return DB::transaction(function () use ($owner, $organizationName, $currency) {
            $baseSlug = Str::slug($organizationName);
            $slug = $baseSlug;
            $counter = 1;

            while (Organization::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $organization = Organization::create([
                'name' => $organizationName,
                'slug' => $slug,
                'billing_email' => $owner->email,
                'currency' => $currency ?? 'RWF',
                'timezone' => 'Africa/Kigali',
            ]);

            // Add owner membership
            OrganizationMember::create([
                'organization_id' => $organization->id,
                'user_id' => $owner->id,
                'role' => MemberRole::OWNER,
                'joined_at' => now(),
            ]);

            // Add default settings
            OrganizationSetting::create([
                'organization_id' => $organization->id,
                'default_currency' => $currency ?? 'RWF',
                'default_tax_rate' => 18.00,
                'stale_lead_threshold_days' => 7,
                'quote_followup_threshold_days' => 3,
                'high_value_threshold' => 1000000,
                'quotation_default_terms' => "1. Quotation valid for 14 days from date of issue.\n2. Payment terms: 50% advance on order confirmation, 50% upon delivery/completion.\n3. Prices include applicable statutory taxes unless stated otherwise.",
            ]);

            // Default Pipeline Stages
            $defaultStages = [
                ['name' => 'New Inquiry', 'slug' => 'new-inquiry', 'color' => '#280047', 'sort_order' => 1, 'is_won' => false, 'is_lost' => false],
                ['name' => 'Contacted', 'slug' => 'contacted', 'color' => '#3B82F6', 'sort_order' => 2, 'is_won' => false, 'is_lost' => false],
                ['name' => 'Qualified', 'slug' => 'qualified', 'color' => '#8B5CF6', 'sort_order' => 3, 'is_won' => false, 'is_lost' => false],
                ['name' => 'Negotiation / Quote', 'slug' => 'negotiation', 'color' => '#F59E0B', 'sort_order' => 4, 'is_won' => false, 'is_lost' => false],
                ['name' => 'Closed Won', 'slug' => 'closed-won', 'color' => '#10B981', 'sort_order' => 5, 'is_won' => true, 'is_lost' => false],
                ['name' => 'Closed Lost', 'slug' => 'closed-lost', 'color' => '#EF4444', 'sort_order' => 6, 'is_won' => false, 'is_lost' => true],
            ];

            foreach ($defaultStages as $stage) {
                PipelineStage::create(array_merge($stage, ['organization_id' => $organization->id]));
            }

            // Default Lead Sources
            $defaultSources = [
                ['name' => 'WhatsApp', 'slug' => 'whatsapp', 'color' => '#25D366'],
                ['name' => 'Instagram', 'slug' => 'instagram', 'color' => '#E1306C'],
                ['name' => 'Website Form', 'slug' => 'website', 'color' => '#2563EB'],
                ['name' => 'Phone Call', 'slug' => 'phone', 'color' => '#10B981'],
                ['name' => 'Referral', 'slug' => 'referral', 'color' => '#8B5CF6'],
                ['name' => 'Walk-in Visit', 'slug' => 'walk-in', 'color' => '#F74D01'],
                ['name' => 'Email Inquiry', 'slug' => 'email', 'color' => '#6B7280'],
            ];

            foreach ($defaultSources as $source) {
                LeadSource::create(array_merge($source, ['organization_id' => $organization->id]));
            }

            // Default Starter Subscription (14 day trial)
            $starterPlan = SubscriptionPlan::firstOrCreate(
                ['slug' => 'starter'],
                [
                    'name' => 'Starter',
                    'price_monthly' => 19.00,
                    'currency' => 'USD',
                    'max_leads' => 150,
                    'max_users' => 3,
                    'has_quotations' => true,
                    'has_revenue_recovery' => true,
                ]
            );

            Subscription::create([
                'organization_id' => $organization->id,
                'subscription_plan_id' => $starterPlan->id,
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays(14),
            ]);

            return $organization;
        });
    }
}

