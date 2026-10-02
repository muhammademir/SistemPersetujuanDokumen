<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Jobs\ProcessDocumentUpload;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreDocumentRequest $request, Application $application)
    {
        $this->authorize('update', $application);

        $saved = collect($request->file('files'))->map(function ($file) use ($application, $request) {
            $path = $file->store("applications/{$application->id}", 'local');

            $doc = $application->documents()->create([
                'uploaded_by'     => $request->user()->id,
                'original_name'   => $file->getClientOriginalName(),
                'path'            => $path,
                'mime_type'       => $file->getMimeType(),
                'size_bytes'      => $file->getSize(),
                'revision_number' => $application->revision_count,
            ]);

            ProcessDocumentUpload::dispatch($doc);   // checksum + thumbnail di background

            return [
                'id'              => $doc->id,
                'original_name'   => $doc->original_name,
                'mime_type'       => $doc->mime_type,
                'size_bytes'      => $doc->size_bytes,
                'revision_number' => $doc->revision_number,
                'uploaded_at'     => $doc->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $saved
        ]);
    }
}
