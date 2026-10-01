<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function summary(User $user): array
    {
        $key = "dashboard:summary:{$user->id}";

        return Cache::tags(['dashboard'])->remember($key, now()->addMinutes(5), function () use ($user) {
            $isPemohon = $user->hasRole('pemohon');

            return [
                'by_status' => Application::query()
                    ->selectRaw('status, COUNT(*) as total')
                    ->when($isPemohon, fn ($q) => $q->where('applicant_id', $user->id))
                    ->groupBy('status')
                    ->pluck('total', 'status'),

                'monthly' => Application::query()
                    ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as period, COUNT(*) as total")
                    ->when($isPemohon, fn ($q) => $q->where('applicant_id', $user->id))
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get(),

                'avg_processing_days' => Application::query()
                    ->whereNotNull('decided_at')
                    ->when($isPemohon, fn ($q) => $q->where('applicant_id', $user->id))
                    ->selectRaw('AVG(EXTRACT(EPOCH FROM (decided_at - submitted_at)) / 86400) as avg_days')
                    ->value('avg_days'),
            ];
        });
    }
}