<?php

namespace App\Http\Requests\Admin;

use App\Support\CooperationTaxonomy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document
            ? ($this->user()?->can('update', $document) ?? false)
            : false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cooperation_area' => $this->blankToNull($this->input('cooperation_area')),
            'cooperation_subtype' => $this->blankToNull($this->input('cooperation_subtype')),
            'document_date' => $this->blankToNull($this->input('document_date')),
            'cooperation_start_date' => $this->blankToNull($this->input('cooperation_start_date')),
            'cooperation_end_date' => $this->blankToNull($this->input('cooperation_end_date')),
        ]);
    }

    public function rules(): array
    {
        $extensions = config('documents.allowed_extensions', []);
        $maxKb = (int) config('documents.max_upload_mb', 20) * 1024;
        $previewMaxKb = (int) config('documents.preview_max_mb', 5) * 1024;
        $document = $this->route('document');
        $yearFolderId = $document?->year_folder_id;
        $areas = array_keys(CooperationTaxonomy::areas());

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
            'document_date' => ['nullable', 'date'],
            'cooperation_area' => ['nullable', 'string', Rule::in($areas)],
            'cooperation_subtype' => ['nullable', 'string', 'max:128'],
            'cooperation_start_date' => ['nullable', 'date'],
            'cooperation_end_date' => ['nullable', 'date', 'after_or_equal:cooperation_start_date'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $area = $this->input('cooperation_area');
            $subtype = $this->input('cooperation_subtype');
            if (filled($subtype) && ! CooperationTaxonomy::isValidSubtype($area, $subtype)) {
                $validator->errors()->add('cooperation_subtype', 'Sub dokumen tidak sesuai dengan bidang yang dipilih.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'document_folder_id.exists' => 'Folder tidak valid untuk folder tahun dokumen ini.',
            'cooperation_end_date.after_or_equal' => 'Tanggal berakhir kerjasama tidak boleh lebih awal dari tanggal mulai.',
        ];
    }

    private function blankToNull(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
