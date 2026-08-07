@extends('layouts.admin')
@section('title', 'Pengguna')
@section('heading', 'Manajemen Pengguna')
@section('content')
@can('create', App\Models\User::class)
<div class="mb-4"><a href="{{ route('admin.users.create') }}" class="btn-teal !py-2 !text-xs">Tambah Pengguna</a></div>
@endcan
<div class="card-surface overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-teal-50 text-left"><tr>
            <th class="px-4 py-2">Nama</th><th class="px-4 py-2">Email</th><th class="px-4 py-2">Peran</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th>
        </tr></thead>
        <tbody>
        @foreach($users as $user)
            <tr class="border-t">
                <td class="px-4 py-2 font-semibold">{{ $user->name }}</td>
                <td class="px-4 py-2">{{ $user->email }}</td>
                <td class="px-4 py-2">{{ $user->role?->label() }}</td>
                <td class="px-4 py-2">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td class="px-4 py-2 text-right space-x-2">
                    @can('update', $user)
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-teal-700 hover:underline">Edit</a>
                    @endcan
                    @can('delete', $user)
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Arsipkan pengguna?')">@csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
