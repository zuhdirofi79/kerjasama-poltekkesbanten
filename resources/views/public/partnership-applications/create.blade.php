@extends('layouts.public')

@section('title', 'Ajukan Kerjasama')
@section('nav', 'beranda')

@php
    $hasErrors = $errors->any();
    $oldUnits = old('target_units', []);
    $oldScopes = old('proposed_scopes', []);
    $oldFunding = old('funding_sources', []);
    $initialStep = $hasErrors ? 1 : 0;
@endphp

@section('content')
<div class="content-section">
    <div
        class="pa-shell"
        x-data="partnershipWizard({
            initialStep: {{ (int) $initialStep }},
            hasErrors: {{ $hasErrors ? 'true' : 'false' }},
            today: @js($today),
            prefillEmail: @js($prefill['email'] ?? ''),
            prefillName: @js($prefill['name'] ?? ''),
            old: {
                institution_type: @js(old('institution_type')),
                partner_status: @js(old('partner_status', 'dalam_negeri')),
                country: @js(old('country', 'Indonesia')),
                proposal_type: @js(old('proposal_type')),
                target_units: @js($oldUnits),
                proposed_scopes: @js($oldScopes),
                document_form: @js(old('document_form')),
                proposed_duration: @js(old('proposed_duration')),
                requires_funding: @js(old('requires_funding')),
                funding_sources: @js($oldFunding),
                institution_name: @js(old('institution_name')),
                contact_person_name: @js(old('contact_person_name')),
                proposal_title: @js(old('proposal_title')),
                submission_date: @js(old('submission_date', $today)),
            }
        })"
        x-cloak
    >
        <div class="mb-6 text-center sm:text-left">
            <p class="text-sm font-semibold uppercase tracking-wide" style="color: var(--brand-teal);">Unit Kerja Sama</p>
            <h1 class="mt-1 text-2xl font-bold sm:text-3xl" style="color: var(--brand-teal-deep);">Formulir Pengajuan Kerja Sama</h1>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed" style="color: var(--brand-muted);">
                Calon Mitra Poltekkes Kemenkes Banten
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-semibold">Pengajuan belum dapat dikirim. Periksa kembali isian berikut:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <p class="mt-2 text-xs">Jika Anda mengunggah berkas, unggah ulang lampiran sebelum mengirim kembali.</p>
            </div>
        @endif

        {{-- Intro --}}
        <div class="pa-card" x-show="step === 0" x-transition>
            <h2 class="text-xl font-bold" style="color: var(--brand-teal-deep);">Formulir Pengajuan Kerja Sama</h2>
            <p class="mt-1 text-sm font-medium" style="color: var(--brand-teal);">Calon Mitra Poltekkes Kemenkes Banten</p>
            <p class="mt-4 text-sm leading-relaxed" style="color: var(--brand-muted);">
                Formulir ini digunakan oleh calon mitra untuk mengajukan kerja sama dengan Politeknik Kesehatan Kementerian Kesehatan Banten.
                Informasi yang disampaikan akan digunakan sebagai bahan telaah, koordinasi, dan penyusunan dokumen kerja sama.
            </p>
            <ul class="mt-5 space-y-2 text-sm" style="color: var(--brand-muted);">
                <li>7 langkah singkat: identitas, narahubung, usulan, ruang lingkup, pelaksanaan, dokumen, dan pernyataan.</li>
                <li>Siapkan surat resmi pengajuan (PDF/DOC/DOCX, maks. {{ $maxUploadMb }} MB).</li>
                <li>Data tersimpan di sistem Unit Kerja Sama Poltekkes Kemenkes Banten.</li>
            </ul>
            <div class="mt-8">
                <button type="button" class="btn-solid" @click="step = 1">Mulai Pengajuan</button>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('partnership-applications.store') }}"
            enctype="multipart/form-data"
            x-show="step > 0"
            x-transition
            @submit="onSubmit"
            novalidate
        >
            @csrf

            <div class="pa-honeypot" aria-hidden="true">
                <label for="company_fax">Fax</label>
                <input type="text" name="company_fax" id="company_fax" tabindex="-1" autocomplete="off">
            </div>

            <div class="pa-card">
                <div class="pa-progress" x-show="step >= 1 && step <= 7">
                    <p class="mb-2 text-sm font-semibold" style="color: var(--brand-teal-deep);">
                        Langkah <span x-text="step"></span> dari 7
                    </p>
                    <div class="pa-progress__track" role="list" aria-label="Progres formulir">
                        <template x-for="n in 7" :key="n">
                            <div class="flex items-center gap-1" role="listitem">
                                <span
                                    class="pa-dot"
                                    :class="{ 'pa-dot--active': step === n, 'pa-dot--done': step > n }"
                                    x-text="n"
                                ></span>
                                <span class="pa-line" :class="{ 'pa-line--done': step > n }" x-show="n < 7"></span>
                            </div>
                        </template>
                    </div>
                    <p class="mt-2 text-xs font-medium" style="color: var(--brand-muted);" x-text="stepTitles[step]"></p>
                </div>

                {{-- Step 1 --}}
                <section x-show="step === 1" aria-labelledby="pa-step-1-title">
                    <h2 id="pa-step-1-title" class="text-lg font-bold" style="color: var(--brand-teal-deep);">1. Identitas Calon Mitra</h2>
                    <div class="mt-5 grid gap-4">
                        <div>
                            <label class="pa-label" for="institution_email">Alamat email resmi instansi <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="email" name="institution_email" id="institution_email" value="{{ old('institution_email', $prefill['email'] ?? '') }}" maxlength="255" required aria-describedby="err-institution_email">
                            @error('institution_email')<p class="pa-error" id="err-institution_email">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="institution_name">Nama resmi instansi atau lembaga <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="institution_name" id="institution_name" x-model="form.institution_name" value="{{ old('institution_name') }}" maxlength="255" required>
                            @error('institution_name')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="institution_address">Alamat lengkap instansi atau lembaga <span class="text-red-600">*</span></label>
                            <textarea class="pa-textarea" name="institution_address" id="institution_address" maxlength="2000" required>{{ old('institution_address') }}</textarea>
                            @error('institution_address')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="institution_phone">Nomor telepon instansi <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="institution_phone" id="institution_phone" value="{{ old('institution_phone') }}" maxlength="64" required>
                            @error('institution_phone')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="institution_website">Situs web instansi (opsional)</label>
                            <input class="pa-input" type="url" name="institution_website" id="institution_website" value="{{ old('institution_website') }}" maxlength="255" placeholder="https://">
                            @error('institution_website')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="legal_registration_number">Nomor legalitas, izin operasional, atau akreditasi (opsional)</label>
                            <input class="pa-input" type="text" name="legal_registration_number" id="legal_registration_number" value="{{ old('legal_registration_number') }}" maxlength="255">
                            @error('legal_registration_number')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="institution_profile">Profil singkat calon mitra <span class="text-red-600">*</span></label>
                            <textarea class="pa-textarea" name="institution_profile" id="institution_profile" maxlength="5000" required>{{ old('institution_profile') }}</textarea>
                            @error('institution_profile')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <fieldset>
                            <legend class="pa-label">Jenis instansi atau lembaga <span class="text-red-600">*</span></legend>
                            <div class="pa-choice-grid">
                                @foreach($options['institution_types'] as $value => $label)
                                    <label class="pa-choice">
                                        <input type="radio" name="institution_type" value="{{ $value }}" x-model="form.institution_type" @checked(old('institution_type') === $value) required>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('institution_type')<p class="pa-error">{{ $message }}</p>@enderror
                            <div class="mt-3" x-show="form.institution_type === 'lainnya'">
                                <label class="pa-label" for="institution_type_other">Jenis instansi lainnya <span class="text-red-600">*</span></label>
                                <input class="pa-input" type="text" name="institution_type_other" id="institution_type_other" value="{{ old('institution_type_other') }}" maxlength="255">
                                @error('institution_type_other')<p class="pa-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="pa-label">Status calon mitra <span class="text-red-600">*</span></legend>
                            <div class="pa-choice-grid">
                                @foreach($options['partner_statuses'] as $value => $label)
                                    <label class="pa-choice">
                                        <input type="radio" name="partner_status" value="{{ $value }}" x-model="form.partner_status" @checked(old('partner_status', 'dalam_negeri') === $value) required>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('partner_status')<p class="pa-error">{{ $message }}</p>@enderror
                        </fieldset>
                        <div>
                            <label class="pa-label" for="country">Negara asal calon mitra <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="country" id="country" x-model="form.country" maxlength="120" required>
                            @error('country')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Step 2 --}}
                <section x-show="step === 2" aria-labelledby="pa-step-2-title">
                    <h2 id="pa-step-2-title" class="text-lg font-bold" style="color: var(--brand-teal-deep);">2. Penanggung Jawab dan Narahubung</h2>
                    <div class="mt-5 grid gap-4">
                        <div>
                            <label class="pa-label" for="authorized_officer_name">Nama pimpinan atau pejabat yang berwenang <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="authorized_officer_name" id="authorized_officer_name" value="{{ old('authorized_officer_name') }}" maxlength="255" required>
                            @error('authorized_officer_name')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="authorized_officer_position">Jabatan pimpinan atau pejabat yang berwenang <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="authorized_officer_position" id="authorized_officer_position" value="{{ old('authorized_officer_position') }}" maxlength="255" required>
                            @error('authorized_officer_position')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="contact_person_name">Nama narahubung kerja sama <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="contact_person_name" id="contact_person_name" x-model="form.contact_person_name" value="{{ old('contact_person_name', $prefill['name'] ?? '') }}" maxlength="255" required>
                            @error('contact_person_name')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="contact_person_position">Jabatan narahubung <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="contact_person_position" id="contact_person_position" value="{{ old('contact_person_position') }}" maxlength="255" required>
                            @error('contact_person_position')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="contact_person_whatsapp">Nomor WhatsApp narahubung <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="contact_person_whatsapp" id="contact_person_whatsapp" value="{{ old('contact_person_whatsapp') }}" maxlength="64" placeholder="+62..." required>
                            <p class="pa-hint">Dapat memakai format +62, 08..., atau nomor internasional.</p>
                            @error('contact_person_whatsapp')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="contact_person_email">Alamat email narahubung <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="email" name="contact_person_email" id="contact_person_email" value="{{ old('contact_person_email', $prefill['email'] ?? '') }}" maxlength="255" required>
                            @error('contact_person_email')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Step 3 --}}
                <section x-show="step === 3" aria-labelledby="pa-step-3-title">
                    <h2 id="pa-step-3-title" class="text-lg font-bold" style="color: var(--brand-teal-deep);">3. Informasi Usulan Kerja Sama</h2>
                    <div class="mt-5 grid gap-4">
                        <fieldset>
                            <legend class="pa-label">Jenis pengajuan kerja sama <span class="text-red-600">*</span></legend>
                            <div class="pa-choice-grid">
                                @foreach($options['proposal_types'] as $value => $label)
                                    <label class="pa-choice">
                                        <input type="radio" name="proposal_type" value="{{ $value }}" x-model="form.proposal_type" @checked(old('proposal_type') === $value) required>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('proposal_type')<p class="pa-error">{{ $message }}</p>@enderror
                            <div class="mt-3" x-show="form.proposal_type === 'lainnya'">
                                <label class="pa-label" for="proposal_type_other">Jenis pengajuan lainnya <span class="text-red-600">*</span></label>
                                <input class="pa-input" type="text" name="proposal_type_other" id="proposal_type_other" value="{{ old('proposal_type_other') }}" maxlength="255">
                                @error('proposal_type_other')<p class="pa-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                        <div>
                            <label class="pa-label" for="proposal_title">Judul atau topik kerja sama <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="proposal_title" id="proposal_title" x-model="form.proposal_title" value="{{ old('proposal_title') }}" maxlength="500" required>
                            @error('proposal_title')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="proposal_purpose">Tujuan kerja sama <span class="text-red-600">*</span></label>
                            <textarea class="pa-textarea" name="proposal_purpose" id="proposal_purpose" maxlength="5000" required>{{ old('proposal_purpose') }}</textarea>
                            @error('proposal_purpose')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="expected_benefits">Manfaat yang diharapkan bagi para pihak <span class="text-red-600">*</span></label>
                            <textarea class="pa-textarea" name="expected_benefits" id="expected_benefits" maxlength="5000" required>{{ old('expected_benefits') }}</textarea>
                            @error('expected_benefits')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <fieldset>
                            <legend class="pa-label">Unit atau program studi tujuan <span class="text-red-600">*</span></legend>
                            <div class="pa-choice-grid">
                                @foreach($options['target_units'] as $value => $label)
                                    <label class="pa-choice">
                                        <input type="checkbox" name="target_units[]" value="{{ $value }}" x-model="form.target_units" @checked(in_array($value, $oldUnits, true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('target_units')<p class="pa-error">{{ $message }}</p>@enderror
                            <div class="mt-3" x-show="form.target_units.includes('unit_lainnya')">
                                <label class="pa-label" for="target_unit_other">Nama unit lainnya <span class="text-red-600">*</span></label>
                                <input class="pa-input" type="text" name="target_unit_other" id="target_unit_other" value="{{ old('target_unit_other') }}" maxlength="255">
                                @error('target_unit_other')<p class="pa-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                    </div>
                </section>

                {{-- Step 4 --}}
                <section x-show="step === 4" aria-labelledby="pa-step-4-title">
                    <h2 id="pa-step-4-title" class="text-lg font-bold" style="color: var(--brand-teal-deep);">4. Ruang Lingkup Kerja Sama</h2>
                    <div class="mt-5 grid gap-4">
                        <fieldset>
                            <legend class="pa-label">Ruang lingkup yang diusulkan <span class="text-red-600">*</span></legend>
                            <div class="pa-choice-grid">
                                @foreach($options['proposed_scopes'] as $value => $label)
                                    <label class="pa-choice">
                                        <input type="checkbox" name="proposed_scopes[]" value="{{ $value }}" x-model="form.proposed_scopes" @checked(in_array($value, $oldScopes, true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('proposed_scopes')<p class="pa-error">{{ $message }}</p>@enderror
                            <div class="mt-3" x-show="form.proposed_scopes.includes('lainnya')">
                                <label class="pa-label" for="proposed_scope_other">Ruang lingkup lainnya <span class="text-red-600">*</span></label>
                                <input class="pa-input" type="text" name="proposed_scope_other" id="proposed_scope_other" value="{{ old('proposed_scope_other') }}" maxlength="255">
                                @error('proposed_scope_other')<p class="pa-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="pa-label">Bentuk dokumen kerja sama yang diusulkan <span class="text-red-600">*</span></legend>
                            <div class="pa-choice-grid">
                                @foreach($options['document_forms'] as $value => $label)
                                    <label class="pa-choice">
                                        <input type="radio" name="document_form" value="{{ $value }}" x-model="form.document_form" @checked(old('document_form') === $value) required>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('document_form')<p class="pa-error">{{ $message }}</p>@enderror
                        </fieldset>
                        <div>
                            <label class="pa-label" for="activity_description">Uraian kegiatan yang akan dilaksanakan <span class="text-red-600">*</span></label>
                            <textarea class="pa-textarea min-h-[140px]" name="activity_description" id="activity_description" maxlength="10000" required>{{ old('activity_description') }}</textarea>
                            @error('activity_description')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="target_description">Sasaran <span class="text-red-600">*</span></label>
                            <textarea class="pa-textarea" name="target_description" id="target_description" maxlength="5000" required>{{ old('target_description') }}</textarea>
                            @error('target_description')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Step 5 --}}
                <section x-show="step === 5" aria-labelledby="pa-step-5-title">
                    <h2 id="pa-step-5-title" class="text-lg font-bold" style="color: var(--brand-teal-deep);">5. Rencana Pelaksanaan dan Pembiayaan</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="pa-label" for="planned_start_date">Rencana tanggal mulai <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="date" name="planned_start_date" id="planned_start_date" value="{{ old('planned_start_date') }}" required>
                            @error('planned_start_date')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="planned_end_date">Rencana tanggal selesai <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="date" name="planned_end_date" id="planned_end_date" value="{{ old('planned_end_date') }}" required>
                            @error('planned_end_date')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <fieldset>
                                <legend class="pa-label">Usulan jangka waktu dokumen <span class="text-red-600">*</span></legend>
                                <div class="pa-choice-grid">
                                    @foreach($options['proposed_durations'] as $value => $label)
                                        <label class="pa-choice">
                                            <input type="radio" name="proposed_duration" value="{{ $value }}" x-model="form.proposed_duration" @checked(old('proposed_duration') === $value) required>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('proposed_duration')<p class="pa-error">{{ $message }}</p>@enderror
                                <div class="mt-3" x-show="form.proposed_duration === 'lainnya'">
                                    <label class="pa-label" for="proposed_duration_other">Usulan jangka waktu lainnya <span class="text-red-600">*</span></label>
                                    <input class="pa-input" type="text" name="proposed_duration_other" id="proposed_duration_other" value="{{ old('proposed_duration_other') }}" maxlength="255">
                                    @error('proposed_duration_other')<p class="pa-error">{{ $message }}</p>@enderror
                                </div>
                            </fieldset>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="pa-label" for="activity_location">Lokasi pelaksanaan kegiatan <span class="text-red-600">*</span></label>
                            <textarea class="pa-textarea" name="activity_location" id="activity_location" maxlength="1000" required>{{ old('activity_location') }}</textarea>
                            @error('activity_location')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="pa-label" for="expected_poltekkes_contribution">Kontribusi yang diharapkan dari Poltekkes Kemenkes Banten (opsional)</label>
                            <textarea class="pa-textarea" name="expected_poltekkes_contribution" id="expected_poltekkes_contribution" maxlength="5000">{{ old('expected_poltekkes_contribution') }}</textarea>
                            @error('expected_poltekkes_contribution')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <fieldset>
                                <legend class="pa-label">Apakah kerja sama memerlukan pembiayaan? <span class="text-red-600">*</span></legend>
                                <div class="pa-choice-grid">
                                    @foreach($options['requires_funding_options'] as $value => $label)
                                        <label class="pa-choice">
                                            <input type="radio" name="requires_funding" value="{{ $value }}" x-model="form.requires_funding" @checked(old('requires_funding') === $value) required>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('requires_funding')<p class="pa-error">{{ $message }}</p>@enderror
                            </fieldset>
                        </div>
                        <div class="sm:col-span-2">
                            <fieldset>
                                <legend class="pa-label">Rencana sumber pembiayaan <span class="text-red-600">*</span></legend>
                                <div class="pa-choice-grid">
                                    @foreach($options['funding_sources'] as $value => $label)
                                        <label class="pa-choice">
                                            <input type="checkbox" name="funding_sources[]" value="{{ $value }}" x-model="form.funding_sources" @checked(in_array($value, $oldFunding, true))>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('funding_sources')<p class="pa-error">{{ $message }}</p>@enderror
                            </fieldset>
                        </div>
                    </div>
                </section>

                {{-- Step 6 --}}
                <section x-show="step === 6" aria-labelledby="pa-step-6-title">
                    <h2 id="pa-step-6-title" class="text-lg font-bold" style="color: var(--brand-teal-deep);">6. Dokumen Pendukung</h2>
                    <p class="mt-2 text-sm" style="color: var(--brand-muted);">Lampiran disimpan secara privat di server Unit Kerja Sama. Maksimal {{ $maxUploadMb }} MB per berkas.</p>
                    <div class="mt-5 grid gap-4">
                        <div class="pa-file">
                            <label class="pa-label" for="official_request_letter">Unggah surat resmi pengajuan kerja sama <span class="text-red-600">*</span></label>
                            <input class="mt-2 block w-full text-sm" type="file" name="official_request_letter" id="official_request_letter" accept=".pdf,.doc,.docx,application/pdf" required>
                            <p class="pa-hint">Format: PDF, DOC, DOCX. Disarankan PDF.</p>
                            @error('official_request_letter')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="pa-file">
                            <label class="pa-label" for="institution_profile_file">Unggah profil instansi atau lembaga (opsional)</label>
                            <input class="mt-2 block w-full text-sm" type="file" name="institution_profile_file" id="institution_profile_file" accept=".pdf,.doc,.docx,.ppt,.pptx">
                            <p class="pa-hint">Format: PDF, DOC, DOCX, PPT, PPTX.</p>
                            @error('institution_profile_file')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="pa-file">
                            <label class="pa-label" for="supporting_document">Unggah dokumen legalitas, jadwal, RAB, atau draft kerja sama (opsional)</label>
                            <input class="mt-2 block w-full text-sm" type="file" name="supporting_document" id="supporting_document" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                            <p class="pa-hint">Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX.</p>
                            @error('supporting_document')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Step 7 --}}
                <section x-show="step === 7" aria-labelledby="pa-step-7-title">
                    <h2 id="pa-step-7-title" class="text-lg font-bold" style="color: var(--brand-teal-deep);">7. Pernyataan Calon Mitra</h2>
                    <fieldset class="mt-5 space-y-3">
                        <legend class="pa-label">Pernyataan dan persetujuan <span class="text-red-600">*</span></legend>
                        <label class="pa-choice">
                            <input type="checkbox" name="declaration_data_accurate" value="1" @checked(old('declaration_data_accurate')) required>
                            <span>Data dan dokumen yang disampaikan benar serta dapat dipertanggungjawabkan.</span>
                        </label>
                        <label class="pa-choice">
                            <input type="checkbox" name="declaration_willing_verification" value="1" @checked(old('declaration_willing_verification')) required>
                            <span>Bersedia mengikuti proses verifikasi dan penyempurnaan dokumen kerja sama.</span>
                        </label>
                        <label class="pa-choice">
                            <input type="checkbox" name="declaration_data_usage" value="1" @checked(old('declaration_data_usage')) required>
                            <span>Menyetujui penggunaan data untuk keperluan administrasi kerja sama.</span>
                        </label>
                        @error('declaration_data_accurate')<p class="pa-error">{{ $message }}</p>@enderror
                    </fieldset>
                    <div class="mt-5 grid gap-4">
                        <div>
                            <label class="pa-label" for="declarant_name">Nama pengisi formulir <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="declarant_name" id="declarant_name" value="{{ old('declarant_name', $prefill['name'] ?? '') }}" maxlength="255" required>
                            @error('declarant_name')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="declarant_position">Jabatan pengisi formulir <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="text" name="declarant_position" id="declarant_position" value="{{ old('declarant_position') }}" maxlength="255" required>
                            @error('declarant_position')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="pa-label" for="submission_date">Tanggal pengajuan <span class="text-red-600">*</span></label>
                            <input class="pa-input" type="date" name="submission_date" id="submission_date" x-model="form.submission_date" required>
                            @error('submission_date')<p class="pa-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Confirm --}}
                <section x-show="step === 8" aria-labelledby="pa-step-8-title">
                    <h2 id="pa-step-8-title" class="text-lg font-bold" style="color: var(--brand-teal-deep);">Konfirmasi Pengajuan</h2>
                    <p class="mt-2 text-sm" style="color: var(--brand-muted);">Periksa ringkasan berikut sebelum mengirim.</p>
                    <div class="pa-summary mt-4">
                        <p><strong>Instansi:</strong> <span x-text="form.institution_name || '—'"></span></p>
                        <p><strong>Narahubung:</strong> <span x-text="form.contact_person_name || '—'"></span></p>
                        <p><strong>Jenis pengajuan:</strong> <span x-text="proposalTypeLabel()"></span></p>
                        <p><strong>Judul kerja sama:</strong> <span x-text="form.proposal_title || '—'"></span></p>
                        <p><strong>Tanggal pengajuan:</strong> <span x-text="form.submission_date || '—'"></span></p>
                        <p><strong>Dokumen:</strong> Surat resmi wajib; profil & pendukung opsional</p>
                    </div>
                </section>

                <div class="pa-actions" x-show="step >= 1 && step <= 8">
                    <button type="button" class="btn-outline" @click="back()" x-text="step === 1 ? 'Kembali ke Intro' : 'Kembali'"></button>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="btn-solid" x-show="step < 7" @click="next()">Selanjutnya</button>
                        <button type="button" class="btn-solid" x-show="step === 7" @click="next()">Lanjut ke Konfirmasi</button>
                        <button type="button" class="btn-outline" x-show="step === 8" @click="step = 7">Kembali dan Edit</button>
                        <button type="submit" class="btn-solid" x-show="step === 8" :disabled="submitting">
                            <span x-show="!submitting">Kirim Pengajuan</span>
                            <span x-show="submitting">Mengirim...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function partnershipWizard(config) {
    const proposalTypes = @json($options['proposal_types']);
    return {
        step: config.initialStep,
        submitting: false,
        stepTitles: {
            1: 'Identitas',
            2: 'Narahubung',
            3: 'Usulan',
            4: 'Ruang Lingkup',
            5: 'Pelaksanaan',
            6: 'Dokumen',
            7: 'Pernyataan',
        },
        form: {
            institution_type: config.old.institution_type || '',
            partner_status: config.old.partner_status || 'dalam_negeri',
            country: config.old.country || 'Indonesia',
            proposal_type: config.old.proposal_type || '',
            target_units: Array.isArray(config.old.target_units) ? config.old.target_units : [],
            proposed_scopes: Array.isArray(config.old.proposed_scopes) ? config.old.proposed_scopes : [],
            document_form: config.old.document_form || '',
            proposed_duration: config.old.proposed_duration || '',
            requires_funding: config.old.requires_funding || '',
            funding_sources: Array.isArray(config.old.funding_sources) ? config.old.funding_sources : [],
            institution_name: config.old.institution_name || '',
            contact_person_name: config.old.contact_person_name || config.prefillName || '',
            proposal_title: config.old.proposal_title || '',
            submission_date: config.old.submission_date || config.today,
        },
        init() {
            this.$watch('form.partner_status', (value) => {
                if (value === 'dalam_negeri' && (!this.form.country || this.form.country === '')) {
                    this.form.country = 'Indonesia';
                }
            });
        },
        proposalTypeLabel() {
            return proposalTypes[this.form.proposal_type] || '—';
        },
        next() {
            if (this.step < 8) this.step += 1;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        back() {
            if (this.step > 0) this.step -= 1;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        onSubmit(event) {
            if (this.submitting) {
                event.preventDefault();
                return;
            }
            this.submitting = true;
        },
    };
}
window.partnershipWizard = partnershipWizard;
</script>
@endpush
@endsection
