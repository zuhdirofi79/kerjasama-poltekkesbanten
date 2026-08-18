@extends('layouts.admin')
@section('title', 'Notifikasi Masa Berlaku')
@section('heading', 'Notifikasi Masa Berlaku')
@section('content')
<p class="mb-4 text-sm text-slate-600">Daftar kerjasama yang akan berakhir dalam 30 hari atau sudah berakhir.</p>
<div class="card-surface overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left">
            <tr>
                <th class="px-4 py-2">Dokumen</th>
                <th class="px-4 py-2">Folder</th>
                <th class="px-4 py-2">Berakhir</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($documents as $document)
            @php
                $status = $document->expiryStatus();
                $days = (new \App\Services\CooperationExpiryService())->daysUntilEnd($document->cooperation_end_date);
            @endphp
            <tr class="border-t">
                <td class="px-4 py-2 font-semibold">{{ $document->title }}</td>
                <td class="px-4 py-2 text-xs">{{ $document->locationLabel() }}</td>
                <td class="px-4 py-2">{{ $document->cooperation_end_date?->format('d M Y') }}</td>
                <td class="px-4 py-2">
                    @if($status)
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $status->badgeClass() }}">{{ $status->label() }}</span>
                        <span class="ml-1 text-xs text-slate-500">
                            @if($days !== null && $days >= 0)
                                {{ $days }} hari lagi
                            @elseif($days !== null)
                                {{ abs($days) }} hari yang lalu
                            @endif
                        </span>
                    @endif
                </td>
                <td class="px-4 py-2 text-right">
                    <a href="{{ route('admin.documents.edit', $document) }}" class="text-teal-700 hover:underline">Edit Metadata</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Tidak ada notifikasi masa berlaku.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $documents->links() }}</div>
@endsection
