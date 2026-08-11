<?php

namespace Tests\Feature;

use App\Enums\SectionType;
use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\Document;
use App\Models\User;
use App\Models\YearFolder;
use App\Services\DocumentStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentPreviewPublicTest extends TestCase
{
    use RefreshDatabase;

    private YearFolder $year;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private_docs');
        Storage::fake('public');

        $section = CooperationSection::query()->create([
            'name' => 'Dalam Negeri',
            'slug' => 'dalam-negeri',
            'type' => SectionType::Domestic,
            'is_active' => true,
        ]);
        $category = CooperationCategory::query()->create([
            'section_id' => $section->id,
            'name' => 'Rumah Sakit',
            'slug' => 'rumah-sakit',
            'is_active' => true,
        ]);
        $this->year = YearFolder::query()->create([
            'category_id' => $category->id,
            'year' => 2026,
            'slug' => '2026',
            'is_active' => true,
        ]);
        $this->admin = User::factory()->admin()->create();
    }

    public function test_preview_upload_stores_on_public_disk_and_original_on_private(): void
    {
        $document = app(DocumentStorageService::class)->store(
            UploadedFile::fake()->create('mou.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('preview.png', 800, 600),
            $this->year,
            null,
            ['title' => 'MOU Preview Test'],
            $this->admin->id,
        );

        $this->assertNotNull($document->preview_image_path);
        $this->assertStringStartsWith('previews/', $document->preview_image_path);
        Storage::disk('public')->assertExists($document->preview_image_path);
        Storage::disk('private_docs')->assertExists($document->private_file_path);
        $this->assertStringNotContainsString('private', (string) $document->preview_image_path);
    }

    public function test_preview_url_uses_public_preview_route_not_private_path(): void
    {
        $document = Document::query()->create([
            'year_folder_id' => $this->year->id,
            'title' => 'MOU',
            'original_filename' => 'mou.pdf',
            'stored_filename' => 'uuid.pdf',
            'private_file_path' => 'kerjasama/secret.pdf',
            'preview_image_path' => 'previews/cover.png',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 10,
            'is_active' => true,
        ]);

        Storage::disk('public')->put('previews/cover.png', 'fake-image-bytes');

        $url = $document->previewUrl();
        $this->assertNotNull($url);
        $this->assertStringContainsString('/previews/'.$document->id, $url);
        $this->assertStringNotContainsString('secret.pdf', $url);
        $this->assertStringNotContainsString('private', $url);
    }

    public function test_public_preview_route_returns_image_without_auth(): void
    {
        $document = Document::query()->create([
            'year_folder_id' => $this->year->id,
            'title' => 'MOU',
            'original_filename' => 'mou.pdf',
            'stored_filename' => 'uuid.pdf',
            'private_file_path' => 'kerjasama/secret.pdf',
            'preview_image_path' => 'previews/cover.png',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 10,
            'is_active' => true,
        ]);

        Storage::disk('public')->put('previews/cover.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

        $response = $this->get(route('documents.preview', $document));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function test_public_preview_route_rejects_inactive_document(): void
    {
        $document = Document::query()->create([
            'year_folder_id' => $this->year->id,
            'title' => 'Hidden',
            'original_filename' => 'mou.pdf',
            'stored_filename' => 'uuid.pdf',
            'private_file_path' => 'kerjasama/secret.pdf',
            'preview_image_path' => 'previews/cover.png',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 10,
            'is_active' => false,
        ]);

        Storage::disk('public')->put('previews/cover.png', 'x');

        $this->get(route('documents.preview', $document))->assertNotFound();
    }

    public function test_document_card_renders_preview_img_and_never_exposes_private_path(): void
    {
        $section = $this->year->category->section;
        $category = $this->year->category;

        $document = Document::query()->create([
            'year_folder_id' => $this->year->id,
            'title' => 'MOU Card',
            'original_filename' => 'mou.pdf',
            'stored_filename' => 'uuid.pdf',
            'private_file_path' => 'kerjasama/secret-private.pdf',
            'preview_image_path' => 'previews/cover.png',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 650800,
            'is_active' => true,
        ]);

        Storage::disk('public')->put('previews/cover.png', 'img');

        $html = view('components.document-card', [
            'document' => $document,
            'statusLabel' => 'Minta Akses',
        ])->render();

        $this->assertStringContainsString('src="'.route('documents.preview', $document).'"', $html);
        $this->assertStringContainsString('alt="Preview MOU Card"', $html);
        $this->assertStringNotContainsString('secret-private.pdf', $html);
        $this->assertStringNotContainsString('private_file_path', $html);

        // Keep hierarchy context available for future page assertions.
        $this->assertTrue($section->is_active && $category->is_active);
    }

    public function test_document_card_shows_fallback_when_preview_missing(): void
    {
        $document = Document::query()->create([
            'year_folder_id' => $this->year->id,
            'title' => 'No Preview',
            'original_filename' => 'mou.pdf',
            'stored_filename' => 'uuid.pdf',
            'private_file_path' => 'kerjasama/secret.pdf',
            'preview_image_path' => 'previews/placeholder.png',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 10,
            'is_active' => true,
        ]);

        $document->preview_image_path = '';

        $html = view('components.document-card', [
            'document' => $document,
        ])->render();

        $this->assertStringContainsString('Preview belum tersedia', $html);
        $this->assertStringNotContainsString('<img', $html);
    }
}
