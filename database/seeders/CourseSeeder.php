<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get teacher ID (using the first teacher we find)
        $teacherId = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'Teacher')
            ->value('users.id');

        if (!$teacherId) {
            $this->command->error('No teacher found in the database!');
            return;
        }

        // Sample courses
        $courses = [
            [
                'code' => 'CS101',
                'name' => 'Introduction to Programming',
                'description' => 'A foundational course covering basic programming concepts and problem-solving techniques using a modern programming language.',
                'credit_hours' => 3,
                'semester' => '2024-1',
            ],
            [
                'code' => 'CS201',
                'name' => 'Data Structures',
                'description' => 'Study of fundamental data structures, algorithms, and their applications.',
                'credit_hours' => 3,
                'semester' => '2024-1',
            ],
            [
                'code' => 'CS301',
                'name' => 'Database Systems',
                'description' => 'Introduction to database design, implementation, and management.',
                'credit_hours' => 3,
                'semester' => '2024-2',
            ],
            [
                'code' => 'CS401',
                'name' => 'Software Engineering',
                'description' => 'Principles and practices of software development, including project management and team collaboration.',
                'credit_hours' => 3,
                'semester' => '2024-2',
            ],
        ];

        // Insert courses and assign teachers
        foreach ($courses as $course) {
            $courseId = DB::table('courses')->insertGetId($course);
            
            // Assign teacher to course
            DB::table('course_teacher')->insert([
                'course_id' => $courseId,
                'teacher_id' => $teacherId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Sample courses created successfully!');
    }
}
