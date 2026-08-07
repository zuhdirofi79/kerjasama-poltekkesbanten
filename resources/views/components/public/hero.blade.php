@props([
    'title',
    'subtitle' => 'Membangun kemitraan strategis antara Poltekkes Kemenkes Banten dan institusi layanan kesehatan untuk pendidikan, penelitian, dan pengabdian masyarakat.',
    'mode' => 'compact',
    'icon' => null,
    'showActions' => true,
])

@php
    $modeClass = match ($mode) {
        'home' => 'hero--home',
        'category' => 'hero--category',
        default => 'hero--compact',
    };
@endphp

<section {{ $attributes->merge(['class' => "hero {$modeClass}"]) }}>
    <img
        src="{{ asset('images/hero/hero-building.png') }}"
        alt="Gedung Poltekkes Kemenkes Banten"
        class="hero-bg"
    >

    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="hero-content">
        @if($mode === 'home')
            <h1 class="hero-title">{{ $title }}</h1>
        @else
            <h1 class="hero-title-pill">
                @if($icon)
                    <img src="{{ $icon }}" alt="" aria-hidden="true">
                @endif
                <span>{{ $title }}</span>
            </h1>
        @endif

        @if($subtitle)
            <p class="hero-subtitle">{{ $subtitle }}</p>
        @endif

        @if($showActions)
            <div class="hero-actions">
                <a href="{{ route('pages.mitra') }}" class="btn-solid">
                    <img src="{{ asset('images/icons/icon-users.svg') }}" alt="" aria-hidden="true">
                    Lihat Mitra
                </a>
                <a href="{{ route('pages.ajukan') }}" class="btn-outline {{ $mode === 'category' ? 'btn-outline--on-dark' : '' }}">
                    <img src="{{ asset('images/icons/icon-edit.svg') }}" alt="" aria-hidden="true">
                    Ajukan Kerjasama
                </a>
            </div>
        @endif
    </div>

    <div class="hero-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path fill="#ffffff" d="M0,48 C240,90 480,10 720,40 C960,70 1200,20 1440,52 L1440,90 L0,90 Z"/>
        </svg>
    </div>
</section>
