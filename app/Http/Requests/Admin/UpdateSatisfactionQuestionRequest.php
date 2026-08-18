<?php

namespace App\Http\Requests\Admin;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSatisfactionQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Document::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
