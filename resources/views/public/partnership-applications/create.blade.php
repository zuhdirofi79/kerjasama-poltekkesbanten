@extends('layouts.public')

@section('title', 'Ajukan Kerjasama')
@section('nav', 'beranda')

@php
    $hasErrors = $errors->any();
    $oldUnits = old('target_units', []);
    $oldScopes = old('proposed_scopes', []);
    $oldFunding = old('funding_sources', []);
    $initialStep = $hasErrors ? 1 : 0;
    $stepMeta = [
        1 => ['eyebrow' => 'Identitas', 'title' => 'Identitas Calon Mitra', 'desc' => 'Isi identitas resmi institusi atau lembaga calon mitra.'],
        2 => ['eyebrow' => 'Narahubung', 'title' => 'Penanggung Jawab dan Narahubung', 'desc' => 'Cantumkan pejabat berwenang dan kontak yang dapat dihubungi.'],
        3 => ['eyebrow' => 'Usulan', 'title' => 'Informasi Usulan Kerja Sama', 'desc' => 'Jelaskan jenis, judul, tujuan, dan unit tujuan kerja sama.'],
        4 => ['eyebrow' => 'Ruang Lingkup', 'title' => 'Ruang Lingkup Kerja Sama', 'desc' => 'Pilih ruang lingkup, bentuk dokumen, serta uraian kegiatan.'],
        5 => ['eyebrow' => 'Pelaksanaan', 'title' => 'Rencana Pelaksanaan dan Pembiayaan', 'desc' => 'Susun jadwal, lokasi, dan informasi pembiayaan.'],
        6 => ['eyebrow' => 'Dokumen', 'title' => 'Dokumen Pendukung', 'desc' => 'Unggah surat resmi dan dokumen pendukung lainnya.'],
        7 => ['eyebrow' => 'Pernyataan', 'title' => 'Pernyataan Calon Mitra', 'desc' => 'Baca dan setujui pernyataan sebelum mengirim pengajuan.'],
        8 => ['eyebrow' => 'Konfirmasi', 'title' => 'Konfirmasi Pengajuan', 'desc' => 'Periksa ringkasan data sebelum mengirim.'],
    ];
@endphp

