@extends('layouts.public')

@section('title', 'Skema Kerjasama')
@section('nav', 'skema')

@section('content')
<x-public.hero
    mode="compact"
    title="Skema Kerjasama"
    subtitle="Pilih jalur kemitraan yang sesuai, lalu telusuri kategori, tahun, dan dokumen terkait."
/>

<div class="content-section space-y-8">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="page-panel space-y-3">
            <h2>Dalam Negeri</h2>
            <p>
                Skema kemitraan dengan institusi layanan kesehatan dan pendidikan di Indonesia,
                mencakup praktik klinik, penelitian bersama, serta pengabdian masyarakat.
            </p>
            <a href="{{ route('cooperation.section', 'dalam-negeri') }}" class="btn-solid !text-xs">Buka Dalam Negeri</a>
        </div>
        <div class="page-panel space-y-3">
            <h2>Luar Negeri</h2>
            <p>
                Skema internasional untuk pertukaran mahasiswa, magang, kelas internasional,
                dan penguatan kapasitas tenaga kesehatan.
            </p>
            <a href="{{ route('cooperation.section', 'luar-negeri') }}" class="btn-solid !text-xs">Buka Luar Negeri</a>
        </div>
    </div>

    <div class="page-panel space-y-3">
        <h2>Alur Penelusuran Dokumen</h2>
        <ol class="list-decimal space-y-1 pl-5">
            <li>Pilih jalur kerjasama (dalam negeri / luar negeri).</li>
            <li>Pilih kategori mitra sesuai jenis institusi atau program.</li>
            <li>Pilih folder tahun dokumentasi.</li>
            <li>Buka institusi atau dokumen, lalu ajukan akses bila diperlukan.</li>
        </ol>
    </div>
</div>
@endsection
