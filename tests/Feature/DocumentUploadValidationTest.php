<?php

namespace Tests\Feature;

use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\User;
use App\Models\YearFolder;
use App\Enums\SectionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_php_upload_is_rejected(): void
    {
        Storage::fake('private_docs');
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        $section = CooperationSection::query()->create([
            'name' => 'Dalam Negeri',
            'slug' => 'dalam-negeri',
            'type' => SectionType::Domestic,
            'is_active' => true,
        ]);
        $category = CooperationCategory::query()->create([
            'section_id' => $section->id,
            'name' => 'RS',
            'slug' => 'rs',
            'is_active' => true,
        ]);
        $year = YearFolder::query()->create([
            'category_id' => $category->id,
            'year' => 2026,
            'slug' => '2026',
            'is_active' => true,
        ]);

        $phpFile = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');
        $preview = UploadedFile::fake()->image('preview.jpg');

        $response = $this->actingAs($admin)->post(route('admin.documents.store'), [
            'year_folder_id' => $year->id,
            'title' => 'Malicious',
            'document' => $phpFile,
            'preview' => $preview,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('documents', ['title' => 'Malicious']);
    }
}
