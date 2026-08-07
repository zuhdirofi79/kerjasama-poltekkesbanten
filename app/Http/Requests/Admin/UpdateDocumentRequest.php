<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'institution_folder_id' => ['nullable', 'exists:institution_folders,id'],
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
}
