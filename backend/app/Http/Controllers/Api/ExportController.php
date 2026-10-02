<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\ApplicationsExport;

class ExportController extends Controller
{
    public function applications(Request $request)
    {
        $format = $request->query('format', 'excel');
        $export = new ApplicationsExport($request->only('status'));

        if ($format === 'pdf') {
            return $export->download('applications.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return $export->download('applications.xlsx');
    }
}
