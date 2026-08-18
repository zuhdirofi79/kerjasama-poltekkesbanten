<?php

namespace Tests\Concerns;

use App\Enums\SectionType;
use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\YearFolder;

trait CreatesCooperationHierarchy
{
    /**
     * @return array{section: CooperationSection, category: CooperationCategory, year: YearFolder}
     */
    protected function makeHierarchy(): array
    {
        $section = CooperationSection::query()->create([
            'name' => 'Kerjasama Dalam Negeri',
            'slug' => 'dalam-negeri',
            'type' => SectionType::Domestic,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $category = CooperationCategory::query()->create([
            'section_id' => $section->id,
            'name' => 'Rumah Sakit',
            'slug' => 'rumah-sakit',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $year = YearFolder::query()->create([
            'category_id' => $category->id,
            'year' => 2026,
            'slug' => '2026',
            'is_active' => true,
        ]);

        return compact('section', 'category', 'year');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeDocument(YearFolder $year, array $overrides = []): Document
    {
        return Document::query()->create(array_merge([
            'year_folder_id' => $year->id,
            'title' => 'Dokumen Uji',
            'original_filename' => 'dokumen.pdf',
            'stored_filename' => 'uuid.pdf',
            'private_file_path' => 'kerjasama/test.pdf',
            'preview_image_path' => 'previews/test.jpg',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 12,
            'is_active' => true,
        ], $overrides));
    }

    protected function makeFolder(YearFolder $year, string $name): DocumentFolder
    {
        return DocumentFolder::query()->create([
            'year_folder_id' => $year->id,
            'name' => $name,
            'slug' => str()->slug($name).'-'.uniqid(),
            'is_active' => true,
        ]);
    }
}
