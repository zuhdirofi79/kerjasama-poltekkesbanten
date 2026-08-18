<?php

namespace App\Http\Requests\Admin;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class StoreSatisfactionQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Document::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:500'],
        ];
    }
}
