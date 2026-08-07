<?php

namespace Tests\Feature;

use App\Enums\AccessRequestStatus;
use App\Enums\UserRole;
use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\Document;
use App\Models\DocumentAccessRequest;
use App\Models\User;
use App\Models\YearFolder;
use App\Enums\SectionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Document $document;

    private User $visitor;

    private User $otherVisitor;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('private_docs');
        Storage::fake('public');

        $section = CooperationSection::query()->create([
            'name' => 'Dalam Negeri',
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

        Storage::disk('private_docs')->put('kerjasama/test.pdf', 'secret-pdf-content');
        Storage::disk('public')->put('previews/test.jpg', 'preview');

        $this->document = Document::query()->create([
            'year_folder_id' => $year->id,
            'title' => 'MoU Test',
            'original_filename' => 'mou.pdf',
            'stored_filename' => 'uuid.pdf',
            'private_file_path' => 'kerjasama/test.pdf',
            'preview_image_path' => 'previews/test.jpg',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => 12,
            'is_active' => true,
        ]);

        $this->visitor = User::factory()->visitor()->create();
        $this->otherVisitor = User::factory()->visitor()->create();
    }

    public function test_guest_is_redirected_from_document_view(): void
    {
        $this->get(route('documents.view', $this->document))
            ->assertRedirect(route('login'));
    }

    public function test_pending_request_cannot_view_document(): void
    {
        DocumentAccessRequest::query()->create([
            'document_id' => $this->document->id,
            'user_id' => $this->visitor->id,
            'status' => AccessRequestStatus::Pending,
            'requested_at' => now(),
        ]);

        $this->actingAs($this->visitor)
            ->get(route('documents.view', $this->document))
            ->assertForbidden();
    }

    public function test_approved_visitor_can_view_document(): void
    {
        DocumentAccessRequest::query()->create([
            'document_id' => $this->document->id,
            'user_id' => $this->visitor->id,
            'status' => AccessRequestStatus::Approved,
            'requested_at' => now(),
            'reviewed_at' => now(),
        ]);

        $this->actingAs($this->visitor)
            ->get(route('documents.view', $this->document))
            ->assertOk();
    }

    public function test_idor_other_user_cannot_view_even_if_peer_approved(): void
    {
        DocumentAccessRequest::query()->create([
            'document_id' => $this->document->id,
            'user_id' => $this->visitor->id,
            'status' => AccessRequestStatus::Approved,
            'requested_at' => now(),
            'reviewed_at' => now(),
        ]);

        $this->actingAs($this->otherVisitor)
            ->get(route('documents.view', $this->document))
            ->assertForbidden();
    }

    public function test_duplicate_pending_request_is_rejected(): void
    {
        DocumentAccessRequest::query()->create([
            'document_id' => $this->document->id,
            'user_id' => $this->visitor->id,
            'status' => AccessRequestStatus::Pending,
            'requested_at' => now(),
        ]);

        $this->actingAs($this->visitor)
            ->post(route('documents.request-access', $this->document), [
                'request_message' => 'Tolong',
            ])
            ->assertSessionHasErrors('document');
    }

    public function test_private_file_path_is_never_exposed_in_json_or_public_page(): void
    {
        $this->assertArrayNotHasKey('private_file_path', $this->document->toArray());

        $this->get(route('home'))->assertOk()->assertDontSee('kerjasama/test.pdf');
    }
}
