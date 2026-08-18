@extends('layouts.admin')
@section('title', 'Edit Dokumen')
@section('heading', 'Edit Dokumen')
@section('content')
<form method="POST" action="{{ route('admin.documents.update', $document) }}" enctype="multipart/form-data" class="card-surface max-w-2xl space-y-4 p-5">
    @csrf @method('PUT')
    @if($document->previewUrl())
        <img src="{{ $document->previewUrl() }}" alt="" class="h-40 rounded-lg object-cover">
    @endif
    <div>
        <p class="text-sm text-slate-600">
            Tahun:
            <span class="font-semibold text-teal-900">
                {{ $document->yearFolder?->category?->section?->name }}
                / {{ $document->yearFolder?->category?->name }}
                / {{ $document->yearFolder?->year }}
            </span>
        </p>
    </div>
    <div>
        <label class="block text-sm font-medium">Judul</label>
        <input type="text" name="title" value="{{ old('title', $document->title) }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-teal-200">{{ old('description', $document->description) }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium">Folder</label>
        <select name="document_folder_id" class="mt-1 w-full rounded-lg border-teal-200">
            <option value="">— Tingkat tahun (root) —</option>
            @foreach($folderOptions as $folder)
                <option value="{{ $folder['id'] }}" @selected(old('document_folder_id', $document->document_folder_id) == $folder['id'])>
                    {{ $folder['label'] }}
                </option>
            @endforeach
        </select>
        @if(count($folderOptions) === 0)
            <p class="mt-1 text-xs text-slate-500">Belum ada subfolder. Dokumen akan disimpan di tingkat tahun.</p>
        @endif
    </div>
    <div>
        <label class="block text-sm font-medium">Ganti file dokumen (opsional)</label>
        <input type="file" name="document" class="mt-1 w-full text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium">Ganti preview (opsional)</label>
        <input type="file" name="preview" accept="image/jpeg,image/png,image/webp" class="mt-1 w-full text-sm">
    </div>
    <label class="inline-flex items-center gap-2 text-sm">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $document->is_active))> Aktif
    </label>
    @include('admin.documents.partials.metadata-fields', ['document' => $document, 'areas' => $areas, 'subtypes' => $subtypes])
    <button class="btn-teal">Perbarui</button>
</form>
@endsection
