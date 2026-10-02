<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function decide(Application $application, User $reviewer, string $decision, string $note): Application
    {
        return DB::transaction(function () use ($application, $reviewer, $decision, $note) {
            // Lock baris supaya dua penilai tidak memutuskan bersamaan
            $application = Application::lockForUpdate()->findOrFail($application->id);

            $from = $application->status;
            $to   = ApplicationStatus::from($decision);

            if (! $from->canTransitionTo($to)) {
                throw new \InvalidArgumentException(
                    "Status {$from->value} tidak dapat diubah menjadi {$to->value}."
                );
            }

            $application->update([
                'status'         => $to,
                'decided_at'     => in_array($to, [ApplicationStatus::Approved, ApplicationStatus::Rejected]) ? now() : null,
                'revision_count' => $to === ApplicationStatus::RevisionRequired
                    ? $application->revision_count + 1
                    : $application->revision_count,
            ]);

            $application->reviews()->create([
                'reviewer_id'     => $reviewer->id,
                'decision'        => $decision,
                'note'            => $note,
                'revision_number' => $application->revision_count,
                'reviewed_at'     => now(),
            ]);

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'actor_id'       => $reviewer->id,
                'from_status'    => $from->value,
                'to_status'      => $to->value,
                'note'           => $note,
                'metadata'       => ['ip' => request()->ip()],
            ]);

            try {
                Cache::tags(['dashboard'])->flush();
            } catch (\Throwable $e) {
                Cache::flush();
            }

            return $application->fresh(['reviews', 'statusLogs']);
        });
    }
}