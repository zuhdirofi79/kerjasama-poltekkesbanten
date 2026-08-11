<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document
            ? ($this->user()?->can('update', $document) ?? false)
            : false;
    }

    public function rules(): array
    {
        $extensions = config('documents.allowed_extensions', []);
        $maxKb = (int) config('documents.max_upload_mb', 20) * 1024;
        $previewMaxKb = (int) config('documents.preview_max_mb', 5) * 1024;
        $document = $this->route('document');
        $yearFolderId = $document?->year_folder_id;

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'document_folder_id' => [
                'nullable',
                Rule::exists('document_folders', 'id')->where(function ($query) use ($yearFolderId) {
                    $query->where('year_folder_id', $yearFolderId)
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'document' => [
                'nullable',
                'file',
                'max:'.$maxKb,
                'extensions:'.implode(',', $extensions),
            ],
            'preview' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:'.$previewMaxKb,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document_folder_id.exists' => 'Folder tidak valid untuk folder tahun dokumen ini.',
        ];
    }
}
