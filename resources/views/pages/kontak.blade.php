@extends('layouts.public')

@section('title', 'Kontak')
@section('nav', 'kontak')

@section('content')
<x-public.hero
    mode="compact"
    title="Kontak"
    subtitle="Hubungi Unit Kerjasama Poltekkes Kemenkes Banten untuk informasi kemitraan dan dokumentasi."
/>

<div class="content-section">
    <div class="page-panel space-y-6 max-w-2xl">
        <div>
            <h2>Unit Kerjasama</h2>
            <p class="mt-2">Poltekkes Kemenkes Banten</p>
            <p>Portal: kerjasama.poltekkesbanten.ac.id</p>
        </div>
        <div>
            <h2>Email</h2>
            <p class="mt-2">
                <a href="mailto:kerjasama@poltekkesbanten.ac.id" class="font-semibold" style="color: var(--brand-teal);">
                    kerjasama@poltekkesbanten.ac.id
                </a>
            </p>
        </div>
        <div>
            <h2>Layanan</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>Informasi skema dan kategori kerjasama</li>
                <li>Konsultasi pengajuan kemitraan baru</li>
                <li>Bantuan akses dokumen (setelah login Google)</li>
            </ul>
        </div>
        <a href="{{ route('partnership-applications.create') }}" class="btn-cta inline-flex">
            <img src="{{ asset('images/icons/icon-edit.svg') }}" alt="" aria-hidden="true">
            Ajukan Kerjasama
        </a>
    </div>
</div>
@endsection
