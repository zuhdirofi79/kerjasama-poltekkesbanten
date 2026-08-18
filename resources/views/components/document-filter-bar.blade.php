@props([
    'action',
    'filters' => [],
    'areas' => [],
    'subtypes' => [],
    'sorts' => [],
    'showReset' => true,
])

@php
    $currentArea = $filters['area'] ?? '';
    $currentSubtype = $filters['subtype'] ?? '';
    $areaSubtypes = $currentArea !== '' ? ($subtypes[$currentArea] ?? []) : [];
@endphp

<form
    method="GET"
    action="{{ $action }}"
    class="document-filter-bar"
    x-data="documentFilterBar({
        area: @js($currentArea),
        subtype: @js($currentSubtype),
        subtypesMap: @js($subtypes),
    })"
>
    <input
        type="search"
        name="q"
        value="{{ $filters['q'] ?? '' }}"
        placeholder="Cari dokumen..."
        class="document-filter-bar__search"
        aria-label="Cari dokumen"
    >

    <select name="area" class="document-filter-bar__select" x-model="area" @change="onAreaChange()">
        <option value="">Bidang</option>
        @foreach($areas as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    <select name="subtype" class="document-filter-bar__select" x-model="subtype" :disabled="!area || Object.keys(subtypes()).length === 0">
        <option value="">Sub Dokumen</option>
        <template x-for="(label, value) in subtypes()" :key="value">
            <option :value="value" x-text="label"></option>
        </template>
    </select>

    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="document-filter-bar__date" aria-label="Dari tanggal">
    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="document-filter-bar__date" aria-label="Sampai tanggal">

    <select name="sort" class="document-filter-bar__select">
        @foreach($sorts as $value => $label)
            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'document_date_desc') === $value)>{{ $label }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn-solid !px-3 !py-2 !text-xs">Filter</button>
    @if($showReset)
        <a href="{{ $action }}" class="btn-outline !px-3 !py-2 !text-xs">Reset</a>
    @endif
</form>
