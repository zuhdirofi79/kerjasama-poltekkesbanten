<?php

namespace App\Http\Requests\Admin;

use App\Models\DocumentFolder;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DocumentFolder::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'year_folder_id' => ['required', 'exists:year_folders,id'],
            'parent_id' => ['nullable', 'exists:document_folders,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
