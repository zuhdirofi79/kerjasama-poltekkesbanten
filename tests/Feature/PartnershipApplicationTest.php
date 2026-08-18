<?php

namespace Tests\Feature;

use App\Enums\PartnershipApplicationStatus;
use App\Http\Controllers\PartnershipApplicationController;
use App\Mail\NewPartnershipApplicationMail;
use App\Mail\PartnershipApplicationConfirmationMail;
use App\Models\PartnershipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnershipApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('private_docs');

        User::factory()->superadmin1()->create([
            'email' => 'webmaster@poltekkesbanten.ac.id',
        ]);
        User::factory()->superadmin2()->create([
            'email' => 'rifaldi.tri@poltekkesbanten.ac.id',
        ]);
    }

    public function test_guest_can_open_application_form(): void
    {
        $this->get(route('partnership-applications.create'))
            ->assertOk()
            ->assertSee('Formulir Pengajuan Kerja Sama')
            ->assertDontSee('tally.so', false);
    }

    public function test_ajukan_route_does_not_use_page_controller(): void
    {
        $route = app('router')->getRoutes()->getByName('partnership-applications.create');

        $this->assertNotNull($route);
        $this->assertSame(
            PartnershipApplicationController::class.'@create',
            $route->getAction('controller')
        );
    }

    public function test_navbar_cta_uses_named_partnership_route(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('partnership-applications.create'), false);
    }

    public function test_valid_submission_creates_application_attachments_and_mails(): void
    {
        $response = $this->post(route('partnership-applications.store'), $this->validPayload());

        $application = PartnershipApplication::query()->first();
        $this->assertNotNull($application);
        $this->assertMatchesRegularExpression('/^KS-\d{4}-\d{6}$/', $application->submission_code);
        $this->assertSame(PartnershipApplicationStatus::Submitted, $application->status);
        $this->assertSame(['tingkat_direktorat', 'd3_keperawatan'], $application->target_units);
        $this->assertSame(['pendidikan_pembelajaran', 'penelitian'], $application->proposed_scopes);
        $this->assertSame(['calon_mitra'], $application->funding_sources);

        $this->assertDatabaseCount('partnership_application_attachments', 2);
        $official = $application->attachments()->where('type', 'official_request_letter')->first();
        $this->assertNotNull($official);
        Storage::disk('private_docs')->assertExists($official->private_path);
        $this->assertStringNotContainsString('Surat.pdf', $official->stored_filename);

        $response->assertRedirect(route('partnership-applications.success', $application));

        Mail::assertSent(NewPartnershipApplicationMail::class);
        Mail::assertSent(PartnershipApplicationConfirmationMail::class);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'partnership_application.created',
        ]);
    }

    public function test_required_and_conditional_validation(): void
    {
        $this->from(route('partnership-applications.create'))
            ->post(route('partnership-applications.store'), [])
            ->assertSessionHasErrors([
                'institution_email',
                'institution_name',
                'official_request_letter',
                'proposed_scopes',
                'target_units',
            ]);

        $payload = $this->validPayload([
            'institution_type' => 'lainnya',
            'institution_type_other' => null,
            'proposal_type' => 'lainnya',
            'proposal_type_other' => null,
            'target_units' => ['unit_lainnya'],
            'target_unit_other' => null,
            'proposed_scopes' => ['lainnya'],
            'proposed_scope_other' => null,
            'proposed_duration' => 'lainnya',
            'proposed_duration_other' => null,
        ]);

        $this->from(route('partnership-applications.create'))
            ->post(route('partnership-applications.store'), $payload)
            ->assertSessionHasErrors([
                'institution_type_other',
                'proposal_type_other',
                'target_unit_other',
                'proposed_scope_other',
                'proposed_duration_other',
            ]);
    }

    public function test_end_date_before_start_is_rejected(): void
    {
        $this->from(route('partnership-applications.create'))
            ->post(route('partnership-applications.store'), $this->validPayload([
                'planned_start_date' => '2026-12-01',
                'planned_end_date' => '2026-01-01',
            ]))
            ->assertSessionHasErrors(['planned_end_date']);
    }

    public function test_forbidden_executable_and_oversized_files_are_rejected(): void
    {
        $this->from(route('partnership-applications.create'))
            ->post(route('partnership-applications.store'), $this->validPayload([
                'official_request_letter' => UploadedFile::fake()->create('shell.php', 100, 'application/x-php'),
            ]))
            ->assertSessionHasErrors(['official_request_letter']);

        $tooLargeKb = ((int) config('partnership_applications.max_upload_mb', 10) * 1024) + 100;

        $this->from(route('partnership-applications.create'))
            ->post(route('partnership-applications.store'), $this->validPayload([
                'official_request_letter' => UploadedFile::fake()->create('surat.pdf', $tooLargeKb, 'application/pdf'),
            ]))
            ->assertSessionHasErrors(['official_request_letter']);
    }

    public function test_funding_source_no_cost_cannot_combine_with_others(): void
    {
        $this->from(route('partnership-applications.create'))
            ->post(route('partnership-applications.store'), $this->validPayload([
                'funding_sources' => ['tidak_memerlukan_biaya', 'calon_mitra'],
            ]))
            ->assertSessionHasErrors(['funding_sources']);
    }

    public function test_private_attachment_not_publicly_reachable_and_idor_blocked(): void
    {
        $this->post(route('partnership-applications.store'), $this->validPayload());
        $applicationA = PartnershipApplication::query()->firstOrFail();
        $attachmentA = $applicationA->attachments()->firstOrFail();

        $this->post(route('partnership-applications.store'), $this->validPayload([
            'institution_email' => 'other@example.com',
            'institution_name' => 'Instansi Lain',
        ]));
        $applicationB = PartnershipApplication::query()->where('id', '!=', $applicationA->id)->firstOrFail();

        $publicGuess = $this->get('/storage/'.$attachmentA->private_path);
        $this->assertContains($publicGuess->status(), [403, 404]);

        $visitor = User::factory()->visitor()->create();
        $this->actingAs($visitor)
            ->get(route('admin.partnership-applications.show', $applicationA))
            ->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get(route('admin.partnership-applications.attachments.download', [$applicationB, $attachmentA]))
            ->assertNotFound();
    }

    public function test_staff_can_view_list_and_update_status_with_audit(): void
    {
        $this->post(route('partnership-applications.store'), $this->validPayload());
        $application = PartnershipApplication::query()->firstOrFail();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.partnership-applications.index'))
            ->assertOk()
            ->assertSee($application->submission_code);

        $this->actingAs($admin)
            ->patch(route('admin.partnership-applications.status', $application), [
                'status' => PartnershipApplicationStatus::UnderReview->value,
                'review_note' => 'Sedang ditelaah',
            ])
            ->assertRedirect(route('admin.partnership-applications.show', $application));

        $this->assertSame(PartnershipApplicationStatus::UnderReview, $application->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'partnership_application.status_changed',
        ]);
    }

    public function test_guest_cannot_access_admin_detail(): void
    {
        $this->post(route('partnership-applications.store'), $this->validPayload());
        $application = PartnershipApplication::query()->firstOrFail();

        $this->get(route('admin.partnership-applications.show', $application))
            ->assertRedirect(route('login'));
    }

    public function test_honeypot_rejects_bot_submission(): void
    {
        $this->from(route('partnership-applications.create'))
            ->post(route('partnership-applications.store'), $this->validPayload([
                'company_fax' => 'spam',
            ]))
            ->assertSessionHasErrors(['company_fax']);

        $this->assertDatabaseCount('partnership_applications', 0);
    }

    public function test_success_page_uses_public_token_not_numeric_id_alone(): void
    {
        $this->post(route('partnership-applications.store'), $this->validPayload());
        $application = PartnershipApplication::query()->firstOrFail();

        $this->get(route('partnership-applications.success', $application))
            ->assertOk()
            ->assertSee($application->submission_code)
            ->assertSee('Pengajuan Kerja Sama Berhasil Dikirim');

        $this->assertTrue(str_contains(
            route('partnership-applications.success', $application),
            $application->public_token
        ));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'applicant_type' => 'external',
            'institution_email' => 'mitra@example.com',
            'institution_name' => 'RS Contoh Sehat',
            'institution_address' => 'Jl. Contoh No. 1, Serang',
            'institution_phone' => '+62 254 123456',
            'institution_website' => 'https://example.com',
            'legal_registration_number' => null,
            'institution_profile' => 'Rumah sakit pendidikan mitra.',
            'institution_type' => 'rumah_sakit_fasyankes',
            'partner_status' => 'dalam_negeri',
            'country' => 'Indonesia',
            'authorized_officer_name' => 'Dr. Direktur',
            'authorized_officer_position' => 'Direktur',
            'contact_person_name' => 'Ani Narahubung',
            'contact_person_position' => 'Kepala Kerjasama',
            'contact_person_whatsapp' => '081234567890',
            'contact_person_email' => 'ani@example.com',
            'proposal_type' => 'kerja_sama_baru',
            'proposal_title' => 'Kerja Sama Praktik Klinik',
            'proposal_purpose' => 'Meningkatkan mutu praktik klinik mahasiswa.',
            'expected_benefits' => 'Mahasiswa mendapat lahan praktik; RS mendapat SDM terlatih.',
            'target_units' => ['tingkat_direktorat', 'd3_keperawatan'],
            'proposed_scopes' => ['pendidikan_pembelajaran', 'penelitian'],
            'document_form' => 'mou',
            'activity_description' => 'Penempatan mahasiswa praktik klinik dan joint research.',
            'target_description' => 'Mahasiswa dan dosen keperawatan.',
            'planned_start_date' => '2026-03-01',
            'planned_end_date' => '2027-02-28',
            'proposed_duration' => '1_tahun',
            'activity_location' => 'RS Contoh Sehat, Serang',
            'expected_poltekkes_contribution' => 'Pembimbingan akademik',
            'requires_funding' => 'tidak',
            'funding_sources' => ['calon_mitra'],
            'official_request_letter' => UploadedFile::fake()->create('Surat.pdf', 200, 'application/pdf'),
            'institution_profile_file' => UploadedFile::fake()->create('Profil.pdf', 150, 'application/pdf'),
            'declaration_data_accurate' => '1',
            'declaration_willing_verification' => '1',
            'declaration_data_usage' => '1',
            'declarant_name' => 'Ani Narahubung',
            'declarant_position' => 'Kepala Kerjasama',
            'submission_date' => now()->toDateString(),
        ], $overrides);
    }
}
