<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnership_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_token')->unique();
            $table->string('submission_code', 32)->unique();
            $table->string('status', 32)->default('submitted')->index();

            $table->string('institution_email');
            $table->string('institution_name');
            $table->text('institution_address');
            $table->string('institution_phone', 64);
            $table->string('institution_website')->nullable();
            $table->string('legal_registration_number')->nullable();
            $table->text('institution_profile');
            $table->string('institution_type', 64)->index();
            $table->string('institution_type_other')->nullable();
            $table->string('partner_status', 32)->index();
            $table->string('country', 120);

            $table->string('authorized_officer_name');
            $table->string('authorized_officer_position');
            $table->string('contact_person_name');
            $table->string('contact_person_position');
            $table->string('contact_person_whatsapp', 64);
            $table->string('contact_person_email');

            $table->string('proposal_type', 64);
            $table->string('proposal_type_other')->nullable();
            $table->string('proposal_title', 500);
            $table->text('proposal_purpose');
            $table->text('expected_benefits');
            $table->json('target_units');
            $table->string('target_unit_other')->nullable();

            $table->json('proposed_scopes');
            $table->string('proposed_scope_other')->nullable();
            $table->string('document_form', 64);
            $table->text('activity_description');
            $table->text('target_description');

            $table->date('planned_start_date');
            $table->date('planned_end_date');
            $table->string('proposed_duration', 32);
            $table->string('proposed_duration_other')->nullable();
            $table->string('activity_location', 1000);
            $table->text('expected_poltekkes_contribution')->nullable();
            $table->string('requires_funding', 32);
            $table->json('funding_sources');

            $table->boolean('declaration_data_accurate');
            $table->boolean('declaration_willing_verification');
            $table->boolean('declaration_data_usage');
            $table->string('declarant_name');
            $table->string('declarant_position');
            $table->date('submission_date')->index();

            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->string('applicant_ip', 45)->nullable();
            $table->string('applicant_user_agent', 512)->nullable();

            $table->timestamps();

            $table->index('institution_email');
            $table->index('contact_person_email');
            $table->index('created_at');
        });

        Schema::create('partnership_application_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partnership_application_id')
                ->constrained('partnership_applications')
                ->cascadeOnDelete();
            $table->string('type', 64);
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('private_path');
            $table->string('mime_type', 128);
            $table->string('extension', 16);
            $table->unsignedBigInteger('file_size');
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->index(['partnership_application_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_application_attachments');
        Schema::dropIfExists('partnership_applications');
    }
};
