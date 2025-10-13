@extends('layouts.app')

@section('title', 'Edit Role')
@section('subtitle', 'Perbarui informasi role dan perizinan')

@section('content')
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Nama Role (unik)</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Nama Tampilan</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $role->display_name) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Deskripsi</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('description', $role->description) }}</textarea>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Izin Akses</label>
                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 hover:border-indigo-200">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="mt-1 size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
