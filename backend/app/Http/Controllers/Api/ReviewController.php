<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationReview;
use App\Services\ReviewService;
use App\Http\Resources\ApplicationResource;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $service) {}

    public function store(Request $request, Application $application)
    {
        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:approved,rejected,revision_required'],
            'note'     => ['nullable', 'string'],
        ]);

        $updatedApp = $this->service->decide(
            $application,
            $request->user(),
            $validated['decision'],
            $validated['note'] ?? ''
        );

        return new ApplicationResource($updatedApp);
    }

    public function history(Request $request)
    {
        $reviews = ApplicationReview::with('application', 'reviewer')
            ->where('reviewer_id', $request->user()->id)
            ->latest('reviewed_at')
            ->paginate();

        return response()->json($reviews);
    }
}
