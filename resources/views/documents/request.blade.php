@extends('layouts.public')

@section('title', 'Minta Akses Dokumen')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
    <h1 class="section-title">Permintaan Akses Dokumen</h1>

    <div class="card-surface mt-6 overflow-hidden">
        @if($document->previewUrl())
            <img src="{{ $document->previewUrl() }}" alt="Preview" class="h-48 w-full object-cover">
        @endif
        <div class="space-y-3 p-5">
            <h2 class="text-xl font-bold text-teal-950">{{ $document->title }}</h2>
            @if($document->description)
                <p class="text-sm text-teal-800/80">{{ $document->description }}</p>
            @endif
            <p class="text-xs text-teal-700/70">{{ strtoupper($document->extension) }} · {{ $document->formattedSize() }}</p>
            <p class="text-sm font-medium text-teal-800">Status: {{ $statusLabel }}</p>

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            @if($latest?->status === \App\Enums\AccessRequestStatus::Pending)
                <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    Permintaan Anda sedang menunggu persetujuan.
                </p>
            @elseif(app(\App\Services\AccessRequestService::class)->userCanViewDocument(auth()->user(), $document))
                <div class="flex gap-2">
                    <a href="{{ route('documents.view', $document) }}" class="btn-teal">Buka Dokumen</a>
                    <a href="{{ route('documents.download', $document) }}" class="btn-outline-teal">Unduh</a>
                </div>
            @elseif(auth()->user()->isVisitor())
                <form method="POST" action="{{ route('documents.request-access', $document) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="request_message" class="block text-sm font-medium text-teal-900">Pesan (opsional)</label>
                        <textarea id="request_message" name="request_message" rows="3" class="mt-1 w-full rounded-lg border-teal-200 focus:border-teal-500 focus:ring-teal-500">{{ old('request_message') }}</textarea>
                    </div>
                    <button type="submit" class="btn-teal">Kirim Permintaan Akses</button>
                </form>
            @else
                <p class="text-sm text-teal-800/80">Akun staff dapat membuka dokumen langsung dari panel admin.</p>
            @endif
        </div>
    </div>
</div>
@endsection
