@extends('layouts.admin')
@section('title', 'Audit Log')
@section('heading', 'Audit Log')
@section('content')
<form method="GET" class="mb-4 flex gap-2">
    <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter action..." class="rounded-lg border-teal-200 text-sm">
    <button class="btn-outline-teal !py-2 !text-xs">Cari</button>
</form>
<div class="card-surface overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left"><tr>
            <th class="px-4 py-2">Waktu</th><th class="px-4 py-2">User</th><th class="px-4 py-2">Action</th><th class="px-4 py-2">Entity</th>
        </tr></thead>
        <tbody>
        @foreach($logs as $log)
            <tr class="border-t">
                <td class="px-4 py-2 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-2">{{ $log->user?->email ?? '—' }}</td>
                <td class="px-4 py-2 font-mono text-xs">{{ $log->action }}</td>
                <td class="px-4 py-2 text-xs">{{ class_basename((string) $log->entity_type) }} #{{ $log->entity_id }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
