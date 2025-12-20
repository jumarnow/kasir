@extends('layouts.app')

@section('title', 'Pengguna')
@section('subtitle', 'Kelola akun kasir dan manajemen')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Daftar Pengguna</h2>
            <p class="text-sm text-slate-500">Atur akses kasir berdasarkan role</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            + Pengguna Baru
        </a>
    </div>

    <!-- Desktop Table View -->
    <div class="mt-6 hidden md:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                            <p class="text-xs text-slate-400">Username: {{ $user->username }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2 text-xs">
                                @forelse ($user->roles as $role)
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 font-semibold text-indigo-600">
                                        {{ $role->display_name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400">Belum ada role</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('users.edit', $user) }}" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500 hover:border-indigo-200 hover:text-indigo-600">
                                    Edit
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="delete-form inline" data-message="Hapus pengguna {{ $user->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-red-200 px-3 py-1 text-xs text-red-500 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-sm text-slate-500">
                            Belum ada pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="mt-6 md:hidden space-y-4">
        @forelse ($users as $user)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $user->name }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Username: {{ $user->username }}</p>
                    </div>
                </div>
                
                <div class="mt-3 space-y-2">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="text-xs w-12 text-slate-400">Email:</span>
                        <span>{{ $user->email }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-xs w-12 text-slate-400 mt-1">Role:</span>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->roles as $role)
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-600">
                                    {{ $role->display_name }}
                                </span>
                            @empty
                                <span class="text-[10px] text-slate-400">Belum ada role</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('users.edit', $user) }}" class="flex-1 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-center text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                        Edit
                    </a>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="delete-form flex-1" data-message="Hapus pengguna {{ $user->name }}?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-100">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">Belum ada pengguna.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection
