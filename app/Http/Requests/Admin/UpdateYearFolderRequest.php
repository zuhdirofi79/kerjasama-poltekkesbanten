<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateYearFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $yearFolder = $this->route('yearFolder') ?? $this->route('year_folder');

        return $yearFolder
            ? ($this->user()?->can('update', $yearFolder) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
