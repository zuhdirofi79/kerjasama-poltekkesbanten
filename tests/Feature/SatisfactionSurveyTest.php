<?php

namespace Tests\Feature;

use App\Models\SatisfactionQuestion;
use App\Models\SatisfactionSurvey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SatisfactionSurveyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_survey_without_google_account(): void
    {
        $survey = SatisfactionSurvey::query()->where('is_active', true)->firstOrFail();
        $questions = SatisfactionQuestion::query()->active()->ordered()->get();

        $answers = [];
        foreach ($questions as $question) {
            $answers[$question->id] = 4;
        }

        $this->post(route('satisfaction.store', $survey), [
            'respondent_name' => 'Mitra ABC',
            'answers' => $answers,
            'suggestion' => 'Koordinasi sudah baik.',
        ])->assertRedirect(route('satisfaction.thanks', $survey));

        $this->assertDatabaseHas('satisfaction_responses', [
            'satisfaction_survey_id' => $survey->id,
            'respondent_name' => 'Mitra ABC',
        ]);
        $this->assertDatabaseCount('satisfaction_answers', $questions->count());
    }

    public function test_guest_cannot_open_monitoring_dashboard(): void
    {
        $this->get(route('admin.monitoring.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_open_monitoring_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.monitoring.index'))
            ->assertOk()
            ->assertSee('Monitoring & Evaluasi')
            ->assertSee('Rata-rata Kepuasan');
    }
}
