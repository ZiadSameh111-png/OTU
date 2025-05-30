<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get role IDs
        $teacherRoleId = DB::table('roles')->where('name', 'Teacher')->value('id');
        $studentRoleId = DB::table('roles')->where('name', 'Student')->value('id');

        // Create a teacher
        $teacherId = DB::table('users')->insertGetId([
            'name' => 'Teacher User',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
        ]);

        // Assign teacher role
        if ($teacherRoleId) {
            DB::table('role_user')->insert([
                'user_id' => $teacherId,
                'role_id' => $teacherRoleId,
            ]);
        }

        // Create a student
        $studentId = DB::table('users')->insertGetId([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
        ]);

        // Assign student role
        if ($studentRoleId) {
            DB::table('role_user')->insert([
                'user_id' => $studentId,
                'role_id' => $studentRoleId,
            ]);
        }

        $this->command->info('Users created successfully with different roles!');
    }
}
