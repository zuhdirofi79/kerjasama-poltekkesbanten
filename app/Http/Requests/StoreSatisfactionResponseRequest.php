<?php

namespace App\Http\Requests;

use App\Services\SatisfactionSurveyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSatisfactionResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $questionIds = $this->surveyQuestionIds();

        return [
            'respondent_name' => ['nullable', 'string', 'max:255'],
            'respondent_institution' => ['nullable', 'string', 'max:255'],
            'suggestion' => ['nullable', 'string', 'max:5000'],
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
        ] + $this->requiredQuestionRules($questionIds);
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Mohon isi seluruh indikator kepuasan.',
            'answers.*.in' => 'Skala kepuasan harus bernilai 1 sampai 5.',
        ];
    }

    /**
     * @return array<int, int>
     */
    public function scores(): array
    {
        $raw = $this->validated('answers') ?? [];
        $scores = [];
        foreach ($raw as $questionId => $score) {
            $scores[(int) $questionId] = (int) $score;
        }

        return $scores;
    }

    /**
     * @return list<int>
     */
    private function surveyQuestionIds(): array
    {
        return app(SatisfactionSurveyService::class)
            ->activeQuestions()
            ->pluck('id')
            ->all();
    }

    /**
     * @param  list<int>  $questionIds
     * @return array<string, mixed>
     */
    private function requiredQuestionRules(array $questionIds): array
    {
        $rules = [];
        foreach ($questionIds as $id) {
            $rules['answers.'.$id] = ['required', 'integer', Rule::in([1, 2, 3, 4, 5])];
        }

        return $rules;
    }
}
