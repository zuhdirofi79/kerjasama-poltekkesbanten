<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSatisfactionQuestionRequest;
use App\Http\Requests\Admin\UpdateSatisfactionQuestionRequest;
use App\Models\Document;
use App\Models\SatisfactionQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SatisfactionQuestionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Document::class);

        $questions = SatisfactionQuestion::query()->ordered()->get();

        return view('admin.satisfaction.questions', compact('questions'));
    }

    public function store(StoreSatisfactionQuestionRequest $request): RedirectResponse
    {
        $max = (int) SatisfactionQuestion::query()->max('sort_order');

        SatisfactionQuestion::query()->create([
            'body' => $request->validated('body'),
            'sort_order' => $max + 1,
            'is_active' => true,
        ]);

        return back()->with('status', 'Indikator ditambahkan.');
    }

    public function update(UpdateSatisfactionQuestionRequest $request, SatisfactionQuestion $question): RedirectResponse
    {
        $question->update($request->validated());

        return back()->with('status', 'Indikator diperbarui.');
    }
}
