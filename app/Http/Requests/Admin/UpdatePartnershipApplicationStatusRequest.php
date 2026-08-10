<?php

namespace App\Http\Requests\Admin;

use App\Enums\PartnershipApplicationStatus;
use App\Models\PartnershipApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartnershipApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var PartnershipApplication $application */
        $application = $this->route('application');

        return $this->user()?->can('updateStatus', $application) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(PartnershipApplicationStatus::class)],
            'review_note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status pengajuan wajib dipilih.',
        ];
    }
}
