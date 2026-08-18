<?php

namespace App\Http\Requests\Admin;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCooperationMonitoringRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Document::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'monitoring_date' => ['required', 'date'],
            'implementation_status' => ['required', 'string', Rule::in([
                'belum_mulai',
                'berjalan',
                'selesai',
                'tertunda',
            ])],
            'progress_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
