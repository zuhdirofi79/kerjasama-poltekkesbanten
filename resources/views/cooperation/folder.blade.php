@extends('layouts.public')

@section('title', $folder->name.' — '.$yearFolder->year)
@section('nav', 'beranda')

@section('content')
@php
    $icon = \App\Support\CategoryIcon::path($category->slug ?? null);
@endphp

<x-public.hero
    mode="compact"
    :title="$folder->name"
    :icon="$icon"
    :subtitle="$folder->description ?: null"
/>

<nav class="breadcrumb-bar" aria-label="Breadcrumb">
    <a href="{{ route('home') }}">Beranda</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.section', $section) }}">{{ $section->name }}</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.category', [$section, $category]) }}">{{ $category->name }}</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.year', [$section, $category, $yearFolder->slug]) }}">{{ $yearFolder->year }}</a>
    @foreach($ancestors as $ancestor)
        <span class="mx-2">/</span>
        <a href="{{ route('cooperation.folder', [$section, $category, $yearFolder->slug, $ancestor]) }}">{{ $ancestor->name }}</a>
    @endforeach
    <span class="mx-2">/</span>
    <span class="breadcrumb-bar__current">{{ $folder->name }}</span>
</nav>

<div class="content-section">
    <h2 class="content-section__title">Folder</h2>
    <div class="folder-grid mt-4">
        @forelse($children as $child)
            <a
                href="{{ route('cooperation.folder', [$section, $category, $yearFolder->slug, $child]) }}"
                class="folder-card"
            >
                <div class="folder-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 015.5 5H9l2 2h7.5A2.5 2.5 0 0121 9.5v7A2.5 2.5 0 0118.5 19h-13A2.5 2.5 0 013 16.5v-9z"/>
                    </svg>
                </div>
                <div class="folder-card__body">
                    <h3 class="folder-card__title">{{ $child->name }}</h3>
                    <p class="folder-card__meta">
                        {{ $child->children_count }} folder · {{ $child->documents_count }} dokumen
                    </p>
                </div>
                <span class="folder-card__chevron" aria-hidden="true">&rsaquo;</span>
            </a>
        @empty
            @if($documents->isEmpty())
                <p class="text-sm" style="color: var(--brand-muted);">Belum ada dokumen pada folder ini.</p>
            @endif
        @endforelse
    </div>

    <h2 class="content-section__title mt-10">Dokumen</h2>
    <div class="document-grid mt-4">
        @forelse($documents as $document)
            <x-document-card :document="$document" :status-label="$statusLabels[$document->id] ?? 'Minta Akses'" />
        @empty
            @if($children->isNotEmpty())
                <p class="text-sm" style="color: var(--brand-muted);">Belum ada dokumen pada folder ini.</p>
            @endif
        @endforelse
    </div>
</div>
@endsection
