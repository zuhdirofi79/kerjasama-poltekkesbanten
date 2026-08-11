<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveDocumentFolderRequest;
use App\Http\Requests\Admin\StoreDocumentFolderRequest;
use App\Http\Requests\Admin\UpdateDocumentFolderRequest;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\YearFolder;
use App\Services\DocumentFolderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DocumentFolderController extends Controller
{
    public function __construct(
        private readonly DocumentFolderService $documentFolderService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DocumentFolder::class);

        $yearFolders = YearFolder::query()
            ->with('category.section')
            ->ordered()
            ->get();

        $selectedYearFolderId = $request->integer('year_folder_id') ?: ($yearFolders->first()?->id);

        $yearFolder = $selectedYearFolderId
            ? YearFolder::query()->with('category.section')->find($selectedYearFolderId)
            : null;

        $currentFolder = null;
        if ($request->filled('folder')) {
            $currentFolder = DocumentFolder::query()
                ->where('uuid', $request->string('folder'))
                ->firstOrFail();
            abort_unless($yearFolder && $currentFolder->year_folder_id === $yearFolder->id, 404);
        }

        $search = trim((string) $request->string('q'));

        $foldersQuery = DocumentFolder::query()
            ->when($yearFolder, fn ($q) => $q->where('year_folder_id', $yearFolder->id))
            ->when($currentFolder, fn ($q) => $q->where('parent_id', $currentFolder->id))
            ->when(! $currentFolder && $yearFolder, fn ($q) => $q->whereNull('parent_id'))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->ordered()
            ->withCount(['children', 'documents']);

        $folders = $foldersQuery->get();

        $documents = collect();
        if ($yearFolder) {
            $documents = Document::query()
                ->with('uploader')
                ->where('year_folder_id', $yearFolder->id)
                ->when($currentFolder, fn ($q) => $q->where('document_folder_id', $currentFolder->id))
                ->when(! $currentFolder, fn ($q) => $q->whereNull('document_folder_id'))
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($inner) use ($search) {
                        $inner->where('title', 'like', '%'.$search.'%')
                            ->orWhere('original_filename', 'like', '%'.$search.'%');
                    });
                })
                ->orderBy('title')
                ->get();
        }

        $ancestors = $currentFolder
            ? $currentFolder->loadMissing('parent.parent.parent.parent.parent')->ancestorChain()
            : [];

        $moveTargets = $yearFolder
            ? DocumentFolder::query()
                ->where('year_folder_id', $yearFolder->id)
                ->ordered()
                ->get()
            : collect();

        return view('admin.folders.index', compact(
            'yearFolders',
            'yearFolder',
            'currentFolder',
            'folders',
            'documents',
            'ancestors',
            'moveTargets',
            'search',
        ));
    }

    public function store(StoreDocumentFolderRequest $request): RedirectResponse
    {
        $yearFolder = YearFolder::query()->findOrFail($request->integer('year_folder_id'));
        $parent = $request->filled('parent_id')
            ? DocumentFolder::query()->findOrFail($request->integer('parent_id'))
            : null;

        try {
            $folder = $this->documentFolderService->createFolder(
                $yearFolder,
                $request->validated('name'),
                $parent,
                $request->validated('description') ?? null,
                $request->user()->id,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['name' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.folders.index', array_filter([
                'year_folder_id' => $yearFolder->id,
                'folder' => $parent?->uuid,
            ]))
            ->with('status', "Folder \"{$folder->name}\" berhasil dibuat.");
    }

    public function update(UpdateDocumentFolderRequest $request, DocumentFolder $folder): RedirectResponse
    {
        try {
            $this->documentFolderService->renameFolder(
                $folder,
                $request->validated('name'),
                $request->validated('description') ?? null,
                $request->user()->id,
            );

            if ($request->has('is_active')) {
                $folder->update([
                    'is_active' => $request->boolean('is_active'),
                    'updated_by' => $request->user()->id,
                ]);
            }
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['name' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Folder diperbarui.');
    }

    public function move(MoveDocumentFolderRequest $request, DocumentFolder $folder): RedirectResponse
    {
        $destination = $request->filled('parent_id')
            ? DocumentFolder::query()->findOrFail($request->integer('parent_id'))
            : null;

        try {
            $this->documentFolderService->moveFolder($folder, $destination, $request->user()->id);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['parent_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.folders.index', array_filter([
                'year_folder_id' => $folder->year_folder_id,
                'folder' => $destination?->uuid,
            ]))
            ->with('status', 'Folder dipindahkan.');
    }

    public function reorder(Request $request, DocumentFolder $folder): RedirectResponse
    {
        $this->authorize('update', $folder);

        $direction = $request->string('direction')->toString();
        if (! in_array($direction, ['up', 'down'], true)) {
            return back()->withErrors(['direction' => 'Arah urutan tidak valid.']);
        }

        $this->documentFolderService->reorder($folder, $direction, $request->user()->id);

        return back()->with('status', 'Urutan folder diperbarui.');
    }

    public function destroy(Request $request, DocumentFolder $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);

        $parentUuid = $folder->parent?->uuid;
        $yearFolderId = $folder->year_folder_id;

        try {
            $this->documentFolderService->deleteFolder(
                $folder,
                $request->boolean('confirm_non_empty'),
                $request->user()->id,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.folders.index', array_filter([
                'year_folder_id' => $yearFolderId,
                'folder' => $parentUuid,
            ]))
            ->with('status', 'Folder diarsipkan (soft delete).');
    }
}
