<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePartnershipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'institution_email' => $this->normalizeEmail($this->input('institution_email')),
            'contact_person_email' => $this->normalizeEmail($this->input('contact_person_email')),
            'institution_phone' => $this->normalizePhone($this->input('institution_phone')),
            'contact_person_whatsapp' => $this->normalizePhone($this->input('contact_person_whatsapp')),
            'institution_website' => $this->blankToNull($this->input('institution_website')),
            'legal_registration_number' => $this->blankToNull($this->input('legal_registration_number')),
            'institution_type_other' => $this->blankToNull($this->input('institution_type_other')),
            'proposal_type_other' => $this->blankToNull($this->input('proposal_type_other')),
            'target_unit_other' => $this->blankToNull($this->input('target_unit_other')),
            'proposed_scope_other' => $this->blankToNull($this->input('proposed_scope_other')),
            'proposed_duration_other' => $this->blankToNull($this->input('proposed_duration_other')),
            'expected_poltekkes_contribution' => $this->blankToNull($this->input('expected_poltekkes_contribution')),
            'target_units' => array_values(array_filter((array) $this->input('target_units', []))),
            'proposed_scopes' => array_values(array_filter((array) $this->input('proposed_scopes', []))),
            'funding_sources' => array_values(array_filter((array) $this->input('funding_sources', []))),
            'declaration_data_accurate' => $this->boolean('declaration_data_accurate'),
            'declaration_willing_verification' => $this->boolean('declaration_willing_verification'),
            'declaration_data_usage' => $this->boolean('declaration_data_usage'),
        ]);

        if ($this->input('partner_status') === 'dalam_negeri' && blank($this->input('country'))) {
            $this->merge(['country' => 'Indonesia']);
        }

        if ($this->input('applicant_type') === 'internal') {
            $canonical = config('partnership_applications.internal_institution', []);
            $this->merge([
                'institution_name' => $canonical['name'] ?? 'Poltekkes Kemenkes Banten',
                'institution_address' => $canonical['address'] ?? '',
                'institution_phone' => $canonical['phone'] ?? '',
                'institution_website' => $canonical['website'] ?? null,
            ]);
        }
    }

    public function rules(): array
    {
        $maxMb = (int) config('partnership_applications.max_upload_mb', 10);
        $maxKb = $maxMb * 1024;

        $institutionTypes = array_keys(config('partnership_applications.institution_types', []));
        $partnerStatuses = array_keys(config('partnership_applications.partner_statuses', []));
        $proposalTypes = array_keys(config('partnership_applications.proposal_types', []));
        $targetUnits = array_keys(config('partnership_applications.target_units', []));
        $scopes = array_keys(config('partnership_applications.proposed_scopes', []));
        $documentForms = array_keys(config('partnership_applications.document_forms', []));
        $durations = array_keys(config('partnership_applications.proposed_durations', []));
        $fundingNeeds = array_keys(config('partnership_applications.requires_funding_options', []));
        $fundingSources = array_keys(config('partnership_applications.funding_sources', []));

        $officialExt = implode(',', config('partnership_applications.allowed_extensions.official_request_letter', []));
        $profileExt = implode(',', config('partnership_applications.allowed_extensions.institution_profile', []));
        $supportExt = implode(',', config('partnership_applications.allowed_extensions.supporting_document', []));

        return [
            'company_fax' => ['nullable', 'max:0'],

            'applicant_type' => ['required', Rule::in(['internal', 'external'])],

            'institution_email' => ['required', 'email:rfc', 'max:255'],
            'institution_name' => ['required', 'string', 'max:255'],
            'institution_address' => ['required', 'string', 'max:2000'],
            'institution_phone' => ['required', 'string', 'max:64'],
            'institution_website' => ['nullable', 'url', 'max:255'],
            'legal_registration_number' => ['nullable', 'string', 'max:255'],
            'institution_profile' => ['required', 'string', 'max:5000'],
            'institution_type' => ['required', Rule::in($institutionTypes)],
            'institution_type_other' => ['nullable', 'required_if:institution_type,lainnya', 'string', 'max:255'],
            'partner_status' => ['required', Rule::in($partnerStatuses)],
            'country' => ['required', 'string', 'max:120'],

            'authorized_officer_name' => ['required', 'string', 'max:255'],
            'authorized_officer_position' => ['required', 'string', 'max:255'],
            'contact_person_name' => ['required', 'string', 'max:255'],
            'contact_person_position' => ['required', 'string', 'max:255'],
            'contact_person_whatsapp' => ['required', 'string', 'max:64'],
            'contact_person_email' => ['required', 'email:rfc', 'max:255'],

            'proposal_type' => ['required', Rule::in($proposalTypes)],
            'proposal_type_other' => ['nullable', 'required_if:proposal_type,lainnya', 'string', 'max:255'],
            'proposal_title' => ['required', 'string', 'max:500'],
            'proposal_purpose' => ['required', 'string', 'max:5000'],
            'expected_benefits' => ['required', 'string', 'max:5000'],
            'target_units' => ['required', 'array', 'min:1'],
            'target_units.*' => [Rule::in($targetUnits)],
            'target_unit_other' => ['nullable', 'string', 'max:255'],

            'proposed_scopes' => ['required', 'array', 'min:1'],
            'proposed_scopes.*' => [Rule::in($scopes)],
            'proposed_scope_other' => ['nullable', 'string', 'max:255'],
            'document_form' => ['required', Rule::in($documentForms)],
            'activity_description' => ['required', 'string', 'max:10000'],
            'target_description' => ['required', 'string', 'max:5000'],

            'planned_start_date' => ['required', 'date'],
            'planned_end_date' => ['required', 'date', 'after_or_equal:planned_start_date'],
            'proposed_duration' => ['required', Rule::in($durations)],
            'proposed_duration_other' => ['nullable', 'required_if:proposed_duration,lainnya', 'string', 'max:255'],
            'activity_location' => ['required', 'string', 'max:1000'],
            'expected_poltekkes_contribution' => ['nullable', 'string', 'max:5000'],
            'requires_funding' => ['required', Rule::in($fundingNeeds)],
            'funding_sources' => ['required', 'array', 'min:1'],
            'funding_sources.*' => [Rule::in($fundingSources)],

            'official_request_letter' => ['required', 'file', "max:{$maxKb}", "mimes:{$officialExt}"],
            'filled_template' => ['nullable', 'file', "max:{$maxKb}", 'mimes:pdf,doc,docx'],
            'institution_profile_file' => ['nullable', 'file', "max:{$maxKb}", "mimes:{$profileExt}"],
            'supporting_document' => ['nullable', 'file', "max:{$maxKb}", "mimes:{$supportExt}"],

            'declaration_data_accurate' => ['accepted'],
            'declaration_willing_verification' => ['accepted'],
            'declaration_data_usage' => ['accepted'],
            'declarant_name' => ['required', 'string', 'max:255'],
            'declarant_position' => ['required', 'string', 'max:255'],
            'submission_date' => ['required', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (filled($this->input('company_fax'))) {
                $validator->errors()->add('company_fax', 'Pengajuan tidak valid.');
            }

            $targetUnits = (array) $this->input('target_units', []);
            if (in_array('unit_lainnya', $targetUnits, true) && blank($this->input('target_unit_other'))) {
                $validator->errors()->add('target_unit_other', 'Nama unit lainnya wajib diisi.');
            }

            $scopes = (array) $this->input('proposed_scopes', []);
            if (in_array('lainnya', $scopes, true) && blank($this->input('proposed_scope_other'))) {
                $validator->errors()->add('proposed_scope_other', 'Ruang lingkup lainnya wajib diisi.');
            }

            $sources = (array) $this->input('funding_sources', []);
            if (
                in_array('tidak_memerlukan_biaya', $sources, true)
                && count(array_diff($sources, ['tidak_memerlukan_biaya'])) > 0
            ) {
                $validator->errors()->add(
                    'funding_sources',
                    'Opsi "Tidak memerlukan biaya" tidak dapat digabung dengan sumber pembiayaan lain.'
                );
            }
        });
    }

    public function messages(): array
    {
        $maxMb = (int) config('partnership_applications.max_upload_mb', 10);

        return [
            'applicant_type.required' => 'Jenis pengajuan Internal atau Eksternal wajib dipilih.',
            'institution_email.required' => 'Alamat email resmi instansi wajib diisi.',
            'institution_email.email' => 'Format alamat email tidak valid.',
            'institution_name.required' => 'Nama resmi instansi atau lembaga wajib diisi.',
            'institution_address.required' => 'Alamat lengkap instansi wajib diisi.',
            'institution_phone.required' => 'Nomor telepon instansi wajib diisi.',
            'institution_profile.required' => 'Profil singkat calon mitra wajib diisi.',
            'institution_type.required' => 'Jenis instansi atau lembaga wajib dipilih.',
            'institution_type_other.required_if' => 'Jenis instansi lainnya wajib diisi.',
            'partner_status.required' => 'Status calon mitra wajib dipilih.',
            'country.required' => 'Negara asal calon mitra wajib diisi.',
            'authorized_officer_name.required' => 'Nama pimpinan atau pejabat yang berwenang wajib diisi.',
            'authorized_officer_position.required' => 'Jabatan pimpinan wajib diisi.',
            'contact_person_name.required' => 'Nama narahubung kerja sama wajib diisi.',
            'contact_person_position.required' => 'Jabatan narahubung wajib diisi.',
            'contact_person_whatsapp.required' => 'Nomor WhatsApp narahubung wajib diisi.',
            'contact_person_email.required' => 'Alamat email narahubung wajib diisi.',
            'contact_person_email.email' => 'Format email narahubung tidak valid.',
            'proposal_type.required' => 'Jenis pengajuan kerja sama wajib dipilih.',
            'proposal_type_other.required_if' => 'Jenis pengajuan lainnya wajib diisi.',
            'proposal_title.required' => 'Judul atau topik kerja sama wajib diisi.',
            'proposal_purpose.required' => 'Tujuan kerja sama wajib diisi.',
            'expected_benefits.required' => 'Manfaat yang diharapkan wajib diisi.',
            'target_units.required' => 'Pilih setidaknya satu unit atau program studi tujuan.',
            'target_units.min' => 'Pilih setidaknya satu unit atau program studi tujuan.',
            'proposed_scopes.required' => 'Pilih setidaknya satu ruang lingkup kerja sama.',
            'proposed_scopes.min' => 'Pilih setidaknya satu ruang lingkup kerja sama.',
            'document_form.required' => 'Bentuk dokumen kerja sama wajib dipilih.',
            'activity_description.required' => 'Uraian kegiatan wajib diisi.',
            'target_description.required' => 'Sasaran wajib diisi.',
            'planned_start_date.required' => 'Rencana tanggal mulai wajib diisi.',
            'planned_end_date.required' => 'Rencana tanggal selesai wajib diisi.',
            'planned_end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'proposed_duration.required' => 'Usulan jangka waktu dokumen wajib dipilih.',
            'proposed_duration_other.required_if' => 'Usulan jangka waktu lainnya wajib diisi.',
            'activity_location.required' => 'Lokasi pelaksanaan kegiatan wajib diisi.',
            'requires_funding.required' => 'Informasi kebutuhan pembiayaan wajib dipilih.',
            'funding_sources.required' => 'Pilih setidaknya satu rencana sumber pembiayaan.',
            'funding_sources.min' => 'Pilih setidaknya satu rencana sumber pembiayaan.',
            'official_request_letter.required' => 'Surat resmi pengajuan kerja sama wajib diunggah.',
            'official_request_letter.max' => "Ukuran surat pengajuan maksimal {$maxMb} MB.",
            'official_request_letter.mimes' => 'Format file surat pengajuan tidak didukung.',
            'filled_template.max' => "Ukuran form template maksimal {$maxMb} MB.",
            'filled_template.mimes' => 'Format form template harus PDF, DOC, atau DOCX.',
            'institution_profile_file.max' => "Ukuran profil instansi maksimal {$maxMb} MB.",
            'institution_profile_file.mimes' => 'Format file profil instansi tidak didukung.',
            'supporting_document.max' => "Ukuran dokumen pendukung maksimal {$maxMb} MB.",
            'supporting_document.mimes' => 'Format file dokumen pendukung tidak didukung.',
            'declaration_data_accurate.accepted' => 'Anda harus menyetujui seluruh pernyataan sebelum mengirim pengajuan.',
            'declaration_willing_verification.accepted' => 'Anda harus menyetujui seluruh pernyataan sebelum mengirim pengajuan.',
            'declaration_data_usage.accepted' => 'Anda harus menyetujui seluruh pernyataan sebelum mengirim pengajuan.',
            'declarant_name.required' => 'Nama pengisi formulir wajib diisi.',
            'declarant_position.required' => 'Jabatan pengisi formulir wajib diisi.',
            'submission_date.required' => 'Tanggal pengajuan wajib diisi.',
            'company_fax.max' => 'Pengajuan tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'institution_email' => 'alamat email resmi instansi',
            'institution_name' => 'nama resmi instansi',
            'institution_website' => 'situs web instansi',
            'official_request_letter' => 'surat resmi pengajuan',
            'institution_profile_file' => 'profil instansi',
            'supporting_document' => 'dokumen pendukung',
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();
        unset(
            $validated['company_fax'],
            $validated['official_request_letter'],
            $validated['filled_template'],
            $validated['institution_profile_file'],
            $validated['supporting_document'],
            $validated['declaration_data_accurate'],
            $validated['declaration_willing_verification'],
            $validated['declaration_data_usage'],
        );

        return $validated;
    }

    public function uploadedFiles(): array
    {
        return array_filter([
            'official_request_letter' => $this->file('official_request_letter'),
            'filled_template' => $this->file('filled_template'),
            'institution_profile' => $this->file('institution_profile_file'),
            'supporting_document' => $this->file('supporting_document'),
        ]);
    }

    private function normalizeEmail(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return strtolower(trim($value));
    }

    private function normalizePhone(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = preg_replace('/[^\d+\-\(\)\s]/', '', trim($value)) ?? '';

        return $normalized === '' ? null : $normalized;
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
