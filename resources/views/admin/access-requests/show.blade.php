@extends('layouts.admin')
@section('title', 'Detail Permintaan')
@section('heading', 'Detail Permintaan Akses')
@section('content')
<div class="grid gap-6 lg:grid-cols-2">
    <div class="card-surface space-y-3 p-5">
        <h2 class="font-semibold text-teal-950">Informasi</h2>
        <p><span class="text-slate-500">Pengguna:</span> {{ $accessRequest->user?->name }} ({{ $accessRequest->user?->email }})</p>
        <p><span class="text-slate-500">Dokumen:</span> {{ $accessRequest->document?->title }}</p>
        <p><span class="text-slate-500">Status:</span> {{ $accessRequest->status->label() }}</p>
        <p><span class="text-slate-500">Pesan:</span> {{ $accessRequest->request_message ?: '—' }}</p>
        <p><span class="text-slate-500">Catatan review:</span> {{ $accessRequest->review_note ?: '—' }}</p>
        @if($accessRequest->document?->previewUrl())
            <img src="{{ $accessRequest->document->previewUrl() }}" alt="" class="mt-2 h-40 rounded object-cover">
        @endif
    </div>

    <div class="card-surface space-y-4 p-5">
        <h2 class="font-semibold text-teal-950">Keputusan</h2>
        <form method="POST" action="{{ route('admin.access-requests.approve', $accessRequest) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium">Catatan</label>
                <textarea name="review_note" rows="2" class="mt-1 w-full rounded-lg border-teal-200"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium">Kedaluwarsa akses (opsional)</label>
                <input type="datetime-local" name="access_expires_at" class="mt-1 w-full rounded-lg border-teal-200">
            </div>
            <button class="btn-teal">Setujui</button>
        </form>
        <form method="POST" action="{{ route('admin.access-requests.reject', $accessRequest) }}" class="space-y-3 border-t pt-4">
            @csrf
            <textarea name="review_note" rows="2" placeholder="Alasan penolakan" class="w-full rounded-lg border-teal-200"></textarea>
            <button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Tolak</button>
        </form>
        <form method="POST" action="{{ route('admin.access-requests.revoke', $accessRequest) }}" class="space-y-3 border-t pt-4">
            @csrf
            <textarea name="review_note" rows="2" placeholder="Alasan pencabutan" class="w-full rounded-lg border-teal-200"></textarea>
            <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white">Cabut Akses</button>
        </form>
    </div>
</div>
@endsection
