<x-mail::message>
# Keputusan Permintaan Akses

Permintaan akses Anda untuk dokumen **{{ $accessRequest->document->title }}** telah diperbarui.

**Status:** {{ $accessRequest->status->label() }}

@if($accessRequest->review_note)
**Catatan reviewer:** {{ $accessRequest->review_note }}
@endif

@if($accessRequest->status === \App\Enums\AccessRequestStatus::Approved)
Anda sekarang dapat membuka dokumen melalui portal kerjasama.

<x-mail::button :url="url('/documents/'.$accessRequest->document_id.'/view')">
Buka Dokumen
</x-mail::button>
@endif

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
