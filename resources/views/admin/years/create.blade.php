@extends('layouts.admin')
@section('title', 'Tambah Tahun')
@section('heading', 'Tambah Folder Tahun')
@section('content')
<form method="POST" action="{{ route('admin.years.store') }}" class="card-surface max-w-xl space-y-4 p-5">
    @csrf
    <div>
        <label class="block text-sm font-medium">Kategori</label>
        <select name="category_id" required class="mt-1 w-full rounded-lg border-teal-200">
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected($selectedCategoryId == $category->id)>
                    {{ $category->section?->name }} — {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium">Tahun</label>
        <input type="number" name="year" value="{{ old('year', date('Y')) }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-teal-200">{{ old('description') }}</textarea>
    </div>
    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
    <button class="btn-teal">Simpan</button>
</form>
@endsection
