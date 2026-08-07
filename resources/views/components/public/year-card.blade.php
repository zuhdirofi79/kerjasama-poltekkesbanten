@props([
    'yearFolder',
    'section',
    'category',
])

<a
    href="{{ route('cooperation.year', [$section, $category, $yearFolder->slug]) }}"
    {{ $attributes->merge(['class' => 'year-card']) }}
>
    {{ $yearFolder->year }}
</a>
