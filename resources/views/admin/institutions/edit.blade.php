@extends('layouts.admin')
@section('title', 'Edit Institusi')
@section('heading', 'Edit Folder Institusi')
@section('content')
<form method="POST" action="{{ route('admin.institutions.update', $institution) }}" class="card-surface max-w-xl space-y-4 p-5">
    @csrf @method('PUT')
    <p class="text-sm text-slate-600">{{ $institution->yearFolder?->category?->name }} / {{ $institution->yearFolder?->year }}</p>
    <div>
        <label class="block text-sm font-medium">Nama</label>
        <input type="text" name="name" value="{{ old('name', $institution->name) }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-teal-200">{{ old('description', $institution->description) }}</textarea>
    </div>
    <label class="inline-flex items-center gap-2 text-sm">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $institution->is_active))> Aktif
    </label>
    <button class="btn-teal">Perbarui</button>
</form>
@endsection
