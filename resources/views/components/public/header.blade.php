@props([
    'active' => 'beranda',
])

@php
    $active = strtolower((string) $active);
@endphp

<header class="site-header">
    <div class="site-header__inner">
        <a href="{{ route('home') }}" class="site-header__brand">
            <img
                src="{{ asset('images/branding/logo-poltekkes.png') }}"
                alt="Kemenkes Poltekkes Banten"
                class="brand-logo-poltekkes"
                width="180"
                height="52"
            >
            <img
                src="{{ asset('images/branding/logo-blu.svg') }}"
                alt="Badan Layanan Umum"
                class="brand-logo-blu"
                width="52"
                height="52"
            >
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Utama">
            <a href="{{ route('home') }}" class="nav-link {{ $active === 'beranda' ? 'nav-link-active' : '' }}">Beranda</a>
            <a href="{{ route('pages.profil') }}" class="nav-link {{ $active === 'profil' ? 'nav-link-active' : '' }}">Profil</a>
            <a href="{{ route('pages.mitra') }}" class="nav-link {{ $active === 'mitra' ? 'nav-link-active' : '' }}">Mitra</a>
            <a href="{{ route('pages.skema') }}" class="nav-link {{ $active === 'skema' ? 'nav-link-active' : '' }}">Skema Kerjasama</a>
            <a href="{{ route('pages.kontak') }}" class="nav-link {{ $active === 'kontak' ? 'nav-link-active' : '' }}">Kontak / Helpdesk</a>
            <a href="{{ route('partnership-applications.create') }}" class="btn-cta ml-3 !py-2 !text-xs">
                <img src="{{ asset('images/icons/icon-edit.svg') }}" alt="" aria-hidden="true">
                Ajukan Kerjasama
            </a>
        </nav>

        <div class="flex items-center gap-2">
            @auth
                @if(auth()->user()->canAccessAdminPanel())
                    <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline text-sm font-semibold" style="color: var(--brand-teal);">Admin</a>
                    <a href="{{ route('admin.rekapitulasi.index') }}" class="hidden xl:inline text-sm font-semibold" style="color: var(--brand-teal);">Rekapitulasi</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium" style="color: var(--brand-muted);">Keluar</button>
                </form>
            @else
                <a href="{{ route('auth.google') }}" class="btn-outline !py-2 !px-3 !text-xs hidden sm:inline-flex">Masuk Google</a>
                <a href="{{ route('login') }}" class="hidden md:inline text-sm font-medium" style="color: var(--brand-muted);">Staff</a>
            @endauth

            <button type="button" id="nav-toggle" class="inline-flex items-center justify-center rounded-lg border p-2 lg:hidden" style="border-color: var(--brand-border); color: var(--brand-teal);" aria-label="Menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>

    <div id="mobile-nav" class="hidden border-t bg-white lg:hidden" style="border-color: var(--brand-border);">
        <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3">
            <a href="{{ route('home') }}" class="nav-link {{ $active === 'beranda' ? 'nav-link-active' : '' }}">Beranda</a>
            <a href="{{ route('pages.profil') }}" class="nav-link {{ $active === 'profil' ? 'nav-link-active' : '' }}">Profil</a>
            <a href="{{ route('pages.mitra') }}" class="nav-link {{ $active === 'mitra' ? 'nav-link-active' : '' }}">Mitra</a>
            <a href="{{ route('pages.skema') }}" class="nav-link {{ $active === 'skema' ? 'nav-link-active' : '' }}">Skema Kerjasama</a>
            <a href="{{ route('pages.kontak') }}" class="nav-link {{ $active === 'kontak' ? 'nav-link-active' : '' }}">Kontak / Helpdesk</a>
            <a href="{{ route('partnership-applications.create') }}" class="btn-cta mt-2 !text-xs">
                <img src="{{ asset('images/icons/icon-edit.svg') }}" alt="" aria-hidden="true">
                Ajukan Kerjasama
            </a>
        </div>
    </div>
</header>
