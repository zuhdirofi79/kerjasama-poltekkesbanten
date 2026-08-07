@extends('layouts.public')

@section('title', $yearFolder->year.' — '.$category->name)
@section('nav', 'beranda')

@section('content')
@php
    $icon = \App\Support\CategoryIcon::path($category->slug ?? null);
    $pillTitle = $category->name.' '.$yearFolder->year;
@endphp

<x-public.hero
    mode="compact"
    :title="$pillTitle"
    :icon="$icon"
/>

<nav class="breadcrumb-bar" aria-label="Breadcrumb">
    <a href="{{ route('home') }}">Beranda</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.section', $section) }}">{{ $section->name }}</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.category', [$section, $category]) }}">{{ $category->name }}</a>
    <span class="mx-2">/</span>
    <span class="breadcrumb-bar__current">{{ $yearFolder->year }}</span>
</nav>

<div class="content-section">
    @if($yearFolder->institutions->isNotEmpty())
        <h2 class="content-section__title">Institusi</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($yearFolder->institutions as $institution)
                <a
                    href="{{ route('cooperation.institution', [$section, $category, $yearFolder->slug, $institution]) }}"
                    class="institution-card"
                >
                    <h3 class="institution-card__title">{{ $institution->name }}</h3>
                    @if($institution->description)
                        <p class="institution-card__desc">{{ $institution->description }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    <h2 class="content-section__title {{ $yearFolder->institutions->isNotEmpty() ? 'mt-10' : '' }}">Dokumen</h2>
    <div class="document-grid mt-4">
        @forelse($yearFolder->documents as $document)
            <x-document-card :document="$document" :status-label="$statusLabels[$document->id] ?? 'Minta Akses'" />
        @empty
            <p class="text-sm" style="color: var(--brand-muted);">Belum ada dokumen pada tingkat tahun ini.</p>
        @endforelse
    </div>
</div>
@endsection
