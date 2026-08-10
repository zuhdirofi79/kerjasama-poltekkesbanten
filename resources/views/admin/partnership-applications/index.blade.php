@extends('layouts.admin')
@section('title', 'Pengajuan Kerjasama')
@section('heading', 'Pengajuan Kerjasama')
@section('content')
<form method="GET" class="mb-4 grid gap-2 md:grid-cols-6">
    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari nomor / instansi / email" class="rounded-lg border-teal-200 text-sm md:col-span-2">
    <select name="status" class="rounded-lg border-teal-200 text-sm">
        <option value="">Semua status</option>
        @foreach($statuses as $status)
            <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <select name="partner_status" class="rounded-lg border-teal-200 text-sm">
        <option value="">DN / LN</option>
        @foreach($partnerStatuses as $value => $label)
            <option value="{{ $value }}" @selected($filters['partner_status'] === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="institution_type" class="rounded-lg border-teal-200 text-sm">
        <option value="">Jenis instansi</option>
        @foreach($institutionTypes as $value => $label)
            <option value="{{ $value }}" @selected($filters['institution_type'] === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="flex gap-2 md:col-span-6">
        <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="rounded-lg border-teal-200 text-sm" aria-label="Dari tanggal">
        <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="rounded-lg border-teal-200 text-sm" aria-label="Sampai tanggal">
        <button class="btn-outline-teal !py-2 !text-xs">Filter</button>
        <a href="{{ route('admin.partnership-applications.index') }}" class="self-center text-xs text-teal-700 hover:underline">Reset</a>
    </div>
</form>

@if($submittedCount > 0)
    <p class="mb-3 text-sm text-teal-800">{{ $submittedCount }} pengajuan baru menunggu telaah.</p>
@endif

<div class="card-surface overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left">
            <tr>
                <th class="px-4 py-2">Nomor</th>
                <th class="px-4 py-2">Tanggal</th>
                <th class="px-4 py-2">Instansi</th>
                <th class="px-4 py-2">Jenis</th>
                <th class="px-4 py-2">Status Mitra</th>
                <th class="px-4 py-2">Narahubung</th>
                <th class="px-4 py-2">Jenis Pengajuan</th>
                <th class="px-4 py-2">Judul</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($applications as $item)
            <tr class="border-t">
                <td class="px-4 py-2 font-medium">{{ $item->submission_code }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $item->submission_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-2">{{ $item->institution_name }}</td>
                <td class="px-4 py-2">{{ $item->labelFor('institution_types', $item->institution_type) }}</td>
                <td class="px-4 py-2">{{ $item->labelFor('partner_statuses', $item->partner_status) }}</td>
                <td class="px-4 py-2">{{ $item->contact_person_name }}</td>
                <td class="px-4 py-2">{{ $item->labelFor('proposal_types', $item->proposal_type) }}</td>
                <td class="px-4 py-2 max-w-[220px] truncate" title="{{ $item->proposal_title }}">{{ $item->proposal_title }}</td>
                <td class="px-4 py-2">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $item->status->badgeClass() }}">
                        {{ $item->status->label() }}
                    </span>
                </td>
                <td class="px-4 py-2 text-right">
                    <a href="{{ route('admin.partnership-applications.show', $item) }}" class="text-teal-700 hover:underline">Detail</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="10" class="px-4 py-8 text-center text-slate-500">Belum ada pengajuan kerja sama.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $applications->links() }}</div>
@endsection
