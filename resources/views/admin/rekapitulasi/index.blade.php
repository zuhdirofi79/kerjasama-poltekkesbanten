@extends('layouts.admin')
@section('title', 'Rekapitulasi')
@section('heading', 'Rekapitulasi')
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Total Tri Dharma</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $kpi['total'] }}</p>
        <p class="mt-1 text-xs text-slate-500">dokumen</p>
    </div>
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Pendidikan</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $kpi['education'] }}</p>
        <p class="mt-1 text-xs text-slate-500">dokumen</p>
    </div>
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Penelitian</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $kpi['research'] }}</p>
        <p class="mt-1 text-xs text-slate-500">dokumen</p>
    </div>
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Pengabdian Masyarakat</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $kpi['community_service'] }}</p>
        <p class="mt-1 text-xs text-slate-500">dokumen</p>
    </div>
</div>
<p class="mt-3 text-xs text-slate-500">Pengembangan SDM tidak dihitung sebagai Tri Dharma.</p>

<form method="GET" action="{{ route('admin.rekapitulasi.index') }}" class="card-surface mt-6 grid gap-3 p-4 lg:grid-cols-4">
    <select name="year" class="rounded-lg border-teal-200 text-sm">
        <option value="">Tahun</option>
        @foreach($years as $year)
            <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
        @endforeach
    </select>
    <select name="section" class="rounded-lg border-teal-200 text-sm">
        <option value="">Dalam/Luar Negeri</option>
        <option value="domestic" @selected(($filters['section'] ?? '') === 'domestic')>Dalam Negeri</option>
        <option value="international" @selected(($filters['section'] ?? '') === 'international')>Luar Negeri</option>
    </select>
    <select name="category_id" class="rounded-lg border-teal-200 text-sm">
        <option value="">Kategori</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>
                {{ $category->section?->name }} / {{ $category->name }}
            </option>
        @endforeach
    </select>
    <select name="area" class="rounded-lg border-teal-200 text-sm">
        <option value="">Bidang</option>
        @foreach($areas as $value => $label)
            <option value="{{ $value }}" @selected(($filters['area'] ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="subtype" class="rounded-lg border-teal-200 text-sm">
        <option value="">Sub Dokumen</option>
        @foreach($subtypes as $area => $items)
            @foreach($items as $value => $label)
                <option value="{{ $value }}" @selected(($filters['subtype'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        @endforeach
    </select>
    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-lg border-teal-200 text-sm" aria-label="Dari tanggal">
    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-lg border-teal-200 text-sm" aria-label="Sampai tanggal">
    <select name="expiry_status" class="rounded-lg border-teal-200 text-sm">
        <option value="">Status Masa Berlaku</option>
        <option value="active" @selected(($filters['expiry_status'] ?? '') === 'active')>Aktif</option>
        <option value="expiring_soon" @selected(($filters['expiry_status'] ?? '') === 'expiring_soon')>Akan Berakhir</option>
        <option value="expired" @selected(($filters['expiry_status'] ?? '') === 'expired')>Kedaluwarsa</option>
        <option value="unspecified" @selected(($filters['expiry_status'] ?? '') === 'unspecified')>Belum diisi</option>
    </select>
    <div class="flex gap-2 lg:col-span-4">
        <button class="btn-teal !py-2 !text-xs">Filter</button>
        <a href="{{ route('admin.rekapitulasi.index') }}" class="btn-outline-teal !py-2 !text-xs">Reset</a>
    </div>
</form>

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <div class="card-surface overflow-hidden">
        <div class="border-b border-teal-100 px-4 py-3 font-semibold text-teal-950">Per bidang</div>
        <table class="min-w-full text-sm">
            <tbody>
            @forelse($areas as $value => $label)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $label }}</td>
                    <td class="px-4 py-2 text-right font-semibold">{{ $byArea[$value] ?? 0 }}</td>
                </tr>
            @empty
                <tr><td class="px-4 py-3 text-slate-500">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-surface overflow-hidden">
        <div class="border-b border-teal-100 px-4 py-3 font-semibold text-teal-950">Per tahun</div>
        <table class="min-w-full text-sm">
            <tbody>
            @forelse($byYear as $year => $total)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $year }}</td>
                    <td class="px-4 py-2 text-right font-semibold">{{ $total }}</td>
                </tr>
            @empty
                <tr><td class="px-4 py-3 text-slate-500">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-surface mt-6 overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left">
            <tr>
                <th class="px-4 py-2">Judul</th>
                <th class="px-4 py-2">Bidang</th>
                <th class="px-4 py-2">Sub Dokumen</th>
                <th class="px-4 py-2">Tanggal Dokumen</th>
                <th class="px-4 py-2">Mulai</th>
                <th class="px-4 py-2">Berakhir</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Folder</th>
            </tr>
        </thead>
        <tbody>
        @forelse($documents as $document)
            <tr class="border-t">
                <td class="px-4 py-2 font-semibold">
                    <a href="{{ route('admin.documents.edit', $document) }}" class="text-teal-800 hover:underline">{{ $document->title }}</a>
                </td>
                <td class="px-4 py-2">{{ $document->areaLabel() ?: 'Belum diklasifikasikan' }}</td>
                <td class="px-4 py-2">{{ $document->subtypeLabel() ?: '—' }}</td>
                <td class="px-4 py-2">{{ $document->document_date?->format('d/m/Y') ?: '—' }}</td>
                <td class="px-4 py-2">{{ $document->cooperation_start_date?->format('d/m/Y') ?: '—' }}</td>
                <td class="px-4 py-2">{{ $document->cooperation_end_date?->format('d/m/Y') ?: '—' }}</td>
                <td class="px-4 py-2">
                    @if($document->expiryStatus())
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $document->expiryStatus()->badgeClass() }}">{{ $document->expiryStatus()->label() }}</span>
                    @else
                        —
                    @endif
                </td>
                <td class="px-4 py-2 text-xs">{{ $document->locationLabel() }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">Tidak ada dokumen yang sesuai dengan filter.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $documents->links() }}</div>
@endsection
