@extends('layouts.app')

@php
    $isEdit = isset($role);
    $title = $isEdit ? 'Edit Role' : 'Role Baru';
    $subtitle = $isEdit ? 'Perbarui informasi role dan perizinan' : 'Atur izin akses untuk role baru';
    $formAction = $isEdit ? route('roles.update', $role) : route('roles.store');
    $selectedPermissions = old('permissions', $isEdit ? $role->permissions->pluck('id')->all() : []);
    $selectedPermissions = is_array($selectedPermissions) ? $selectedPermissions : [];
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
                    <label class="text-sm font-medium text-slate-600">Nama Role (unik)</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $isEdit ? $role->name : '') }}"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        required
                    >
                    <p class="mt-1 text-xs text-slate-400">Contoh: cashier, manager.</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Nama Tampilan</label>
                    <input
                        type="text"
                        name="display_name"
                        value="{{ old('display_name', $isEdit ? $role->display_name : '') }}"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        required
                    >
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Deskripsi</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description', $isEdit ? $role->description : '') }}</textarea>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Izin Akses</label>
                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 hover:border-indigo-200">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                class="mt-1 size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                {{ in_array($permission->id, $selectedPermissions) ? 'checked' : '' }}
                            >
                            <span>
                                <span class="font-semibold text-slate-700">{{ $permission->display_name }}</span>
                                <span class="block text-xs text-slate-400">{{ $permission->name }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('roles.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Role' }}
                </button>
            </div>
        </form>
    </div>
@endsection
