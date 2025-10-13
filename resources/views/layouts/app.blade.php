<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Kasir App') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <style>
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(100 116 139);
            transition: all 0.2s ease;
        }
        .nav-link:hover {
            background-color: rgb(248 250 252);
            color: rgb(79 70 229);
        }
        .nav-link.active {
            background-color: rgb(238 242 255);
            color: rgb(79 70 229);
            border: 1px solid rgb(224 231 255);
        }
        .nav-link .icon {
            font-size: 1rem;
        }
        .mobile-nav-link {
            display: block;
            border: 1px solid rgb(226 232 240);
            border-radius: 0.75rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(100 116 139);
        }
        .mobile-nav-link:hover {
            background-color: rgb(238 242 255);
            color: rgb(79 70 229);
        }
    </style>
</head>
<body class="bg-slate-100 font-[Inter] text-slate-800">
    <div class="min-h-screen flex">
        <aside class="hidden md:flex md:flex-col md:w-64 bg-white border-r border-slate-200">
            <div class="px-6 py-8 border-b border-slate-200">
                <span class="text-lg font-semibold text-indigo-600">Kasir Modern</span>
                <p class="text-sm text-slate-500 mt-1">Dashboard kasir &amp; laporan</p>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <span class="icon">📊</span> Dashboard
                </a>
                <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2">Produk</p>
                <a href="{{ route('products.index') }}" class="nav-link">
                    <span class="icon">📦</span> Produk
                </a>
                <a href="{{ route('categories.index') }}" class="nav-link">
                    <span class="icon">🏷️</span> Kategori
                </a>
                <a href="{{ route('customers.index') }}" class="nav-link">
                    <span class="icon">🧑‍🤝‍🧑</span> Pelanggan
                </a>
                <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2">Transaksi</p>
                <a href="{{ route('transactions.index') }}" class="nav-link">
                    <span class="icon">🧾</span> Transaksi
                </a>
                <a href="{{ route('transactions.create') }}" class="nav-link">
                    <span class="icon">➕</span> Transaksi Baru
                </a>
                <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2">Kendali Akses</p>
                <a href="{{ route('users.index') }}" class="nav-link">
                    <span class="icon">👥</span> Pengguna
                </a>
                <a href="{{ route('roles.index') }}" class="nav-link">
                    <span class="icon">🔐</span> Role &amp; Izin
                </a>
                <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2">Laporan</p>
                <a href="{{ route('reports.sales') }}" class="nav-link">
                    <span class="icon">💰</span> Penjualan
                </a>
                <a href="{{ route('reports.profit') }}" class="nav-link">
                    <span class="icon">📈</span> Profit
                </a>
            </nav>
            <div class="px-6 py-6 border-t border-slate-200 text-sm text-slate-500">
                &copy; {{ date('Y') }} Kasir Modern
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button id="mobile-nav-toggle" class="md:hidden inline-flex items-center justify-center p-2 rounded-md border border-slate-200 text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-semibold text-slate-800">@yield('title', 'Kasir')</h1>
                        <p class="text-sm text-slate-500">@yield('subtitle', 'Kelola operasional kasir secara mudah')</p>
                    </div>
                </div>
                @php
                    $currentUser = auth()->user();
                    $roleLabel = $currentUser ? $currentUser->roles->pluck('display_name')->join(', ') : null;
                @endphp
                <div class="hidden md:flex items-center gap-3">
                    <span class="text-sm text-slate-600">
                        {{ $currentUser?->name ?? 'Guest' }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">
                        {{ $roleLabel ?: 'Kasir' }}
                    </span>
                </div>
            </header>
            <main class="px-5 py-6">
                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div id="mobile-nav" class="md:hidden fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60"></div>
        <div class="relative w-72 max-w-full bg-white min-h-full shadow-xl">
            <div class="px-5 py-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-base font-semibold text-indigo-600">Kasir Modern</p>
                    <p class="text-xs text-slate-500">Menu navigasi</p>
                </div>
                <button id="mobile-nav-close" class="text-slate-500">
                    ✕
                </button>
            </div>
            <div class="px-4 py-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="mobile-nav-link">Dashboard</a>
                <a href="{{ route('products.index') }}" class="mobile-nav-link">Produk</a>
                <a href="{{ route('categories.index') }}" class="mobile-nav-link">Kategori</a>
                <a href="{{ route('customers.index') }}" class="mobile-nav-link">Pelanggan</a>
                <a href="{{ route('transactions.index') }}" class="mobile-nav-link">Transaksi</a>
                <a href="{{ route('transactions.create') }}" class="mobile-nav-link">Transaksi Baru</a>
                <a href="{{ route('users.index') }}" class="mobile-nav-link">Pengguna</a>
                <a href="{{ route('roles.index') }}" class="mobile-nav-link">Role &amp; Izin</a>
                <a href="{{ route('reports.sales') }}" class="mobile-nav-link">Laporan Penjualan</a>
                <a href="{{ route('reports.profit') }}" class="mobile-nav-link">Laporan Profit</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        $(function () {
            $('.nav-link').each(function () {
                const current = window.location.pathname;
                const href = $(this).attr('href');
                if (current === '/' && href === '{{ route('dashboard') }}') {
                    $(this).addClass('active');
                } else if (href !== '{{ route('dashboard') }}' && current.startsWith(href)) {
                    $(this).addClass('active');
                }
            });

            $('#mobile-nav-toggle').on('click', function () {
                $('#mobile-nav').removeClass('hidden');
            });

            $('#mobile-nav-close').on('click', function () {
                $('#mobile-nav').addClass('hidden');
            });

            $('#mobile-nav .mobile-nav-link').on('click', function () {
                $('#mobile-nav').addClass('hidden');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
