<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInstitutionFolderRequest;
use App\Http\Requests\Admin\UpdateInstitutionFolderRequest;
use App\Models\InstitutionFolder;
use App\Models\YearFolder;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstitutionFolderController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InstitutionFolder::class);

        $institutions = InstitutionFolder::query()
            ->with('yearFolder.category.section')
            ->when($request->integer('year_folder_id'), fn ($q, $id) => $q->where('year_folder_id', $id))
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        $yearFolders = YearFolder::query()->with('category.section')->ordered()->get();

        return view('admin.institutions.index', compact('institutions', 'yearFolders'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', InstitutionFolder::class);

        $yearFolders = YearFolder::query()->with('category.section')->ordered()->get();
        $selectedYearFolderId = $request->integer('year_folder_id') ?: null;

        return view('admin.institutions.create', compact('yearFolders', 'selectedYearFolderId'));
    }

    public function store(StoreInstitutionFolderRequest $request): RedirectResponse
    {
        $yearFolder = YearFolder::query()->findOrFail($request->integer('year_folder_id'));
        $name = $request->validated('name');
        $slug = $this->uniqueSlug($yearFolder->id, Str::slug($name));

        $institution = InstitutionFolder::query()->create([
            'year_folder_id' => $yearFolder->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $request->validated('description') ?? null,
            'sort_order' => $request->validated('sort_order') ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->auditLogService->log('institution_folder.created', $institution);

        return redirect()
            ->route('admin.institutions.index', ['year_folder_id' => $yearFolder->id])
            ->with('status', 'Folder institusi berhasil dibuat.');
    }

    public function edit(InstitutionFolder $institution): View
    {
        $this->authorize('update', $institution);

        $institution->load('yearFolder.category.section');

        return view('admin.institutions.edit', compact('institution'));
    }

    public function update(UpdateInstitutionFolderRequest $request, InstitutionFolder $institution): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['name'])) {
            $data['slug'] = $this->uniqueSlug($institution->year_folder_id, Str::slug($data['name']), $institution->id);
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $data['updated_by'] = $request->user()->id;

        $old = $institution->only(['name', 'description', 'is_active', 'sort_order']);
        $institution->update($data);

        $this->auditLogService->log(
            'institution_folder.updated',
            $institution,
            $old,
            $institution->only(['name', 'description', 'is_active', 'sort_order']),
        );

        return redirect()
            ->route('admin.institutions.index', ['year_folder_id' => $institution->year_folder_id])
            ->with('status', 'Folder institusi diperbarui.');
    }

    public function destroy(Request $request, InstitutionFolder $institution): RedirectResponse
    {
        $this->authorize('delete', $institution);

        $hasChildren = $institution->documents()->exists();

        if ($request->boolean('force')) {
            $this->authorize('forceDelete', $institution);

            if ($hasChildren && ! $request->boolean('confirm_children')) {
                return back()->withErrors([
                    'force' => 'Folder masih memiliki dokumen. Centang konfirmasi untuk force-delete.',
                ]);
            }

            $id = $institution->id;
            $institution->forceDelete();
            $this->auditLogService->log('institution_folder.force_deleted', null, ['id' => $id]);

            return redirect()->route('admin.institutions.index')->with('status', 'Folder institusi dihapus permanen.');
        }

        $institution->delete();
        $this->auditLogService->log('institution_folder.soft_deleted', $institution);

        return redirect()->route('admin.institutions.index')->with('status', 'Folder institusi diarsipkan.');
    }

    private function uniqueSlug(int $yearFolderId, string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'institusi';
        $candidate = $slug;
        $i = 2;

        while (
            InstitutionFolder::query()
                ->where('year_folder_id', $yearFolderId)
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
