<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $document = $this->route('document');
        $yearFolderId = $document?->year_folder_id;

        return [
            'document_folder_id' => [
                'nullable',
                Rule::exists('document_folders', 'id')->where(function ($query) use ($yearFolderId) {
                    $query->where('year_folder_id', $yearFolderId)
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document_folder_id.exists' => 'Folder tujuan tidak valid untuk tahun dokumen ini.',
        ];
    }
}
