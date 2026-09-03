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
            overflow: hidden;
            width: 100%;
            height: 100%;
            position: relative;
        }

        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(100 116 139);
            transition: all 0.2s ease;
            margin-bottom: 0.125rem;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: -1rem;
            top: 50%;
            transform: translateY(-50%);
            height: 0;
            width: 3px;
            background-color: rgb(99 102 241);
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
        }

        .nav-link:hover {
            background-color: rgb(248 250 252);
            color: rgb(51 65 85);
        }

        .nav-link.active {
            background-color: rgb(238 242 255);
            color: rgb(79 70 229);
            font-weight: 600;
        }

        .nav-link.active::before {
            height: 60%;
            opacity: 1;
        }

        .nav-link .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgb(148 163 184);
            transition: all 0.2s ease;
        }
        
        .nav-link:hover .icon {
            color: rgb(100 116 139);
        }

        .nav-link.active .icon {
            color: rgb(79 70 229);
        }

        .mobile-nav-link {
            display: block;
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(71 85 105);
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
        }

        .mobile-nav-link:hover {
            background-color: rgb(241 245 249);
            color: rgb(15 23 42);
        }

        .sidebar {
            width: 16rem;
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.2s ease;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 5px;
        }
        .sidebar:hover::-webkit-scrollbar-thumb {
            background: #94a3b8;
        }

        .sidebar-brand-icon {
            display: none;
        }

        .section-title {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgb(148 163 184);
            margin-top: 1.25rem;
            margin-bottom: 0.375rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        body.sidebar-collapsed .sidebar {
            width: 4.5rem;
        }

        body.sidebar-collapsed .nav-link::before {
            left: -0.75rem;
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
        body.sidebar-collapsed .sidebar .sidebar-brand-text-container,
        body.sidebar-collapsed .sidebar .sidebar-footer,
        body.sidebar-collapsed .sidebar .chevron {
            display: none;
        }

        body.sidebar-collapsed .sidebar .nav-link .icon {
            font-size: 1.25rem;
            color: rgb(100 116 139);
        }

        body.sidebar-collapsed .layout-content {
            margin-left: 0;
        }

        body.sidebar-collapsed .sidebar .nav-link {
            position: relative;
        }
        
        body.sidebar-collapsed .sidebar:hover .nav-link:hover::after {
            content: attr(title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 0.75rem;
            background: rgb(30 41 59);
            color: white;
            padding: 0.375rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 50;
            pointer-events: none;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-100 font-[Inter] text-slate-800">
    <div class="h-screen flex overflow-hidden">
        @php
            $currentUser = auth()->user();
            if ($currentUser) {
                $currentUser->loadMissing('roles.permissions');
            }
            $roleLabel = $currentUser ? $currentUser->roles->pluck('display_name')->join(', ') : null;
        @endphp
        <aside id="sidebar" class="sidebar hidden md:flex md:flex-col bg-[#fafafa] border-r border-slate-200/80 shadow-[2px_0_8px_rgba(0,0,0,0.01)] relative z-20 h-full">
            <div class="px-5 py-5 flex items-center gap-3 sidebar-brand">
                @if(isset($settings['store_logo']))
                    <img src="{{ Storage::url($settings['store_logo']) }}" alt="Logo" class="h-7 w-auto object-contain sidebar-brand-icon-img">
                @else
                    <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm sidebar-brand-icon shadow-sm">
                        K
                    </div>
                @endif
                <div class="flex flex-col sidebar-brand-text-container overflow-hidden">
                    <span class="text-[15px] font-bold text-slate-900 truncate sidebar-brand-text">{{ $settings['store_name'] ?? 'Kasir Modern' }}</span>
                </div>
            </div>
            
            <nav class="flex-1 px-4 py-2 space-y-0.5">
                @can('manage_dashboard')
                    <a href="{{ route('dashboard') }}" class="nav-link" title="Dashboard">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg></span>
                        <span class="label">Dashboard</span>
                    </a>
                @endcan

                @canany(['manage_products', 'manage_categories'])
                    <p class="section-title">Inventaris</p>

                    {{-- Dropdown Toggle --}}
                    <button type="button" class="nav-link w-full justify-between group select-none"
                        onclick="$(this).next().slideToggle(150); $(this).find('.chevron').toggleClass('rotate-180')">
                        <div class="flex items-center gap-3">
                            <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg></span>
                            <span class="label">Produk &amp; Stok</span>
                        </div>
                        <span class="chevron text-[10px] text-slate-400 transition-transform duration-200"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg></span>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div class="pl-[1.75rem] space-y-0.5 mt-0.5 hidden overflow-hidden" id="product-menu">
                        <div class="border-l border-slate-200/80 ml-1.5 pl-2 my-1">
                            @can('manage_products')
                                <a href="{{ route('products.index') }}" class="nav-link !py-1.5 hover:!bg-transparent hover:!text-indigo-600" title="Daftar Produk">
                                    <span class="text-[13px] label">Daftar Produk</span>
                                </a>
                                <a href="{{ route('raw-materials.index') }}" class="nav-link !py-1.5 hover:!bg-transparent hover:!text-indigo-600" title="Bahan Baku">
                                    <span class="text-[13px] label">Bahan Baku</span>
                                </a>
                                <a href="{{ route('finishings.index') }}" class="nav-link !py-1.5 hover:!bg-transparent hover:!text-indigo-600" title="Finishing">
                                    <span class="text-[13px] label">Finishing</span>
                                </a>
                            @endcan
                            @can('manage_categories')
                                <a href="{{ route('categories.index') }}" class="nav-link !py-1.5 hover:!bg-transparent hover:!text-indigo-600" title="Kategori">
                                    <span class="text-[13px] label">Kategori</span>
                                </a>
                            @endcan
                        </div>
                    </div>
                @endcanany

                @can('view_customers')
                    <p class="section-title">Pelanggan</p>
                    <a href="{{ route('customers.index') }}" class="nav-link" title="Pelanggan">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg></span>
                        <span class="label">Pelanggan</span>
                    </a>
                @endcan

                @can('manage_transactions')
                    <p class="section-title">Transaksi</p>
                    <a href="{{ route('transactions.create') }}" class="nav-link" title="Transaksi Baru">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg></span>
                        <span class="label">Transaksi Baru</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" class="nav-link" title="Transaksi">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 15.75h3.75M18 19.5V4.5a2.25 2.25 0 00-2.25-2.25H8.25A2.25 2.25 0 006 4.5v15a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 19.5z" /></svg></span>
                        <span class="label">Transaksi</span>
                    </a>

                    {{-- Expense Management --}}
                    <p class="section-title">Pengeluaran</p>
                    <a href="{{ route('expenses.index') }}" class="nav-link" title="Pengeluaran">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg></span>
                        <span class="label">Pengeluaran</span>
                    </a>
                    <a href="{{ route('expense-categories.index') }}" class="nav-link" title="Kategori Pengeluaran">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /></svg></span>
                        <span class="label">Kategori</span>
                    </a>
                @endcan

                @can('monitoring_process')
                    <p class="section-title">Monitoring Process</p>
                    <a href="{{ route('monitoring.track-in') }}" class="nav-link" title="Track In Order">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 11.25l-3-3m0 0l-3 3m3-3v7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                        <span class="label">Track In Order</span>
                    </a>
                    <a href="{{ route('monitoring.track-out') }}" class="nav-link" title="Track Out Order">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                        <span class="label">Track Out Order</span>
                    </a>
                    @can('monitoring_status')
                    <a href="{{ route('monitoring.status') }}" class="nav-link" title="Status Order">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg></span>
                        <span class="label">Status Order</span>
                    </a>
                    @endcan
                @endcan

                @can('manage_salary')
                    <p class="section-title">Penggajian</p>

                    {{-- Dropdown Toggle --}}
                    <button type="button" class="nav-link w-full justify-between group select-none"
                        onclick="$(this).next().slideToggle(150); $(this).find('.chevron').toggleClass('rotate-180')">
                        <div class="flex items-center gap-3">
                            <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg></span>
                            <span class="label">Penggajian</span>
                        </div>
                        <span class="chevron text-[10px] text-slate-400 transition-transform duration-200"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg></span>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div class="pl-[1.75rem] space-y-0.5 mt-0.5 hidden overflow-hidden" id="payroll-menu">
                        <div class="border-l border-slate-200/80 ml-1.5 pl-2 my-1">
                            @can('view_employees')
                                <a href="{{ route('employees.index') }}" class="nav-link text-sm !py-1.5 hover:!bg-transparent hover:!text-indigo-600" title="Pegawai">
                                    <span class="text-[13px] label">Pegawai</span>
                                </a>
                            @endcan
                            @can('view_payrolls')
                                <a href="{{ route('payrolls.index') }}" class="nav-link text-sm !py-1.5 hover:!bg-transparent hover:!text-indigo-600" title="Slip Gaji">
                                    <span class="text-[13px] label">Slip Gaji</span>
                                </a>
                            @endcan
                        </div>
                    </div>
                @endcan

                @canany(['manage_users', 'manage_roles'])
                    <p class="section-title">Kendali Akses</p>
                    @can('manage_users')
                        <a href="{{ route('users.index') }}" class="nav-link" title="Pengguna">
                            <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg></span>
                            <span class="label">Pengguna</span>
                        </a>
                    @endcan
                    @can('manage_roles')
                        <a href="{{ route('roles.index') }}" class="nav-link" title="Role &amp; Izin">
                            <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg></span>
                            <span class="label">Role &amp; Izin</span>
                        </a>
                    @endcan
                @endcanany

                @can('view_reports')
                    <p class="section-title">Laporan</p>
                    <a href="{{ route('reports.performance') }}" class="nav-link" title="Dashboard Performa">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" /></svg></span>
                        <span class="label">Dashboard Performa</span>
                    </a>
                    <a href="{{ route('reports.sales') }}" class="nav-link" title="Laporan Penjualan">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                        <span class="label">Penjualan</span>
                    </a>
                    <a href="{{ route('reports.profit') }}" class="nav-link" title="Laporan Profit">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22M12 3h9v9" /></svg></span>
                        <span class="label">Profit</span>
                    </a>
                @endcan

                {{-- Settings --}}
                @can('manage_settings')
                    <div class="h-4"></div>
                    <a href="{{ route('settings.index') }}" class="nav-link" title="Pengaturan Toko">
                        <span class="icon"><svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                        <span class="label">Pengaturan Toko</span>
                    </a>
                @endcan
                <div class="h-6"></div>
            </nav>
            <div class="px-5 py-4 border-t border-slate-100/60 text-xs font-medium text-slate-400 sidebar-footer shrink-0 relative bg-[#fafafa]">
                &copy; {{ date('Y') }} {{ $settings['store_name'] ?? 'Kasir Modern' }}
            </div>
        </aside>

        <div class="flex-1 flex flex-col layout-content transition-all duration-200 h-full overflow-hidden">
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
            <main class="px-3 md:px-5 py-6 flex-1 overflow-y-auto">
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
                @can('manage_dashboard')
                    <a href="{{ route('dashboard') }}" class="mobile-nav-link">Dashboard</a>
                @endcan
                @can('manage_products')
                    <a href="{{ route('products.index') }}" class="mobile-nav-link">Produk</a>
                    <a href="{{ route('raw-materials.index') }}" class="mobile-nav-link">Bahan Baku</a>
                    <a href="{{ route('finishings.index') }}" class="mobile-nav-link">Finishing</a>
                @endcan
                @can('manage_categories')
                    <a href="{{ route('categories.index') }}" class="mobile-nav-link">Kategori</a>
                @endcan
                @can('view_customers')
                    <a href="{{ route('customers.index') }}" class="mobile-nav-link">Pelanggan</a>
                @endcan
                @can('manage_transactions')
                    <a href="{{ route('transactions.index') }}" class="mobile-nav-link">Transaksi</a>
                    <a href="{{ route('transactions.create') }}" class="mobile-nav-link">Transaksi Baru</a>
                       <a href="{{ route('expenses.index') }}" class="mobile-nav-link">Pengeluaran</a>
                        <a href="{{ route('expense-categories.index') }}" class="mobile-nav-link">Kategori Pengeluaran</a>
                @endcan
                @can('monitoring_process')
                    <div class="border-t border-slate-100 my-2 pt-2">
                        <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Monitoring Process</p>
                        <a href="{{ route('monitoring.track-in') }}" class="mobile-nav-link">Track In Order</a>
                        <a href="{{ route('monitoring.track-out') }}" class="mobile-nav-link">Track Out Order</a>
                        @can('monitoring_status')
                            <a href="{{ route('monitoring.status') }}" class="mobile-nav-link">Status Order</a>
                        @endcan
                    </div>
                @endcan
                @can('manage_salary')
                    <div class="border-t border-slate-100 my-2 pt-2">
                        <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Penggajian</p>
                        @can('view_employees')
                            <a href="{{ route('employees.index') }}" class="mobile-nav-link">Pegawai</a>
                        @endcan
                        @can('view_payrolls')
                            <a href="{{ route('payrolls.index') }}" class="mobile-nav-link">Slip Gaji</a>
                        @endcan
                    </div>
                @endcan
                @can('manage_users')
                    <a href="{{ route('users.index') }}" class="mobile-nav-link">Pengguna</a>
                @endcan
                @can('manage_roles')
                    <a href="{{ route('roles.index') }}" class="mobile-nav-link">Role &amp; Izin</a>
                @endcan
                @can('view_reports')
                    <a href="{{ route('reports.performance') }}" class="mobile-nav-link">Dashboard Performa</a>
                    <a href="{{ route('reports.sales') }}" class="mobile-nav-link">Laporan Penjualan</a>
                    @can('view_profit')
                        <a href="{{ route('reports.profit') }}" class="mobile-nav-link">Laporan Profit</a>
                    @endcan
                @endcan
                @can('manage_settings')
                    <a href="{{ route('settings.index') }}" class="mobile-nav-link">Pengaturan Toko</a>
                @endcan
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
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
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