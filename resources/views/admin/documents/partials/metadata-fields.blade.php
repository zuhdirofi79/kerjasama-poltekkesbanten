@php
    $doc = $document ?? null;
    $selectedArea = old('cooperation_area', $doc?->cooperation_area ?? '');
    $selectedSubtype = old('cooperation_subtype', $doc?->cooperation_subtype ?? '');
@endphp

<section
    class="rounded-xl border border-teal-100 bg-teal-50/40 p-4 space-y-3"
    x-data="documentMetadataForm({
        area: @js($selectedArea),
        subtype: @js($selectedSubtype),
        subtypesMap: @js($subtypes),
    })"
>
    <h2 class="text-sm font-semibold text-teal-950">Metadata Dokumen</h2>
    <p class="text-xs text-slate-500">Bidang dan sub dokumen tersimpan di database, terpisah dari nama folder. Mengganti nama folder tidak mengubah rekapitulasi.</p>

    <div>
        <label class="block text-sm font-medium" for="document_date">Tanggal Dokumen</label>
        <input
            id="document_date"
            type="date"
            name="document_date"
            value="{{ old('document_date', $doc?->document_date?->toDateString()) }}"
            class="mt-1 w-full rounded-lg border-teal-200"
        >
    </div>

    <div>
        <label class="block text-sm font-medium" for="cooperation_area">Bidang</label>
        <select id="cooperation_area" name="cooperation_area" class="mt-1 w-full rounded-lg border-teal-200" x-model="area" @change="onAreaChange()">
            <option value="">— Pilih Bidang —</option>
            @foreach($areas as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium" for="cooperation_subtype">Sub Dokumen</label>
        <select
            id="cooperation_subtype"
            name="cooperation_subtype"
            class="mt-1 w-full rounded-lg border-teal-200"
            x-model="subtype"
            :disabled="!area || Object.keys(subtypes()).length === 0"
        >
            <option value="">— Pilih Sub Dokumen —</option>
            <template x-for="(label, value) in subtypes()" :key="value">
                <option :value="value" x-text="label"></option>
            </template>
        </select>
        <p class="mt-1 text-xs text-slate-500" x-show="area === 'other' || (area && Object.keys(subtypes()).length === 0)">Bidang ini tidak memiliki sub dokumen.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium" for="cooperation_start_date">Tanggal Mulai Kerjasama</label>
            <input
                id="cooperation_start_date"
                type="date"
                name="cooperation_start_date"
                value="{{ old('cooperation_start_date', $doc?->cooperation_start_date?->toDateString()) }}"
                class="mt-1 w-full rounded-lg border-teal-200"
            >
        </div>
        <div>
            <label class="block text-sm font-medium" for="cooperation_end_date">Tanggal Berakhir Kerjasama</label>
            <input
                id="cooperation_end_date"
                type="date"
                name="cooperation_end_date"
                value="{{ old('cooperation_end_date', $doc?->cooperation_end_date?->toDateString()) }}"
                class="mt-1 w-full rounded-lg border-teal-200"
            >
        </div>
    </div>
    <p class="text-xs text-slate-500">Opsional. Isi hanya jika dokumen merupakan perjanjian/kerja sama yang memiliki masa berlaku.</p>
</section>
