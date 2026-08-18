<?php

namespace App\Http\Requests\Admin;

use App\Models\Document;
use App\Support\CooperationTaxonomy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Document::class) ?? false;
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
        $yearFolderId = $this->integer('year_folder_id');
        $areas = array_keys(CooperationTaxonomy::areas());

        return [
            'year_folder_id' => ['required', 'exists:year_folders,id'],
            'document_folder_id' => [
                'nullable',
                Rule::exists('document_folders', 'id')->where(function ($query) use ($yearFolderId) {
                    $query->where('year_folder_id', $yearFolderId)
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'document_date' => ['nullable', 'date'],
            'cooperation_area' => ['nullable', 'string', Rule::in($areas)],
            'cooperation_subtype' => ['nullable', 'string', 'max:128'],
            'cooperation_start_date' => ['nullable', 'date'],
            'cooperation_end_date' => ['nullable', 'date', 'after_or_equal:cooperation_start_date'],
            'is_active' => ['sometimes', 'boolean'],
            'document' => [
                'required',
                'file',
                'max:'.$maxKb,
                'extensions:'.implode(',', $extensions),
            ],
            'preview' => [
                'required',
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
            'preview.required' => 'Preview gambar wajib diunggah sebelum dokumen dipublikasikan.',
            'document.extensions' => 'Ekstensi file dokumen tidak diizinkan.',
            'document_folder_id.exists' => 'Folder tidak valid untuk folder tahun yang dipilih.',
            'cooperation_end_date.after_or_equal' => 'Tanggal berakhir kerjasama tidak boleh lebih awal dari tanggal mulai.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return collect($this->validated())->only([
            'title',
            'description',
            'is_active',
            'document_date',
            'cooperation_area',
            'cooperation_subtype',
            'cooperation_start_date',
            'cooperation_end_date',
        ])->all();
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
