<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreYearFolderRequest;
use App\Http\Requests\Admin\UpdateYearFolderRequest;
use App\Models\CooperationCategory;
use App\Models\YearFolder;
use App\Services\AuditLogService;
use App\Services\DocumentFolderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class YearFolderController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly DocumentFolderService $documentFolderService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', YearFolder::class);

        $years = YearFolder::query()
            ->with('category.section')
            ->when($request->integer('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        $categories = CooperationCategory::query()
            ->with('section')
            ->orderBy('sort_order')
            ->get();

        return view('admin.years.index', compact('years', 'categories'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', YearFolder::class);

        $categories = CooperationCategory::query()->with('section')->orderBy('sort_order')->get();
        $selectedCategoryId = $request->integer('category_id') ?: null;

        return view('admin.years.create', compact('categories', 'selectedCategoryId'));
    }

    public function store(StoreYearFolderRequest $request): RedirectResponse
    {
        $category = CooperationCategory::query()->findOrFail($request->integer('category_id'));
        $year = (int) $request->validated('year');

        $folder = DB::transaction(function () use ($request, $category, $year) {
            $folder = YearFolder::query()->create([
                'category_id' => $category->id,
                'year' => $year,
                'slug' => (string) $year,
                'description' => $request->validated('description') ?? null,
                'sort_order' => $request->validated('sort_order') ?? 0,
                'is_active' => $request->boolean('is_active', true),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->documentFolderService->provisionDefaultFolders($folder, $request->user()->id);

            return $folder;
        });

        $this->auditLogService->log('year_folder.created', $folder);

        return redirect()
            ->route('admin.years.index', ['category_id' => $category->id])
            ->with('status', 'Folder tahun berhasil dibuat beserta folder default.');
    }

    public function edit(YearFolder $yearFolder): View
    {
        $this->authorize('update', $yearFolder);

        $yearFolder->load('category.section');

        return view('admin.years.edit', compact('yearFolder'));
    }

    public function update(UpdateYearFolderRequest $request, YearFolder $yearFolder): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['year'])) {
            $data['slug'] = (string) $data['year'];
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $data['updated_by'] = $request->user()->id;

        $old = $yearFolder->only(['year', 'description', 'is_active', 'sort_order']);
        $yearFolder->update($data);

        $this->auditLogService->log(
            'year_folder.updated',
            $yearFolder,
            $old,
            $yearFolder->only(['year', 'description', 'is_active', 'sort_order']),
        );

        return redirect()
            ->route('admin.years.index', ['category_id' => $yearFolder->category_id])
            ->with('status', 'Folder tahun diperbarui.');
    }

    public function destroy(Request $request, YearFolder $yearFolder): RedirectResponse
    {
        $this->authorize('delete', $yearFolder);

        $hasChildren = $yearFolder->institutions()->exists()
            || $yearFolder->documentFolders()->exists()
            || $yearFolder->documents()->exists();

        if ($request->boolean('force')) {
            $this->authorize('forceDelete', $yearFolder);

            if ($hasChildren && ! $request->boolean('confirm_children')) {
                return back()->withErrors([
                    'force' => 'Folder masih memiliki isi. Centang konfirmasi untuk force-delete.',
                ]);
            }

            $id = $yearFolder->id;
            $yearFolder->forceDelete();
            $this->auditLogService->log('year_folder.force_deleted', null, ['id' => $id]);

            return redirect()->route('admin.years.index')->with('status', 'Folder tahun dihapus permanen.');
        }

        $yearFolder->delete();
        $this->auditLogService->log('year_folder.soft_deleted', $yearFolder);

        return redirect()->route('admin.years.index')->with('status', 'Folder tahun diarsipkan (soft delete).');
    }
}
