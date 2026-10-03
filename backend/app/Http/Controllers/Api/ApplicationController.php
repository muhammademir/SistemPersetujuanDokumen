<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Application\StoreApplicationRequest;
use App\Http\Requests\Application\UpdateApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService
    ) {}

    /**
     * Menampilkan daftar permohonan sesuai role pengguna.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Application::query()
            ->with(['applicant:id,name,email', 'reviewer:id,name'])
            ->withCount('documents')
            ->status($request->query('status'))
            ->search($request->query('search'));

        if ($request->filled('document_type') && $request->query('document_type') !== 'all') {
            $query->where('document_type', $request->query('document_type'));
        }

        // Pemohon hanya dapat melihat permohonan miliknya
        if ($user->hasRole('pemohon')) {
            $query->where('applicant_id', $user->id);
        } elseif ($user->hasRole('penilai')) {
            // Penilai melihat semua permohonan yang bukan draft
            $query->where('status', '!=', ApplicationStatus::Draft->value);
        } else {
            abort(403, 'Akses tidak diizinkan untuk role ini.');
        }

        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        return ApplicationResource::collection(
            $query->latest('created_at')->paginate($perPage)->withQueryString()
        );
    }

    /**
     * Membuat permohonan baru berstatus draft.
     */
    public function store(StoreApplicationRequest $request): JsonResponse
    {
        Gate::authorize('create', Application::class);

        $application = $this->applicationService
            ->create($request->user(), $request->validated())
            ->refresh();

        return (new ApplicationResource($application))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Menampilkan detail permohonan lengkap dengan dokumen, review, dan log status.
     */
    public function show(Application $application): ApplicationResource
    {
        Gate::authorize('view', $application);

        $application->load([
            'applicant:id,name,email',
            'reviewer:id,name',
            'documents',
            'reviews.reviewer:id,name',
            'statusLogs.actor:id,name',
        ]);

        return new ApplicationResource($application);
    }

    /**
     * Memperbarui informasi permohonan (hanya jika draft atau revision_required).
     */
    public function update(UpdateApplicationRequest $request, Application $application): ApplicationResource
    {
        Gate::authorize('update', $application);

        $application = $this->applicationService->update($application, $request->validated());

        return new ApplicationResource($application);
    }

    /**
     * Menghapus draft permohonan.
     */
    public function destroy(Application $application): Response
    {
        Gate::authorize('delete', $application);

        $this->applicationService->delete($application);

        return response()->noContent();
    }

    /**
     * Mengajukan permohonan (draft / revision_required -> submitted).
     */
    public function submit(Request $request, Application $application): ApplicationResource
    {
        Gate::authorize('submit', $application);

        $application = $this->applicationService->submit($application, $request->user());

        return new ApplicationResource($application);
    }
}
