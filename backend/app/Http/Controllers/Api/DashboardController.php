<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function summary(Request $request)
    {
        return response()->json(
            $this->dashboardService->summary($request->user())
        );
    }
}
