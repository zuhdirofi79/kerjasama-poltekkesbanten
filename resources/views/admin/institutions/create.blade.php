@extends('layouts.admin')
@section('title', 'Tambah Institusi')
@section('heading', 'Tambah Folder Institusi')
@section('content')
<form method="POST" action="{{ route('admin.institutions.store') }}" class="card-surface max-w-xl space-y-4 p-5">
    @csrf
    <div>
        <label class="block text-sm font-medium">Folder Tahun</label>
        <select name="year_folder_id" required class="mt-1 w-full rounded-lg border-teal-200">
            @foreach($yearFolders as $yf)
                <option value="{{ $yf->id }}" @selected($selectedYearFolderId == $yf->id)>
                    {{ $yf->category?->section?->name }} / {{ $yf->category?->name }} / {{ $yf->year }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium">Nama Institusi</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-teal-200">{{ old('description') }}</textarea>
    </div>
    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
    <button class="btn-teal">Simpan</button>
</form>
@endsection
