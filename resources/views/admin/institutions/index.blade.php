@extends('layouts.admin')
@section('title', 'Institusi')
@section('heading', 'Folder Institusi')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="flex gap-2">
        <select name="year_folder_id" class="rounded-lg border-teal-200 text-sm">
            <option value="">Semua tahun</option>
            @foreach($yearFolders as $yf)
                <option value="{{ $yf->id }}" @selected(request('year_folder_id') == $yf->id)>
                    {{ $yf->category?->name }} — {{ $yf->year }}
                </option>
            @endforeach
        </select>
        <button class="btn-outline-teal !py-2 !text-xs">Filter</button>
    </form>
    <a href="{{ route('admin.institutions.create') }}" class="btn-teal !py-2 !text-xs">Tambah Institusi</a>
</div>
<div class="card-surface overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left"><tr>
            <th class="px-4 py-2">Nama</th><th class="px-4 py-2">Tahun</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th>
        </tr></thead>
        <tbody>
        @foreach($institutions as $institution)
            <tr class="border-t">
                <td class="px-4 py-2 font-semibold">{{ $institution->name }}</td>
                <td class="px-4 py-2">{{ $institution->yearFolder?->category?->name }} / {{ $institution->yearFolder?->year }}</td>
                <td class="px-4 py-2">{{ $institution->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td class="px-4 py-2 text-right space-x-2">
                    <a href="{{ route('admin.institutions.edit', $institution) }}" class="text-teal-700 hover:underline">Edit</a>
                    <form action="{{ route('admin.institutions.destroy', $institution) }}" method="POST" class="inline" onsubmit="return confirm('Arsipkan institusi?')">@csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $institutions->links() }}</div>
@endsection
