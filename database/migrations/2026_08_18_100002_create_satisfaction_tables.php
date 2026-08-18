<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satisfaction_questions', function (Blueprint $table) {
            $table->id();
            $table->string('body', 500);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_token')->unique();
            $table->string('title')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('partnership_application_id')->nullable()->constrained('partnership_applications')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'created_at']);
        });

        Schema::create('satisfaction_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satisfaction_survey_id')->constrained('satisfaction_surveys')->cascadeOnDelete();
            $table->string('respondent_name')->nullable();
            $table->string('respondent_institution')->nullable();
            $table->text('suggestion')->nullable();
            $table->string('respondent_ip', 45)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['satisfaction_survey_id', 'submitted_at']);
        });

        Schema::create('satisfaction_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satisfaction_response_id')->constrained('satisfaction_responses')->cascadeOnDelete();
            $table->foreignId('satisfaction_question_id')->constrained('satisfaction_questions')->restrictOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['satisfaction_response_id', 'satisfaction_question_id'], 'satisfaction_answers_response_question_unique');
        });

        $now = now();
        $questions = config('document_classification.default_satisfaction_questions', []);
        foreach (array_values($questions) as $index => $body) {
            DB::table('satisfaction_questions')->insert([
                'body' => $body,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('satisfaction_surveys')->insert([
            'public_token' => (string) Str::uuid(),
            'title' => 'Evaluasi Kepuasan Kerja Sama',
            'document_id' => null,
            'partnership_application_id' => null,
            'is_active' => true,
            'created_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('satisfaction_answers');
        Schema::dropIfExists('satisfaction_responses');
        Schema::dropIfExists('satisfaction_surveys');
        Schema::dropIfExists('satisfaction_questions');
    }
};
