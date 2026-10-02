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

        $fetchData = function () use ($user) {
            $isPemohon = $user->hasRole('pemohon');

            $byStatus = Application::query()
                ->selectRaw('status, COUNT(*) as total')
                ->when($isPemohon, fn ($q) => $q->where('applicant_id', $user->id))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->map(fn ($val) => (int) $val)
                ->all();

            $monthly = Application::query()
                ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as period, COUNT(*) as total")
                ->when($isPemohon, fn ($q) => $q->where('applicant_id', $user->id))
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->toArray();

            $avgDays = Application::query()
                ->whereNotNull('decided_at')
                ->when($isPemohon, fn ($q) => $q->where('applicant_id', $user->id))
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (decided_at - submitted_at)) / 86400) as avg_days')
                ->value('avg_days');

            return [
                'by_status'           => $byStatus,
                'monthly'             => $monthly,
                'avg_processing_days' => $avgDays !== null ? round((float) $avgDays, 2) : 0,
            ];
        };

        try {
            return Cache::tags(['dashboard'])->remember($key, now()->addMinutes(5), $fetchData);
        } catch (\Throwable $e) {
            return Cache::remember($key, now()->addMinutes(5), $fetchData);
        }
    }
}