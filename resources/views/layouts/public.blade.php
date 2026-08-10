<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kerjasama') — Poltekkes Kemenkes Banten</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/partnership.css', 'resources/js/app.js'])
</head>
<body class="page-shell">
    <x-public.header :active="trim($__env->yieldContent('nav', 'beranda'))" />

    @if (session('status'))
        <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border px-4 py-3 text-sm" style="border-color: var(--brand-border); background: var(--brand-cyan-soft); color: var(--brand-teal-deep);">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <x-public.footer />

    <script>
        document.getElementById('nav-toggle')?.addEventListener('click', () => {
            document.getElementById('mobile-nav')?.classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
