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
    <div class="repository-folder-grid mt-4">
        @forelse($folders as $folder)
            <x-public.folder-card
                :href="route('cooperation.folder', [$section, $category, $yearFolder->slug, $folder])"
                :name="$folder->name"
                :children-count="$folder->children_count"
                :documents-count="$folder->documents_count"
            />
        @empty
            <p class="text-sm" style="color: var(--brand-muted);">Belum ada folder pada tahun ini.</p>
        @endforelse
    </div>

    @if($documents->isNotEmpty())
        <h2 class="content-section__title mt-10">Dokumen</h2>
        <x-document-filter-bar
            class="mt-4"
            :action="route('cooperation.year', [$section, $category, $yearFolder->slug])"
            :filters="$filters"
            :areas="$areas"
            :subtypes="$subtypes"
            :sorts="$sorts"
        />
        <div class="document-grid mt-4">
            @foreach($documents as $document)
                <x-document-card :document="$document" :status-label="$statusLabels[$document->id] ?? 'Minta Akses'" />
            @endforeach
        </div>
        <div class="mt-6">{{ $documents->links() }}</div>
    @else
        <h2 class="content-section__title mt-10">Dokumen</h2>
        <x-document-filter-bar
            class="mt-4"
            :action="route('cooperation.year', [$section, $category, $yearFolder->slug])"
            :filters="$filters"
            :areas="$areas"
            :subtypes="$subtypes"
            :sorts="$sorts"
        />
        @if($filtering)
            <p class="mt-4 text-sm" style="color: var(--brand-muted);">Tidak ada dokumen yang sesuai dengan filter.</p>
            <a href="{{ route('cooperation.year', [$section, $category, $yearFolder->slug]) }}" class="btn-outline mt-3 inline-flex !px-3 !py-2 !text-xs">Reset Filter</a>
        @endif
    @endif
</div>
@endsection
