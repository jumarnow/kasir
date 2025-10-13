@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('subtitle', 'Buat akun kasir atau manajer baru')

@section('content')
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Password</label>
                    <input type="password" name="password" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Role</label>
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($roles as $id => $label)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:border-indigo-200">
                            <input type="checkbox" name="roles[]" value="{{ $id }}" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ in_array($id, old('roles', [])) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Simpan Pengguna</button>
            </div>
        </form>
    </div>
@endsection
