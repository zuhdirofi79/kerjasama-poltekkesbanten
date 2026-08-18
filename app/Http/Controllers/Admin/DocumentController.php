<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveDocumentRequest;
use App\Http\Requests\Admin\StoreDocumentRequest;
use App\Http\Requests\Admin\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\YearFolder;
use App\Services\DocumentFolderService;
use App\Services\DocumentSearchQuery;
use App\Services\DocumentStorageService;
use App\Support\CooperationTaxonomy;
use App\Support\DocumentFolderOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $documentStorageService,
        private readonly DocumentFolderService $documentFolderService,
        private readonly DocumentSearchQuery $documentSearchQuery,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);

        $filters = $this->documentSearchQuery->filtersFromRequest($request);

        $documents = $this->documentSearchQuery
            ->apply(
                Document::query()->with(['yearFolder.category.section', 'documentFolder', 'uploader']),
                $filters,
            )
            ->when($request->integer('year_folder_id'), fn ($q, $id) => $q->where('year_folder_id', $id))
            ->when($request->integer('document_folder_id'), fn ($q, $id) => $q->where('document_folder_id', $id))
            ->paginate(25)
            ->withQueryString();

        $yearFolders = YearFolder::query()->with('category')->ordered()->get();

        return view('admin.documents.index', [
            'documents' => $documents,
            'yearFolders' => $yearFolders,
            'filters' => $filters,
            'areas' => CooperationTaxonomy::areas(),
            'subtypes' => CooperationTaxonomy::subtypes(),
            'sorts' => CooperationTaxonomy::sorts(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Document::class);

        $yearFolders = YearFolder::query()->with('category.section')->ordered()->get();
        $selectedYearFolderId = $request->integer('year_folder_id')
            ?: ($yearFolders->first()?->id);

        $selectedFolderId = $request->integer('document_folder_id') ?: null;
        $folderOptions = $selectedYearFolderId
            ? DocumentFolderOptions::forYear($selectedYearFolderId)
            : [];

        if ($selectedFolderId && ! collect($folderOptions)->contains(fn ($opt) => $opt['id'] === $selectedFolderId)) {
            $selectedFolderId = null;
        }

        return view('admin.documents.create', compact(
            'yearFolders',
            'folderOptions',
            'selectedYearFolderId',
            'selectedFolderId',
        ) + [
            'areas' => CooperationTaxonomy::areas(),
            'subtypes' => CooperationTaxonomy::subtypes(),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $yearFolder = YearFolder::query()->findOrFail($request->integer('year_folder_id'));
        $folder = $request->filled('document_folder_id')
            ? DocumentFolder::query()
                ->whereKey($request->integer('document_folder_id'))
                ->where('year_folder_id', $yearFolder->id)
                ->active()
                ->firstOrFail()
            : null;

        try {
            $document = $this->documentStorageService->store(
                $request->file('document'),
                $request->file('preview'),
                $yearFolder,
                $folder,
                [
                    'title' => $request->validated('title'),
                    'description' => $request->validated('description') ?? null,
                    'is_active' => $request->boolean('is_active', true),
                    'document_date' => $request->validated('document_date') ?? null,
                    'cooperation_area' => $request->validated('cooperation_area') ?? null,
                    'cooperation_subtype' => $request->validated('cooperation_subtype') ?? null,
                    'cooperation_start_date' => $request->validated('cooperation_start_date') ?? null,
                    'cooperation_end_date' => $request->validated('cooperation_end_date') ?? null,
                ],
                $request->user()->id,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['document' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.folders.index', array_filter([
                'year_folder_id' => $yearFolder->id,
                'folder' => $folder?->uuid,
            ]))
            ->with('status', "Dokumen \"{$document->title}\" berhasil diunggah.");
    }

    public function edit(Document $document): View
    {
        $this->authorize('update', $document);

        $document->load(['yearFolder.category.section', 'documentFolder']);
        $folderOptions = DocumentFolderOptions::forYear($document->year_folder_id);

        return view('admin.documents.edit', compact('document', 'folderOptions') + [
            'areas' => CooperationTaxonomy::areas(),
            'subtypes' => CooperationTaxonomy::subtypes(),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = collect($request->validated())->only([
            'title',
            'description',
            'document_folder_id',
            'document_date',
            'cooperation_area',
            'cooperation_subtype',
            'cooperation_start_date',
            'cooperation_end_date',
        ])->all();

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if (array_key_exists('document_folder_id', $data) && $data['document_folder_id']) {
            $folder = DocumentFolder::query()
                ->whereKey($data['document_folder_id'])
                ->where('year_folder_id', $document->year_folder_id)
                ->active()
                ->firstOrFail();
            $data['document_folder_id'] = $folder->id;
            $data['institution_folder_id'] = $folder->legacy_institution_folder_id;
        } elseif (array_key_exists('document_folder_id', $data) && ! $data['document_folder_id']) {
            $data['document_folder_id'] = null;
            $data['institution_folder_id'] = null;
        }

        $document->update($data);

        try {
            if ($request->hasFile('document')) {
                $this->documentStorageService->replaceFile($document, $request->file('document'));
            }
            if ($request->hasFile('preview')) {
                $this->documentStorageService->replacePreview($document, $request->file('preview'));
            }
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['document' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.documents.index')
            ->with('status', 'Dokumen diperbarui.');
    }

    public function move(MoveDocumentRequest $request, Document $document): RedirectResponse
    {
        $folder = null;
        if ($request->filled('document_folder_id')) {
            $folder = DocumentFolder::query()
                ->whereKey($request->integer('document_folder_id'))
                ->where('year_folder_id', $document->year_folder_id)
                ->active()
                ->firstOrFail();
        }

        try {
            $this->documentFolderService->moveDocument($document, $folder, $request->user()->id);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['document_folder_id' => $e->getMessage()]);
        }

        return back()->with('status', 'Dokumen dipindahkan.');
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        if ($request->boolean('force')) {
            $this->authorize('forceDelete', $document);
            $this->documentStorageService->forceDelete($document);

            return redirect()->route('admin.documents.index')->with('status', 'Dokumen dihapus permanen.');
        }

        $this->documentStorageService->softDelete($document);

        return redirect()->route('admin.documents.index')->with('status', 'Dokumen diarsipkan.');
    }
}
