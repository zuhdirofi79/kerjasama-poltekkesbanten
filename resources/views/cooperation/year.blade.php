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
    <h2 class="content-section__title">Folder</h2>
    <div class="folder-grid mt-4">
        @forelse($folders as $folder)
            <a
                href="{{ route('cooperation.folder', [$section, $category, $yearFolder->slug, $folder]) }}"
                class="folder-card"
            >
                <div class="folder-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 015.5 5H9l2 2h7.5A2.5 2.5 0 0121 9.5v7A2.5 2.5 0 0118.5 19h-13A2.5 2.5 0 013 16.5v-9z"/>
                    </svg>
                </div>
                <div class="folder-card__body">
                    <h3 class="folder-card__title">{{ $folder->name }}</h3>
                    <p class="folder-card__meta">
                        {{ $folder->children_count }} folder · {{ $folder->documents_count }} dokumen
                    </p>
                </div>
                <span class="folder-card__chevron" aria-hidden="true">&rsaquo;</span>
            </a>
        @empty
            <p class="text-sm" style="color: var(--brand-muted);">Belum ada folder pada tahun ini.</p>
        @endforelse
    </div>

    @if($documents->isNotEmpty())
        <h2 class="content-section__title mt-10">Dokumen</h2>
        <div class="document-grid mt-4">
            @foreach($documents as $document)
                <x-document-card :document="$document" :status-label="$statusLabels[$document->id] ?? 'Minta Akses'" />
            @endforeach
        </div>
    @endif
</div>
@endsection
