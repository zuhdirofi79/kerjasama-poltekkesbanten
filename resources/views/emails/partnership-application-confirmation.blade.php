<x-mail::message>
# Pengajuan Kerja Sama Telah Diterima

Terima kasih. Pengajuan kerja sama dari **{{ $application->institution_name }}** telah kami terima.

**Nomor pengajuan:** {{ $application->submission_code }}  
**Tanggal pengajuan:** {{ $application->submission_date?->format('d/m/Y') }}

Tim pengelola kerja sama Poltekkes Kemenkes Banten akan memeriksa kelengkapan pengajuan dan menghubungi calon mitra melalui kontak yang telah diberikan.

Simpan nomor pengajuan ini untuk keperluan komunikasi.

Terima kasih,<br>
Unit Kerja Sama — {{ config('app.name') }}
</x-mail::message>
