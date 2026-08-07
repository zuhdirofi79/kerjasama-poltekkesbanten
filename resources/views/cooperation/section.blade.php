@extends('layouts.public')

@section('title', $section->name)
@section('nav', 'beranda')

@section('content')
<x-public.hero
    mode="compact"
    :title="$section->name"
/>

<nav class="breadcrumb-bar" aria-label="Breadcrumb">
    <a href="{{ route('home') }}">Beranda</a>
    <span class="mx-2">/</span>
    <span class="breadcrumb-bar__current">{{ $section->name }}</span>
</nav>

@php
    $isIntl = str_contains(strtolower($section->slug), 'luar')
        || str_contains(strtolower($section->name), 'luar');
@endphp

<div class="category-grid {{ $isIntl ? 'category-grid--intl' : '' }}">
    @forelse($section->categories as $category)
        <x-public.category-card :category="$category" :section="$section" />
    @empty
        <p class="col-span-full text-center text-sm" style="color: var(--brand-muted);">Belum ada kategori aktif.</p>
    @endforelse
</div>
@endsection
