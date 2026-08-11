<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $folder = $this->route('folder');

        return $folder
            ? ($this->user()?->can('update', $folder) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
