<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MoveDocumentFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $folder = $this->route('folder');

        return $folder
            ? ($this->user()?->can('move', $folder) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'exists:document_folders,id'],
        ];
    }
}
