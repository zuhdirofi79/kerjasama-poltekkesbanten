<?php

namespace App\Http\Requests\Admin;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class StoreSatisfactionSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Document::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'document_id' => ['nullable', 'exists:documents,id'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }
}
