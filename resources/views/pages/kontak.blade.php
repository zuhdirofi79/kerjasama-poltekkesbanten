@extends('layouts.public')

@section('title', 'Kontak / Helpdesk')
@section('nav', 'kontak')

@section('content')
<x-public.hero
    mode="compact"
    title="Kontak / Helpdesk"
    subtitle="Silakan hubungi Unit Kerjasama Poltekkes Kemenkes Banten."
/>

<div class="content-section">
    <div class="page-panel space-y-6 max-w-2xl">
        <div>
            <h2>Kontak / Helpdesk</h2>
            <p class="mt-2">Silakan hubungi Unit Kerjasama Poltekkes Kemenkes Banten.</p>
        </div>

        <div class="contact-person-list">
            <article class="contact-person-item">
                <div class="contact-person-main">
                    <div class="contact-person-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;min-height:20px;">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <div class="contact-person-text">
                        <div class="contact-person-name">Email</div>
                        <a href="mailto:kerjasama@poltekkesbanten.ac.id" class="contact-person-phone">kerjasama@poltekkesbanten.ac.id</a>
                    </div>
                </div>
            </article>

            <article class="contact-person-item">
                <div class="contact-person-main">
                    <div class="contact-person-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;min-height:20px;">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="contact-person-text">
                        <div class="contact-person-name">Rifaldi Tri Pamungkas</div>
                        <a href="tel:+6282184024334" class="contact-person-phone">082184024334</a>
                    </div>
                </div>
                <a
                    href="https://wa.me/6282184024334"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="contact-person-whatsapp"
                    aria-label="Hubungi Rifaldi Tri Pamungkas melalui WhatsApp"
                >
                    <span class="sr-only">WhatsApp</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="width:20px;height:20px;min-width:20px;min-height:20px;">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                    </svg>
                    <span class="contact-whatsapp-label">WhatsApp</span>
                </a>
            </article>

            <article class="contact-person-item">
                <div class="contact-person-main">
                    <div class="contact-person-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;min-height:20px;">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="contact-person-text">
                        <div class="contact-person-name">Diyas Arliandini</div>
                        <a href="tel:+6289508394483" class="contact-person-phone">089508394483</a>
                    </div>
                </div>
                <a
                    href="https://wa.me/6289508394483"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="contact-person-whatsapp"
                    aria-label="Hubungi Diyas Arliandini melalui WhatsApp"
                >
                    <span class="sr-only">WhatsApp</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="width:20px;height:20px;min-width:20px;min-height:20px;">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                    </svg>
                    <span class="contact-whatsapp-label">WhatsApp</span>
                </a>
            </article>
        </div>

        <div>
            <h2>Layanan</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>Informasi skema dan kategori kerjasama</li>
                <li>Konsultasi pengajuan kemitraan baru</li>
                <li>Bantuan akses dokumen (setelah login Google)</li>
            </ul>
        </div>
        <a href="{{ route('partnership-applications.create') }}" class="btn-cta inline-flex">
            <img src="{{ asset('images/icons/icon-edit.svg') }}" alt="" aria-hidden="true">
            Ajukan Kerjasama
        </a>
    </div>
</div>
@endsection
