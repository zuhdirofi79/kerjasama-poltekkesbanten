<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSatisfactionResponseRequest;
use App\Models\SatisfactionSurvey;
use App\Services\SatisfactionSurveyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SatisfactionSurveyController extends Controller
{
    public function __construct(
        private readonly SatisfactionSurveyService $surveyService,
    ) {}

    public function show(SatisfactionSurvey $survey): View
    {
        abort_unless($survey->is_active, 404);

        $survey->load(['document:id,title']);
        $questions = $this->surveyService->activeQuestions();

        return view('public.satisfaction.show', [
            'survey' => $survey,
            'questions' => $questions,
            'scale' => config('document_classification.satisfaction_scale'),
        ]);
    }

    public function store(StoreSatisfactionResponseRequest $request, SatisfactionSurvey $survey): RedirectResponse
    {
        abort_unless($survey->is_active, 404);

        $this->surveyService->submit(
            $survey,
            $request->scores(),
            $request->validated('suggestion'),
            $request->validated('respondent_name'),
            $request->validated('respondent_institution'),
            $request->ip(),
        );

        return redirect()
            ->route('satisfaction.thanks', $survey)
            ->with('status', 'Terima kasih. Evaluasi Anda telah tersimpan.');
    }

    public function thanks(SatisfactionSurvey $survey): View
    {
        return view('public.satisfaction.thanks', compact('survey'));
    }
}
