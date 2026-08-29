<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'view_spk_chart'
        ], [
            'display_name' => 'Lihat Grafik SPK'
        ]);

        $roles = Role::all();
        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::where('name', 'view_spk_chart')->first();
        if ($permission) {
            $roles = Role::all();
            foreach ($roles as $role) {
                $role->permissions()->detach($permission->id);
            }
            $permission->delete();
        }
    }
};
