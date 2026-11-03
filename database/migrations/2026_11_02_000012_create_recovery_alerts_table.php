<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('alert_type'); // overdue_followup, stalled_lead, unanswered_quote, high_value_risk
            $table->string('severity')->default('warning'); // critical, warning, notice
            $table->text('reason');
            $table->text('suggested_action');
            $table->decimal('potential_revenue_at_risk', 14, 2)->default(0.00);

            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_resolved', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_alerts');
    }
};

