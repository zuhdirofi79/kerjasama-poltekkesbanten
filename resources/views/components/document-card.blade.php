@props([
    'document',
    'statusLabel' => 'Minta Akses',
])

@php
    $user = auth()->user();
    $canOpen = $user && app(\App\Services\AccessRequestService::class)->userCanViewDocument($user, $document);
    $previewUrl = $document->previewUrl();
@endphp

<article {{ $attributes->merge(['class' => 'document-card']) }}>
    <div class="document-card__preview">
        @if($previewUrl)
            <img
                src="{{ $previewUrl }}"
                alt="Preview {{ $document->title }}"
                class="document-card__preview-img"
                loading="lazy"
                decoding="async"
                onerror="this.classList.add('is-broken'); this.nextElementSibling?.classList.remove('hidden');"
            >
            <div class="document-card__preview-fallback hidden" aria-hidden="true">
                <span>Preview belum tersedia</span>
            </div>
        @else
            <div class="document-card__preview-fallback">
                <span>Preview belum tersedia</span>
            </div>
        @endif
    </div>
    <div class="document-card__body">
        <div>
            <h3 class="document-card__title">{{ $document->title }}</h3>
            <p class="document-card__meta">{{ strtoupper($document->extension) }} · {{ $document->formattedSize() }}</p>
            @if($document->isClassified() || $document->document_date || $document->cooperation_end_date)
                <p class="document-card__classification">
                    @if($document->areaLabel())
                        <span>{{ $document->areaLabel() }}</span>
                    @endif
                    @if($document->subtypeLabel())
                        <span>{{ $document->subtypeLabel() }}</span>
                    @endif
                    @if($document->document_date)
                        <span>{{ $document->document_date->format('d/m/Y') }}</span>
                    @endif
                    @if($document->expiryStatus())
                        <span>
                            {{ $document->expiryStatus()->label() }}
                            @if($document->cooperation_end_date)
                                sampai {{ $document->cooperation_end_date->format('d/m/Y') }}
                            @endif
                        </span>
                    @endif
                </p>
            @endif
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
