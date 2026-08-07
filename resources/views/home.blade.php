@extends('layouts.public')

@section('title', 'Beranda')
@section('nav', 'beranda')

@section('content')
<x-public.hero
    mode="home"
    title="Kerjasama Rumah Sakit"
    subtitle="Membangun kemitraan strategis antara Poltekkes Kemenkes Banten dan institusi layanan kesehatan untuk pendidikan, penelitian, dan pengabdian masyarakat."
/>

<div class="feature-grid">
    <article class="feature-card">
        <div class="feature-card__icon">
            <img src="{{ asset('images/icons/icon-stethoscope.svg') }}" alt="" aria-hidden="true">
        </div>
        <div class="feature-card__body">
            <h2 class="feature-card__title">Praktik Klinik</h2>
            <p class="feature-card__desc">Fasilitas praktik mahasiswa di rumah sakit mitra</p>
        </div>
        <span class="feature-card__chevron" aria-hidden="true">&rsaquo;</span>
    </article>

    <article class="feature-card">
        <div class="feature-card__icon">
            <img src="{{ asset('images/icons/icon-microscope.svg') }}" alt="" aria-hidden="true">
        </div>
        <div class="feature-card__body">
            <h2 class="feature-card__title">Penelitian</h2>
            <p class="feature-card__desc">Kolaborasi riset kesehatan berbasis bukti</p>
        </div>
        <span class="feature-card__chevron" aria-hidden="true">&rsaquo;</span>
    </article>

    <article class="feature-card">
        <div class="feature-card__icon">
            <img src="{{ asset('images/icons/icon-pengabdian.svg') }}" alt="" aria-hidden="true">
        </div>
        <div class="feature-card__body">
            <h2 class="feature-card__title">Pengabdian</h2>
            <p class="feature-card__desc">Pelayanan masyarakat bersama mitra kesehatan</p>
        </div>
        <span class="feature-card__chevron" aria-hidden="true">&rsaquo;</span>
    </article>
</div>

<section id="mitra" class="cooperation-selector">
    <h2 class="cooperation-selector__title">Kerjasama</h2>
    <div class="coop-pill-grid">
        @foreach($sections as $section)
            <a href="{{ route('cooperation.section', $section) }}" class="coop-pill">
                {{ $section->name }}
            </a>
        @endforeach
    </div>
</section>

<section class="stats-bar" aria-label="Statistik kerjasama">
    <div class="stats-bar__inner">
        <div class="stat-item">
            <div class="stat-item__icon">
                <img src="{{ asset('images/icons/icon-users.svg') }}" alt="" aria-hidden="true">
            </div>
            <div>
                <p class="stat-item__value">25+ Mitra</p>
                <p class="stat-item__label">Rumah sakit &amp; institusi kesehatan</p>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-item__icon">
                <img src="{{ asset('images/icons/icon-perguruan.svg') }}" alt="" aria-hidden="true">
            </div>
            <div>
                <p class="stat-item__value">10 Program Studi</p>
                <p class="stat-item__label">Berbagai bidang kesehatan</p>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-item__icon">
                <img src="{{ asset('images/icons/icon-institusi.svg') }}" alt="" aria-hidden="true">
            </div>
            <div>
                <p class="stat-item__value">Berbasis BLU</p>
                <p class="stat-item__label">Badan Layanan Umum: Akuntabel, fleksibel, dan profesional</p>
            </div>
        </div>
    </div>
</section>
@endsection
