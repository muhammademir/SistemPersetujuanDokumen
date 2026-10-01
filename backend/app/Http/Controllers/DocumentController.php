<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
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

        return $doc;
    });

    return DocumentResource::collection($saved);
}
}
