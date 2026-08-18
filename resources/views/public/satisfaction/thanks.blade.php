@extends('layouts.public')

@section('title', 'Terima kasih')
@section('nav', 'beranda')

@section('content')
<div class="content-section">
    <div class="page-panel max-w-xl text-center">
        <h1 class="section-title">Terima kasih</h1>
        <p class="mt-3 text-sm" style="color: var(--brand-muted);">Evaluasi kepuasan kerja sama Anda telah tersimpan.</p>
        <a href="{{ route('home') }}" class="btn-cta mt-6 inline-flex">Kembali ke Beranda</a>
    </div>
</div>
@endsection
