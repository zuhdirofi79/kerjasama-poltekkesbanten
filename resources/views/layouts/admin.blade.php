<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Kerjasama Poltekkes Banten</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-100">
<div class="flex min-h-screen">
    <aside class="hidden w-64 shrink-0 bg-teal-900 text-white md:flex md:flex-col">
        <div class="border-b border-teal-800 px-5 py-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="rounded-lg bg-white px-2 py-1.5">
                    <img
                        src="{{ asset('images/branding/logo-poltekkes.png') }}"
                        alt="Kemenkes Poltekkes Banten"
                        class="h-9 w-auto object-contain"
                        width="140"
                        height="36"
                    >
                </div>
                <img
                    src="{{ asset('images/branding/logo-blu.svg') }}"
                    alt="Badan Layanan Umum"
                    class="h-10 w-10 object-contain"
                    width="40"
                    height="40"
                >
            </a>
            <span class="mt-3 block text-xs font-semibold uppercase tracking-wider text-teal-300">Admin Panel</span>
        </div>
        <nav class="flex-1 space-y-1 px-3 py-4">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'admin-nav-link-active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.rekapitulasi.index') }}" class="admin-nav-link {{ request()->routeIs('admin.rekapitulasi.*') ? 'admin-nav-link-active' : '' }}">Rekapitulasi</a>
            <a href="{{ route('admin.monitoring.index') }}" class="admin-nav-link {{ request()->routeIs('admin.monitoring.*') || request()->routeIs('admin.satisfaction.*') ? 'admin-nav-link-active' : '' }}">Monitoring &amp; Evaluasi</a>
            <a href="{{ route('admin.years.index') }}" class="admin-nav-link {{ request()->routeIs('admin.years.*') ? 'admin-nav-link-active' : '' }}">Folder Tahun</a>
            <a href="{{ route('admin.folders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.folders.*') ? 'admin-nav-link-active' : '' }}">Repository</a>
            <a href="{{ route('admin.documents.index') }}" class="admin-nav-link {{ request()->routeIs('admin.documents.*') ? 'admin-nav-link-active' : '' }}">Dokumen</a>
            @if(auth()->user()->role?->canManagePartnershipApplications())
                @php
                    $partnershipSubmittedCount = \App\Models\PartnershipApplication::query()
                        ->where('status', \App\Enums\PartnershipApplicationStatus::Submitted)
                        ->count();
                @endphp
                <a href="{{ route('admin.partnership-applications.index') }}" class="admin-nav-link {{ request()->routeIs('admin.partnership-applications.*') ? 'admin-nav-link-active' : '' }}">
                    Pengajuan Kerjasama
                    @if($partnershipSubmittedCount > 0)
                        <span class="ml-1 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-amber-400 px-1.5 text-[10px] font-bold text-teal-950">{{ $partnershipSubmittedCount }}</span>
                    @endif
                </a>
            @endif
            @if(auth()->user()->role?->canManageAccessRequests())
                <a href="{{ route('admin.access-requests.index') }}" class="admin-nav-link {{ request()->routeIs('admin.access-requests.*') ? 'admin-nav-link-active' : '' }}">Permintaan Akses</a>
            @endif
            @if(auth()->user()->isSuperadmin1() || auth()->user()->isSuperadmin2())
                <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'admin-nav-link-active' : '' }}">Pengguna</a>
            @endif
            @if(auth()->user()->role?->canViewAuditLogs())
                <a href="{{ route('admin.audit-logs.index') }}" class="admin-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'admin-nav-link-active' : '' }}">Audit Log</a>
            @endif
        </nav>
        <div class="border-t border-teal-800 px-4 py-4 text-sm">
            <p class="truncate font-medium">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-teal-300">{{ auth()->user()->role?->label() }}</p>
            <div class="mt-3 flex gap-3 text-xs">
                <a href="{{ route('home') }}" class="text-teal-200 hover:underline">Situs Publik</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-teal-200 hover:underline">Keluar</button></form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 md:px-6">
            <h1 class="text-lg font-bold text-teal-950">@yield('heading', 'Admin')</h1>
            <div class="flex items-center gap-3">
                @if(auth()->user()?->canAccessAdminPanel())
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-teal-100 text-teal-800" @click="open = !open" aria-label="Notifikasi masa berlaku">
                            <svg width="20" height="20" min-width="20" min-height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width:20px;height:20px;min-width:20px;min-height:20px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/>
                            </svg>
                            @if(($expiryNotificationCount ?? 0) > 0)
                                <span class="absolute -right-1 -top-1 inline-flex min-h-[1.15rem] min-w-[1.15rem] items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-white">{{ $expiryNotificationCount }}</span>
                            @endif
                        </button>
                        <div x-cloak x-show="open" @click.outside="open = false" class="absolute right-0 z-30 mt-2 w-80 overflow-hidden rounded-xl border border-teal-100 bg-white shadow-sm">
                            <div class="border-b border-teal-100 px-4 py-2 text-sm font-semibold text-teal-950">Notifikasi Masa Berlaku</div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse($expiryNotifications ?? [] as $item)
                                    @php
                                        $days = (new \App\Services\CooperationExpiryService())->daysUntilEnd($item->cooperation_end_date);
                                        $status = $item->expiryStatus();
                                    @endphp
                                    <a href="{{ route('admin.documents.edit', $item) }}" class="block border-b border-slate-100 px-4 py-3 text-sm hover:bg-teal-50">
                                        <p class="font-semibold text-teal-950">{{ $item->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            @if($days !== null && $days >= 0)
                                                Berakhir {{ $days }} hari lagi
                                            @elseif($days !== null)
                                                Sudah berakhir {{ abs($days) }} hari
                                            @endif
                                            · {{ $item->cooperation_end_date?->format('d M Y') }}
                                        </p>
                                    </a>
                                @empty
                                    <p class="px-4 py-4 text-sm text-slate-500">Tidak ada notifikasi.</p>
                                @endforelse
                            </div>
                            <a href="{{ route('admin.expiry.index') }}" class="block px-4 py-2 text-center text-xs font-semibold text-teal-700 hover:bg-teal-50">Lihat Semua</a>
                        </div>
                    </div>
                @endif
                <a href="{{ route('home') }}" class="text-sm font-medium text-teal-700 md:hidden">Publik</a>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
