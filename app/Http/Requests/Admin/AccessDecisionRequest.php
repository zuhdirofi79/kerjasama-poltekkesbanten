<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AccessDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $accessRequest = $this->route('accessRequest')
            ?? $this->route('access_request');

        if (! $accessRequest) {
            return false;
        }

        $ability = match ($this->route()->getActionMethod()) {
            'approve' => 'approve',
            'reject' => 'reject',
            'revoke' => 'revoke',
            default => 'approve',
        };

        return $this->user()?->can($ability, $accessRequest) ?? false;
    }

    public function rules(): array
    {
        return [
            'review_note' => ['nullable', 'string', 'max:2000'],
            'access_expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
