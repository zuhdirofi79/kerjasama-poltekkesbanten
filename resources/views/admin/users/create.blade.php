@extends('layouts.admin')
@section('title', 'Tambah Pengguna')
@section('heading', 'Tambah Pengguna')
@section('content')
<form method="POST" action="{{ route('admin.users.store') }}" class="card-surface max-w-xl space-y-4 p-5">
    @csrf
    <div>
        <label class="block text-sm font-medium">Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Password</label>
        <input type="password" name="password" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Peran</label>
        <select name="role" class="mt-1 w-full rounded-lg border-teal-200">
            @foreach($roles as $role)
                <option value="{{ $role->value }}">{{ $role->label() }}</option>
            @endforeach
        </select>
    </div>
    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
    <button class="btn-teal">Simpan</button>
</form>
@endsection
