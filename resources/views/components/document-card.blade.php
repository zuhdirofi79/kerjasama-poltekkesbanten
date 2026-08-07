@props([
    'document',
    'statusLabel' => 'Minta Akses',
])

@php
    $user = auth()->user();
    $canOpen = $user && app(\App\Services\AccessRequestService::class)->userCanViewDocument($user, $document);
@endphp

<article {{ $attributes->merge(['class' => 'document-card']) }}>
    <div class="document-card__preview">
        @if($document->previewUrl())
            <img src="{{ $document->previewUrl() }}" alt="Preview {{ $document->title }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center text-sm" style="color: var(--brand-teal);">Tidak ada preview</div>
        @endif
    </div>
    <div class="document-card__body">
        <div>
            <h3 class="document-card__title">{{ $document->title }}</h3>
            <p class="document-card__meta">{{ strtoupper($document->extension) }} · {{ $document->formattedSize() }}</p>
        </div>

        @if($canOpen)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('documents.view', $document) }}" class="btn-solid !px-3 !py-2 !text-xs">Buka</a>
                <a href="{{ route('documents.download', $document) }}" class="btn-outline !px-3 !py-2 !text-xs">Unduh</a>
            </div>
        @elseif($user)
            <a href="{{ route('documents.request', $document) }}" class="btn-outline !w-full !px-3 !py-2 !text-xs">{{ $statusLabel }}</a>
        @else
            <a href="{{ route('auth.google') }}" class="btn-outline !w-full !px-3 !py-2 !text-xs">{{ $statusLabel }}</a>
        @endif
    </div>
</article>
