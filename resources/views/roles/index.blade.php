@extends('layouts.app')

@section('title', 'Role & Izin')
@section('subtitle', 'Kelola hak akses kasir dan manajemen')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Daftar Role</h2>
            <p class="text-sm text-slate-500">Atur hak akses fitur untuk setiap role</p>
        </div>
        <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            + Role Baru
        </a>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($roles as $role)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">{{ $role->display_name }}</h3>
                        <p class="text-sm text-slate-500">{{ $role->description ?? 'Tidak ada deskripsi' }}</p>
                    </div>
                    <div class="text-sm text-slate-500">
                        {{ $role->users_count }} pengguna
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    @forelse ($role->permissions as $permission)
                        <span class="rounded-full bg-indigo-50 px-3 py-1 font-medium text-indigo-600">
                            {{ $permission->display_name }}
                        </span>
                    @empty
                        <span class="text-slate-400">Belum ada izin ditetapkan.</span>
                    @endforelse
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <a href="{{ route('roles.edit', $role) }}" class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                        Edit Role
                    </a>
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Hapus role ini?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-full border border-red-200 px-4 py-2 text-xs font-semibold text-red-500 hover:bg-red-50">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500">
                Belum ada role. Tambahkan role baru untuk mengatur akses.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $roles->links() }}
    </div>
@endsection
