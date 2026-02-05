<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard & Umum
            ['name' => 'manage_dashboard', 'display_name' => 'Kelola Dashboard'],

            // Master Data
            ['name' => 'manage_products', 'display_name' => 'Kelola Produk'],
            ['name' => 'manage_categories', 'display_name' => 'Kelola Kategori'],
            ['name' => 'view_customers', 'display_name' => 'Lihat Pelanggan'],
            ['name' => 'create_customers', 'display_name' => 'Tambah Pelanggan'],
            ['name' => 'edit_customers', 'display_name' => 'Edit Pelanggan'],
            ['name' => 'delete_customers', 'display_name' => 'Hapus Pelanggan'],
            ['name' => 'manage_materials', 'display_name' => 'Kelola Bahan'],
            ['name' => 'manage_finishings', 'display_name' => 'Kelola Finishing'],
            ['name' => 'manage_displays', 'display_name' => 'Kelola Display'],

            // Transaksi & Pesanan
            ['name' => 'manage_transactions', 'display_name' => 'Kelola Transaksi'],
            ['name' => 'edit_transactions', 'display_name' => 'Edit Transaksi'],
            ['name' => 'delete_transactions', 'display_name' => 'Hapus Transaksi'],
            ['name' => 'edit_orders', 'display_name' => 'Edit Pesanan'],
            ['name' => 'apply_discounts', 'display_name' => 'Mengatur Diskon'],

            // Cetak Dokumen
            ['name' => 'print_invoices', 'display_name' => 'Cetak Invoice'],
            ['name' => 'print_spk', 'display_name' => 'Cetak SPK'],
            ['name' => 'print_receipts', 'display_name' => 'Cetak Faktur'],

            // View/Lihat
            ['name' => 'view_invoices_unpaid', 'display_name' => 'Lihat Invoice Belum Lunas'],
            ['name' => 'view_stock', 'display_name' => 'Lihat Stok Bahan'],
            ['name' => 'view_reports', 'display_name' => 'Lihat Laporan'],
            ['name' => 'view_omset', 'display_name' => 'Lihat Omset'],
            ['name' => 'view_profit', 'display_name' => 'Lihat Profit/Laba Rugi'],
            ['name' => 'view_receivables', 'display_name' => 'Lihat Piutang'],
            ['name' => 'view_cashflow', 'display_name' => 'Lihat Pembukuan Kas'],

            // User Management
            ['name' => 'manage_users', 'display_name' => 'Kelola Pengguna'],
            ['name' => 'manage_roles', 'display_name' => 'Kelola Role dan Izin'],
            ['name' => 'manage_settings', 'display_name' => 'Kelola Pengaturan'],
        ];

        $permissionMap = collect($permissions)
            ->mapWithKeys(function ($permission) {
                $model = Permission::firstOrCreate(
                    ['name' => $permission['name']],
                    ['display_name' => $permission['display_name']]
                );

                return [$permission['name'] => $model->id];
            });

        $roles = [
            // Kasir: hanya bisa Input dan tidak bisa melihat Omset
            'kasir' => [
                'display_name' => 'Kasir',
                'description' => 'Hanya bisa input transaksi, tidak bisa melihat omset.',
                'permissions' => [
                    'manage_dashboard',
                    'manage_transactions',
                    'apply_discounts',
                    'print_invoices',
                    'print_receipts',
                    'view_customers',
                    'create_customers',
                ],
            ],

            // Admin: Bisa Input, Lihat Invoice belum lunas, Lihat Faktur, Lihat stock bahan (tidak bisa melihat Omset)
            'admin' => [
                'display_name' => 'Admin',
                'description' => 'Bisa input, lihat invoice belum lunas, faktur, stok bahan. Tidak bisa lihat omset.',
                'permissions' => [
                    'manage_dashboard',
                    'manage_products',
                    'manage_categories',
                    'view_customers',
                    'create_customers',
                    'edit_customers',
                    'delete_customers',
                    'manage_materials',
                    'manage_finishings',
                    'manage_displays',
                    'manage_transactions',
                    'edit_transactions',
                    'delete_transactions',
                    'apply_discounts',
                    'print_invoices',
                    'print_spk',
                    'print_receipts',
                    'view_invoices_unpaid',
                    'view_stock',
                    'view_reports',
                ],
            ],

            // Kepala Toko: Sama seperti Admin + Bisa Edit pesanan (tidak bisa melihat Omset)
            'kepala_toko' => [
                'display_name' => 'Kepala Toko',
                'description' => 'Sama seperti admin + bisa edit pesanan. Tidak bisa lihat omset.',
                'permissions' => [
                    'manage_dashboard',
                    'manage_products',
                    'manage_categories',
                    'view_customers',
                    'create_customers',
                    'edit_customers',
                    'delete_customers',
                    'manage_materials',
                    'manage_finishings',
                    'manage_displays',
                    'manage_transactions',
                    'edit_transactions',
                    'delete_transactions',
                    'edit_orders',
                    'apply_discounts',
                    'print_invoices',
                    'print_spk',
                    'print_receipts',
                    'view_invoices_unpaid',
                    'view_stock',
                    'view_reports',
                ],
            ],

            // Finance: Bisa semua
            'finance' => [
                'display_name' => 'Finance',
                'description' => 'Akses penuh ke semua fitur termasuk keuangan.',
                'permissions' => $permissionMap->keys()->all(),
            ],

            // Manager: Bisa semua
            'manager' => [
                'display_name' => 'Manager',
                'description' => 'Akses penuh ke semua fitur.',
                'permissions' => $permissionMap->keys()->all(),
            ],
        ];

        foreach ($roles as $name => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                ]
            );

            $permissionIds = collect($roleData['permissions'])
                ->map(fn($permissionName) => $permissionMap[$permissionName])
                ->filter()
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}
