<?php

declare(strict_types=1);

namespace App\Actions\FollowUps;

use App\Enums\ActivityType;
use App\Models\FollowUp;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class CompleteFollowUpAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function execute(FollowUp $followUp, ?string $completionNotes = null): FollowUp
    {
        return DB::transaction(function () use ($followUp, $completionNotes) {
            $followUp->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completion_notes' => $completionNotes,
            ]);

            $lead = $followUp->lead;
            if ($lead) {
                $lead->update(['last_contacted_at' => now()]);

                $lead->activities()->create([
                    'organization_id' => $followUp->organization_id,
                    'user_id' => auth()->id(),
                    'type' => ActivityType::NOTE,
                    'subject' => "Follow-up Completed: {$followUp->title}",
                    'description' => $completionNotes ?? 'Follow-up executed successfully.',
                    'metadata' => [
                        'follow_up_id' => $followUp->id,
                        'completed_at' => $followUp->completed_at->toISOString(),
                    ],
                ]);
            }

            $this->auditLogger->log('followup.completed', $followUp);

            return $followUp;
        });
    }
}
