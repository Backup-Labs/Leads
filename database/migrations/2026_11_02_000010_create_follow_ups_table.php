<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('pending'); // pending, completed, cancelled, overdue
            $table->text('completion_notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();

            $table->index(['organization_id', 'status', 'scheduled_at']);
            $table->index(['organization_id', 'assigned_to_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};

