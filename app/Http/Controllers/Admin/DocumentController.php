<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentRequest;
use App\Http\Requests\Admin\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\InstitutionFolder;
use App\Models\YearFolder;
use App\Services\DocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $documentStorageService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);

        $documents = Document::query()
            ->with(['yearFolder.category.section', 'institutionFolder', 'uploader'])
            ->when($request->integer('year_folder_id'), fn ($q, $id) => $q->where('year_folder_id', $id))
            ->when($request->integer('institution_folder_id'), fn ($q, $id) => $q->where('institution_folder_id', $id))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $yearFolders = YearFolder::query()->with('category')->ordered()->get();

        return view('admin.documents.index', compact('documents', 'yearFolders'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Document::class);

        $yearFolders = YearFolder::query()->with('category.section')->ordered()->get();
        $institutions = InstitutionFolder::query()->ordered()->get();
        $selectedYearFolderId = $request->integer('year_folder_id') ?: null;

        return view('admin.documents.create', compact('yearFolders', 'institutions', 'selectedYearFolderId'));
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $yearFolder = YearFolder::query()->findOrFail($request->integer('year_folder_id'));
        $institution = $request->filled('institution_folder_id')
            ? InstitutionFolder::query()->findOrFail($request->integer('institution_folder_id'))
            : null;

        if ($institution && $institution->year_folder_id !== $yearFolder->id) {
            return back()->withErrors(['institution_folder_id' => 'Institusi tidak cocok dengan folder tahun.'])->withInput();
        }

        try {
            $document = $this->documentStorageService->store(
                $request->file('document'),
                $request->file('preview'),
                $yearFolder,
                $institution,
                [
                    'title' => $request->validated('title'),
                    'description' => $request->validated('description') ?? null,
                    'is_active' => $request->boolean('is_active', true),
                ],
                $request->user()->id,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['document' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.documents.index')
            ->with('status', "Dokumen \"{$document->title}\" berhasil diunggah.");
    }

    public function edit(Document $document): View
    {
        $this->authorize('update', $document);

        $document->load(['yearFolder.category.section', 'institutionFolder']);
        $institutions = InstitutionFolder::query()
            ->where('year_folder_id', $document->year_folder_id)
            ->ordered()
            ->get();

        return view('admin.documents.edit', compact('document', 'institutions'));
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = collect($request->validated())->only(['title', 'description', 'institution_folder_id'])->all();

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if (array_key_exists('institution_folder_id', $data) && $data['institution_folder_id']) {
            $institution = InstitutionFolder::query()->findOrFail($data['institution_folder_id']);
            if ($institution->year_folder_id !== $document->year_folder_id) {
                return back()->withErrors(['institution_folder_id' => 'Institusi tidak cocok dengan folder tahun.'])->withInput();
            }
        }

        $document->update($data);

        try {
            if ($request->hasFile('document')) {
                $this->documentStorageService->replaceFile($document, $request->file('document'));
            }
            if ($request->hasFile('preview')) {
                $this->documentStorageService->replacePreview($document, $request->file('preview'));
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['document' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.documents.index')
            ->with('status', 'Dokumen diperbarui.');
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
