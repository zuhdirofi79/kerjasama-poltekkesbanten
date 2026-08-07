@extends('layouts.admin')
@section('title', 'Permintaan Akses')
@section('heading', 'Permintaan Akses')
@section('content')
<form method="GET" class="mb-4 flex gap-2">
    <select name="status" class="rounded-lg border-teal-200 text-sm">
        <option value="">Semua status</option>
        @foreach($statuses as $status)
            <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <button class="btn-outline-teal !py-2 !text-xs">Filter</button>
</form>
<div class="card-surface overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left"><tr>
            <th class="px-4 py-2">Pengguna</th><th class="px-4 py-2">Dokumen</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Diminta</th><th class="px-4 py-2"></th>
        </tr></thead>
        <tbody>
        @foreach($requests as $item)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $item->user?->email }}</td>
                <td class="px-4 py-2">{{ $item->document?->title }}</td>
                <td class="px-4 py-2">{{ $item->status->label() }}</td>
                <td class="px-4 py-2">{{ $item->requested_at?->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-2 text-right"><a href="{{ route('admin.access-requests.show', $item) }}" class="text-teal-700 hover:underline">Detail</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $requests->links() }}</div>
@endsection
