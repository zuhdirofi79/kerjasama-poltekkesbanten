@extends('layouts.admin')
@section('title', 'Indikator Kepuasan')
@section('heading', 'Indikator Kuesioner Kepuasan')
@section('content')
<p class="mb-4 text-sm text-slate-600">Indikator ini dapat diubah. Bukan indikator resmi Kemenkes.</p>

<form method="POST" action="{{ route('admin.satisfaction.questions.store') }}" class="card-surface mb-6 flex gap-2 p-4">
    @csrf
    <input type="text" name="body" required maxlength="500" placeholder="Indikator baru" class="w-full rounded-lg border-teal-200 text-sm">
    <button class="btn-teal !py-2 !text-xs">Tambah</button>
</form>

<div class="card-surface overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left"><tr>
            <th class="px-4 py-2">Urutan</th>
            <th class="px-4 py-2">Indikator</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2"></th>
        </tr></thead>
        <tbody>
        @foreach($questions as $question)
            <tr class="border-t">
                <td class="px-4 py-2 align-top" colspan="4">
                    <form method="POST" action="{{ route('admin.satisfaction.questions.update', $question) }}" class="grid gap-2 md:grid-cols-[5rem_1fr_8rem_auto]">
                        @csrf @method('PUT')
                        <input type="number" name="sort_order" value="{{ $question->sort_order }}" class="rounded-lg border-teal-200 text-sm">
                        <input type="text" name="body" value="{{ $question->body }}" class="rounded-lg border-teal-200 text-sm">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($question->is_active)> Aktif
                        </label>
                        <button class="btn-outline-teal !py-2 !text-xs">Simpan</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
