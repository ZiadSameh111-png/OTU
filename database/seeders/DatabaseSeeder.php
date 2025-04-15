<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RoleSeeder::class,
            // AdminUserSeeder::class, // Commented out as we'll use UsersTableSeeder instead
            UsersTableSeeder::class, // Our new seeder that creates 3 users with different roles
            GroupSeeder::class,
        ]);

        // Seed the courses
        $this->call(CourseSeeder::class);
    }
}
