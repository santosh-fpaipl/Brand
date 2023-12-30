<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use App\Models\Party;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'staff',
            ],
            [
                'name' => 'fabricator',
            ],
            [
                'name' => 'manager',
            ]
        ];

        foreach ($roles as $role) {
            Role::create([
                'name' => $role['name'],
                'guard_name' => 'web'
            ]);
        }

        $users = [
            [
                'name' => 'Santosh Singh',
                'email' => 'santosh@gmail.com',
                'role' => 'manager',
                'sid' => 'A-001',
            ],
            [
                'name' => 'Fabricator',
                'email' => 'fabricator@gmail.com',
                'role' => 'fabricator',
                'sid' => 'A-002',
            ],
            [
                'name' => 'Fabricator 11',
                'email' => 'fabricator11@gmail.com',
                'role' => 'fabricator',
                'sid' => 'A-003',
            ],
            [
                'name' => 'Fabricator 22',
                'email' => 'fabricator22@gmail.com',
                'role' => 'fabricator',
                'sid' => 'A-004',
            ],
            [
                'name' => 'Fabricator 33',
                'email' => 'fabricator33@gmail.com',
                'role' => 'fabricator',
                'sid' => 'A-005',
            ],
            [
                'name' => 'Fabricator 44',
                'email' => 'fabricator44@gmail.com',
                'role' => 'fabricator',
                'sid' => 'A-006',
            ],
            [
                'name' => 'Staff',
                'email' => 'staff@gmail.com',
                'role' => 'staff',
                'sid' => 'A-007',
            ],
            [
                'name' => 'Staff 11',
                'email' => 'staff11@gmail.com',
                'role' => 'staff',
                'sid' => 'A-008',
            ],
            [
                'name' => 'Staff 22',
                'email' => 'staff22@gmail.com',
                'role' => 'staff',
                'sid' => 'A-009',
            ],

        ];

        foreach($users as $user){
            $newUser = \App\Models\User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
            ]);

            if($user['role'] == 'manager' || $user['sid'] == 'A-007'){
                $newUser->assignRole($user['role']);
                Party::create([
                    'user_id' => $newUser->id,
                    'business' => '',
                    'gst' => '',
                    'pan' => '',
                    'sid' => $user['sid'],
                    'type' => $user['role'],
                    'info' => '',
                ]);
            }
        }
    }
}
