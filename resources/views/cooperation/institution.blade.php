@extends('layouts.public')

@section('title', $institution->name)
@section('nav', 'beranda')

@section('content')
@php
    $icon = \App\Support\CategoryIcon::path($category->slug ?? null);
    $pillTitle = $institution->name;
@endphp

<x-public.hero
    mode="compact"
    :title="$pillTitle"
    :icon="$icon"
    :subtitle="$institution->description ?: 'Dokumentasi kerjasama institusi mitra Poltekkes Kemenkes Banten.'"
/>

<nav class="breadcrumb-bar" aria-label="Breadcrumb">
    <a href="{{ route('home') }}">Beranda</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.section', $section) }}">{{ $section->name }}</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.category', [$section, $category]) }}">{{ $category->name }}</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.year', [$section, $category, $yearFolder->slug]) }}">{{ $yearFolder->year }}</a>
    <span class="mx-2">/</span>
    <span class="breadcrumb-bar__current">{{ $institution->name }}</span>
</nav>

<div class="content-section">
    <h2 class="content-section__title">Dokumen</h2>
    <div class="document-grid mt-4">
        @forelse($institution->documents as $document)
            <x-document-card :document="$document" :status-label="$statusLabels[$document->id] ?? 'Minta Akses'" />
        @empty
            <p class="text-sm" style="color: var(--brand-muted);">Belum ada dokumen untuk institusi ini.</p>
        @endforelse
    </div>
</div>
@endsection
