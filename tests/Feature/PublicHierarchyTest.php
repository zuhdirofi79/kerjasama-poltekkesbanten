<?php

namespace Tests\Feature;

use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\YearFolder;
use Database\Seeders\CooperationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_ok_and_shows_cooperation_sections(): void
    {
        $this->seed(CooperationSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Kerjasama Dalam Negeri');
        $response->assertSee('Kerjasama Luar Negeri');
        $response->assertDontSee('feature-grid', false);
        $response->assertSee(route('partnership-applications.create'), false);
    }

    public function test_domestic_page_shows_exact_five_categories(): void
    {
        $this->seed(CooperationSeeder::class);

        $section = CooperationSection::query()->where('slug', 'dalam-negeri')->firstOrFail();

        $response = $this->get(route('cooperation.section', $section));

        $response->assertOk();
        $response->assertSee('Rumah Sakit');
        $response->assertSee('Dinkes & Puskesmas');
        $response->assertSee('Klinik & Praktik Mandiri');
        $response->assertSee('Perguruan Tinggi');
        $response->assertSee('Institusi Lainnya');
    }

    public function test_international_page_shows_exact_three_programs(): void
    {
        $this->seed(CooperationSeeder::class);

        $section = CooperationSection::query()->where('slug', 'luar-negeri')->firstOrFail();

        $response = $this->get(route('cooperation.section', $section));

        $response->assertOk();
        $response->assertSee('Student Exchange');
        $response->assertSee('Internship');
        $response->assertSee('International Class');
        $response->assertDontSee('Pertukaran Pendidikan');
        $response->assertDontSee('Kolaborasi Penelitian');
        $response->assertDontSee('Pengembangan Kapasitas');
    }

    public function test_category_page_renders_years_from_database_including_2020(): void
    {
        $this->seed(CooperationSeeder::class);

        $section = CooperationSection::query()->where('slug', 'dalam-negeri')->firstOrFail();
        $category = CooperationCategory::query()
            ->where('section_id', $section->id)
            ->where('slug', 'rumah-sakit')
            ->firstOrFail();

        $response = $this->get(route('cooperation.category', [$section, $category]));

        $response->assertOk();
        foreach ([2026, 2025, 2024, 2023, 2022, 2021, 2020] as $year) {
            $response->assertSee((string) $year);
        }
    }

    public function test_new_year_folder_appears_without_code_change(): void
    {
        $this->seed(CooperationSeeder::class);

        $section = CooperationSection::query()->where('slug', 'dalam-negeri')->firstOrFail();
        $category = CooperationCategory::query()
            ->where('section_id', $section->id)
            ->where('slug', 'rumah-sakit')
            ->firstOrFail();

        YearFolder::query()->create([
            'category_id' => $category->id,
            'year' => 2027,
            'slug' => '2027',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->get(route('cooperation.category', [$section, $category]))
            ->assertOk()
            ->assertSee('2027');
    }

    public function test_seeder_renames_legacy_international_categories_without_duplicating(): void
    {
        $section = CooperationSection::query()->create([
            'name' => 'Kerjasama Luar Negeri',
            'slug' => 'luar-negeri',
            'type' => 'international',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $legacy = CooperationCategory::query()->create([
            'section_id' => $section->id,
            'name' => 'Pertukaran Pendidikan',
            'slug' => 'pertukaran-pendidikan',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        YearFolder::query()->create([
            'category_id' => $legacy->id,
            'year' => 2025,
            'slug' => '2025',
            'is_active' => true,
        ]);

        $this->seed(CooperationSeeder::class);

        $this->assertSame(1, CooperationCategory::query()
            ->where('section_id', $section->id)
            ->where('sort_order', 1)
            ->count());

        $updated = CooperationCategory::query()->findOrFail($legacy->id);
        $this->assertSame('Student Exchange', $updated->name);
        $this->assertSame('student-exchange', $updated->slug);
        $this->assertTrue(YearFolder::query()->where('category_id', $legacy->id)->where('year', 2025)->exists());
    }

    public function test_inactive_section_returns_404(): void
    {
        $this->seed(CooperationSeeder::class);

        $section = CooperationSection::query()->where('slug', 'dalam-negeri')->firstOrFail();
        $section->update(['is_active' => false]);

        $this->get(route('cooperation.section', $section))->assertNotFound();
    }
}
