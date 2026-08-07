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
        <label class="block text-sm font-medium">Judul</label>
        <input type="text" name="title" value="{{ old('title', $document->title) }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-teal-200">{{ old('description', $document->description) }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium">Institusi</label>
        <select name="institution_folder_id" class="mt-1 w-full rounded-lg border-teal-200">
            <option value="">— Tingkat tahun —</option>
            @foreach($institutions as $inst)
                <option value="{{ $inst->id }}" @selected(old('institution_folder_id', $document->institution_folder_id) == $inst->id)>{{ $inst->name }}</option>
            @endforeach
        </select>
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
    <button class="btn-teal">Perbarui</button>
</form>
@endsection
