<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Manager',
                'username' => 'manager',
                'email' => 'manager@kasir.test',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ],
            [
                'name' => 'Finance',
                'username' => 'finance',
                'email' => 'finance@kasir.test',
                'password' => Hash::make('password'),
                'role' => 'finance',
            ],
            [
                'name' => 'Kepala Toko',
                'username' => 'kepalatoko',
                'email' => 'kepalatoko@kasir.test',
                'password' => Hash::make('password'),
                'role' => 'kepala_toko',
            ],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@kasir.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Kasir',
                'username' => 'kasir',
                'email' => 'kasir@kasir.test',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['username' => $userData['username']],
                $userData
            );

            // Attach role to user
            $role = Role::where('name', $roleName)->first();
            if ($role && !$user->roles->contains($role->id)) {
                $user->roles()->attach($role->id);
            }
        }
    }
}

