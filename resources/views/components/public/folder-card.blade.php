@props([
    'href',
    'name',
    'childrenCount' => 0,
    'documentsCount' => 0,
])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'repository-folder-card']) }}
    aria-label="Buka folder {{ $name }}"
>
    <div class="repository-folder-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 015.5 5H9l2 2h7.5A2.5 2.5 0 0121 9.5v7A2.5 2.5 0 0118.5 19h-13A2.5 2.5 0 013 16.5v-9z"/>
        </svg>
    </div>

    <div class="repository-folder-content">
        <h3 class="repository-folder-title">{{ $name }}</h3>
        <p class="repository-folder-meta">
            {{ $childrenCount }} folder · {{ $documentsCount }} dokumen
        </p>
    </div>

    <span class="repository-folder-arrow" aria-hidden="true">&rsaquo;</span>
</a>
