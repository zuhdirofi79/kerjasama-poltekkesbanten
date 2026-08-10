<x-mail::message>
# Pengajuan Kerja Sama Baru

Pengajuan kerja sama baru telah diterima.

**Nomor pengajuan:** {{ $application->submission_code }}  
**Instansi:** {{ $application->institution_name }}  
**Narahubung:** {{ $application->contact_person_name }} ({{ $application->contact_person_email }})  
**Jenis pengajuan:** {{ $application->labelFor('proposal_types', $application->proposal_type) }}  
**Judul:** {{ $application->proposal_title }}  
**Tanggal:** {{ $application->submission_date?->format('d/m/Y') }}

<x-mail::button :url="route('admin.partnership-applications.show', $application)">
Buka Detail Admin
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
