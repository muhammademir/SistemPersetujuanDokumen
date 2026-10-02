<?php

namespace App\Http\Requests\Application;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Application;
use Illuminate\Validation\Rule;
class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'document_type' => ['required', Rule::in(Application::DOCUMENT_TYPES)],
        ];
    }
    public function messages(): array
    {
        return [
            'title.required'         => 'Judul permohonan wajib diisi.',
            'title.max'              => 'Judul permohonan maksimal 255 karakter.',
            'description.max'        => 'Deskripsi maksimal 5000 karakter.',
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
            'document_type.in'       => 'Jenis dokumen tidak valid. Pilihan: '
                                        .implode(', ', Application::DOCUMENT_TYPES).'.',
        ];
    }
}
