<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Kasir Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    @yield('head')
    <style>
        html,
        body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }

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

        .sidebar {
            width: 16rem;
            overflow-x: hidden;
            transition: width 0.2s ease;
        }

        .sidebar .nav-link {
            transition: all 0.2s ease;
        }

        .sidebar-brand-icon {
            display: none;
        }

        body.sidebar-collapsed .sidebar {
            width: 5rem;
        }

        body.sidebar-collapsed .sidebar .sidebar-brand {
            padding: 1.25rem 0.75rem;
            justify-content: center;
        }

        body.sidebar-collapsed .sidebar nav {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        body.sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            padding: 0.75rem;
        }

        body.sidebar-collapsed .sidebar .sidebar-brand-icon {
            display: inline-flex;
        }

        body.sidebar-collapsed .sidebar .nav-link .label,
        body.sidebar-collapsed .sidebar .section-title,
        body.sidebar-collapsed .sidebar .sidebar-description,
        body.sidebar-collapsed .sidebar .sidebar-brand-text,
        body.sidebar-collapsed .sidebar .sidebar-footer {
            display: none;
        }

        body.sidebar-collapsed .sidebar .nav-link .icon {
            font-size: 1.25rem;
        }

        body.sidebar-collapsed .layout-content {
            margin-left: 0;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-100 font-[Inter] text-slate-800">
    <div class="min-h-screen flex">
        @php
            $currentUser = auth()->user();
            if ($currentUser) {
                $currentUser->loadMissing('roles.permissions');
            }
            $roleLabel = $currentUser ? $currentUser->roles->pluck('display_name')->join(', ') : null;
            $permissionNames = $currentUser
                ? $currentUser->roles->flatMap(fn($role) => $role->permissions)->pluck('name')->unique()
                : collect();
            $isAdmin = $currentUser?->hasRole('admin') ?? false;
            $permissions = [
                'dashboard' => $isAdmin || $permissionNames->contains('manage_dashboard'),
                'products' => $isAdmin || $permissionNames->contains('manage_products'),
                'categories' => $isAdmin || $permissionNames->contains('manage_categories'),
                'customers' => $isAdmin || $permissionNames->contains('view_customers'),
                'transactions' => $isAdmin || $permissionNames->contains('manage_transactions'),
                'users' => $isAdmin || $permissionNames->contains('manage_users'),
                'roles' => $isAdmin || $permissionNames->contains('manage_roles'),
                'reports' => $isAdmin || $permissionNames->contains('view_reports'),
                'view_profit' => $isAdmin || $permissionNames->contains('view_profit'),
                'payroll_menu' => $isAdmin || $permissionNames->contains('menu_penggajian'),
                'view_employees' => $isAdmin || $permissionNames->contains('view_employees'),
                'view_payrolls' => $isAdmin || $permissionNames->contains('view_payrolls'),
            ];
        @endphp
        <aside id="sidebar" class="sidebar hidden md:flex md:flex-col bg-white border-r border-slate-200">
            <div class="px-4 py-4 border-b border-slate-200 flex items-center gap-3 sidebar-brand">
                @if(isset($settings['store_logo']))
                    <img src="{{ Storage::url($settings['store_logo']) }}" alt="Logo" class="h-8 w-auto object-contain">
                @else
                    <div class="h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center text-xl">
                        🛒
                    </div>
                @endif
                <div class="flex flex-col sidebar-brand-text-container">
                    <span
                        class="text-sm font-bold text-indigo-600 truncate sidebar-brand-text">{{ $settings['store_name'] ?? 'Kasir Modern' }}</span>
                </div>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-1">
                @if ($permissions['dashboard'])
                    <a href="{{ route('dashboard') }}" class="nav-link" title="Dashboard">
                        <span class="icon">📊</span> <span class="label">Dashboard</span>
                    </a>
                @endif

                @if ($permissions['products'] || $permissions['categories'])
                    <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2 section-title">Inventaris</p>

                    {{-- Dropdown Toggle --}}
                    <button type="button" class="nav-link w-full flex items-center justify-between group select-none"
                        onclick="$(this).next().slideToggle(200); $(this).find('.chevron').toggleClass('rotate-180')">
                        <div class="flex items-center gap-2">
                            <span class="icon">📦</span> <span class="label">Produk & Stok</span>
                        </div>
                        <span class="chevron text-xs text-slate-400 transition-transform duration-200">▼</span>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div class="pl-4 space-y-1 mt-1 hidden" id="product-menu">
                        @if ($permissions['products'])
                            <a href="{{ route('products.index') }}" class="nav-link text-sm" title="Daftar Produk">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2 group-hover:bg-indigo-400"></span> <span
                                    class="label">Daftar Produk</span>
                            </a>
                            <a href="{{ route('finishings.index') }}" class="nav-link text-sm" title="Finishing">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2 group-hover:bg-indigo-400"></span> <span
                                    class="label">Finishing</span>
                            </a>
                            <a href="{{ route('materials.index') }}" class="nav-link text-sm" title="Material">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2 group-hover:bg-indigo-400"></span> <span
                                    class="label">Material</span>
                            </a>
                            <a href="{{ route('displays.index') }}" class="nav-link text-sm" title="Display">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2 group-hover:bg-indigo-400"></span> <span
                                    class="label">Display</span>
                            </a>
                        @endif
                        @if ($permissions['categories'])
                            <a href="{{ route('categories.index') }}" class="nav-link text-sm" title="Kategori">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2 group-hover:bg-indigo-400"></span> <span
                                    class="label">Kategori</span>
                            </a>
                        @endif
                    </div>
                @endif

                @if ($permissions['customers'])
                    <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2 section-title">Pelanggan</p>
                    <a href="{{ route('customers.index') }}" class="nav-link" title="Pelanggan">
                        <span class="icon">🧑‍🤝‍🧑</span> <span class="label">Pelanggan</span>
                    </a>
                @endif

                @if ($permissions['transactions'])
                    <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2 section-title">Transaksi</p>
                    <a href="{{ route('transactions.create') }}" class="nav-link" title="Transaksi Baru">
                        <span class="icon">➕</span> <span class="label">Transaksi Baru</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" class="nav-link" title="Transaksi">
                        <span class="icon">🧾</span> <span class="label">Transaksi</span>
                    </a>
                @endif

                @if ($permissions['payroll_menu'])
                    <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2 section-title">Penggajian</p>

                    {{-- Dropdown Toggle --}}
                    <button type="button" class="nav-link w-full flex items-center justify-between group select-none"
                        onclick="$(this).next().slideToggle(200); $(this).find('.chevron').toggleClass('rotate-180')">
                        <div class="flex items-center gap-2">
                            <span class="icon">💸</span> <span class="label">Penggajian</span>
                        </div>
                        <span class="chevron text-xs text-slate-400 transition-transform duration-200">▼</span>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div class="pl-4 space-y-1 mt-1 hidden" id="payroll-menu">
                        @if ($permissions['view_employees'])
                            <a href="{{ route('employees.index') }}" class="nav-link text-sm" title="Pegawai">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2 group-hover:bg-indigo-400"></span> 
                                <span class="label">Pegawai</span>
                            </a>
                        @endif
                        @if ($permissions['view_payrolls'])
                            <a href="{{ route('payrolls.index') }}" class="nav-link text-sm" title="Slip Gaji">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2 group-hover:bg-indigo-400"></span> 
                                <span class="label">Slip Gaji</span>
                            </a>
                        @endif
                    </div>
                @endif

                @if ($permissions['users'] || $permissions['roles'])
                    <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2 section-title">Kendali Akses</p>
                    @if ($permissions['users'])
                        <a href="{{ route('users.index') }}" class="nav-link" title="Pengguna">
                            <span class="icon">👥</span> <span class="label">Pengguna</span>
                        </a>
                    @endif
                    @if ($permissions['roles'])
                        <a href="{{ route('roles.index') }}" class="nav-link" title="Role &amp; Izin">
                            <span class="icon">🔐</span> <span class="label">Role &amp; Izin</span>
                        </a>
                    @endif
                @endif

                @if ($permissions['reports'])
                    <a href="{{ route('reports.sales') }}" class="nav-link" title="Laporan Penjualan">
                        <span class="icon">💰</span> <span class="label">Penjualan</span>
                    </a>
                    @if ($permissions['view_profit'])
                        <a href="{{ route('reports.profit') }}" class="nav-link" title="Laporan Profit">
                            <span class="icon">📈</span> <span class="label">Profit</span>
                        </a>
                    @endif
                @endif

                {{-- Settings --}}
                <p class="text-xs uppercase text-slate-400 mt-6 mb-2 px-2 section-title">Pengaturan</p>
                <a href="{{ route('settings.index') }}" class="nav-link" title="Pengaturan Toko">
                    <span class="icon">⚙️</span> <span class="label">Pengaturan Toko</span>
                </a>
            </nav>
            <div class="px-6 py-6 border-t border-slate-200 text-sm text-slate-500 sidebar-footer">
                &copy; {{ date('Y') }} {{ $settings['store_name'] ?? 'Kasir Modern' }}
            </div>
        </aside>

        <div class="flex-1 flex flex-col layout-content transition-all duration-200">
            <header class="bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button id="mobile-nav-toggle"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-md border border-slate-200 text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <button id="sidebar-toggle"
                        class="hidden md:inline-flex items-center justify-center p-2 rounded-md border border-slate-200 text-slate-600 transition hover:bg-slate-100"
                        aria-label="Toggle sidebar" title="Sembunyikan sidebar">
                        <span id="sidebar-toggle-icon">◀</span>
                    </button>
                    <button id="fullscreen-toggle"
                        class="hidden md:inline-flex items-center justify-center p-2 rounded-md border border-slate-200 text-slate-600 transition hover:bg-slate-100"
                        aria-label="Aktifkan layar penuh" title="Aktifkan layar penuh">
                        <span id="fullscreen-toggle-icon">⛶</span>
                    </button>
                    <div>
                        <h1 class="text-xl font-semibold text-slate-800">@yield('title', 'Kasir')</h1>
                        <p class="text-sm text-slate-500">@yield('subtitle', 'Kelola operasional kasir secara mudah')
                        </p>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-3">
                    <span class="text-sm text-slate-600">
                        {{ $currentUser?->name ?? 'Guest' }}
                    </span>
                    <span
                        class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">
                        {{ $roleLabel ?: 'Kasir' }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                            <span>🚪</span>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>
            <main class="px-3 md:px-5 py-6">
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
                @if ($permissions['dashboard'])
                    <a href="{{ route('dashboard') }}" class="mobile-nav-link">Dashboard</a>
                @endif
                @if ($permissions['products'])
                    <a href="{{ route('products.index') }}" class="mobile-nav-link">Produk</a>
                    <a href="{{ route('finishings.index') }}" class="mobile-nav-link">Finishing</a>
                    <a href="{{ route('materials.index') }}" class="mobile-nav-link">Material</a>
                    <a href="{{ route('displays.index') }}" class="mobile-nav-link">Display</a>
                @endif
                @if ($permissions['categories'])
                    <a href="{{ route('categories.index') }}" class="mobile-nav-link">Kategori</a>
                @endif
                @if ($permissions['customers'])
                    <a href="{{ route('customers.index') }}" class="mobile-nav-link">Pelanggan</a>
                @endif
                @if ($permissions['transactions'])
                    <a href="{{ route('transactions.index') }}" class="mobile-nav-link">Transaksi</a>
                    <a href="{{ route('transactions.create') }}" class="mobile-nav-link">Transaksi Baru</a>
                @endif
                @if ($permissions['payroll_menu'])
                    <div class="border-t border-slate-100 my-2 pt-2">
                        <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Penggajian</p>
                        @if ($permissions['view_employees'])
                            <a href="{{ route('employees.index') }}" class="mobile-nav-link">Pegawai</a>
                        @endif
                        @if ($permissions['view_payrolls'])
                            <a href="{{ route('payrolls.index') }}" class="mobile-nav-link">Slip Gaji</a>
                        @endif
                    </div>
                @endif
                @if ($permissions['users'])
                    <a href="{{ route('users.index') }}" class="mobile-nav-link">Pengguna</a>
                @endif
                @if ($permissions['roles'])
                    <a href="{{ route('roles.index') }}" class="mobile-nav-link">Role &amp; Izin</a>
                @endif
                @if ($permissions['reports'])
                    <a href="{{ route('reports.sales') }}" class="mobile-nav-link">Laporan Penjualan</a>
                    <a href="{{ route('reports.profit') }}" class="mobile-nav-link">Laporan Profit</a>
                @endif
                <a href="{{ route('settings.index') }}" class="mobile-nav-link">Pengaturan Toko</a>
                <form method="POST" action="{{ route('logout') }}" class="pt-3 border-t border-slate-200">
                    @csrf
                    <button type="submit"
                        class="mobile-nav-link text-center bg-red-50 border-red-200 text-red-600 hover:bg-red-100">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            const $body = $('body');
            const $sidebarToggle = $('#sidebar-toggle');
            const $toggleIcon = $('#sidebar-toggle-icon');
            const $fullscreenToggle = $('#fullscreen-toggle');
            const $fullscreenIcon = $('#fullscreen-toggle-icon');
            const storageKey = 'sidebarCollapsed';
            const fullscreenKey = 'fullscreenEnabled';
            const transactionsCreatePath = @json(parse_url(route('transactions.create'), PHP_URL_PATH));
            let fullscreenPreference = false;
            const getPathname = (href) => {
                try {
                    return new URL(href, window.location.origin).pathname;
                } catch (error) {
                    return href;
                }
            };
            const setToggleIcon = (collapsed) => {
                $toggleIcon.text(collapsed ? '▶' : '◀');
                $sidebarToggle.attr('title', collapsed ? 'Tampilkan sidebar' : 'Sembunyikan sidebar');
            };
            const persistState = (collapsed) => {
                try {
                    localStorage.setItem(storageKey, collapsed ? '1' : '0');
                } catch (error) {
                    console.warn('Tidak dapat menyimpan status sidebar', error);
                }
            };
            const applySidebarState = (collapsed, persist = true) => {
                $body.toggleClass('sidebar-collapsed', collapsed);
                setToggleIcon(collapsed);
                if (persist) {
                    persistState(collapsed);
                }
            };
            const savedState = (() => {
                try {
                    return localStorage.getItem(storageKey);
                } catch (error) {
                    return null;
                }
            })();
            if (savedState === '1') {
                applySidebarState(true, false);
            } else if (savedState === '0') {
                applySidebarState(false, false);
            } else {
                setToggleIcon($body.hasClass('sidebar-collapsed'));
            }
            if (window.location.pathname === transactionsCreatePath) {
                applySidebarState(true, false);
            }
            $sidebarToggle.on('click', function () {
                const collapsed = !$body.hasClass('sidebar-collapsed');
                applySidebarState(collapsed);
            });
            const fullscreenSupported = () => document.fullscreenEnabled || document.webkitFullscreenEnabled || document.mozFullScreenEnabled || document.msFullscreenEnabled;
            const fullscreenElement = () => document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
            const enterFullscreen = () => {
                const el = document.documentElement;
                if (el.requestFullscreen) {
                    return el.requestFullscreen();
                }
                if (el.webkitRequestFullscreen) {
                    return el.webkitRequestFullscreen();
                }
                if (el.mozRequestFullScreen) {
                    return el.mozRequestFullScreen();
                }
                if (el.msRequestFullscreen) {
                    return el.msRequestFullscreen();
                }
            };
            const exitFullscreen = () => {
                if (document.exitFullscreen) {
                    return document.exitFullscreen();
                }
                if (document.webkitExitFullscreen) {
                    return document.webkitExitFullscreen();
                }
                if (document.mozCancelFullScreen) {
                    return document.mozCancelFullScreen();
                }
                if (document.msExitFullscreen) {
                    return document.msExitFullscreen();
                }
            };
            const updateFullscreenButton = () => {
                const active = Boolean(fullscreenElement());
                $fullscreenIcon.text(active ? '🗗' : '⛶');
                const label = active ? 'Keluar layar penuh' : 'Aktifkan layar penuh';
                $fullscreenToggle.attr('aria-label', label).attr('title', label);
            };
            const persistFullscreenPreference = () => {
                try {
                    localStorage.setItem(fullscreenKey, fullscreenPreference ? '1' : '0');
                } catch (error) {
                    console.warn('Tidak dapat menyimpan preferensi fullscreen', error);
                }
            };
            const savedFullscreen = (() => {
                try {
                    return localStorage.getItem(fullscreenKey);
                } catch (error) {
                    return null;
                }
            })();
            fullscreenPreference = savedFullscreen === '1';
            const setupFullscreenRestoreOnGesture = () => {
                let restored = false;
                const handler = () => {
                    if (restored) {
                        return;
                    }
                    restored = true;
                    $(document).off('click.fullscreenRestore', handler);
                    $(document).off('keydown.fullscreenRestore', handler);
                    const result = enterFullscreen();
                    if (result && typeof result.then === 'function') {
                        result.then(() => {
                            updateFullscreenButton();
                        }).catch(() => {
                            updateFullscreenButton();
                        });
                    } else {
                        updateFullscreenButton();
                    }
                };
                $(document).on('click.fullscreenRestore', handler);
                $(document).on('keydown.fullscreenRestore', handler);
            };
            if (fullscreenSupported()) {
                updateFullscreenButton();
                $fullscreenToggle.on('click', function () {
                    if (fullscreenElement()) {
                        fullscreenPreference = false;
                        persistFullscreenPreference();
                        exitFullscreen();
                    } else {
                        fullscreenPreference = true;
                        persistFullscreenPreference();
                        const result = enterFullscreen();
                        if (result && typeof result.then === 'function') {
                            result.catch(() => {
                                updateFullscreenButton();
                            });
                        }
                    }
                });
                ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'].forEach(event => {
                    document.addEventListener(event, () => {
                        if (!fullscreenElement() && document.visibilityState === 'visible') {
                            fullscreenPreference = false;
                            persistFullscreenPreference();
                        }
                        updateFullscreenButton();
                    });
                });
                $(document).on('keydown.fullscreenPreference', function (event) {
                    if (event.key === 'Escape' && fullscreenElement()) {
                        fullscreenPreference = false;
                        persistFullscreenPreference();
                    }
                });
                window.addEventListener('beforeunload', () => {
                    persistFullscreenPreference();
                });
                if (fullscreenPreference && !fullscreenElement()) {
                    setTimeout(() => {
                        const attempt = enterFullscreen();
                        if (attempt && typeof attempt.then === 'function') {
                            attempt.then(() => {
                                updateFullscreenButton();
                            }).catch(() => {
                                setupFullscreenRestoreOnGesture();
                            });
                        } else {
                            setupFullscreenRestoreOnGesture();
                        }
                    }, 120);
                }
            } else {
                $fullscreenToggle.addClass('md:hidden').attr('aria-hidden', 'true');
            }
            $('.nav-link').each(function () {
                const current = window.location.pathname;
                const href = $(this).attr('href');
                if (!href) return;
                const hrefPath = getPathname(href);
                if (!hrefPath) {
                    return;
                }
                if (current === hrefPath || (hrefPath !== '/' && current.startsWith(hrefPath + '/'))) {
                    $(this).addClass('active');
                    // Open dropdown if this link is inside one
                    const $dropdown = $(this).closest('.space-y-1.hidden');
                    if ($dropdown.length) {
                        $dropdown.removeClass('hidden').show();
                        $dropdown.prev().find('.chevron').addClass('rotate-180');
                    }
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

    <script>
        $(document).on('input', '.currency-input', function () {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                value = new Intl.NumberFormat('id-ID', { style: 'decimal', minimumFractionDigits: 0 }).format(Number(value));
            }
            this.value = value;
        });

        $(document).on('submit', '.delete-form', function (e) {
            e.preventDefault();
            const form = this;
            const message = $(form).data('message') || 'Data ini akan dihapus permanen!';

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '1rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>