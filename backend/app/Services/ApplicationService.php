<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Exceptions\TransitionNotAllowedException;
use App\Models\Application;
use App\Models\ApplicationStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    /**
     * Buat permohonan baru berstatus draft.
     */
    public function create(User $applicant, array $data): Application
    {
        return DB::transaction(function () use ($applicant, $data) {
            $application = Application::create([
                'code'          => $this->generateCode(),
                'applicant_id'  => $applicant->id,
                'title'         => $data['title'],
                'description'   => $data['description'] ?? null,
                'document_type' => $data['document_type'],
                'status'        => ApplicationStatus::Draft,
            ]);

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'actor_id'       => $applicant->id,
                'from_status'    => null,
                'to_status'      => ApplicationStatus::Draft->value,
                'note'           => 'Permohonan dibuat.',
            ]);

            return $application;
        });
        $this->flushDashboardCache();

        return $application;
    }

    /**
     * Update permohonan hanya boleh selama masih draft atau perlu revisi.
     */
    public function update(Application $application, array $data): Application
    {
        if (! in_array($application->status, [ApplicationStatus::Draft, ApplicationStatus::RevisionRequired], true)) {
            throw new TransitionNotAllowedException(
                'Permohonan hanya dapat diubah saat berstatus draft atau perlu revisi.'
            );
        }

        $application->update([
            'title'         => $data['title'] ?? $application->title,
            'description'   => array_key_exists('description', $data) ? $data['description'] : $application->description,
            'document_type' => $data['document_type'] ?? $application->document_type,
        ]);

        return $application->fresh();
    }

    /**
     * Ajukan permohonan: draft/revision_required -> submitted.
     */
    public function submit(Application $application, User $actor): Application
    {
        return DB::transaction(function () use ($application, $actor) {
            $application = Application::lockForUpdate()->findOrFail($application->id);

            $from = $application->status;
            $to   = ApplicationStatus::Submitted;

            if (! $from->canTransitionTo($to)) {
                throw new TransitionNotAllowedException(
                    "Status {$from->value} tidak dapat diajukan (submit)."
                );
            }

            $application->update([
                'status'       => $to,
                'submitted_at' => now(),
            ]);

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'actor_id'       => $actor->id,
                'from_status'    => $from->value,
                'to_status'      => $to->value,
                'note'           => $from === ApplicationStatus::RevisionRequired
                    ? 'Permohonan diajukan ulang setelah revisi.'
                    : 'Permohonan diajukan oleh pemohon.',
            ]);


            return $application;
        });
        $this->flushDashboardCache();

        return $result->fresh();
    }

    public function delete(Application $application): void
    {
        if ($application->status !== ApplicationStatus::Draft) {
            throw new TransitionNotAllowedException(
                'Hanya permohonan berstatus draft yang dapat dihapus.'
            );
        }

        $application->delete();

        $this->flushDashboardCache();
    }

    /**
     * Generate kode unik: PMH-2026-000001
     */
    private function generateCode(): string
    {
        $year = now()->format('Y');

         do {
            $code = sprintf('PMH-%s-%06d', $year, random_int(1, 999999));
        } while (Application::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    private function flushDashboardCache(): void
    {
        Cache::tags(['dashboard'])->flush();
    }
}