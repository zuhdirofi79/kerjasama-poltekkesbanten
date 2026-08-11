<?php

namespace Tests\Feature;

use App\Enums\SectionType;
use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\User;
use App\Models\YearFolder;
use App\Services\DocumentFolderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentFolderDropdownScopingTest extends TestCase
{
    use RefreshDatabase;

    private YearFolder $yearA;

    private YearFolder $yearB;

    private DocumentFolder $folderA;

    private DocumentFolder $folderB;

    private DocumentFolder $nestedA;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private_docs');
        Storage::fake('public');

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

        $this->yearA = YearFolder::query()->create([
            'category_id' => $category->id,
            'year' => 2026,
            'slug' => '2026',
            'is_active' => true,
        ]);

        $this->yearB = YearFolder::query()->create([
            'category_id' => $category->id,
            'year' => 2025,
            'slug' => '2025',
            'is_active' => true,
        ]);

        $service = app(DocumentFolderService::class);
        $this->folderA = $service->createFolder($this->yearA, 'Pendidikan dan pembelajaran');
        $this->nestedA = $service->createFolder($this->yearA, 'Kurikulum', $this->folderA);
        $this->folderB = $service->createFolder($this->yearB, 'Pendidikan dan pembelajaran');

        $this->admin = User::factory()->admin()->create();
    }

    public function test_create_form_only_lists_folders_for_selected_year(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.documents.create', ['year_folder_id' => $this->yearA->id]));

        $response->assertOk();
        $response->assertDontSee('(YF#');
        $response->assertSee('Pendidikan dan pembelajaran');
        $response->assertSee('— Kurikulum');
        $response->assertDontSee('value="'.$this->folderB->id.'"', false);
    }

    public function test_ajax_endpoint_scopes_folders_to_year(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.years.document-folders', $this->yearA));

        $response->assertOk();
        $ids = collect($response->json('folders'))->pluck('id');

        $this->assertTrue($ids->contains($this->folderA->id));
        $this->assertTrue($ids->contains($this->nestedA->id));
        $this->assertFalse($ids->contains($this->folderB->id));
    }

    public function test_forged_cross_year_folder_is_rejected_on_store(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), [
            'year_folder_id' => $this->yearA->id,
            'document_folder_id' => $this->folderB->id,
            'title' => 'Cross Year',
            'document' => UploadedFile::fake()->create('mou.pdf', 20, 'application/pdf'),
            'preview' => UploadedFile::fake()->image('preview.jpg'),
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('document_folder_id');
        $this->assertDatabaseMissing('documents', ['title' => 'Cross Year']);
    }

    public function test_null_document_folder_stores_at_year_root(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), [
            'year_folder_id' => $this->yearA->id,
            'document_folder_id' => null,
            'title' => 'Root Doc',
            'document' => UploadedFile::fake()->create('mou.pdf', 20, 'application/pdf'),
            'preview' => UploadedFile::fake()->image('preview.jpg'),
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'title' => 'Root Doc',
            'year_folder_id' => $this->yearA->id,
            'document_folder_id' => null,
        ]);
    }

    public function test_soft_deleted_folder_cannot_be_selected(): void
    {
        $this->folderA->delete();

        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), [
            'year_folder_id' => $this->yearA->id,
            'document_folder_id' => $this->folderA->id,
            'title' => 'Deleted Folder',
            'document' => UploadedFile::fake()->create('mou.pdf', 20, 'application/pdf'),
            'preview' => UploadedFile::fake()->image('preview.jpg'),
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('document_folder_id');
    }

    public function test_inactive_folder_cannot_be_selected(): void
    {
        $this->folderA->update(['is_active' => false]);

        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), [
            'year_folder_id' => $this->yearA->id,
            'document_folder_id' => $this->folderA->id,
            'title' => 'Inactive Folder',
            'document' => UploadedFile::fake()->create('mou.pdf', 20, 'application/pdf'),
            'preview' => UploadedFile::fake()->image('preview.jpg'),
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('document_folder_id');
    }

    public function test_edit_form_retains_valid_folder_and_scopes_options(): void
    {
        $document = Document::query()->create([
            'year_folder_id' => $this->yearA->id,
            'document_folder_id' => $this->nestedA->id,
            'title' => 'Existing',
            'original_filename' => 'a.pdf',
            'stored_filename' => 'a.pdf',
            'private_file_path' => 'kerjasama/a.pdf',
            'preview_image_path' => 'previews/a.jpg',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.documents.edit', $document));

        $response->assertOk();
        $response->assertSee('value="'.$this->nestedA->id.'"', false);
        $response->assertDontSee('value="'.$this->folderB->id.'"', false);
    }

    public function test_visitor_cannot_access_folder_options_endpoint(): void
    {
        $visitor = User::factory()->visitor()->create();

        $this->actingAs($visitor)
            ->getJson(route('admin.years.document-folders', $this->yearA))
            ->assertForbidden();
    }

    public function test_same_folder_name_across_years_is_valid_data_not_true_duplicate(): void
    {
        $this->assertSame('Pendidikan dan pembelajaran', $this->folderA->name);
        $this->assertSame('Pendidikan dan pembelajaran', $this->folderB->name);
        $this->assertNotSame($this->folderA->year_folder_id, $this->folderB->year_folder_id);

        $sameYearDupes = DocumentFolder::query()
            ->selectRaw('year_folder_id, parent_id, name, COUNT(*) as total')
            ->groupBy('year_folder_id', 'parent_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $this->assertSame(0, $sameYearDupes);
    }
}
