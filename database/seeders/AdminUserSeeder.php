<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        $userId = DB::table('users')->insertGetId([
            'name' => 'مدير النظام',
            'email' => 'admin@otu.edu',
            'password' => Hash::make('password123'),
        ]);

        // Get admin role ID
        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('id');

        // Assign admin role to user
        if ($adminRoleId) {
            DB::table('role_user')->insert([
                'user_id' => $userId,
                'role_id' => $adminRoleId,
            ]);
        }
    }
} 