<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('default_currency')->default('RWF');
            $table->decimal('default_tax_rate', 5, 2)->default(18.00); // Standard VAT rate in Rwanda
            $table->unsignedInteger('stale_lead_threshold_days')->default(7);
            $table->unsignedInteger('quote_followup_threshold_days')->default(3);
            $table->unsignedInteger('high_value_threshold')->default(1000000); // 1,000,000 RWF or 1,000 USD
            $table->jsonb('notification_preferences')->nullable();
            $table->text('quotation_default_terms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};

