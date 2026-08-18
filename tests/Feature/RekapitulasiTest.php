<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RekapitulasiService;
use App\Support\CooperationTaxonomy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCooperationHierarchy;
use Tests\TestCase;

class RekapitulasiTest extends TestCase
{
    use CreatesCooperationHierarchy;
    use RefreshDatabase;

    public function test_guest_cannot_open_admin_rekapitulasi(): void
    {
        $this->get(route('admin.rekapitulasi.index'))
            ->assertRedirect(route('login'));
    }

    public function test_visitor_cannot_open_rekapitulasi(): void
    {
        $visitor = User::factory()->visitor()->create();

        $this->actingAs($visitor)
            ->get(route('admin.rekapitulasi.index'))
            ->assertForbidden();
    }

    public function test_tri_dharma_counts_exclude_hr_development(): void
    {
        ['year' => $year] = $this->makeHierarchy();
        $this->makeDocument($year, ['title' => 'Pend 1', 'cooperation_area' => CooperationTaxonomy::EDUCATION]);
        $this->makeDocument($year, ['title' => 'Pend 2', 'cooperation_area' => CooperationTaxonomy::EDUCATION]);
        $this->makeDocument($year, ['title' => 'Riset', 'cooperation_area' => CooperationTaxonomy::RESEARCH]);
        $this->makeDocument($year, ['title' => 'Pengmas', 'cooperation_area' => CooperationTaxonomy::COMMUNITY_SERVICE]);
        $this->makeDocument($year, ['title' => 'SDM', 'cooperation_area' => CooperationTaxonomy::HR_DEVELOPMENT]);
        $this->makeDocument($year, ['title' => 'Lainnya', 'cooperation_area' => CooperationTaxonomy::OTHER]);
        $this->makeDocument($year, ['title' => 'Tanpa kelas']);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.rekapitulasi.index'))
            ->assertOk()
            ->assertSee('Total Tri Dharma')
            ->assertSee('4')
            ->assertSee('Pendidikan')
            ->assertSee('Penelitian')
            ->assertSee('Pengabdian Masyarakat');

        $kpi = app(RekapitulasiService::class)->dashboard([
            'q' => '',
            'area' => '',
            'subtype' => '',
            'date_from' => '',
            'date_to' => '',
            'sort' => 'document_date_desc',
            'year' => '',
            'section' => '',
            'category_id' => '',
            'expiry_status' => '',
        ])['kpi'];

        $this->assertSame(4, $kpi['total']);
        $this->assertSame(2, $kpi['education']);
        $this->assertSame(1, $kpi['research']);
        $this->assertSame(1, $kpi['community_service']);
    }
}
