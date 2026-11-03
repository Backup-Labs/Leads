<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->foreignId('lead_source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('customer_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('requested_service')->nullable();
            $table->decimal('estimated_value', 14, 2)->default(0.00);
            $table->string('currency', 3)->default('RWF');
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('active'); // active, won, lost
            $table->text('description')->nullable();
            $table->text('lost_reason')->nullable();

            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'pipeline_stage_id', 'status']);
            $table->index(['organization_id', 'assigned_to_user_id']);
            $table->index(['organization_id', 'last_contacted_at']);
            $table->index(['organization_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

