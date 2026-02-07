<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PayrollPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Menu
            ['name' => 'menu_penggajian', 'display_name' => 'Akses Menu Penggajian', 'group' => 'penggajian'],

            // Pegawai
            ['name' => 'view_employees', 'display_name' => 'Lihat Daftar Pegawai', 'group' => 'penggajian'],
            ['name' => 'create_employees', 'display_name' => 'Tambah Pegawai', 'group' => 'penggajian'],
            ['name' => 'edit_employees', 'display_name' => 'Edit Pegawai', 'group' => 'penggajian'],
            ['name' => 'delete_employees', 'display_name' => 'Hapus Pegawai', 'group' => 'penggajian'],

            // Slip Gaji
            ['name' => 'view_payrolls', 'display_name' => 'Lihat Daftar Slip Gaji', 'group' => 'penggajian'],
            ['name' => 'create_payrolls', 'display_name' => 'Buat Slip Gaji', 'group' => 'penggajian'],
            ['name' => 'edit_payrolls', 'display_name' => 'Edit Slip Gaji', 'group' => 'penggajian'],
            ['name' => 'delete_payrolls', 'display_name' => 'Hapus Slip Gaji', 'group' => 'penggajian'],
        ];

        foreach ($permissions as $permission) {
            $exists = Permission::where('name', $permission['name'])->exists();
            if (!$exists) {
                Permission::create([
                    'name' => $permission['name'],
                    'display_name' => $permission['display_name'],
                    'description' => $permission['group'] ?? null,
                ]);
            }
        }

        // Auto assign to admin role if exists
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $permissionNames = collect($permissions)->pluck('name')->toArray();
            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id')->toArray();
            $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
