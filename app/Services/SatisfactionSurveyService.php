<?php

namespace App\Services;

use App\Models\SatisfactionAnswer;
use App\Models\SatisfactionQuestion;
use App\Models\SatisfactionResponse;
use App\Models\SatisfactionSurvey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SatisfactionSurveyService
{
    public function createSurvey(
        ?int $documentId = null,
        ?int $applicationId = null,
        ?string $title = null,
        ?int $createdBy = null,
    ): SatisfactionSurvey {
        return SatisfactionSurvey::query()->create([
            'public_token' => (string) Str::uuid(),
            'title' => $title ?: 'Evaluasi Kepuasan Kerja Sama',
            'document_id' => $documentId,
            'partnership_application_id' => $applicationId,
            'is_active' => true,
            'created_by' => $createdBy,
        ]);
    }

    public function activeQuestions(): Collection
    {
        return SatisfactionQuestion::query()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * @param  array<int, int>  $answers  questionId => score
     */
    public function submit(
        SatisfactionSurvey $survey,
        array $answers,
        ?string $suggestion = null,
        ?string $respondentName = null,
        ?string $respondentInstitution = null,
        ?string $ip = null,
    ): SatisfactionResponse {
        $questions = $this->activeQuestions();

        return DB::transaction(function () use (
            $survey,
            $answers,
            $suggestion,
            $respondentName,
            $respondentInstitution,
            $ip,
            $questions,
        ) {
            $response = SatisfactionResponse::query()->create([
                'satisfaction_survey_id' => $survey->id,
                'respondent_name' => $respondentName,
                'respondent_institution' => $respondentInstitution,
                'suggestion' => $suggestion,
                'respondent_ip' => $ip,
                'submitted_at' => now(),
            ]);

            foreach ($questions as $question) {
                $score = (int) ($answers[$question->id] ?? 0);
                SatisfactionAnswer::query()->create([
                    'satisfaction_response_id' => $response->id,
                    'satisfaction_question_id' => $question->id,
                    'score' => $score,
                ]);
            }

            return $response->fresh('answers');
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function analytics(): array
    {
        $responseCount = SatisfactionResponse::query()->count();

        $average = SatisfactionAnswer::query()->avg('score');

        $distribution = SatisfactionAnswer::query()
            ->selectRaw('score, count(*) as total')
            ->groupBy('score')
            ->pluck('total', 'score');

        $perQuestion = SatisfactionQuestion::query()
            ->ordered()
            ->withCount('answers')
            ->withAvg('answers', 'score')
            ->get();

        $driver = SatisfactionResponse::query()->getConnection()->getDriverName();
        $periodSql = $driver === 'sqlite'
            ? "strftime('%Y-%m', submitted_at) as period"
            : "DATE_FORMAT(submitted_at, '%Y-%m') as period";

        $trend = SatisfactionResponse::query()
            ->selectRaw("{$periodSql}, count(*) as total")
            ->whereNotNull('submitted_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'response_count' => $responseCount,
            'average' => $average !== null ? round((float) $average, 2) : null,
            'distribution' => collect(range(1, 5))
                ->mapWithKeys(fn (int $score) => [$score => (int) ($distribution[$score] ?? 0)]),
            'per_question' => $perQuestion,
            'trend' => $trend,
        ];
    }

    public function averageForDocument(int $documentId): ?float
    {
        $avg = SatisfactionAnswer::query()
            ->whereHas('response.survey', fn ($q) => $q->where('document_id', $documentId))
            ->avg('score');

        return $avg !== null ? round((float) $avg, 2) : null;
    }
}
