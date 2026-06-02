<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonitoringPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'monitoring_process', 'display_name' => 'Akses Monitoring Process'],
            ['name' => 'monitoring_track_in', 'display_name' => 'Track In Order'],
            ['name' => 'monitoring_track_out', 'display_name' => 'Track Out Order'],
            ['name' => 'monitoring_status', 'display_name' => 'Lihat Status Order'],
            ['name' => 'monitoring_admin_out', 'display_name' => 'Track Out Admin (Final)'],
        ];

        $permissionIds = [];
        foreach ($permissions as $permission) {
            $model = Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
            $permissionIds[] = $model->id;
        }

        // Create Operator Role
        $operatorRole = Role::firstOrCreate(
            ['name' => 'operator'],
            ['display_name' => 'Operator Produksi', 'description' => 'Operator bagian produksi.']
        );
        $operatorPermissions = Permission::whereIn('name', ['monitoring_process', 'monitoring_track_in', 'monitoring_track_out'])->pluck('id')->toArray();
        $operatorRole->permissions()->syncWithoutDetaching($operatorPermissions);

        // Create Designer Role
        $designerRole = Role::firstOrCreate(
            ['name' => 'designer'],
            ['display_name' => 'Designer', 'description' => 'Bagian desain grafis.']
        );
        $designerPermissions = Permission::whereIn('name', ['monitoring_process', 'monitoring_track_in', 'monitoring_track_out'])->pluck('id')->toArray();
        $designerRole->permissions()->syncWithoutDetaching($designerPermissions);

        // Add to Admin, Kepala Toko, Finance, Manager
        $existingRoles = ['admin', 'kepala_toko', 'finance', 'manager'];
        foreach ($existingRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->permissions()->syncWithoutDetaching($permissionIds);
            }
        }
    }
}
