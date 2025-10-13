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
            ['name' => 'manage_dashboard', 'display_name' => 'Kelola Dashboard'],
            ['name' => 'manage_products', 'display_name' => 'Kelola Produk'],
            ['name' => 'manage_categories', 'display_name' => 'Kelola Kategori'],
            ['name' => 'manage_customers', 'display_name' => 'Kelola Pelanggan'],
            ['name' => 'manage_transactions', 'display_name' => 'Kelola Transaksi'],
            ['name' => 'apply_discounts', 'display_name' => 'Mengatur Diskon'],
            ['name' => 'print_invoices', 'display_name' => 'Cetak Invoice'],
            ['name' => 'view_reports', 'display_name' => 'Lihat Laporan'],
            ['name' => 'manage_users', 'display_name' => 'Kelola Pengguna'],
            ['name' => 'manage_roles', 'display_name' => 'Kelola Role dan Izin'],
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
            'admin' => [
                'display_name' => 'Administrator',
                'description' => 'Memiliki akses penuh ke seluruh fitur.',
                'permissions' => $permissionMap->keys()->all(),
            ],
            'manager' => [
                'display_name' => 'Manajer',
                'description' => 'Mengelola laporan dan operasi harian.',
                'permissions' => [
                    'manage_dashboard',
                    'manage_products',
                    'manage_categories',
                    'manage_customers',
                    'manage_transactions',
                    'view_reports',
                    'print_invoices',
                ],
            ],
            'cashier' => [
                'display_name' => 'Kasir',
                'description' => 'Melayani penjualan dan pelanggan.',
                'permissions' => [
                    'manage_dashboard',
                    'manage_transactions',
                    'apply_discounts',
                    'print_invoices',
                ],
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
                ->map(fn ($permissionName) => $permissionMap[$permissionName])
                ->filter()
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}
