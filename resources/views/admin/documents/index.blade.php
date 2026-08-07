@extends('layouts.admin')
@section('title', 'Dokumen')
@section('heading', 'Dokumen')
@section('content')
<div class="mb-4 flex justify-between">
    <form method="GET" class="flex gap-2">
        <select name="year_folder_id" class="rounded-lg border-teal-200 text-sm">
            <option value="">Semua tahun</option>
            @foreach($yearFolders as $yf)
                <option value="{{ $yf->id }}" @selected(request('year_folder_id') == $yf->id)>{{ $yf->category?->name }} — {{ $yf->year }}</option>
            @endforeach
        </select>
        <button class="btn-outline-teal !py-2 !text-xs">Filter</button>
    </form>
    <a href="{{ route('admin.documents.create') }}" class="btn-teal !py-2 !text-xs">Unggah Dokumen</a>
</div>
<div class="card-surface overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left"><tr>
            <th class="px-4 py-2">Preview</th><th class="px-4 py-2">Judul</th><th class="px-4 py-2">Lokasi</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th>
        </tr></thead>
        <tbody>
        @foreach($documents as $document)
            <tr class="border-t">
                <td class="px-4 py-2">
                    @if($document->previewUrl())
                        <img src="{{ $document->previewUrl() }}" alt="" class="h-12 w-16 rounded object-cover">
                    @endif
                </td>
                <td class="px-4 py-2 font-semibold">{{ $document->title }}</td>
                <td class="px-4 py-2 text-xs">
                    {{ $document->yearFolder?->category?->name }} / {{ $document->yearFolder?->year }}
                    @if($document->institutionFolder) / {{ $document->institutionFolder->name }} @endif
                </td>
                <td class="px-4 py-2">{{ $document->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td class="px-4 py-2 text-right space-x-2">
                    <a href="{{ route('documents.view', $document) }}" class="text-teal-700 hover:underline" target="_blank">Lihat</a>
                    <a href="{{ route('admin.documents.edit', $document) }}" class="text-teal-700 hover:underline">Edit</a>
                    <form action="{{ route('admin.documents.destroy', $document) }}" method="POST" class="inline" onsubmit="return confirm('Arsipkan dokumen?')">@csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $documents->links() }}</div>
@endsection
