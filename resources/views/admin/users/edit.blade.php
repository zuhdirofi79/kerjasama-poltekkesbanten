@extends('layouts.admin')
@section('title', 'Edit Pengguna')
@section('heading', 'Edit Pengguna')
@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="card-surface max-w-xl space-y-4 p-5">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm font-medium">Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium">Password baru (opsional)</label>
        <input type="password" name="password" class="mt-1 w-full rounded-lg border-teal-200">
    </div>
    @can('changeRole', $user)
    <div>
        <label class="block text-sm font-medium">Peran</label>
        <select name="role" class="mt-1 w-full rounded-lg border-teal-200">
            @foreach($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
    </div>
    @endcan
    @can('deactivate', $user)
    <label class="inline-flex items-center gap-2 text-sm">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))> Aktif
    </label>
    @endcan
    <button class="btn-teal">Perbarui</button>
</form>
@endsection
