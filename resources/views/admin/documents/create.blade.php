@extends('layouts.admin')
@section('title', 'Unggah Dokumen')
@section('heading', 'Unggah Dokumen')
@section('content')
@php
    $initialFolders = collect($folderOptions)->map(fn ($opt) => [
        'id' => $opt['id'],
        'label' => $opt['label'],
    ])->values();
@endphp

<form
    method="POST"
    action="{{ route('admin.documents.store') }}"
    enctype="multipart/form-data"
    class="card-surface max-w-2xl space-y-4 p-5"
    x-data="documentUploadForm({
        yearFolderId: @js(old('year_folder_id', $selectedYearFolderId)),
        folderId: @js(old('document_folder_id', $selectedFolderId)),
        folders: @js($initialFolders),
        endpointTemplate: @js(route('admin.years.document-folders', ['yearFolder' => '__YEAR__'])),
    })"
>
    @csrf
    <div>
        <label class="block text-sm font-medium">Folder Tahun</label>
        <select
            name="year_folder_id"
            required
            class="mt-1 w-full rounded-lg border-teal-200"
            x-model="yearFolderId"
            @change="onYearChange()"
        >
            @foreach($yearFolders as $yf)
                <option value="{{ $yf->id }}">
                    {{ $yf->category?->section?->name }} / {{ $yf->category?->name }} / {{ $yf->year }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium">Folder (opsional)</label>
        <select
            name="document_folder_id"
            class="mt-1 w-full rounded-lg border-teal-200"
            x-model="folderId"
            :disabled="!yearFolderId || loading"
        >
            <option value="">— Tingkat tahun (root) —</option>
            <template x-for="folder in folders" :key="folder.id">
                <option :value="folder.id" x-text="folder.label"></option>
            </template>
        </select>
        <p class="mt-1 text-xs text-slate-500" x-show="!loading && yearFolderId && folders.length === 0">
            Belum ada subfolder. Dokumen akan disimpan di tingkat tahun.
        </p>
        <p class="mt-1 text-xs text-slate-500" x-show="loading">Memuat folder…</p>
        <p class="mt-1 text-xs text-red-600" x-show="error" x-text="error"></p>
    </div>
    <div>
        <label class="block text-sm font-medium">Judul</label>
        <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-teal-200">{{ old('description') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium">File Dokumen (asli — privat)</label>
        <input type="file" name="document" required class="mt-1 w-full text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium">Preview gambar (wajib — publik)</label>
        <input type="file" name="preview" accept="image/jpeg,image/png,image/webp" required class="mt-1 w-full text-sm">
        <p class="mt-1 text-xs text-slate-500">Hanya thumbnail yang dipublikasikan. File asli tidak pernah di-link ke /storage.</p>
    </div>
    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Aktif / publish</label>
    @include('admin.documents.partials.metadata-fields', ['document' => null, 'areas' => $areas, 'subtypes' => $subtypes])
    <button class="btn-teal">Unggah</button>
</form>
@endsection
