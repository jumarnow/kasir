@extends('layouts.app')

@section('title', 'Edit Pelanggan')
@section('subtitle', 'Perbarui data pelanggan')

@section('content')
    <div class="max-w-3xl rounded-2xl bg-white p-6 shadow-sm border border-slate-200">
        <form action="{{ route('customers.update', $customer) }}" method="POST" class="grid gap-6 md:grid-cols-2">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-slate-600">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Catatan</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('notes', $customer->notes) }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                    <span class="text-sm text-slate-600">Pelanggan aktif</span>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-slate-600">Alamat</label>
                    <textarea name="address" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('address', $customer->address) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Kota</label>
                        <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Provinsi</label>
                        <input type="text" name="state" value="{{ old('state', $customer->state) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Kode Pos</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
            </div>
            <div class="md:col-span-2 flex items-center justify-end gap-3">
                <a href="{{ route('customers.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
