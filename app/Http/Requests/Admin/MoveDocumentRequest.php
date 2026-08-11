<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MoveDocumentRequest extends FormRequest
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
        return [
            'document_folder_id' => ['nullable', 'exists:document_folders,id'],
        ];
    }
}
