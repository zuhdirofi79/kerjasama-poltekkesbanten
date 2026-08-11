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
            <a href="{{ route('home') }}" class="text-sm font-medium text-teal-700 md:hidden">Publik</a>
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
</body>
</html>
