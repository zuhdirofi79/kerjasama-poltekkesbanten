<?php

namespace Tests\Feature;

use App\Enums\ApplicantType;
use App\Models\PartnershipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnershipApplicantTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('private_docs');
        User::factory()->superadmin1()->create(['email' => 'webmaster@poltekkesbanten.ac.id']);
        User::factory()->superadmin2()->create(['email' => 'rifaldi.tri@poltekkesbanten.ac.id']);
    }

    public function test_internal_fills_canonical_institution_fields(): void
    {
        $canonical = config('partnership_applications.internal_institution');

        $this->post(route('partnership-applications.store'), $this->payload([
            'applicant_type' => 'internal',
            'institution_name' => 'Hacked Name',
            'institution_address' => 'Alamat palsu',
            'institution_phone' => '000',
            'institution_website' => 'https://evil.example',
        ]))->assertRedirect();

        $application = PartnershipApplication::query()->firstOrFail();
        $this->assertSame(ApplicantType::Internal, $application->applicant_type);
        $this->assertSame($canonical['name'], $application->institution_name);
        $this->assertSame($canonical['address'], $application->institution_address);
        $this->assertSame($canonical['phone'], $application->institution_phone);
        $this->assertSame($canonical['website'], $application->institution_website);
    }

    public function test_external_requires_institution_fields(): void
    {
        $this->from(route('partnership-applications.create'))
            ->post(route('partnership-applications.store'), $this->payload([
                'applicant_type' => 'external',
                'institution_name' => '',
                'institution_address' => '',
                'institution_phone' => '',
            ]))
            ->assertSessionHasErrors(['institution_name', 'institution_address', 'institution_phone']);
    }

    public function test_template_pdf_is_downloadable(): void
    {
        $this->get(route('partnership-applications.template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_filled_template_is_stored_privately(): void
    {
        $file = UploadedFile::fake()->create('Form-Isian.pdf', 120, 'application/pdf');

        $this->post(route('partnership-applications.store'), $this->payload([
            'applicant_type' => 'external',
            'filled_template' => $file,
        ]))->assertRedirect();

        $application = PartnershipApplication::query()->firstOrFail();
        $attachment = $application->attachments()->where('type', 'filled_template')->first();
        $this->assertNotNull($attachment);
        $this->assertSame('Form-Isian.pdf', $attachment->original_filename);
        $this->assertStringNotContainsString('Form-Isian', $attachment->stored_filename);
        Storage::disk('private_docs')->assertExists($attachment->private_path);

        $this->assertContains(
            $this->get('/storage/'.$attachment->private_path)->status(),
            [403, 404],
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'applicant_type' => 'external',
            'institution_email' => 'mitra@example.com',
            'institution_name' => 'RS Contoh Sehat',
            'institution_address' => 'Jl. Contoh No. 1, Serang',
            'institution_phone' => '+62 254 123456',
            'institution_website' => 'https://example.com',
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
            'expected_benefits' => 'Mahasiswa mendapat lahan praktik.',
            'target_units' => ['tingkat_direktorat'],
            'proposed_scopes' => ['pendidikan_pembelajaran'],
            'document_form' => 'mou',
            'activity_description' => 'Penempatan mahasiswa praktik klinik.',
            'target_description' => 'Mahasiswa keperawatan.',
            'planned_start_date' => '2026-03-01',
            'planned_end_date' => '2027-02-28',
            'proposed_duration' => '1_tahun',
            'activity_location' => 'RS Contoh Sehat, Serang',
            'requires_funding' => 'tidak',
            'funding_sources' => ['calon_mitra'],
            'official_request_letter' => UploadedFile::fake()->create('Surat.pdf', 200, 'application/pdf'),
            'declaration_data_accurate' => '1',
            'declaration_willing_verification' => '1',
            'declaration_data_usage' => '1',
            'declarant_name' => 'Ani Narahubung',
            'declarant_position' => 'Kepala Kerjasama',
            'submission_date' => now()->toDateString(),
        ], $overrides);
    }
}
