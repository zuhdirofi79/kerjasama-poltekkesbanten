@extends('layouts.public')

@section('title', 'Ajukan Kerjasama')
@section('nav', 'beranda')

@section('content')
<x-public.hero
    mode="compact"
    title="Ajukan Kerjasama"
    subtitle="Sampaikan minat kemitraan Anda kepada Unit Kerjasama Poltekkes Kemenkes Banten."
    :show-actions="false"
/>

<div class="content-section">
    <div class="page-panel max-w-2xl space-y-6">
        <div>
            <h2>Langkah Pengajuan</h2>
            <ol class="mt-2 list-decimal space-y-2 pl-5">
                <li>Siapkan profil institusi dan tujuan kerjasama (pendidikan, penelitian, atau pengabdian).</li>
                <li>Tentukan skema: dalam negeri atau luar negeri, beserta kategori mitra yang relevan.</li>
                <li>Kirimkan ringkasan proposal ke email Unit Kerjasama.</li>
                <li>Tim kami akan menindaklanjuti untuk penjadwalan pembahasan dan penyusunan dokumen.</li>
            </ol>
        </div>

        <div>
            <h2>Kirim Proposal</h2>
            <p class="mt-2">
                Email resmi:
                <a href="mailto:kerjasama@poltekkesbanten.ac.id?subject=Pengajuan%20Kerjasama%20Poltekkes%20Banten" class="font-semibold" style="color: var(--brand-teal);">
                    kerjasama@poltekkesbanten.ac.id
                </a>
            </p>
            <p class="mt-2">
                Cantumkan nama institusi, narahubung, nomor telepon, dan ringkasan rencana kerjasama.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="mailto:kerjasama@poltekkesbanten.ac.id?subject=Pengajuan%20Kerjasama%20Poltekkes%20Banten" class="btn-solid">
                <img src="{{ asset('images/icons/icon-mail.svg') }}" alt="" aria-hidden="true">
                Kirim Email
            </a>
            <a href="{{ route('pages.skema') }}" class="btn-outline">Lihat Skema</a>
        </div>
    </div>
</div>
@endsection
