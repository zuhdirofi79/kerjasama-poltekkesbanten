@extends('layouts.admin')
@section('title', 'Repository Folder')
@section('heading', 'Dokumen Kerjasama — File Explorer')

@section('content')
<div class="mb-4 flex flex-wrap items-end justify-between gap-3">
    <form method="GET" class="flex flex-wrap items-end gap-2">
        <div>
            <label class="block text-xs font-medium text-slate-600">Tahun</label>
            <select name="year_folder_id" class="mt-1 rounded-lg border-teal-200 text-sm" onchange="this.form.submit()">
                @foreach($yearFolders as $yf)
                    <option value="{{ $yf->id }}" @selected($yearFolder?->id == $yf->id)>
                        {{ $yf->category?->section?->name }} / {{ $yf->category?->name }} / {{ $yf->year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600">Cari</label>
            <input type="search" name="q" value="{{ $search }}" placeholder="Nama folder / dokumen" class="mt-1 rounded-lg border-teal-200 text-sm">
        </div>
        @if($currentFolder)
            <input type="hidden" name="folder" value="{{ $currentFolder->uuid }}">
        @endif
        <button class="btn-outline-teal !py-2 !text-xs">Cari</button>
    </form>

    @if($yearFolder)
        <div class="flex flex-wrap gap-2">
            <details class="relative">
                <summary class="btn-teal !py-2 !text-xs cursor-pointer list-none">+ Folder Baru</summary>
                <form method="POST" action="{{ route('admin.folders.store') }}" class="absolute right-0 z-20 mt-2 w-80 space-y-3 rounded-xl border border-teal-100 bg-white p-4 shadow-lg">
                    @csrf
                    <input type="hidden" name="year_folder_id" value="{{ $yearFolder->id }}">
                    @if($currentFolder)
                        <input type="hidden" name="parent_id" value="{{ $currentFolder->id }}">
                    @endif
                    <div>
                        <label class="block text-xs font-medium">Nama Folder</label>
                        <input type="text" name="name" required maxlength="255" class="mt-1 w-full rounded-lg border-teal-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium">Deskripsi (opsional)</label>
                        <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-teal-200 text-sm"></textarea>
                    </div>
                    <button class="btn-teal !py-2 !text-xs w-full">Simpan</button>
                </form>
            </details>
            <a
                href="{{ route('admin.documents.create', array_filter(['year_folder_id' => $yearFolder->id, 'document_folder_id' => $currentFolder?->id])) }}"
                class="btn-outline-teal !py-2 !text-xs"
            >+ Upload Dokumen</a>
        </div>
    @endif
</div>

@if($yearFolder)
    <nav class="mb-4 flex flex-wrap items-center gap-1 text-sm text-slate-600">
        <a href="{{ route('admin.folders.index', ['year_folder_id' => $yearFolder->id]) }}" class="font-semibold text-teal-800 hover:underline">
            {{ $yearFolder->category?->name }} / {{ $yearFolder->year }}
        </a>
        @foreach($ancestors as $ancestor)
            <span>/</span>
            <a href="{{ route('admin.folders.index', ['year_folder_id' => $yearFolder->id, 'folder' => $ancestor->uuid]) }}" class="hover:underline">
                {{ $ancestor->name }}
            </a>
        @endforeach
        @if($currentFolder)
            <span>/</span>
            <span class="font-medium text-teal-950">{{ $currentFolder->name }}</span>
        @endif
    </nav>
@endif

<div class="card-surface overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left">
            <tr>
                <th class="px-4 py-2">Nama</th>
                <th class="px-4 py-2">Tipe</th>
                <th class="px-4 py-2">Isi</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($folders as $folder)
            <tr class="border-t align-top">
                <td class="px-4 py-3">
                    <a href="{{ route('admin.folders.index', ['year_folder_id' => $yearFolder->id, 'folder' => $folder->uuid]) }}" class="font-semibold text-teal-900 hover:underline">
                        📁 {{ $folder->name }}
                    </a>
                    @if($folder->description)
                        <p class="mt-0.5 text-xs text-slate-500">{{ $folder->description }}</p>
                    @endif
                </td>
                <td class="px-4 py-3">Folder</td>
                <td class="px-4 py-3">{{ $folder->children_count }} folder · {{ $folder->documents_count }} dokumen</td>
                <td class="px-4 py-3 text-right">
                    <details class="relative inline-block text-left">
                        <summary class="cursor-pointer text-teal-700 hover:underline">⋮</summary>
                        <div class="absolute right-0 z-10 mt-1 w-56 space-y-2 rounded-lg border bg-white p-3 shadow-lg">
                            <a href="{{ route('admin.folders.index', ['year_folder_id' => $yearFolder->id, 'folder' => $folder->uuid]) }}" class="block text-sm hover:underline">Buka</a>

                            <form method="POST" action="{{ route('admin.folders.update', $folder) }}" class="space-y-2 border-t pt-2">
                                @csrf @method('PUT')
                                <label class="block text-xs font-medium">Ubah nama</label>
                                <input type="text" name="name" value="{{ $folder->name }}" required class="w-full rounded border-teal-200 text-xs">
                                <textarea name="description" rows="2" class="w-full rounded border-teal-200 text-xs">{{ $folder->description }}</textarea>
                                <button class="text-xs font-semibold text-teal-700 hover:underline">Simpan</button>
                            </form>

                            <form method="POST" action="{{ route('admin.folders.move', $folder) }}" class="space-y-2 border-t pt-2">
                                @csrf
                                <label class="block text-xs font-medium">Pindahkan ke</label>
                                <select name="parent_id" class="w-full rounded border-teal-200 text-xs">
                                    <option value="">— Root tahun —</option>
                                    @foreach($moveTargets as $target)
                                        @if($target->id !== $folder->id)
                                            <option value="{{ $target->id }}">{{ $target->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button class="text-xs font-semibold text-teal-700 hover:underline">Pindahkan</button>
                            </form>

                            <div class="flex gap-3 border-t pt-2">
                                <form method="POST" action="{{ route('admin.folders.reorder', $folder) }}">@csrf<input type="hidden" name="direction" value="up"><button class="text-xs hover:underline">Naik</button></form>
                                <form method="POST" action="{{ route('admin.folders.reorder', $folder) }}">@csrf<input type="hidden" name="direction" value="down"><button class="text-xs hover:underline">Turun</button></form>
                            </div>

                            <form method="POST" action="{{ route('admin.folders.destroy', $folder) }}" class="border-t pt-2" onsubmit="return confirm('Arsipkan folder ini?')">
                                @csrf @method('DELETE')
                                @if($folder->children_count > 0 || $folder->documents_count > 0)
                                    <label class="mb-1 flex items-start gap-2 text-xs text-red-700">
                                        <input type="checkbox" name="confirm_non_empty" value="1" required>
                                        Folder berisi {{ $folder->children_count }} folder dan {{ $folder->documents_count }} dokumen. Konfirmasi hapus.
                                    </label>
                                @endif
                                <button class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </details>
                </td>
            </tr>
        @empty
            @if($documents->isEmpty())
                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Folder masih kosong.</td></tr>
            @endif
        @endforelse

        @foreach($documents as $document)
            <tr class="border-t align-top">
                <td class="px-4 py-3">
                    <span class="font-semibold text-slate-800">📄 {{ $document->title }}</span>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $document->original_filename }} · {{ strtoupper($document->extension) }}</p>
                </td>
                <td class="px-4 py-3">Dokumen</td>
                <td class="px-4 py-3">{{ $document->formattedSize() }}</td>
                <td class="px-4 py-3 text-right">
                    <details class="relative inline-block text-left">
                        <summary class="cursor-pointer text-teal-700 hover:underline">⋮</summary>
                        <div class="absolute right-0 z-10 mt-1 w-56 space-y-2 rounded-lg border bg-white p-3 shadow-lg">
                            <a href="{{ route('admin.documents.edit', $document) }}" class="block text-sm hover:underline">Edit metadata</a>
                            <form method="POST" action="{{ route('admin.documents.move', $document) }}" class="space-y-2 border-t pt-2">
                                @csrf
                                <label class="block text-xs font-medium">Pindahkan ke folder</label>
                                <select name="document_folder_id" class="w-full rounded border-teal-200 text-xs">
                                    <option value="">— Root tahun —</option>
                                    @foreach($moveTargets as $target)
                                        <option value="{{ $target->id }}" @selected($document->document_folder_id == $target->id)>{{ $target->name }}</option>
                                    @endforeach
                                </select>
                                <button class="text-xs font-semibold text-teal-700 hover:underline">Pindahkan</button>
                            </form>
                            <form method="POST" action="{{ route('admin.documents.destroy', $document) }}" class="border-t pt-2" onsubmit="return confirm('Arsipkan dokumen?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </details>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
