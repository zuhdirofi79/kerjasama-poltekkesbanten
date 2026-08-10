@extends('layouts.public')

@section('title', 'Mitra')
@section('nav', 'mitra')

@section('content')
<x-public.hero
    mode="compact"
    title="Mitra Kerjasama"
    subtitle="Jaringan institusi kesehatan dan pendidikan yang bermitra dengan Poltekkes Kemenkes Banten."
/>

<div class="content-section space-y-8">
    <div class="page-panel space-y-4">
        <h2>Jenis Mitra</h2>
        <p>
            Dokumentasi mitra tersedia melalui jalur <strong>Kerjasama Dalam Negeri</strong> dan
            <strong>Kerjasama Luar Negeri</strong>. Setiap kategori memuat folder tahun dan institusi
            beserta dokumen perjanjian yang relevan.
        </p>
        <ul class="list-disc space-y-1 pl-5">
            <li>Rumah sakit pendidikan dan jejaring praktik klinik</li>
            <li>Dinas kesehatan &amp; puskesmas</li>
            <li>Klinik dan praktik mandiri</li>
            <li>Perguruan tinggi serta institusi mitra lainnya</li>
            <li>Program internasional: student exchange, internship, dan international class</li>
        </ul>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('home') }}#mitra" class="btn-solid">Lihat Jalur Kerjasama</a>
        <a href="{{ route('partnership-applications.create') }}" class="btn-outline">
            <img src="{{ asset('images/icons/icon-edit.svg') }}" alt="" aria-hidden="true">
            Ajukan Kerjasama
        </a>
    </div>
</div>
@endsection
