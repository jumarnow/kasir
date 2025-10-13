<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk | Kasir Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .glass-panel {
            backdrop-filter: blur(24px);
            background: rgba(15, 23, 42, 0.65);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.35),_rgba(15,23,42,0.9)_60%)]">
    <div class="relative flex min-h-screen items-center justify-center px-6 py-12">
        <div class="absolute inset-0 bg-[url('https://www.toptal.com/designers/subtlepatterns/uploads/geometry.png')] opacity-5"></div>
        <div class="relative mx-auto grid w-full max-w-5xl gap-10 overflow-hidden rounded-3xl border border-white/10 bg-white/5 shadow-2xl ring-1 ring-white/10 md:grid-cols-2">
            <div class="glass-panel hidden flex-col justify-between p-10 text-white md:flex">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-indigo-200">Kasir Modern</p>
                    <h1 class="mt-4 text-3xl font-semibold leading-tight">Selamat datang kembali!</h1>
                    <p class="mt-3 text-sm text-slate-200/80">
                        Kelola transaksi, stok, dan laporan penjualan dengan dashboard yang bersih dan responsif.
                    </p>
                </div>
                <div class="mt-10 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/30 text-lg">⚡</span>
                        <div>
                            <p class="text-sm font-semibold text-white">Transaksi cepat</p>
                            <p class="text-xs text-indigo-100/80">Scan barcode dan otomatis hitung kembalian.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/30 text-lg">📊</span>
                        <div>
                            <p class="text-sm font-semibold text-white">Laporan realtime</p>
                            <p class="text-xs text-indigo-100/80">Pantau penjualan dan profit harian.</p>
                        </div>
                    </div>
                </div>
                <p class="mt-10 text-xs text-indigo-100/60">© {{ date('Y') }} Kasir Modern. Semua hak dilindungi.</p>
            </div>
            <div class="flex flex-col justify-center bg-white p-10 text-slate-700">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Masuk ke akun</h2>
                    <p class="mt-2 text-sm text-slate-500">Gunakan username dan password yang telah diberikan.</p>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        <p class="font-semibold">Ups, ada kesalahan:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="username" class="text-sm font-medium text-slate-600">Username</label>
                        <div class="mt-1">
                            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                                   class="w-full rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>
                    </div>
                    <div>
                        <label for="password" class="text-sm font-medium text-slate-600">Password</label>
                        <div class="mt-1">
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                   class="w-full rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 text-slate-500">
                            <input class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            Ingat saya
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500" href="{{ route('password.request') }}">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-500">
                        Masuk Sekarang
                    </button>
                </form>

                <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-xs text-slate-500">
                    <p class="font-semibold text-slate-700">Tips keamanan</p>
                    <ul class="mt-2 list-disc space-y-1 pl-4">
                        <li>Gunakan password unik dan rahasiakan dari orang lain.</li>
                        <li>Logout dari aplikasi saat sudah selesai menggunakan kasir.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
