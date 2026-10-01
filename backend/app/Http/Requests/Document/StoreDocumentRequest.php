<?php

namespace App\Http\Requests\Document;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files'   => ['required', 'array', 'max:5'],
        'files.*' => [
            'required',
            'file',
            'mimes:pdf,jpg,jpeg,png,doc,docx',
            'max:5120',                          // 5 MB per file
        ],
        ];
    }

    public function messages(): array
{
    return [
        'files.required' => 'Setidaknya satu file wajib diunggah.',
        'files.max'      => 'Maksimal 5 file per pengajuan.',

        'files.*.required' => 'File tidak boleh kosong.',
        'files.*.file'     => 'File harus berupa file yang valid.',
        'files.*.mimes'    => 'File harus berformat PDF, JPG, JPEG, PNG, DOC, atau DOCX.',
        'files.*.max'      => 'Ukuran file tidak boleh melebihi 5 MB.',
    ];
}

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
