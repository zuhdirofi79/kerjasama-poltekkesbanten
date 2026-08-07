@extends('layouts.public')

@section('title', $category->name)
@section('nav', 'beranda')

@section('content')
@php
    $icon = \App\Support\CategoryIcon::path($category->slug ?? null);
    $pillTitle = $category->name.' - '.$section->name;
@endphp

<x-public.hero
    mode="category"
    :title="$pillTitle"
    :icon="$icon"
/>

<nav class="breadcrumb-bar" aria-label="Breadcrumb">
    <a href="{{ route('home') }}">Beranda</a>
    <span class="mx-2">/</span>
    <a href="{{ route('cooperation.section', $section) }}">{{ $section->name }}</a>
    <span class="mx-2">/</span>
    <span class="breadcrumb-bar__current">{{ $category->name }}</span>
</nav>

<div class="year-grid">
    @forelse($category->yearFolders as $yearFolder)
        <x-public.year-card :year-folder="$yearFolder" :section="$section" :category="$category" />
    @empty
        <p class="col-span-full text-center text-sm" style="color: var(--brand-muted);">Belum ada folder tahun.</p>
    @endforelse
</div>
@endsection
