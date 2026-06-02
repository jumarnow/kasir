<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersData = [
            [
                'name' => 'Admin Test',
                'username' => 'admin_test',
                'email' => 'admin@test.com',
                'role' => 'admin'
            ],
            [
                'name' => 'Designer Test',
                'username' => 'designer_test',
                'email' => 'designer@test.com',
                'role' => 'designer'
            ],
            [
                'name' => 'Operator Test',
                'username' => 'operator_test',
                'email' => 'operator@test.com',
                'role' => 'operator'
            ]
        ];

        foreach ($usersData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => Hash::make('password123'),
                ]
            );

            // Assign role
            $role = Role::where('name', $data['role'])->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }
    }
}
