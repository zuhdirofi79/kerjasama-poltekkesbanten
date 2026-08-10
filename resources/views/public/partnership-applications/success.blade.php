@extends('layouts.public')

@section('title', 'Pengajuan Berhasil')
@section('nav', 'beranda')

@section('content')
<div class="partnership-page partnership-ui-build-marker">
    <div class="partnership-shell">
        <div class="partnership-card" style="text-align:center;">
            <div class="partnership-success-icon" aria-hidden="true">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="partnership-eyebrow" style="margin-top:1.25rem;">Unit Kerja Sama</p>
            <h1 class="partnership-title">Pengajuan Kerja Sama Berhasil Dikirim</h1>
            <p class="partnership-lead">
                Terima kasih. Pengajuan kerja sama dari
                <strong style="color: var(--brand-text);">{{ $application->institution_name }}</strong>
                telah diterima.
            </p>

            <div class="partnership-code" style="max-width:28rem;margin-left:auto;margin-right:auto;">
                <p class="partnership-code__label">Nomor Pengajuan</p>
                <p class="partnership-code__value">{{ $application->submission_code }}</p>
                <p class="form-helper" style="margin-top:0.65rem;">
                    Instansi: {{ $application->institution_name }} · Tanggal: {{ $application->submission_date?->format('d/m/Y') }}
                </p>
            </div>

            <p class="partnership-lead" style="margin-top:1.25rem;">
                Simpan nomor pengajuan ini untuk kebutuhan komunikasi dengan Unit Kerja Sama Poltekkes Kemenkes Banten.
                Tim pengelola akan memeriksa kelengkapan pengajuan dan menghubungi calon mitra melalui kontak yang diberikan.
            </p>

            <div class="partnership-actions" style="justify-content:center;border-top:0;padding-top:0;">
                <div class="partnership-actions__right" style="margin-left:0;">
                    <a href="{{ route('home') }}" class="partnership-btn partnership-btn--primary">Kembali ke Beranda</a>
                    <a href="{{ route('pages.skema') }}" class="partnership-btn partnership-btn--secondary">Lihat Informasi Kerjasama</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
