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
    <div class="repository-folder-grid mt-4">
        @forelse($children as $child)
            <x-public.folder-card
                :href="route('cooperation.folder', [$section, $category, $yearFolder->slug, $child])"
                :name="$child->name"
                :children-count="$child->children_count"
                :documents-count="$child->documents_count"
            />
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
