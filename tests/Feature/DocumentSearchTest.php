<?php

namespace Tests\Feature;

use App\Support\CooperationTaxonomy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCooperationHierarchy;
use Tests\TestCase;

class DocumentSearchTest extends TestCase
{
    use CreatesCooperationHierarchy;
    use RefreshDatabase;

    public function test_search_by_title(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->makeHierarchy();
        $this->makeDocument($year, ['title' => 'MoU Rumah Sakit ABC']);
        $this->makeDocument($year, ['title' => 'Dokumen Lain']);

        $this->get(route('cooperation.year', [$section, $category, $year->slug]).'?q=rumah+sakit')
            ->assertOk()
            ->assertSee('MoU Rumah Sakit ABC')
            ->assertDontSee('Dokumen Lain');
    }

    public function test_search_pendidikan_alias_uses_cooperation_area_not_folder_name(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->makeHierarchy();
        $folder = $this->makeFolder($year, 'Penelitian');
        $this->makeDocument($year, [
            'title' => 'Kelas Internasional 2026',
            'document_folder_id' => $folder->id,
            'cooperation_area' => CooperationTaxonomy::EDUCATION,
        ]);
        $this->makeDocument($year, [
            'title' => 'Riset Kolaboratif',
            'document_folder_id' => $folder->id,
            'cooperation_area' => CooperationTaxonomy::RESEARCH,
        ]);

        $this->get(route('cooperation.year', [$section, $category, $year->slug]).'?q=tri+dharma+bidang+pendidikan')
            ->assertOk()
            ->assertSee('Kelas Internasional 2026')
            ->assertDontSee('Riset Kolaboratif');
    }

    public function test_search_penelitian_alias(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->makeHierarchy();
        $this->makeDocument($year, ['title' => 'Inovasi Lab', 'cooperation_area' => CooperationTaxonomy::RESEARCH]);
        $this->makeDocument($year, ['title' => 'Seminar Nasional', 'cooperation_area' => CooperationTaxonomy::EDUCATION]);

        $this->get(route('cooperation.year', [$section, $category, $year->slug]).'?q=penelitian')
            ->assertOk()
            ->assertSee('Inovasi Lab')
            ->assertDontSee('Seminar Nasional');
    }

    public function test_search_pengmas_alias(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->makeHierarchy();
        $this->makeDocument($year, [
            'title' => 'Praktik Klinik Mitra',
            'cooperation_area' => CooperationTaxonomy::COMMUNITY_SERVICE,
        ]);
        $this->makeDocument($year, [
            'title' => 'Pelatihan Sertifikasi',
            'cooperation_area' => CooperationTaxonomy::EDUCATION,
        ]);

        $this->get(route('cooperation.year', [$section, $category, $year->slug]).'?q=pengmas')
            ->assertOk()
            ->assertSee('Praktik Klinik Mitra')
            ->assertDontSee('Pelatihan Sertifikasi');
    }

    public function test_filter_by_document_date_not_created_at(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->makeHierarchy();
        $this->makeDocument($year, [
            'title' => 'Dokumen 2025',
            'document_date' => '2025-06-01',
            'created_at' => '2026-01-01 00:00:00',
        ]);
        $this->makeDocument($year, [
            'title' => 'Dokumen 2026',
            'document_date' => '2026-02-01',
            'created_at' => '2025-01-01 00:00:00',
        ]);

        $this->get(route('cooperation.year', [$section, $category, $year->slug]).'?date_from=2026-01-01&date_to=2026-12-31')
            ->assertOk()
            ->assertSee('Dokumen 2026')
            ->assertDontSee('Dokumen 2025');
    }

    public function test_sort_document_date_asc_and_desc(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->makeHierarchy();
        $this->makeDocument($year, ['title' => 'Lama', 'document_date' => '2024-01-01']);
        $this->makeDocument($year, ['title' => 'Baru', 'document_date' => '2026-08-01']);

        $desc = $this->get(route('cooperation.year', [$section, $category, $year->slug]).'?sort=document_date_desc')
            ->assertOk()
            ->getContent();
        $this->assertTrue(strpos($desc, 'Baru') < strpos($desc, 'Lama'));

        $asc = $this->get(route('cooperation.year', [$section, $category, $year->slug]).'?sort=document_date_asc')
            ->assertOk()
            ->getContent();
        $this->assertTrue(strpos($asc, 'Lama') < strpos($asc, 'Baru'));
    }

    public function test_pagination_preserves_filters(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->makeHierarchy();
        for ($i = 1; $i <= 13; $i++) {
            $this->makeDocument($year, [
                'title' => 'Penelitian Item '.$i,
                'cooperation_area' => CooperationTaxonomy::RESEARCH,
            ]);
        }

        $this->get(route('cooperation.year', [$section, $category, $year->slug]).'?q=penelitian&page=2')
            ->assertOk()
            ->assertSee('q=penelitian', false);
    }

    public function test_folder_rename_does_not_change_classification(): void
    {
        ['year' => $year] = $this->makeHierarchy();
        $folder = $this->makeFolder($year, 'Penelitian');
        $document = $this->makeDocument($year, [
            'title' => 'MoU Pendidikan',
            'document_folder_id' => $folder->id,
            'cooperation_area' => CooperationTaxonomy::EDUCATION,
        ]);

        $folder->update(['name' => 'Arsip Direktur']);

        $this->assertSame(CooperationTaxonomy::EDUCATION, $document->fresh()->cooperation_area);
        $this->assertSame('Arsip Direktur', $folder->fresh()->name);
    }
}
