<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create default roles
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'System Administrator with full access'
            ],
            [
                'name' => 'Teacher',
                'description' => 'Teacher with access to manage courses and students'
            ],
            [
                'name' => 'Student',
                'description' => 'Student with access to view courses and submit assignments'
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
