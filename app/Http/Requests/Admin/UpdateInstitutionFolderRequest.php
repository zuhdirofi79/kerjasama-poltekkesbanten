<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstitutionFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $institution = $this->route('institutionFolder')
            ?? $this->route('institution_folder')
            ?? $this->route('institution');

        return $institution
            ? ($this->user()?->can('update', $institution) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
