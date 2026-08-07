@extends('layouts.admin')
@section('title', 'Folder Tahun')
@section('heading', 'Folder Tahun')
@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="flex gap-2">
        <select name="category_id" class="rounded-lg border-teal-200 text-sm">
            <option value="">Semua kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                    {{ $category->section?->name }} — {{ $category->name }}
                </option>
            @endforeach
        </select>
        <button class="btn-outline-teal !py-2 !text-xs">Filter</button>
    </form>
    <a href="{{ route('admin.years.create') }}" class="btn-teal !py-2 !text-xs">Tambah Tahun</a>
</div>
<div class="card-surface overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left"><tr>
            <th class="px-4 py-2">Tahun</th><th class="px-4 py-2">Kategori</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th>
        </tr></thead>
        <tbody>
        @foreach($years as $year)
            <tr class="border-t">
                <td class="px-4 py-2 font-semibold">{{ $year->year }}</td>
                <td class="px-4 py-2">{{ $year->category?->section?->name }} / {{ $year->category?->name }}</td>
                <td class="px-4 py-2">{{ $year->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td class="px-4 py-2 text-right space-x-2">
                    <a href="{{ route('admin.years.edit', $year) }}" class="text-teal-700 hover:underline">Edit</a>
                    <form action="{{ route('admin.years.destroy', $year) }}" method="POST" class="inline" onsubmit="return confirm('Arsipkan folder tahun?')">@csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $years->links() }}</div>
@endsection
