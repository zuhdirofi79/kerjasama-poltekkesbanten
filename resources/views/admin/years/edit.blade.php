@extends('layouts.admin')
@section('title', 'Edit Tahun')
@section('heading', 'Edit Folder Tahun')
@section('content')
<form method="POST" action="{{ route('admin.years.update', $yearFolder) }}" class="card-surface max-w-xl space-y-4 p-5">
    @csrf @method('PUT')
    <p class="text-sm text-slate-600">{{ $yearFolder->category?->section?->name }} / {{ $yearFolder->category?->name }}</p>
    <div>
        <label class="block text-sm font-medium">Tahun</label>
        <input type="number" name="year" value="{{ old('year', $yearFolder->year) }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-teal-200">{{ old('description', $yearFolder->description) }}</textarea>
    </div>
    <label class="inline-flex items-center gap-2 text-sm">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $yearFolder->is_active))> Aktif
    </label>
    <button class="btn-teal">Perbarui</button>
</form>
@endsection
