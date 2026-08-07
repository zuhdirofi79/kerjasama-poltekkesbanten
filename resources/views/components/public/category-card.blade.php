@props([
    'category',
    'section',
])

@php
    $icon = \App\Support\CategoryIcon::path($category->slug ?? null);
@endphp

<a
    href="{{ route('cooperation.category', [$section, $category]) }}"
    {{ $attributes->merge(['class' => 'category-card']) }}
>
    <img src="{{ $icon }}" alt="" class="category-card__icon" aria-hidden="true">
    <span class="category-card__label">{{ $category->name }}</span>
</a>
