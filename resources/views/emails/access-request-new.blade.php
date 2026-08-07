<x-mail::message>
# Permintaan Akses Dokumen Baru

Pengguna **{{ $accessRequest->user->name }}** ({{ $accessRequest->user->email }}) meminta akses ke dokumen:

**{{ $accessRequest->document->title }}**

@if($accessRequest->request_message)
> {{ $accessRequest->request_message }}
@endif

<x-mail::button :url="url('/admin/access-requests/'.$accessRequest->id)">
Tinjau Permintaan
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
