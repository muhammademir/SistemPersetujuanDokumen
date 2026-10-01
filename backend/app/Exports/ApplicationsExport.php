<?php

namespace App\Exports;

use App\Models\Application;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
class ApplicationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(private array $filters) {}

    public function query()
    {
        return Application::query()
            ->with('applicant:id,name')
            ->status($this->filters['status'] ?? null);
    }

    public function headings(): array
    {
        return ['Kode', 'Judul', 'Pemohon', 'Jenis', 'Status', 'Tanggal Pengajuan'];
    }

    public function map($app): array
    {
        return [
            $app->code, $app->title, $app->applicant->name,
            $app->document_type, $app->status->label(),
            $app->submitted_at?->format('d/m/Y'),
        ];
    }
}
