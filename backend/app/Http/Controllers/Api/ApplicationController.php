<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Application\StoreApplicationRequest;
use App\Http\Requests\Application\UpdateApplicationRequest;
use App\Services\ApplicationService;
use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $applicationService) {}
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Application::query()
            ->with(['applicant:id,name,company_name', 'reviewer:id,name'])
            ->withCount('documents')
            ->status($request->query('status'))
            ->search($request->query('search'));

        // Pemohon hanya lihat miliknya sendiri
        if ($user->hasRole('pemohon')) {
            $query->where('applicant_id', $user->id);
        } elseif ($user->hasRole('penilai')) {
            $query->where('status', '!=', ApplicationStatus::Draft->value);
        } else {
            abort(403);
        }

        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        return ApplicationResource::collection(
            $query->latest('created_at')->paginate($perPage)->withQueryString()
        );
    }

    public function downloadPdf()
    {
        $data = Application::all(); 
        $pdf = Pdf::loadView('exports.applications', ['applications' => $data])
                  ->setPaper('a4', 'landscape');         
        return $pdf->download('laporan-permohonan.pdf');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreApplicationRequest $request)
    {
       Gate::authorize('create', Application::class);

        $application = $this->applicationService
            ->create($request->user(), $request->validated())
            ->refresh();

        return (new ApplicationResource($application))
            ->response()
            ->setStatusCode(201); 
    }

    public function show(Application $application)
    {
        Gate::authorize('view', $application);

        $application->load([
            'applicant:id,name,company_name',
            'reviewer:id,name',
            'documents',
            'reviews.reviewer:id,name',
            'statusLogs.actor:id,name',
        ]);

        return new ApplicationResource($application);
    }

    public function update(UpdateApplicationRequest $request, Application $application)
    {
    Gate::authorize('update', $application);

        $application = $this->applicationService->update($application, $request->validated());

        return new ApplicationResource($application);
    }

    public function destroy(Application $application)
    {
    Gate::authorize('delete', $application);

        $this->applicationService->delete($application);

        return response()->noContent();
    }

    public function submit(Request $request, Application $application)
    {
        Gate::authorize('submit', $application);

        $application = $this->applicationService->submit($application, $request->user());

        return new ApplicationResource($application);
    }
}