@section('content')
<div class="partnership-page partnership-ui-build-marker" x-data="partnershipWizard({
    initialStep: {{ (int) $initialStep }},
    today: @js($today),
    prefillName: @js($prefill['name'] ?? ''),
    maxUploadMb: {{ (int) $maxUploadMb }},
    internalInstitution: @js($internalInstitution),
    labels: {
        proposal_types: @js($options['proposal_types']),
        institution_types: @js($options['institution_types']),
        partner_statuses: @js($options['partner_statuses']),
        document_forms: @js($options['document_forms']),
    },
    old: {
        applicant_type: @js(old('applicant_type')),
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
        institution_address: @js(old('institution_address')),
        institution_phone: @js(old('institution_phone')),
        institution_website: @js(old('institution_website')),
        institution_email: @js(old('institution_email', $prefill['email'] ?? '')),
        contact_person_name: @js(old('contact_person_name', $prefill['name'] ?? '')),
        contact_person_email: @js(old('contact_person_email', $prefill['email'] ?? '')),
        proposal_title: @js(old('proposal_title')),
        submission_date: @js(old('submission_date', $today)),
        official_name: @js(old('authorized_officer_name')),
        files_note: @js($hasErrors),
    }
})" x-cloak>
    <div class="partnership-shell" x-ref="shell">
        <header class="partnership-header">
            <p class="partnership-eyebrow">Unit Kerja Sama</p>
            <h1 class="partnership-title">Formulir Pengajuan Kerja Sama</h1>
            <p class="partnership-subtitle">Calon Mitra Poltekkes Kemenkes Banten</p>
            <p class="partnership-lead">
                Lengkapi informasi berikut untuk mengajukan usulan kerja sama.
                Data akan digunakan untuk proses telaah dan koordinasi oleh Unit Kerja Sama.
            </p>
            <div class="mt-4">
                <a href="{{ route('partnership-applications.template') }}" class="partnership-btn partnership-btn--secondary">
                    Unduh Template Pengajuan Kerjasama
                </a>
            </div>
        </header>

        @if ($errors->any())
            <div class="partnership-alert" role="alert">
                <p class="font-semibold">Pengajuan belum dapat dikirim. Periksa kembali isian berikut:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <p class="mt-2 text-xs">Berkas tidak dapat dipulihkan setelah validasi gagal — unggah ulang lampiran sebelum mengirim kembali.</p>
            </div>
        @endif

        {{-- Intro --}}
        <div class="partnership-card" x-show="step === 0" x-transition.opacity.duration.200ms>
            <span class="partnership-badge">Pengajuan Mitra</span>
            <h2 class="partnership-section-title" style="margin-top:0.75rem;">Formulir Pengajuan Kerja Sama</h2>
            <p class="partnership-section-desc">
                Formulir ini digunakan oleh calon mitra untuk mengajukan kerja sama dengan Politeknik Kesehatan Kementerian Kesehatan Banten.
                Informasi yang disampaikan akan digunakan sebagai bahan telaah, koordinasi, dan penyusunan dokumen kerja sama.
            </p>
            <p class="partnership-section-desc" style="margin-top:1rem;font-weight:700;color:var(--brand-teal-deep);">Jenis Pengajuan</p>
            <fieldset class="fieldset-reset form-field">
                <legend class="form-label">Pilih jenis pengajuan <span class="form-required">*</span></legend>
                <div class="choice-grid" style="grid-template-columns:1fr 1fr;">
                    <label class="choice-card">
                        <input type="radio" x-model="form.applicant_type" value="internal" @change="applyApplicantType()">
                        <span class="choice-card__body"><span class="choice-card__title">Internal</span><span class="choice-card__hint">Data instansi Poltekkes terisi otomatis</span></span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" x-model="form.applicant_type" value="external" @change="applyApplicantType()">
                        <span class="choice-card__body"><span class="choice-card__title">Eksternal</span><span class="choice-card__hint">Isi identitas instansi mitra</span></span>
                    </label>
                </div>
                <p class="form-error" x-show="applicantTypeError" x-cloak>Pilih Internal atau Eksternal sebelum memulai.</p>
            </fieldset>
            <p class="partnership-section-desc" style="margin-top:1rem;font-weight:700;color:var(--brand-teal-deep);">Sebelum memulai, siapkan:</p>
            <ul class="partnership-checklist">
                <li>Identitas resmi instansi atau lembaga</li>
                <li>Informasi pimpinan dan narahubung</li>
                <li>Gambaran usulan kerja sama</li>
                <li>Surat resmi pengajuan (PDF/DOC/DOCX, maks. {{ $maxUploadMb }} MB)</li>
            </ul>
            <div class="partnership-intro-meta">Estimasi pengisian ± 10–15 menit</div>
            <div class="partnership-actions" style="border-top:0;padding-top:0;margin-top:1.75rem;">
                <span></span>
                <div class="partnership-actions__right">
                    <button type="button" class="partnership-btn partnership-btn--primary" @click="start()">
                        Mulai Pengajuan
                        <span aria-hidden="true">→</span>
                    </button>
                </div>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('partnership-applications.store') }}"
            enctype="multipart/form-data"
            x-show="step > 0"
            x-transition.opacity.duration.200ms
            @submit="onSubmit"
            novalidate
        >
            @csrf
            <input type="hidden" name="applicant_type" :value="form.applicant_type">

            <div class="partnership-honeypot" aria-hidden="true">
                <label for="company_fax">Fax</label>
                <input type="text" name="company_fax" id="company_fax" tabindex="-1" autocomplete="off">
            </div>

            <div class="partnership-card" x-ref="card">
                <div class="partnership-stepper" x-show="step >= 1 && step <= 7">
                    <div class="partnership-stepper__meta">
                        <p class="partnership-stepper__label">
                            Langkah <span x-text="step"></span> dari 7
                        </p>
                        <span class="partnership-stepper__pct" x-text="Math.round((step / 7) * 100) + '%'"></span>
                    </div>
                    <div class="partnership-progress-bar" aria-hidden="true">
                        <div class="partnership-progress-bar__fill" :style="'width:' + ((step / 7) * 100) + '%'"></div>
                    </div>
                    <div class="partnership-steps" role="list" aria-label="Progres formulir">
                        <template x-for="item in stepList" :key="item.n">
                            <div
                                class="partnership-step"
                                role="listitem"
                                :class="{ 'is-active': step === item.n, 'is-done': step > item.n }"
                            >
                                <span class="partnership-step__dot">
                                    <span x-show="step <= item.n" x-text="item.n"></span>
                                    <svg x-show="step > item.n" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414L8.5 11.836l6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <span class="partnership-step__name" x-text="item.label"></span>
                            </div>
                        </template>
                    </div>
                    <p class="partnership-mobile-step" x-text="stepTitles[step]"></p>
                </div>

                @foreach($stepMeta as $n => $meta)
                    @if($n <= 7)
                        <div class="partnership-section-header" x-show="step === {{ $n }}">
                            <span class="partnership-badge">Langkah {{ $n }}</span>
                            <p class="partnership-section-eyebrow">{{ $meta['eyebrow'] }}</p>
                            <h2 class="partnership-section-title" id="pa-step-{{ $n }}-title">{{ $meta['title'] }}</h2>
                            <p class="partnership-section-desc">{{ $meta['desc'] }}</p>
                        </div>
                    @endif
                @endforeach

                {{-- Step 1 --}}
                <section x-show="step === 1" data-step="1" aria-labelledby="pa-step-1-title">
                    <div class="form-stack">
                        <div class="form-field">
                            <label class="form-label" for="institution_email">Alamat email resmi instansi <span class="form-required">*</span></label>
                            <input class="form-control @error('institution_email') is-invalid @enderror" type="email" name="institution_email" id="institution_email" x-model="form.institution_email" maxlength="255" required placeholder="nama@instansi.go.id">
                            @error('institution_email')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="institution_name">Nama resmi instansi atau lembaga <span class="form-required">*</span></label>
                            <input class="form-control @error('institution_name') is-invalid @enderror" type="text" name="institution_name" id="institution_name" x-model="form.institution_name" maxlength="255" required :readonly="isInternal()">
                            @error('institution_name')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="institution_address">Alamat lengkap instansi atau lembaga <span class="form-required">*</span></label>
                            <textarea class="form-textarea @error('institution_address') is-invalid @enderror" name="institution_address" id="institution_address" x-model="form.institution_address" maxlength="2000" required :readonly="isInternal()"></textarea>
                            @error('institution_address')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-grid-2">
                            <div class="form-field">
                                <label class="form-label" for="institution_phone">Nomor telepon instansi <span class="form-required">*</span></label>
                                <input class="form-control @error('institution_phone') is-invalid @enderror" type="text" name="institution_phone" id="institution_phone" x-model="form.institution_phone" maxlength="64" required placeholder="+62 ..." :readonly="isInternal()">
                                @error('institution_phone')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="institution_website">Situs web instansi <span class="form-optional">Opsional</span></label>
                                <input class="form-control @error('institution_website') is-invalid @enderror" type="url" name="institution_website" id="institution_website" x-model="form.institution_website" maxlength="255" placeholder="https://" :readonly="isInternal()">
                                @error('institution_website')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="legal_registration_number">Nomor legalitas, izin operasional, atau akreditasi <span class="form-optional">Opsional</span></label>
                            <input class="form-control" type="text" name="legal_registration_number" id="legal_registration_number" value="{{ old('legal_registration_number') }}" maxlength="255">
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="institution_profile">Profil singkat calon mitra <span class="form-required">*</span></label>
                            <textarea class="form-textarea @error('institution_profile') is-invalid @enderror" name="institution_profile" id="institution_profile" maxlength="5000" required>{{ old('institution_profile') }}</textarea>
                            @error('institution_profile')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Jenis instansi atau lembaga <span class="form-required">*</span></legend>
                            <div class="choice-grid">
                                @foreach($options['institution_types'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="radio" name="institution_type" value="{{ $value }}" x-model="form.institution_type" @checked(old('institution_type') === $value) required>
                                        <span class="choice-card__body"><span class="choice-card__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('institution_type')<p class="form-error">{{ $message }}</p>@enderror
                            <div class="form-field" style="margin-top:0.85rem;" x-show="form.institution_type === 'lainnya'" x-transition>
                                <label class="form-label" for="institution_type_other">Jenis instansi lainnya <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="institution_type_other" id="institution_type_other" value="{{ old('institution_type_other') }}" maxlength="255">
                                @error('institution_type_other')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Status calon mitra <span class="form-required">*</span></legend>
                            <div class="choice-grid choice-grid--3" style="grid-template-columns:1fr 1fr;">
                                @foreach($options['partner_statuses'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="radio" name="partner_status" value="{{ $value }}" x-model="form.partner_status" @checked(old('partner_status', 'dalam_negeri') === $value) required>
                                        <span class="choice-card__body"><span class="choice-card__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('partner_status')<p class="form-error">{{ $message }}</p>@enderror
                        </fieldset>
                        <div class="form-field">
                            <label class="form-label" for="country">Negara asal calon mitra <span class="form-required">*</span></label>
                            <input class="form-control @error('country') is-invalid @enderror" type="text" name="country" id="country" x-model="form.country" maxlength="120" required>
                            @error('country')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Step 2 --}}
                <section x-show="step === 2" data-step="2" aria-labelledby="pa-step-2-title">
                    <div class="partnership-group" style="border-top:0;margin-top:0;padding-top:0;">
                        <p class="partnership-group__title">Pimpinan</p>
                        <div class="form-grid-2">
                            <div class="form-field">
                                <label class="form-label" for="authorized_officer_name">Nama pimpinan atau pejabat yang berwenang <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="authorized_officer_name" id="authorized_officer_name" x-model="form.authorized_officer_name" maxlength="255" required>
                                @error('authorized_officer_name')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="authorized_officer_position">Jabatan pimpinan <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="authorized_officer_position" id="authorized_officer_position" value="{{ old('authorized_officer_position') }}" maxlength="255" required>
                                @error('authorized_officer_position')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="partnership-group">
                        <p class="partnership-group__title">Narahubung</p>
                        <div class="form-stack">
                            <div class="form-grid-2">
                                <div class="form-field">
                                    <label class="form-label" for="contact_person_name">Nama narahubung kerja sama <span class="form-required">*</span></label>
                                    <input class="form-control" type="text" name="contact_person_name" id="contact_person_name" x-model="form.contact_person_name" maxlength="255" required>
                                    @error('contact_person_name')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="contact_person_position">Jabatan narahubung <span class="form-required">*</span></label>
                                    <input class="form-control" type="text" name="contact_person_position" id="contact_person_position" value="{{ old('contact_person_position') }}" maxlength="255" required>
                                    @error('contact_person_position')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div class="form-field">
                                    <label class="form-label" for="contact_person_whatsapp">Nomor WhatsApp narahubung <span class="form-required">*</span></label>
                                    <input class="form-control" type="text" name="contact_person_whatsapp" id="contact_person_whatsapp" value="{{ old('contact_person_whatsapp') }}" maxlength="64" required placeholder="+62 812-3456-7890">
                                    <p class="form-helper">Format Indonesia atau internasional, misalnya +62 812-3456-7890.</p>
                                    @error('contact_person_whatsapp')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="contact_person_email">Alamat email narahubung <span class="form-required">*</span></label>
                                    <input class="form-control" type="email" name="contact_person_email" id="contact_person_email" x-model="form.contact_person_email" maxlength="255" required>
                                    @error('contact_person_email')<p class="form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Step 3 --}}
                <section x-show="step === 3" data-step="3" aria-labelledby="pa-step-3-title">
                    <div class="form-stack">
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Jenis pengajuan kerja sama <span class="form-required">*</span></legend>
                            <div class="choice-grid">
                                @foreach($options['proposal_types'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="radio" name="proposal_type" value="{{ $value }}" x-model="form.proposal_type" @checked(old('proposal_type') === $value) required>
                                        <span class="choice-card__body"><span class="choice-card__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('proposal_type')<p class="form-error">{{ $message }}</p>@enderror
                            <div class="form-field" style="margin-top:0.85rem;" x-show="form.proposal_type === 'lainnya'" x-transition>
                                <label class="form-label" for="proposal_type_other">Jenis pengajuan lainnya <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="proposal_type_other" id="proposal_type_other" value="{{ old('proposal_type_other') }}" maxlength="255">
                                @error('proposal_type_other')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                        <div class="form-field">
                            <label class="form-label" for="proposal_title">Judul atau topik kerja sama <span class="form-required">*</span></label>
                            <input class="form-control" type="text" name="proposal_title" id="proposal_title" x-model="form.proposal_title" maxlength="500" required>
                            @error('proposal_title')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="proposal_purpose">Tujuan kerja sama <span class="form-required">*</span></label>
                            <textarea class="form-textarea" name="proposal_purpose" id="proposal_purpose" maxlength="5000" required>{{ old('proposal_purpose') }}</textarea>
                            @error('proposal_purpose')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="expected_benefits">Manfaat yang diharapkan bagi para pihak <span class="form-required">*</span></label>
                            <textarea class="form-textarea" name="expected_benefits" id="expected_benefits" maxlength="5000" required>{{ old('expected_benefits') }}</textarea>
                            @error('expected_benefits')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Unit atau program studi tujuan <span class="form-required">*</span></legend>
                            <div class="choice-grid">
                                @foreach($options['target_units'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="checkbox" name="target_units[]" value="{{ $value }}" x-model="form.target_units" @checked(in_array($value, $oldUnits, true))>
                                        <span class="choice-card__body"><span class="choice-card__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('target_units')<p class="form-error">{{ $message }}</p>@enderror
                            <div class="form-field" style="margin-top:0.85rem;" x-show="form.target_units.includes('unit_lainnya')" x-transition>
                                <label class="form-label" for="target_unit_other">Nama unit lainnya <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="target_unit_other" id="target_unit_other" value="{{ old('target_unit_other') }}" maxlength="255">
                                @error('target_unit_other')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                    </div>
                </section>

                {{-- Step 4 --}}
                <section x-show="step === 4" data-step="4" aria-labelledby="pa-step-4-title">
                    <div class="form-stack">
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Ruang lingkup yang diusulkan <span class="form-required">*</span></legend>
                            <div class="choice-grid">
                                @foreach($options['proposed_scopes'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="checkbox" name="proposed_scopes[]" value="{{ $value }}" x-model="form.proposed_scopes" @checked(in_array($value, $oldScopes, true))>
                                        <span class="choice-card__body"><span class="choice-card__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('proposed_scopes')<p class="form-error">{{ $message }}</p>@enderror
                            <div class="form-field" style="margin-top:0.85rem;" x-show="form.proposed_scopes.includes('lainnya')" x-transition>
                                <label class="form-label" for="proposed_scope_other">Ruang lingkup lainnya <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="proposed_scope_other" id="proposed_scope_other" value="{{ old('proposed_scope_other') }}" maxlength="255">
                                @error('proposed_scope_other')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Bentuk dokumen kerja sama yang diusulkan <span class="form-required">*</span></legend>
                            <div class="choice-grid choice-grid--docs">
                                @foreach($options['document_forms'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="radio" name="document_form" value="{{ $value }}" x-model="form.document_form" @checked(old('document_form') === $value) required>
                                        <span class="choice-card__body">
                                            <span class="choice-card__title">{{ $label }}</span>
                                            @if($value === 'mou')<span class="choice-card__meta">Nota Kesepahaman</span>
                                            @elseif($value === 'pks')<span class="choice-card__meta">Perjanjian operasional</span>
                                            @elseif($value === 'ia')<span class="choice-card__meta">Implementation Arrangement</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('document_form')<p class="form-error">{{ $message }}</p>@enderror
                        </fieldset>
                        <div class="form-field">
                            <label class="form-label" for="activity_description">Uraian kegiatan yang akan dilaksanakan <span class="form-required">*</span></label>
                            <textarea class="form-textarea" style="min-height:150px;" name="activity_description" id="activity_description" maxlength="10000" required>{{ old('activity_description') }}</textarea>
                            @error('activity_description')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="target_description">Sasaran <span class="form-required">*</span></label>
                            <textarea class="form-textarea" name="target_description" id="target_description" maxlength="5000" required>{{ old('target_description') }}</textarea>
                            @error('target_description')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Step 5 --}}
                <section x-show="step === 5" data-step="5" aria-labelledby="pa-step-5-title">
                    <div class="form-stack">
                        <div class="form-grid-2">
                            <div class="form-field">
                                <label class="form-label" for="planned_start_date">Rencana tanggal mulai <span class="form-required">*</span></label>
                                <input class="form-control" type="date" name="planned_start_date" id="planned_start_date" value="{{ old('planned_start_date') }}" required>
                                @error('planned_start_date')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="planned_end_date">Rencana tanggal selesai <span class="form-required">*</span></label>
                                <input class="form-control" type="date" name="planned_end_date" id="planned_end_date" value="{{ old('planned_end_date') }}" required>
                                @error('planned_end_date')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Usulan jangka waktu dokumen <span class="form-required">*</span></legend>
                            <div class="choice-grid choice-grid--3">
                                @foreach($options['proposed_durations'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="radio" name="proposed_duration" value="{{ $value }}" x-model="form.proposed_duration" @checked(old('proposed_duration') === $value) required>
                                        <span class="choice-card__body"><span class="choice-card__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('proposed_duration')<p class="form-error">{{ $message }}</p>@enderror
                            <div class="form-field" style="margin-top:0.85rem;" x-show="form.proposed_duration === 'lainnya'" x-transition>
                                <label class="form-label" for="proposed_duration_other">Usulan jangka waktu lainnya <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="proposed_duration_other" id="proposed_duration_other" value="{{ old('proposed_duration_other') }}" maxlength="255">
                                @error('proposed_duration_other')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </fieldset>
                        <div class="form-field">
                            <label class="form-label" for="activity_location">Lokasi pelaksanaan kegiatan <span class="form-required">*</span></label>
                            <textarea class="form-textarea" name="activity_location" id="activity_location" maxlength="1000" required>{{ old('activity_location') }}</textarea>
                            @error('activity_location')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="expected_poltekkes_contribution">Kontribusi yang diharapkan dari Poltekkes Kemenkes Banten <span class="form-optional">Opsional</span></label>
                            <textarea class="form-textarea" name="expected_poltekkes_contribution" id="expected_poltekkes_contribution" maxlength="5000">{{ old('expected_poltekkes_contribution') }}</textarea>
                        </div>
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Apakah kerja sama memerlukan pembiayaan? <span class="form-required">*</span></legend>
                            <div class="choice-grid choice-grid--3">
                                @foreach($options['requires_funding_options'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="radio" name="requires_funding" value="{{ $value }}" x-model="form.requires_funding" @checked(old('requires_funding') === $value) required>
                                        <span class="choice-card__body"><span class="choice-card__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('requires_funding')<p class="form-error">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Rencana sumber pembiayaan <span class="form-required">*</span></legend>
                            <div class="choice-grid">
                                @foreach($options['funding_sources'] as $value => $label)
                                    <label class="choice-card">
                                        <input type="checkbox" name="funding_sources[]" value="{{ $value }}" x-model="form.funding_sources" @checked(in_array($value, $oldFunding, true))>
                                        <span class="choice-card__body"><span class="choice-card__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('funding_sources')<p class="form-error">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>
                </section>

                {{-- Step 6 --}}
                <section x-show="step === 6" data-step="6" aria-labelledby="pa-step-6-title">
                    <div class="form-stack">
                        <div class="form-field">
                            <label class="form-label">Unggah surat resmi pengajuan kerja sama <span class="form-required">*</span></label>
                            <div class="upload-zone" :class="{ 'is-filled': files.official_request_letter, 'is-dragover': drag.official_request_letter }" @dragover.prevent="drag.official_request_letter = true" @dragleave.prevent="drag.official_request_letter = false" @drop.prevent="onDrop($event, 'official_request_letter')">
                                <input class="upload-zone__input" type="file" name="official_request_letter" id="official_request_letter" accept=".pdf,.doc,.docx,application/pdf" required @change="onFileChange($event, 'official_request_letter')">
                                <template x-if="!files.official_request_letter">
                                    <div>
                                        <div class="upload-zone__icon" aria-hidden="true">
                                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        </div>
                                        <p class="upload-zone__title">Klik untuk memilih berkas</p>
                                        <p class="upload-zone__hint">atau tarik dan letakkan di sini</p>
                                        <p class="upload-zone__hint">PDF, DOC, DOCX • Maks. {{ $maxUploadMb }} MB</p>
                                    </div>
                                </template>
                                <template x-if="files.official_request_letter">
                                    <div class="upload-selected" @click.stop>
                                        <div class="upload-selected__icon" aria-hidden="true">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div class="upload-selected__meta">
                                            <p class="upload-selected__name" x-text="files.official_request_letter.name"></p>
                                            <p class="upload-selected__size" x-text="formatSize(files.official_request_letter.size)"></p>
                                        </div>
                                        <button type="button" class="upload-selected__clear" @click.prevent="clearFile('official_request_letter')">Hapus</button>
                                    </div>
                                </template>
                            </div>
                            @error('official_request_letter')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-field">
                            <label class="form-label">Unggah Form Pengajuan Kerjasama <span class="form-optional">Opsional</span></label>
                            <p class="partnership-section-desc" style="margin-top:0;">Unduh template resmi, isi, lalu unggah kembali (PDF/DOC/DOCX, maks. {{ $maxUploadMb }} MB).</p>
                            <div class="upload-zone" :class="{ 'is-filled': files.filled_template, 'is-dragover': drag.filled_template }" @dragover.prevent="drag.filled_template = true" @dragleave.prevent="drag.filled_template = false" @drop.prevent="onDrop($event, 'filled_template')">
                                <input class="upload-zone__input" type="file" name="filled_template" id="filled_template" accept=".pdf,.doc,.docx" @change="onFileChange($event, 'filled_template')">
                                <template x-if="!files.filled_template">
                                    <div>
                                        <div class="upload-zone__icon" aria-hidden="true">
                                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        </div>
                                        <p class="upload-zone__title">Klik untuk memilih form template</p>
                                        <p class="upload-zone__hint">PDF, DOC, DOCX • Maks. {{ $maxUploadMb }} MB</p>
                                    </div>
                                </template>
                                <template x-if="files.filled_template">
                                    <div class="upload-selected" @click.stop>
                                        <div class="upload-selected__icon" aria-hidden="true">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div class="upload-selected__meta">
                                            <p class="upload-selected__name" x-text="files.filled_template.name"></p>
                                            <p class="upload-selected__size" x-text="formatSize(files.filled_template.size)"></p>
                                        </div>
                                        <button type="button" class="upload-selected__clear" @click.prevent="clearFile('filled_template')">Hapus</button>
                                    </div>
                                </template>
                            </div>
                            @error('filled_template')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-field">
                            <label class="form-label">Unggah profil instansi atau lembaga <span class="form-optional">Opsional</span></label>
                            <div class="upload-zone" :class="{ 'is-filled': files.institution_profile_file, 'is-dragover': drag.institution_profile_file }" @dragover.prevent="drag.institution_profile_file = true" @dragleave.prevent="drag.institution_profile_file = false" @drop.prevent="onDrop($event, 'institution_profile_file')">
                                <input class="upload-zone__input" type="file" name="institution_profile_file" id="institution_profile_file" accept=".pdf,.doc,.docx,.ppt,.pptx" @change="onFileChange($event, 'institution_profile_file')">
                                <template x-if="!files.institution_profile_file">
                                    <div>
                                        <div class="upload-zone__icon" aria-hidden="true">
                                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        </div>
                                        <p class="upload-zone__title">Klik untuk memilih berkas</p>
                                        <p class="upload-zone__hint">PDF, DOC, DOCX, PPT, PPTX • Maks. {{ $maxUploadMb }} MB</p>
                                    </div>
                                </template>
                                <template x-if="files.institution_profile_file">
                                    <div class="upload-selected" @click.stop>
                                        <div class="upload-selected__icon" aria-hidden="true">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div class="upload-selected__meta">
                                            <p class="upload-selected__name" x-text="files.institution_profile_file.name"></p>
                                            <p class="upload-selected__size" x-text="formatSize(files.institution_profile_file.size)"></p>
                                        </div>
                                        <button type="button" class="upload-selected__clear" @click.prevent="clearFile('institution_profile_file')">Hapus</button>
                                    </div>
                                </template>
                            </div>
                            @error('institution_profile_file')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-field">
                            <label class="form-label">Dokumen legalitas, jadwal, RAB, atau draft kerja sama <span class="form-optional">Opsional</span></label>
                            <div class="upload-zone" :class="{ 'is-filled': files.supporting_document, 'is-dragover': drag.supporting_document }" @dragover.prevent="drag.supporting_document = true" @dragleave.prevent="drag.supporting_document = false" @drop.prevent="onDrop($event, 'supporting_document')">
                                <input class="upload-zone__input" type="file" name="supporting_document" id="supporting_document" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" @change="onFileChange($event, 'supporting_document')">
                                <template x-if="!files.supporting_document">
                                    <div>
                                        <div class="upload-zone__icon" aria-hidden="true">
                                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        </div>
                                        <p class="upload-zone__title">Klik untuk memilih berkas</p>
                                        <p class="upload-zone__hint">PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX • Maks. {{ $maxUploadMb }} MB</p>
                                    </div>
                                </template>
                                <template x-if="files.supporting_document">
                                    <div class="upload-selected" @click.stop>
                                        <div class="upload-selected__icon" aria-hidden="true">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div class="upload-selected__meta">
                                            <p class="upload-selected__name" x-text="files.supporting_document.name"></p>
                                            <p class="upload-selected__size" x-text="formatSize(files.supporting_document.size)"></p>
                                        </div>
                                        <button type="button" class="upload-selected__clear" @click.prevent="clearFile('supporting_document')">Hapus</button>
                                    </div>
                                </template>
                            </div>
                            @error('supporting_document')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Step 7 --}}
                <section x-show="step === 7" data-step="7" aria-labelledby="pa-step-7-title">
                    <div class="form-stack">
                        <fieldset class="fieldset-reset form-field">
                            <legend class="form-label">Pernyataan dan persetujuan <span class="form-required">*</span></legend>
                            <div class="choice-grid" style="grid-template-columns:1fr;">
                                <label class="choice-card">
                                    <input type="checkbox" name="declaration_data_accurate" value="1" @checked(old('declaration_data_accurate')) required>
                                    <span class="choice-card__body"><span class="choice-card__title">Data dan dokumen yang disampaikan benar serta dapat dipertanggungjawabkan.</span></span>
                                </label>
                                <label class="choice-card">
                                    <input type="checkbox" name="declaration_willing_verification" value="1" @checked(old('declaration_willing_verification')) required>
                                    <span class="choice-card__body"><span class="choice-card__title">Bersedia mengikuti proses verifikasi dan penyempurnaan dokumen kerja sama.</span></span>
                                </label>
                                <label class="choice-card">
                                    <input type="checkbox" name="declaration_data_usage" value="1" @checked(old('declaration_data_usage')) required>
                                    <span class="choice-card__body"><span class="choice-card__title">Menyetujui penggunaan data untuk keperluan administrasi kerja sama.</span></span>
                                </label>
                            </div>
                            @error('declaration_data_accurate')<p class="form-error">{{ $message }}</p>@enderror
                        </fieldset>
                        <div class="form-grid-2">
                            <div class="form-field">
                                <label class="form-label" for="declarant_name">Nama pengisi formulir <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="declarant_name" id="declarant_name" value="{{ old('declarant_name', $prefill['name'] ?? '') }}" maxlength="255" required>
                                @error('declarant_name')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="declarant_position">Jabatan pengisi formulir <span class="form-required">*</span></label>
                                <input class="form-control" type="text" name="declarant_position" id="declarant_position" value="{{ old('declarant_position') }}" maxlength="255" required>
                                @error('declarant_position')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="form-field" style="max-width:20rem;">
                            <label class="form-label" for="submission_date">Tanggal pengajuan <span class="form-required">*</span></label>
                            <input class="form-control" type="date" name="submission_date" id="submission_date" x-model="form.submission_date" required>
                            @error('submission_date')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Confirm --}}
                <section x-show="step === 8" data-step="8" aria-labelledby="pa-step-8-title">
                    <div class="partnership-section-header">
                        <span class="partnership-badge">Konfirmasi</span>
                        <h2 class="partnership-section-title" id="pa-step-8-title">Konfirmasi Pengajuan</h2>
                        <p class="partnership-section-desc">Periksa ringkasan berikut. Anda masih dapat kembali untuk mengubah data.</p>
                    </div>
                    <div class="partnership-summary">
                        <div class="partnership-summary__block">
                            <div class="partnership-summary__head">
                                <p class="partnership-summary__title">Identitas Mitra</p>
                                <button type="button" class="partnership-summary__edit" @click="goTo(1)">Edit</button>
                            </div>
                            <div class="partnership-summary__row"><span>Jenis</span><strong x-text="form.applicant_type === 'internal' ? 'Internal' : (form.applicant_type === 'external' ? 'Eksternal' : '—')"></strong></div>
                            <div class="partnership-summary__row"><span>Instansi</span><strong x-text="form.institution_name || '—'"></strong></div>
                            <div class="partnership-summary__row"><span>Email</span><strong x-text="form.institution_email || '—'"></strong></div>
                            <div class="partnership-summary__row"><span>Jenis</span><strong x-text="labelOf('institution_types', form.institution_type)"></strong></div>
                            <div class="partnership-summary__row"><span>Status</span><strong x-text="labelOf('partner_statuses', form.partner_status)"></strong></div>
                        </div>
                        <div class="partnership-summary__block">
                            <div class="partnership-summary__head">
                                <p class="partnership-summary__title">Narahubung</p>
                                <button type="button" class="partnership-summary__edit" @click="goTo(2)">Edit</button>
                            </div>
                            <div class="partnership-summary__row"><span>Nama</span><strong x-text="form.contact_person_name || '—'"></strong></div>
                            <div class="partnership-summary__row"><span>Email</span><strong x-text="form.contact_person_email || '—'"></strong></div>
                        </div>
                        <div class="partnership-summary__block">
                            <div class="partnership-summary__head">
                                <p class="partnership-summary__title">Usulan</p>
                                <button type="button" class="partnership-summary__edit" @click="goTo(3)">Edit</button>
                            </div>
                            <div class="partnership-summary__row"><span>Jenis pengajuan</span><strong x-text="labelOf('proposal_types', form.proposal_type)"></strong></div>
                            <div class="partnership-summary__row"><span>Judul</span><strong x-text="form.proposal_title || '—'"></strong></div>
                        </div>
                        <div class="partnership-summary__block">
                            <div class="partnership-summary__head">
                                <p class="partnership-summary__title">Dokumen & Pernyataan</p>
                                <button type="button" class="partnership-summary__edit" @click="goTo(6)">Edit</button>
                            </div>
                            <div class="partnership-summary__row"><span>Surat resmi</span><strong x-text="files.official_request_letter ? files.official_request_letter.name : 'Belum dipilih'"></strong></div>
                            <div class="partnership-summary__row"><span>Tanggal pengajuan</span><strong x-text="form.submission_date || '—'"></strong></div>
                        </div>
                    </div>
                    <div class="partnership-warning">Pastikan seluruh data telah benar sebelum dikirim. Setelah dikirim, perubahan hanya dapat dilakukan melalui proses perbaikan oleh Unit Kerja Sama.</div>
                </section>

                <div class="partnership-actions" x-show="step >= 1 && step <= 8">
                    <button type="button" class="partnership-btn partnership-btn--secondary" @click="back()">
                        <span aria-hidden="true">←</span>
                        <span x-text="step === 1 ? 'Kembali ke Intro' : (step === 8 ? 'Kembali & Edit' : 'Kembali')"></span>
                    </button>
                    <div class="partnership-actions__right">
                        <button type="button" class="partnership-btn partnership-btn--primary" x-show="step < 7" @click="next()">
                            Selanjutnya <span aria-hidden="true">→</span>
                        </button>
                        <button type="button" class="partnership-btn partnership-btn--primary" x-show="step === 7" @click="next()">
                            Lanjut ke Konfirmasi <span aria-hidden="true">→</span>
                        </button>
                        <button type="submit" class="partnership-btn partnership-btn--primary" x-show="step === 8" :disabled="submitting">
                            <span class="spinner" x-show="submitting" aria-hidden="true"></span>
                            <span x-text="submitting ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan'"></span>
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
    return {
        step: config.initialStep,
        submitting: false,
        applicantTypeError: false,
        internalInstitution: config.internalInstitution || {},
        stepTitles: {
            1: 'Identitas Calon Mitra',
            2: 'Penanggung Jawab dan Narahubung',
            3: 'Informasi Usulan Kerja Sama',
            4: 'Ruang Lingkup Kerja Sama',
            5: 'Rencana Pelaksanaan dan Pembiayaan',
            6: 'Dokumen Pendukung',
            7: 'Pernyataan Calon Mitra',
        },
        stepList: [
            { n: 1, label: 'Identitas' },
            { n: 2, label: 'Narahubung' },
            { n: 3, label: 'Usulan' },
            { n: 4, label: 'Ruang Lingkup' },
            { n: 5, label: 'Pelaksanaan' },
            { n: 6, label: 'Dokumen' },
            { n: 7, label: 'Pernyataan' },
        ],
        labels: config.labels || {},
        form: {
            applicant_type: config.old.applicant_type || '',
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
            institution_address: config.old.institution_address || '',
            institution_phone: config.old.institution_phone || '',
            institution_website: config.old.institution_website || '',
            institution_email: config.old.institution_email || '',
            contact_person_name: config.old.contact_person_name || config.prefillName || '',
            contact_person_email: config.old.contact_person_email || '',
            authorized_officer_name: config.old.official_name || '',
            proposal_title: config.old.proposal_title || '',
            submission_date: config.old.submission_date || config.today,
        },
        files: {
            official_request_letter: null,
            filled_template: null,
            institution_profile_file: null,
            supporting_document: null,
        },
        drag: {
            official_request_letter: false,
            filled_template: false,
            institution_profile_file: false,
            supporting_document: false,
        },
        init() {
            this.$watch('form.partner_status', (value) => {
                if (value === 'dalam_negeri' && !this.form.country) {
                    this.form.country = 'Indonesia';
                }
            });
            if (this.form.applicant_type === 'internal') {
                this.applyApplicantType();
            }
        },
        isInternal() {
            return this.form.applicant_type === 'internal';
        },
        applyApplicantType() {
            this.applicantTypeError = false;
            if (!this.isInternal()) {
                return;
            }
            const c = this.internalInstitution || {};
            this.form.institution_name = c.name || 'Poltekkes Kemenkes Banten';
            this.form.institution_address = c.address || '';
            this.form.institution_phone = c.phone || '';
            this.form.institution_website = c.website || '';
        },
        start() {
            if (!this.form.applicant_type) {
                this.applicantTypeError = true;
                return;
            }
            this.applyApplicantType();
            this.goTo(1);
        },
        labelOf(group, key) {
            return (this.labels[group] && this.labels[group][key]) || '—';
        },
        formatSize(bytes) {
            if (!bytes && bytes !== 0) return '';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
        onFileChange(event, key) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            this.files[key] = file;
        },
        onDrop(event, key) {
            this.drag[key] = false;
            const file = event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0]
                ? event.dataTransfer.files[0]
                : null;
            if (!file) return;
            const input = document.getElementById(key);
            if (!input) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            this.files[key] = file;
        },
        clearFile(key) {
            const input = document.getElementById(key);
            if (input) input.value = '';
            this.files[key] = null;
        },
        validateCurrentStep() {
            const section = this.$root.querySelector('[data-step="' + this.step + '"]');
            if (!section) return true;
            const fields = section.querySelectorAll('input, select, textarea');
            for (const field of fields) {
                if (field.disabled || field.type === 'hidden') continue;
                if (field.closest('.partnership-honeypot')) continue;
                if (!field.checkValidity()) {
                    field.classList.add('is-invalid');
                    field.reportValidity();
                    field.focus({ preventScroll: false });
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
                field.classList.remove('is-invalid');
            }
            return true;
        },
        scrollToCard() {
            const target = this.$refs.card || this.$refs.shell;
            if (target && target.scrollIntoView) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },
        goTo(n) {
            this.step = n;
            this.$nextTick(() => this.scrollToCard());
        },
        next() {
            if (!this.validateCurrentStep()) return;
            if (this.step < 8) this.step += 1;
            this.$nextTick(() => this.scrollToCard());
        },
        back() {
            if (this.step > 0) this.step -= 1;
            this.$nextTick(() => this.scrollToCard());
        },
        onSubmit(event) {
            if (this.step !== 8) {
                event.preventDefault();
                return;
            }
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
