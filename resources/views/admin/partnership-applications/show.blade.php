@extends('layouts.admin')
@section('title', $application->submission_code)
@section('heading', 'Detail Pengajuan '.$application->submission_code)
@section('content')
<div class="mb-4 flex flex-wrap items-center gap-3">
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $application->status->badgeClass() }}">
        {{ $application->status->label() }}
    </span>
    <a href="{{ route('admin.partnership-applications.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali ke daftar</a>
</div>

<div class="grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="card-surface space-y-3 p-5">
            <h2 class="font-semibold text-teal-950">1. Identitas Calon Mitra</h2>
            <p><span class="text-slate-500">Email instansi:</span> {{ $application->institution_email }}</p>
            <p><span class="text-slate-500">Nama instansi:</span> {{ $application->institution_name }}</p>
            <p><span class="text-slate-500">Alamat:</span> {{ $application->institution_address }}</p>
            <p><span class="text-slate-500">Telepon:</span> {{ $application->institution_phone }}</p>
            <p><span class="text-slate-500">Situs web:</span> {{ $application->institution_website ?: '—' }}</p>
            <p><span class="text-slate-500">Legalitas:</span> {{ $application->legal_registration_number ?: '—' }}</p>
            <p><span class="text-slate-500">Jenis:</span> {{ $application->labelFor('institution_types', $application->institution_type) }}@if($application->institution_type_other) ({{ $application->institution_type_other }})@endif</p>
            <p><span class="text-slate-500">Status mitra:</span> {{ $application->labelFor('partner_statuses', $application->partner_status) }}</p>
            <p><span class="text-slate-500">Negara:</span> {{ $application->country }}</p>
            <p class="whitespace-pre-line"><span class="text-slate-500">Profil:</span><br>{{ $application->institution_profile }}</p>
        </div>

        <div class="card-surface space-y-3 p-5">
            <h2 class="font-semibold text-teal-950">2. Penanggung Jawab dan Narahubung</h2>
            <p><span class="text-slate-500">Pimpinan:</span> {{ $application->authorized_officer_name }} — {{ $application->authorized_officer_position }}</p>
            <p><span class="text-slate-500">Narahubung:</span> {{ $application->contact_person_name }} — {{ $application->contact_person_position }}</p>
            <p><span class="text-slate-500">WhatsApp:</span> {{ $application->contact_person_whatsapp }}</p>
            <p><span class="text-slate-500">Email:</span> {{ $application->contact_person_email }}</p>
        </div>

        <div class="card-surface space-y-3 p-5">
            <h2 class="font-semibold text-teal-950">3. Informasi Usulan Kerja Sama</h2>
            <p><span class="text-slate-500">Jenis pengajuan:</span> {{ $application->labelFor('proposal_types', $application->proposal_type) }}@if($application->proposal_type_other) ({{ $application->proposal_type_other }})@endif</p>
            <p><span class="text-slate-500">Judul:</span> {{ $application->proposal_title }}</p>
            <p class="whitespace-pre-line"><span class="text-slate-500">Tujuan:</span><br>{{ $application->proposal_purpose }}</p>
            <p class="whitespace-pre-line"><span class="text-slate-500">Manfaat:</span><br>{{ $application->expected_benefits }}</p>
            <p><span class="text-slate-500">Unit tujuan:</span> {{ implode(', ', $application->labelsFor('target_units', $application->target_units)) }}@if($application->target_unit_other) ({{ $application->target_unit_other }})@endif</p>
        </div>

        <div class="card-surface space-y-3 p-5">
            <h2 class="font-semibold text-teal-950">4. Ruang Lingkup Kerja Sama</h2>
            <p><span class="text-slate-500">Ruang lingkup:</span> {{ implode(', ', $application->labelsFor('proposed_scopes', $application->proposed_scopes)) }}@if($application->proposed_scope_other) ({{ $application->proposed_scope_other }})@endif</p>
            <p><span class="text-slate-500">Bentuk dokumen:</span> {{ $application->labelFor('document_forms', $application->document_form) }}</p>
            <p class="whitespace-pre-line"><span class="text-slate-500">Uraian kegiatan:</span><br>{{ $application->activity_description }}</p>
            <p class="whitespace-pre-line"><span class="text-slate-500">Sasaran:</span><br>{{ $application->target_description }}</p>
        </div>

        <div class="card-surface space-y-3 p-5">
            <h2 class="font-semibold text-teal-950">5. Rencana Pelaksanaan dan Pembiayaan</h2>
            <p><span class="text-slate-500">Periode:</span> {{ $application->planned_start_date?->format('d/m/Y') }} — {{ $application->planned_end_date?->format('d/m/Y') }}</p>
            <p><span class="text-slate-500">Jangka waktu dokumen:</span> {{ $application->labelFor('proposed_durations', $application->proposed_duration) }}@if($application->proposed_duration_other) ({{ $application->proposed_duration_other }})@endif</p>
            <p><span class="text-slate-500">Lokasi:</span> {{ $application->activity_location }}</p>
            <p class="whitespace-pre-line"><span class="text-slate-500">Kontribusi Poltekkes:</span><br>{{ $application->expected_poltekkes_contribution ?: '—' }}</p>
            <p><span class="text-slate-500">Perlu pembiayaan:</span> {{ $application->labelFor('requires_funding_options', $application->requires_funding) }}</p>
            <p><span class="text-slate-500">Sumber pembiayaan:</span> {{ implode(', ', $application->labelsFor('funding_sources', $application->funding_sources)) }}</p>
        </div>

        <div class="card-surface space-y-3 p-5">
            <h2 class="font-semibold text-teal-950">7. Pernyataan Calon Mitra</h2>
            <p><span class="text-slate-500">Pengisi:</span> {{ $application->declarant_name }} — {{ $application->declarant_position }}</p>
            <p><span class="text-slate-500">Tanggal pengajuan:</span> {{ $application->submission_date?->format('d/m/Y') }}</p>
            <p><span class="text-slate-500">Pernyataan:</span> Data akurat, bersedia verifikasi, menyetujui penggunaan data.</p>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card-surface space-y-3 p-5">
            <h2 class="font-semibold text-teal-950">6. Dokumen Pendukung</h2>
            @forelse($application->attachments as $attachment)
                <div class="rounded-lg border border-teal-100 bg-teal-50/40 p-3">
                    <p class="text-sm font-medium text-teal-950">{{ $attachment->typeLabel() }}</p>
                    <p class="text-xs text-slate-500">{{ $attachment->original_filename }} · {{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                    <a
                        href="{{ route('admin.partnership-applications.attachments.download', [$application, $attachment]) }}"
                        class="mt-2 inline-block text-sm font-semibold text-teal-700 hover:underline"
                    >Unduh</a>
                </div>
            @empty
                <p class="text-sm text-slate-500">Tidak ada lampiran.</p>
            @endforelse
        </div>

        <div class="card-surface space-y-4 p-5">
            <h2 class="font-semibold text-teal-950">Tinjauan Status</h2>
            <p class="text-sm text-slate-500">Ditinjau: {{ $application->reviewer?->name ?: '—' }} @if($application->reviewed_at) ({{ $application->reviewed_at->format('d/m/Y H:i') }}) @endif</p>
            <p class="text-sm"><span class="text-slate-500">Catatan terakhir:</span> {{ $application->review_note ?: '—' }}</p>

            <form method="POST" action="{{ route('admin.partnership-applications.status', $application) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium">Status baru</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-teal-200" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected($application->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Catatan review</label>
                    <textarea name="review_note" rows="3" class="mt-1 w-full rounded-lg border-teal-200">{{ old('review_note', $application->review_note) }}</textarea>
                </div>
                <button class="btn-teal">Simpan Status</button>
            </form>
        </div>
    </div>
</div>
@endsection
