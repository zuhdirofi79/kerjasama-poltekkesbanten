@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    @foreach([
        'years' => 'Folder Tahun',
        'institutions' => 'Folder',
        'documents' => 'Dokumen',
        'pending_requests' => 'Pending Akses',
        'users' => 'Pengguna',
    ] as $key => $label)
        <div class="card-surface p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold text-teal-950">{{ $stats[$key] }}</p>
        </div>
    @endforeach
</div>

<div class="card-surface mt-6 overflow-hidden">
    <div class="border-b border-teal-100 px-4 py-3">
        <h2 class="font-semibold text-teal-950">Permintaan akses terbaru</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-teal-50 text-left text-teal-800">
                <tr>
                    <th class="px-4 py-2">Pengguna</th>
                    <th class="px-4 py-2">Dokumen</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPending as $item)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2">{{ $item->user?->email }}</td>
                        <td class="px-4 py-2">{{ $item->document?->title }}</td>
                        <td class="px-4 py-2">{{ $item->requested_at?->diffForHumans() }}</td>
                        <td class="px-4 py-2 text-right">
                            @can('view', $item)
                                <a href="{{ route('admin.access-requests.show', $item) }}" class="font-medium text-teal-700 hover:underline">Tinjau</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Tidak ada permintaan pending.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
