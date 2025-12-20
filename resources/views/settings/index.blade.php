@extends('layouts.app')

@section('title', 'Pengaturan')
@section('subtitle', 'Kelola informasi dan profil toko')

@section('content')
<div class="max-w-3xl mx-auto">

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Store Name -->
                <div>
                    <label for="store_name" class="block text-sm font-medium text-slate-700 mb-1">Nama Toko</label>
                    <input id="store_name" type="text" 
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('store_name') border-red-500 @enderror" 
                        name="store_name" 
                        value="{{ old('store_name', $settings['store_name'] ?? '') }}" 
                        required 
                        autocomplete="store_name" 
                        autofocus>
                    @error('store_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Store Address -->
                <div>
                    <label for="store_address" class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                    <textarea id="store_address" 
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('store_address') border-red-500 @enderror" 
                        name="store_address" 
                        rows="3"
                        required>{{ old('store_address', $settings['store_address'] ?? '') }}</textarea>
                    @error('store_address')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="store_phone" class="block text-sm font-medium text-slate-700 mb-1">Nomor HP / WA</label>
                    <input id="store_phone" type="text" 
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('store_phone') border-red-500 @enderror" 
                        name="store_phone" 
                        value="{{ old('store_phone', $settings['store_phone'] ?? '') }}" 
                        autocomplete="store_phone">
                    @error('store_phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Store Logo -->
                <div>
                    <label for="store_logo" class="block text-sm font-medium text-slate-700 mb-1">Logo Toko</label>
                    
                    <div class="mt-2 flex items-center gap-x-3">
                        @if(isset($settings['store_logo']) && $settings['store_logo'])
                            <img src="{{ Storage::url($settings['store_logo']) }}" alt="Store Logo" class="h-16 w-16 rounded-lg object-contain border border-slate-200 bg-slate-50">
                        @else
                            <div class="h-16 w-16 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center text-2xl">
                                🏪
                            </div>
                        @endif
                        
                        <div>
                            <input id="store_logo" type="file" 
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" 
                                name="store_logo">
                            <p class="mt-1 text-xs text-slate-500">Format: JPG, PNG. Maks: 2MB.</p>
                        </div>
                    </div>

                    @error('store_logo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
