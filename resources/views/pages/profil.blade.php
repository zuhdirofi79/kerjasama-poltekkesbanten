@extends('layouts.public')

@section('title', 'Profil')
@section('nav', 'profil')

@section('content')
<x-public.hero
    mode="compact"
    title="Profil"
    subtitle="Mengenal Unit Kerjasama Poltekkes Kemenkes Banten dan peranannya dalam membangun kemitraan strategis."
/>

<div class="content-section">
    <div class="page-panel space-y-6">
        <div>
            <h2>Unit Kerjasama</h2>
            <p class="mt-2">
                Unit Kerjasama Poltekkes Kemenkes Banten mengelola hubungan kemitraan dengan rumah sakit,
                dinas kesehatan, klinik, perguruan tinggi, serta mitra internasional. Fokus kami adalah
                mendukung Tridharma Perguruan Tinggi melalui praktik klinik, penelitian, dan pengabdian masyarakat.
            </p>
        </div>
        <div>
            <h2>Visi</h2>
            <p class="mt-2">
                Menjadi pusat jejaring kerjasama kesehatan yang akuntabel, adaptif, dan berdampak
                bagi mutu pendidikan tenaga kesehatan di Banten.
            </p>
        </div>
        <div>
            <h2>Misi</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>Memfasilitasi perjanjian kerjasama yang jelas, legal, dan terukur.</li>
                <li>Menyediakan dokumentasi mitra yang mudah ditelusuri dan aman diakses.</li>
                <li>Mendorong kolaborasi akademik dan layanan kesehatan berbasis bukti.</li>
                <li>Menjaga tata kelola BLU yang profesional dan transparan.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
