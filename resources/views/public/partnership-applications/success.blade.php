@extends('layouts.public')

@section('title', 'Pengajuan Berhasil')
@section('nav', 'beranda')

@section('content')
<div class="content-section">
    <div class="pa-shell">
        <div class="pa-card text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full" style="background: rgba(10, 110, 110, 0.12); color: var(--brand-teal);">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="mt-5 text-2xl font-bold sm:text-3xl" style="color: var(--brand-teal-deep);">Pengajuan Kerja Sama Berhasil Dikirim</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed" style="color: var(--brand-muted);">
                Terima kasih. Pengajuan kerja sama dari <strong style="color: var(--brand-text);">{{ $application->institution_name }}</strong> telah diterima.
            </p>

            <div class="pa-summary mx-auto mt-6 max-w-xl text-left">
                <p><strong>Nomor Pengajuan:</strong> {{ $application->submission_code }}</p>
                <p><strong>Instansi:</strong> {{ $application->institution_name }}</p>
                <p><strong>Tanggal:</strong> {{ $application->submission_date?->format('d/m/Y') }}</p>
            </div>

            <p class="mx-auto mt-5 max-w-2xl text-sm leading-relaxed" style="color: var(--brand-muted);">
                Simpan nomor pengajuan ini untuk kebutuhan komunikasi dengan Unit Kerja Sama Poltekkes Kemenkes Banten.
                Tim pengelola kerja sama akan memeriksa kelengkapan pengajuan dan menghubungi calon mitra melalui kontak yang diberikan.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-solid">Kembali ke Beranda</a>
                <a href="{{ route('pages.skema') }}" class="btn-outline">Lihat Informasi Kerjasama</a>
            </div>
        </div>
    </div>
</div>
@endsection
