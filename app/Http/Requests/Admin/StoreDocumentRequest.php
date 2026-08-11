<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Document::class) ?? false;
    }

    public function rules(): array
    {
        $extensions = config('documents.allowed_extensions', []);
        $maxKb = (int) config('documents.max_upload_mb', 20) * 1024;
        $previewMaxKb = (int) config('documents.preview_max_mb', 5) * 1024;

        return [
            'year_folder_id' => ['required', 'exists:year_folders,id'],
            'document_folder_id' => ['nullable', 'exists:document_folders,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'document' => [
                'required',
                'file',
                'max:'.$maxKb,
                'extensions:'.implode(',', $extensions),
            ],
            'preview' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:'.$previewMaxKb,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'preview.required' => 'Preview gambar wajib diunggah sebelum dokumen dipublikasikan.',
            'document.extensions' => 'Ekstensi file dokumen tidak diizinkan.',
        ];
    }
}
