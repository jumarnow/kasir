@extends('layouts.app')

@php
    $isEdit = isset($user);
    $title = $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna';
    $subtitle = $isEdit ? 'Perbarui informasi akun kasir atau manajer' : 'Buat akun kasir atau manajer baru';
    $formAction = $isEdit ? route('users.update', $user) : route('users.store');
    $selectedRoles = old('roles', $isEdit ? $user->roles->pluck('id')->all() : []);
    $selectedRoles = is_array($selectedRoles) ? $selectedRoles : [];
@endphp

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form action="{{ $formAction }}" method="POST" class="space-y-6">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Nama Lengkap</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $isEdit ? $user->name : '') }}"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        required
                    >
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $isEdit ? $user->email : '') }}"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        required
                    >
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-600">Username</label>
                    <input
                        type="text"
                        name="username"
                        value="{{ old('username', $isEdit ? $user->username : '') }}"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        placeholder="Mis. admin"
                        required
                    >
                    <p class="mt-1 text-xs text-slate-400">Gunakan huruf, angka, atau strip bawah tanpa spasi.</p>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">{{ $isEdit ? 'Password Baru' : 'Password' }}</label>
                    <input
                        type="password"
                        name="password"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        @unless($isEdit) required @endunless
                    >
                    @if ($isEdit)
                        <p class="mt-1 text-xs text-slate-400">Biarkan kosong jika tidak mengubah password.</p>
                    @endif
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Konfirmasi Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        @unless($isEdit) required @endunless
                    >
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Role</label>
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($roles as $id => $label)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:border-indigo-200">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $id }}"
                                class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                {{ in_array($id, $selectedRoles) ? 'checked' : '' }}
                            >
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                </button>
            </div>
        </form>
    </div>
@endsection
