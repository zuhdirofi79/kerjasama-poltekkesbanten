<?php

namespace Tests\Feature;

use App\Enums\SectionType;
use App\Enums\UserRole;
use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\User;
use App\Models\YearFolder;
use App\Services\DocumentFolderService;
use App\Support\CooperationFolderTaxonomy;
use Database\Seeders\CooperationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class DocumentFolderExplorerTest extends TestCase
{
    use RefreshDatabase;

    private function hierarchy(): array
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

    private function staff(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
    }

    public function test_home_no_longer_contains_promo_feature_cards(): void
    {
        $this->seed(CooperationSeeder::class);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('feature-grid', $html);
        $this->assertStringNotContainsString('feature-card__title">Praktik Klinik', $html);
        $this->assertStringNotContainsString('feature-card__title">Penelitian', $html);
        $this->assertStringNotContainsString('feature-card__title">Pengabdian', $html);
        $this->assertStringContainsString('Kerjasama Dalam Negeri', $html);
        $this->assertStringContainsString('Kerjasama Luar Negeri', $html);
    }

    public function test_creating_year_provisions_default_folders_idempotently(): void
    {
        ['year' => $year] = $this->hierarchy();
        $service = app(DocumentFolderService::class);

        $first = $service->provisionDefaultFolders($year);
        $second = $service->provisionDefaultFolders($year);

        $expectedFolders = CooperationFolderTaxonomy::expectedFolderCount();
        $expectedRoots = CooperationFolderTaxonomy::expectedRootCount();

        $this->assertSame($expectedFolders, $first['created']);
        $this->assertSame(0, $second['created']);
        $this->assertSame($expectedFolders, $second['skipped']);
        $this->assertSame(
            $expectedRoots,
            DocumentFolder::query()->where('year_folder_id', $year->id)->whereNull('parent_id')->count()
        );
        $this->assertTrue(DocumentFolder::query()->where('name', 'Pendidikan')->whereNull('parent_id')->exists());
        $this->assertTrue(DocumentFolder::query()->where('name', 'Penelitian')->whereNull('parent_id')->exists());
        $this->assertTrue(DocumentFolder::query()->where('name', 'Pengabdian')->whereNull('parent_id')->exists());
        $this->assertTrue(DocumentFolder::query()->where('name', 'Pengembangan SDM')->whereNull('parent_id')->exists());
        $this->assertTrue(DocumentFolder::query()->where('name', 'Praktik Klinik atau Magang')->whereNotNull('parent_id')->exists());
        $this->assertFalse(DocumentFolder::query()->where('name', 'Pengabdian kepada masyarakat')->exists());
    }

    public function test_year_page_renders_folders_from_database(): void
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
            'Teknologi dan Inovasi',
            'Pemanfaatan sarana dan prasarana',
            'Praktik Klinik atau Magang',
        ]);
    }

    public function test_public_folder_page_shows_children_and_documents(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->hierarchy();
        $service = app(DocumentFolderService::class);
        $root = $service->createFolder($year, 'Penelitian');
        $child = $service->createFolder($year, 'RSUD Tangerang', $root);

        Document::query()->create([
            'year_folder_id' => $year->id,
            'document_folder_id' => $child->id,
            'title' => 'MoU Preview',
            'original_filename' => 'mou.pdf',
            'stored_filename' => 'uuid.pdf',
            'private_file_path' => 'kerjasama/test.pdf',
            'preview_image_path' => 'previews/test.jpg',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 10,
            'is_active' => true,
        ]);

        $this->get(route('cooperation.folder', [$section, $category, $year->slug, $root]))
            ->assertOk()
            ->assertSee('RSUD Tangerang')
            ->assertSee('Penelitian');

        $this->get(route('cooperation.folder', [$section, $category, $year->slug, $child]))
            ->assertOk()
            ->assertSee('MoU Preview')
            ->assertSee('RSUD Tangerang');
    }

    public function test_admin_can_create_rename_move_and_delete_folder(): void
    {
        ['year' => $year] = $this->hierarchy();
        $admin = $this->staff();

        $this->actingAs($admin)
            ->post(route('admin.folders.store'), [
                'year_folder_id' => $year->id,
                'name' => 'Arsip Kontrak',
            ])
            ->assertRedirect();

        $folder = DocumentFolder::query()->where('name', 'Arsip Kontrak')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.folders.update', $folder), [
                'name' => 'Riset Kolaboratif',
            ])
            ->assertRedirect();

        $this->assertSame('Riset Kolaboratif', $folder->fresh()->name);

        $parent = app(DocumentFolderService::class)->createFolder($year, 'Induk');

        $this->actingAs($admin)
            ->post(route('admin.folders.move', $folder), [
                'parent_id' => $parent->id,
            ])
            ->assertRedirect();

        $this->assertSame($parent->id, $folder->fresh()->parent_id);

        $this->actingAs($admin)
            ->delete(route('admin.folders.destroy', $folder))
            ->assertRedirect();

        $this->assertSoftDeleted($folder);
    }

    public function test_visitor_cannot_create_folder(): void
    {
        ['year' => $year] = $this->hierarchy();
        $visitor = User::factory()->visitor()->create();

        $this->actingAs($visitor)
            ->post(route('admin.folders.store'), [
                'year_folder_id' => $year->id,
                'name' => 'Hack',
            ])
            ->assertForbidden();
    }

    public function test_cannot_move_folder_into_descendant(): void
    {
        ['year' => $year] = $this->hierarchy();
        $service = app(DocumentFolderService::class);
        $a = $service->createFolder($year, 'A');
        $b = $service->createFolder($year, 'B', $a);

        $this->expectException(InvalidArgumentException::class);
        $service->moveFolder($a, $b);
    }

    public function test_cannot_parent_folder_to_itself(): void
    {
        ['year' => $year] = $this->hierarchy();
        $folder = app(DocumentFolderService::class)->createFolder($year, 'Solo');

        $this->expectException(InvalidArgumentException::class);
        app(DocumentFolderService::class)->moveFolder($folder, $folder);
    }

    public function test_soft_deleted_folder_is_hidden_from_public(): void
    {
        ['section' => $section, 'category' => $category, 'year' => $year] = $this->hierarchy();
        $folder = app(DocumentFolderService::class)->createFolder($year, 'Rahasia');
        $folder->delete();

        $this->get(route('cooperation.year', [$section, $category, $year->slug]))
            ->assertOk()
            ->assertDontSee('Rahasia');

        $this->get(route('cooperation.folder', [$section, $category, $year->slug, $folder]))
            ->assertNotFound();
    }

    public function test_admin_can_rename_default_folder(): void
    {
        ['year' => $year] = $this->hierarchy();
        app(DocumentFolderService::class)->provisionDefaultFolders($year);
        $folder = DocumentFolder::query()->where('name', 'Penelitian')->firstOrFail();
        $admin = $this->staff();

        $this->actingAs($admin)
            ->put(route('admin.folders.update', $folder), ['name' => 'Riset Bersama RS'])
            ->assertRedirect();

        $this->assertSame('Riset Bersama RS', $folder->fresh()->name);
        $this->assertSame('penelitian', $folder->fresh()->slug);
    }

    public function test_canonical_root_cannot_be_moved_or_deleted(): void
    {
        ['year' => $year] = $this->hierarchy();
        app(DocumentFolderService::class)->provisionDefaultFolders($year);
        $root = DocumentFolder::query()->where('name', 'Pendidikan')->whereNull('parent_id')->firstOrFail();
        $other = app(DocumentFolderService::class)->createFolder($year, 'Arsip Lain');
        $admin = $this->staff();

        $this->actingAs($admin)
            ->post(route('admin.folders.move', $root), ['parent_id' => $other->id])
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($root->fresh()->parent_id);

        $this->actingAs($admin)
            ->delete(route('admin.folders.destroy', $root))
            ->assertSessionHasErrors('delete');

        $this->assertNotSoftDeleted($root);
    }
}
