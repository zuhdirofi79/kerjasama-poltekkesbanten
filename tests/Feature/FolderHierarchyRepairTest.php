<?php

namespace Tests\Feature;

use App\Enums\SectionType;
use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\YearFolder;
use App\Services\DocumentFolderService;
use App\Services\FolderHierarchyRepairService;
use App\Support\CooperationFolderTaxonomy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FolderHierarchyRepairTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{section: CooperationSection, category: CooperationCategory, year: YearFolder}
     */
    private function hierarchy(int $year = 2026): array
    {
        $section = CooperationSection::query()->firstOrCreate(
            ['slug' => 'dalam-negeri'],
            [
                'name' => 'Kerjasama Dalam Negeri',
                'type' => SectionType::Domestic,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        $category = CooperationCategory::query()->firstOrCreate(
            ['section_id' => $section->id, 'slug' => 'rumah-sakit'],
            [
                'name' => 'Rumah Sakit',
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        $yearFolder = YearFolder::query()->create([
            'category_id' => $category->id,
            'year' => $year,
            'slug' => (string) $year,
            'is_active' => true,
        ]);

        return compact('section', 'category') + ['year' => $yearFolder];
    }

    /**
     * @return list<string>
     */
    private function legacyFlatNames(): array
    {
        return [
            'Pendidikan dan pembelajaran',
            'Penelitian',
            'Pengabdian kepada masyarakat',
            'Praktik klinik atau magang',
            'Pengembangan kompetensi SDM',
            'Pelatihan dan sertifikasi',
            'Rekrutmen dan penempatan lulusan',
            'Tracer study dan pengembangan alumni',
            'Pertukaran dosen atau mahasiswa',
            'Seminar dan kegiatan ilmiah',
            'Kelas internasional',
            'Pemanfaatan sarana dan prasarana',
            'Teknologi dan inovasi',
        ];
    }

    private function seedLegacyFlat(YearFolder $yearFolder, bool $withDuplicate = false): void
    {
        foreach ($this->legacyFlatNames() as $index => $name) {
            DocumentFolder::query()->create([
                'year_folder_id' => $yearFolder->id,
                'parent_id' => null,
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        if ($withDuplicate) {
            DocumentFolder::query()->create([
                'year_folder_id' => $yearFolder->id,
                'parent_id' => null,
                'name' => 'Pendidikan dan pembelajaran',
                'slug' => 'pendidikan-dan-pembelajaran-dup',
                'sort_order' => 99,
                'is_active' => true,
            ]);
        }
    }

    private function createDocument(YearFolder $yearFolder, ?DocumentFolder $folder, string $title): Document
    {
        return Document::query()->create([
            'year_folder_id' => $yearFolder->id,
            'document_folder_id' => $folder?->id,
            'title' => $title,
            'original_filename' => 'mou.pdf',
            'stored_filename' => $title.'.pdf',
            'private_file_path' => 'kerjasama/'.$title.'.pdf',
            'preview_image_path' => 'previews/'.$title.'.jpg',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 10,
            'is_active' => true,
        ]);
    }

    public function test_year_page_shows_only_four_canonical_roots(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->hierarchy();
        app(DocumentFolderService::class)->provisionDefaultFolders($year);

        $response = $this->get(route('cooperation.year', [$section, $category, $year->slug]));

        $response->assertOk();
        $this->assertPublicFolderCards($response, [
            'Pendidikan',
            'Penelitian',
            'Pengabdian',
            'Pengembangan SDM',
        ], [
            'Pemanfaatan sarana dan prasarana',
            'Praktik Klinik atau Magang',
            'Teknologi dan Inovasi',
            'Kelas Internasional',
            'Pengembangan kompetensi SDM',
        ]);
    }

    public function test_opening_pendidikan_shows_its_four_children(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->hierarchy();
        app(DocumentFolderService::class)->provisionDefaultFolders($year);

        $pendidikan = DocumentFolder::query()->where('year_folder_id', $year->id)->where('name', 'Pendidikan')->firstOrFail();

        $response = $this->get(route('cooperation.folder', [$section, $category, $year->slug, $pendidikan]));

        $response->assertOk();
        $this->assertPublicFolderCards($response, [
            'Pemanfaatan sarana dan prasarana',
            'Kelas Internasional',
            'Seminar dan Kegiatan',
            'Pelatihan dan Sertifikasi',
        ], [
            'Teknologi dan Inovasi',
            'Praktik Klinik atau Magang',
        ]);
    }

    public function test_opening_penelitian_shows_teknologi_dan_inovasi(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->hierarchy();
        app(DocumentFolderService::class)->provisionDefaultFolders($year);

        $penelitian = DocumentFolder::query()->where('year_folder_id', $year->id)->where('name', 'Penelitian')->firstOrFail();

        $this->assertPublicFolderCards(
            $this->get(route('cooperation.folder', [$section, $category, $year->slug, $penelitian]))->assertOk(),
            ['Teknologi dan Inovasi'],
            ['Pelatihan dan Sertifikasi'],
        );
    }

    public function test_opening_pengabdian_shows_its_three_children(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->hierarchy();
        app(DocumentFolderService::class)->provisionDefaultFolders($year);

        $pengabdian = DocumentFolder::query()->where('year_folder_id', $year->id)->where('name', 'Pengabdian')->firstOrFail();

        $response = $this->get(route('cooperation.folder', [$section, $category, $year->slug, $pengabdian]));

        $response->assertOk();
        $this->assertPublicFolderCards($response, [
            'Pertukaran dosen',
            'Rekrutmen dan penempatan lulusan',
            'Praktik Klinik atau Magang',
        ], [
            'Kelas Internasional',
        ]);
    }

    public function test_opening_pengembangan_sdm_shows_its_two_children(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->hierarchy();
        app(DocumentFolderService::class)->provisionDefaultFolders($year);

        $sdm = DocumentFolder::query()->where('year_folder_id', $year->id)->where('name', 'Pengembangan SDM')->firstOrFail();

        $response = $this->get(route('cooperation.folder', [$section, $category, $year->slug, $sdm]));

        $response->assertOk();
        $this->assertPublicFolderCards($response, [
            'Tracer Study dan Pengembangan Alumni',
            'Pengembangan kompetensi SDM',
        ], [
            'Teknologi dan Inovasi',
        ]);
    }

    public function test_repair_preserves_legacy_document_association_and_private_path(): void
    {
        ['year' => $year] = $this->hierarchy();
        $this->seedLegacyFlat($year);

        $praktik = DocumentFolder::query()
            ->where('year_folder_id', $year->id)
            ->where('name', 'Praktik klinik atau magang')
            ->firstOrFail();

        $document = $this->createDocument($year, $praktik, 'MoU Magang 2026');
        $documentId = $document->id;
        $privatePath = $document->private_file_path;

        app(FolderHierarchyRepairService::class)->repairYear($year);

        $document->refresh();
        $praktik->refresh();

        $this->assertSame($documentId, $document->id);
        $this->assertSame($privatePath, $document->private_file_path);
        $this->assertSame($praktik->id, $document->document_folder_id);
        $this->assertSame('Praktik Klinik atau Magang', $praktik->name);
        $this->assertNotNull($praktik->parent_id);
        $this->assertSame('Pengabdian', $praktik->parent->name);
        $this->assertFalse($praktik->trashed());
    }

    public function test_repair_is_idempotent_and_merges_duplicates(): void
    {
        ['year' => $year] = $this->hierarchy();
        $this->seedLegacyFlat($year, withDuplicate: true);

        $dup = DocumentFolder::query()
            ->where('year_folder_id', $year->id)
            ->where('slug', 'pendidikan-dan-pembelajaran-dup')
            ->firstOrFail();
        $this->createDocument($year, $dup, 'Dokumen Duplikat Pendidikan');

        $service = app(FolderHierarchyRepairService::class);
        $first = $service->repairYear($year);
        $second = $service->repairYear($year);

        $this->assertSame(CooperationFolderTaxonomy::expectedRootCount(), DocumentFolder::query()
            ->where('year_folder_id', $year->id)
            ->whereNull('parent_id')
            ->count());

        $this->assertSame(CooperationFolderTaxonomy::expectedFolderCount(), DocumentFolder::query()
            ->where('year_folder_id', $year->id)
            ->count());

        $this->assertSame(0, $second['created']);
        $this->assertGreaterThanOrEqual(1, $first['merged']);

        $this->assertSame(
            DocumentFolder::query()->where('year_folder_id', $year->id)->count(),
            DocumentFolder::query()->where('year_folder_id', $year->id)->count(),
        );

        $this->assertDatabaseHas('documents', ['title' => 'Dokumen Duplikat Pendidikan']);
        $this->assertNotNull(Document::query()->where('title', 'Dokumen Duplikat Pendidikan')->value('document_folder_id'));
    }

    public function test_repair_command_can_run_twice_without_creating_duplicates(): void
    {
        ['year' => $year] = $this->hierarchy();
        $this->seedLegacyFlat($year);

        $this->artisan('cooperation:repair-folder-hierarchy')->assertSuccessful();
        $count = DocumentFolder::query()->where('year_folder_id', $year->id)->count();

        $this->artisan('cooperation:repair-folder-hierarchy')->assertSuccessful();

        $this->assertSame($count, DocumentFolder::query()->where('year_folder_id', $year->id)->count());
        $this->assertSame(
            CooperationFolderTaxonomy::expectedRootCount(),
            DocumentFolder::query()->where('year_folder_id', $year->id)->whereNull('parent_id')->count()
        );
    }

    public function test_folder_uuid_from_another_year_returns_404(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year2026] = $this->hierarchy(2026);
        ['year' => $year2025] = $this->hierarchy(2025);

        app(DocumentFolderService::class)->provisionDefaultFolders($year2026);
        app(DocumentFolderService::class)->provisionDefaultFolders($year2025);

        $folder2025 = DocumentFolder::query()
            ->where('year_folder_id', $year2025->id)
            ->where('name', 'Pendidikan')
            ->firstOrFail();

        $this->get(route('cooperation.folder', [$section, $category, $year2026->slug, $folder2025]))
            ->assertNotFound();
    }

    public function test_breadcrumb_follows_parent_chain(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->hierarchy();
        $service = app(DocumentFolderService::class);
        $service->provisionDefaultFolders($year);

        $pendidikan = DocumentFolder::query()->where('year_folder_id', $year->id)->where('name', 'Pendidikan')->firstOrFail();
        $pelatihan = DocumentFolder::query()
            ->where('parent_id', $pendidikan->id)
            ->where('name', 'Pelatihan dan Sertifikasi')
            ->firstOrFail();
        $workshop = $service->createFolder($year, 'Workshop 2026', $pelatihan);

        $html = $this->get(route('cooperation.folder', [$section, $category, $year->slug, $workshop]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Beranda', $html);
        $this->assertStringContainsString('Kerjasama Dalam Negeri', $html);
        $this->assertStringContainsString('Rumah Sakit', $html);
        $this->assertStringContainsString('2026', $html);
        $this->assertStringContainsString('Pendidikan', $html);
        $this->assertStringContainsString('Pelatihan dan Sertifikasi', $html);
        $this->assertStringContainsString('Workshop 2026', $html);
    }

    public function test_upload_dropdown_is_indented_and_year_scoped(): void
    {
        ['year' => $yearA] = $this->hierarchy(2026);
        ['year' => $yearB] = $this->hierarchy(2025);
        app(DocumentFolderService::class)->provisionDefaultFolders($yearA);
        app(DocumentFolderService::class)->provisionDefaultFolders($yearB);

        $optionsA = \App\Support\DocumentFolderOptions::forYear($yearA);
        $labels = collect($optionsA)->pluck('label');

        $this->assertTrue($labels->contains('Pendidikan'));
        $this->assertTrue($labels->contains(fn ($label) => str_contains($label, 'Pelatihan dan Sertifikasi')));
        $this->assertTrue($labels->contains(fn ($label) => str_contains($label, '├─ ') || str_contains($label, '└─ ')));

        $idsB = collect(\App\Support\DocumentFolderOptions::forYear($yearB))->pluck('id');
        $this->assertEmpty(array_intersect(
            collect($optionsA)->pluck('id')->all(),
            $idsB->all(),
        ));
    }
}
