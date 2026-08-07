<?php

namespace Database\Seeders;

use App\Enums\SectionType;
use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\YearFolder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CooperationSeeder extends Seeder
{
    /**
     * Idempotent hierarchy seed.
     * Safe to re-run on production: renames wrong categories in-place (preserves FKs).
     */
    public function run(): void
    {
        $years = range(2026, 2020);

        $sections = [
            [
                'name' => 'Kerjasama Dalam Negeri',
                'slug' => 'dalam-negeri',
                'type' => SectionType::Domestic,
                'sort_order' => 1,
                'categories' => [
                    [
                        'name' => 'Rumah Sakit',
                        'slug' => 'rumah-sakit',
                        'sort_order' => 1,
                        'legacy_slugs' => ['rumah-sakit'],
                    ],
                    [
                        'name' => 'Dinkes & Puskesmas',
                        'slug' => 'dinkes-puskesmas',
                        'sort_order' => 2,
                        'legacy_slugs' => ['dinkes-puskesmas', 'dinas-kesehatan'],
                    ],
                    [
                        'name' => 'Klinik & Praktik Mandiri',
                        'slug' => 'klinik-praktik-mandiri',
                        'sort_order' => 3,
                        'legacy_slugs' => ['klinik-praktik-mandiri', 'pendidikan-tinggi'],
                    ],
                    [
                        'name' => 'Perguruan Tinggi',
                        'slug' => 'perguruan-tinggi',
                        'sort_order' => 4,
                        'legacy_slugs' => ['perguruan-tinggi', 'pemerintah-daerah'],
                    ],
                    [
                        'name' => 'Institusi Lainnya',
                        'slug' => 'institusi-lainnya',
                        'sort_order' => 5,
                        'legacy_slugs' => ['institusi-lainnya', 'industri-dunia-usaha'],
                    ],
                ],
            ],
            [
                'name' => 'Kerjasama Luar Negeri',
                'slug' => 'luar-negeri',
                'type' => SectionType::International,
                'sort_order' => 2,
                'categories' => [
                    [
                        'name' => 'Student Exchange',
                        'slug' => 'student-exchange',
                        'sort_order' => 1,
                        'legacy_slugs' => ['student-exchange', 'pertukaran-pendidikan'],
                    ],
                    [
                        'name' => 'Internship',
                        'slug' => 'internship',
                        'sort_order' => 2,
                        'legacy_slugs' => ['internship', 'kolaborasi-penelitian'],
                    ],
                    [
                        'name' => 'International Class',
                        'slug' => 'international-class',
                        'sort_order' => 3,
                        'legacy_slugs' => ['international-class', 'pengembangan-kapasitas'],
                    ],
                ],
            ],
        ];

        DB::transaction(function () use ($sections, $years) {
            foreach ($sections as $sectionData) {
                $categories = $sectionData['categories'];
                unset($sectionData['categories']);

                $section = CooperationSection::query()->updateOrCreate(
                    ['slug' => $sectionData['slug']],
                    [
                        'name' => $sectionData['name'],
                        'type' => $sectionData['type'],
                        'sort_order' => $sectionData['sort_order'],
                        'is_active' => true,
                    ],
                );

                foreach ($categories as $categoryData) {
                    $category = $this->resolveCategory($section->id, $categoryData);

                    $category->fill([
                        'name' => $categoryData['name'],
                        'slug' => $categoryData['slug'],
                        'sort_order' => $categoryData['sort_order'],
                        'is_active' => true,
                    ])->save();

                    foreach ($years as $index => $year) {
                        YearFolder::query()->updateOrCreate(
                            [
                                'category_id' => $category->id,
                                'year' => $year,
                            ],
                            [
                                'slug' => (string) $year,
                                'sort_order' => $index,
                                'is_active' => true,
                            ],
                        );
                    }
                }
            }
        });
    }

    /**
     * Prefer existing row by target slug, else by legacy slug (same section),
     * else by sort_order position — never create duplicates that orphan children.
     */
    private function resolveCategory(int $sectionId, array $categoryData): CooperationCategory
    {
        $bySlug = CooperationCategory::withTrashed()
            ->where('section_id', $sectionId)
            ->where('slug', $categoryData['slug'])
            ->first();

        if ($bySlug) {
            if ($bySlug->trashed()) {
                $bySlug->restore();
            }

            return $bySlug;
        }

        foreach ($categoryData['legacy_slugs'] as $legacySlug) {
            if ($legacySlug === $categoryData['slug']) {
                continue;
            }

            $legacy = CooperationCategory::withTrashed()
                ->where('section_id', $sectionId)
                ->where('slug', $legacySlug)
                ->first();

            if ($legacy) {
                if ($legacy->trashed()) {
                    $legacy->restore();
                }

                return $legacy;
            }
        }

        $byOrder = CooperationCategory::withTrashed()
            ->where('section_id', $sectionId)
            ->where('sort_order', $categoryData['sort_order'])
            ->first();

        if ($byOrder) {
            if ($byOrder->trashed()) {
                $byOrder->restore();
            }

            return $byOrder;
        }

        return new CooperationCategory([
            'section_id' => $sectionId,
        ]);
    }
}
