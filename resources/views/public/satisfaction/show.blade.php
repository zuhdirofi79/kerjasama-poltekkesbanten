@extends('layouts.public')

@section('title', 'Evaluasi Kepuasan Kerjasama')
@section('nav', 'beranda')

@section('content')
<div class="content-section">
    <div class="page-panel max-w-2xl space-y-6">
        <div>
            <h1 class="section-title">Evaluasi Kepuasan Kerjasama</h1>
            <p class="mt-2 text-sm" style="color: var(--brand-muted);">
                {{ $survey->title ?: 'Mohon berikan penilaian terhadap pelaksanaan kerja sama.' }}
            </p>
            @if($survey->document)
                <p class="mt-1 text-sm font-medium" style="color: var(--brand-teal-deep);">{{ $survey->document->title }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('satisfaction.store', $survey) }}" class="space-y-5">
            @csrf
            <div class="form-grid-2">
                <div>
                    <label class="block text-sm font-medium">Nama (opsional)</label>
                    <input type="text" name="respondent_name" value="{{ old('respondent_name') }}" class="mt-1 w-full rounded-lg border-teal-200" maxlength="255">
                </div>
                <div>
                    <label class="block text-sm font-medium">Instansi (opsional)</label>
                    <input type="text" name="respondent_institution" value="{{ old('respondent_institution') }}" class="mt-1 w-full rounded-lg border-teal-200" maxlength="255">
                </div>
            </div>

            @foreach($questions as $question)
                <fieldset class="rounded-xl border p-4" style="border-color: var(--brand-border);">
                    <legend class="text-sm font-semibold" style="color: var(--brand-teal-deep);">{{ $loop->iteration }}. {{ $question->body }}</legend>
                    <div class="mt-3 grid gap-2 sm:grid-cols-5">
                        @foreach($scale as $score => $label)
                            <label class="flex cursor-pointer flex-col items-center rounded-lg border px-2 py-2 text-center text-xs" style="border-color: var(--brand-border);">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $score }}" @checked((string) old('answers.'.$question->id) === (string) $score) required>
                                <span class="mt-1 font-semibold">{{ $score }}</span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('answers.'.$question->id)
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </fieldset>
            @endforeach

            <div>
                <label class="block text-sm font-medium">Saran / Masukan <span class="text-xs font-normal text-slate-500">opsional</span></label>
                <textarea name="suggestion" rows="4" class="mt-1 w-full rounded-lg border-teal-200">{{ old('suggestion') }}</textarea>
            </div>

            <button type="submit" class="btn-cta">Kirim Evaluasi</button>
        </form>
    </div>
</div>
@endsection
