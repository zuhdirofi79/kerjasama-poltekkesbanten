@extends('layouts.admin')
@section('title', 'Monitoring & Evaluasi')
@section('heading', 'Monitoring & Evaluasi')
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Kerjasama Aktif</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $stats['active'] }}</p>
    </div>
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Akan Berakhir</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $stats['expiring_soon'] }}</p>
    </div>
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Sudah Berakhir</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $stats['expired'] }}</p>
    </div>
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Jumlah Survey</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $analytics['response_count'] }}</p>
    </div>
    <div class="card-surface p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Rata-rata Kepuasan</p>
        <p class="mt-2 text-3xl font-bold text-teal-950">{{ $analytics['average'] ?? '—' }}</p>
    </div>
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <div class="card-surface p-4">
        <h2 class="font-semibold text-teal-950">Rata-rata per indikator</h2>
        <canvas id="chart-questions" width="480" height="240" style="width:100%;height:240px;max-width:100%;"></canvas>
    </div>
    <div class="card-surface p-4">
        <h2 class="font-semibold text-teal-950">Distribusi rating 1–5</h2>
        <canvas id="chart-distribution" width="480" height="240" style="width:100%;height:240px;max-width:100%;"></canvas>
    </div>
    <div class="card-surface p-4 lg:col-span-2">
        <h2 class="font-semibold text-teal-950">Tren response per bulan</h2>
        <canvas id="chart-trend" width="960" height="220" style="width:100%;height:220px;max-width:100%;"></canvas>
    </div>
</div>

<div class="mt-4 flex flex-wrap gap-3">
    <a href="{{ route('admin.satisfaction.questions.index') }}" class="text-sm font-medium text-teal-700 hover:underline">Kelola indikator kuesioner</a>
</div>

<form method="GET" class="card-surface mt-6 grid gap-3 p-4 md:grid-cols-4">
    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Mitra / judul" class="rounded-lg border-teal-200 text-sm">
    <select name="area" class="rounded-lg border-teal-200 text-sm">
        <option value="">Bidang</option>
        @foreach($areas as $value => $label)
            <option value="{{ $value }}" @selected(($filters['area'] ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="expiry_status" class="rounded-lg border-teal-200 text-sm">
        <option value="">Status</option>
        <option value="active" @selected(($filters['expiry_status'] ?? '') === 'active')>Aktif</option>
        <option value="expiring_soon" @selected(($filters['expiry_status'] ?? '') === 'expiring_soon')>Akan Berakhir</option>
        <option value="expired" @selected(($filters['expiry_status'] ?? '') === 'expired')>Kedaluwarsa</option>
    </select>
    <div class="flex gap-2">
        <button class="btn-teal !py-2 !text-xs">Filter</button>
        <a href="{{ route('admin.monitoring.index') }}" class="btn-outline-teal !py-2 !text-xs">Reset</a>
    </div>
</form>

<div class="card-surface mt-6 overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left">
            <tr>
                <th class="px-4 py-2">Kerjasama</th>
                <th class="px-4 py-2">Mitra</th>
                <th class="px-4 py-2">Bidang</th>
                <th class="px-4 py-2">Mulai</th>
                <th class="px-4 py-2">Berakhir</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Kepuasan</th>
                <th class="px-4 py-2">Tindakan</th>
            </tr>
        </thead>
        <tbody>
        @forelse($documents as $document)
            @php
                $avg = $document->satisfactionSurveys
                    ->flatMap->responses
                    ->flatMap->answers
                    ->avg('score');
            @endphp
            <tr class="border-t align-top">
                <td class="px-4 py-2 font-semibold">{{ $document->title }}</td>
                <td class="px-4 py-2">{{ $document->institutionFolder?->name ?: ($document->documentFolder?->name ?: '—') }}</td>
                <td class="px-4 py-2">{{ $document->areaLabel() ?: 'Belum diklasifikasikan' }}</td>
                <td class="px-4 py-2">{{ $document->cooperation_start_date?->format('d/m/Y') ?: '—' }}</td>
                <td class="px-4 py-2">{{ $document->cooperation_end_date?->format('d/m/Y') ?: '—' }}</td>
                <td class="px-4 py-2">
                    @if($document->expiryStatus())
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $document->expiryStatus()->badgeClass() }}">{{ $document->expiryStatus()->label() }}</span>
                    @else
                        —
                    @endif
                </td>
                <td class="px-4 py-2">{{ $avg ? number_format($avg, 2) : '—' }}</td>
                <td class="px-4 py-2">
                    <form method="POST" action="{{ route('admin.monitoring.surveys.store') }}" class="mb-2">
                        @csrf
                        <input type="hidden" name="document_id" value="{{ $document->id }}">
                        <button class="text-xs font-semibold text-teal-700 hover:underline">Buat tautan survei</button>
                    </form>
                    <a href="{{ route('admin.documents.edit', $document) }}" class="text-xs text-teal-700 hover:underline">Edit Metadata</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">Tidak ada data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $documents->links() }}</div>
@endsection

@push('scripts')
@vite(['resources/js/charts.js'])
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Chart) return;
    const questionLabels = @json($analytics['per_question']->pluck('body'));
    const questionScores = @json($analytics['per_question']->pluck('answers_avg_score')->map(fn ($v) => $v === null ? 0 : round((float) $v, 2)));
    const dist = @json($analytics['distribution']);
    const trendLabels = @json($analytics['trend']->pluck('period'));
    const trendTotals = @json($analytics['trend']->pluck('total'));
    const teal = '#0f766e';

    const q = document.getElementById('chart-questions');
    if (q) new Chart(q, {
        type: 'bar',
        data: { labels: questionLabels, datasets: [{ label: 'Rata-rata', data: questionScores, backgroundColor: teal }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { min: 0, max: 5 } } }
    });
    const d = document.getElementById('chart-distribution');
    if (d) new Chart(d, {
        type: 'doughnut',
        data: { labels: ['1','2','3','4','5'], datasets: [{ data: [dist[1]||0, dist[2]||0, dist[3]||0, dist[4]||0, dist[5]||0], backgroundColor: ['#fecaca','#fdba74','#fde68a','#99f6e4','#5eead4'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    const t = document.getElementById('chart-trend');
    if (t) new Chart(t, {
        type: 'line',
        data: { labels: trendLabels, datasets: [{ label: 'Response', data: trendTotals, borderColor: teal, tension: 0.2 }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
});
</script>
@endpush
