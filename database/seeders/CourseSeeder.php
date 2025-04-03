<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create test courses
        $courses = [
            [
                'name' => 'Introduction to Programming',
                'code' => 'CS101',
                'description' => 'A foundational course covering basic programming concepts and problem-solving techniques using a modern programming language.'
            ],
            [
                'name' => 'Database Systems',
                'code' => 'CS202',
                'description' => 'Comprehensive introduction to database design, implementation, and management including SQL and relational database concepts.'
            ],
            [
                'name' => 'Web Development',
                'code' => 'CS303',
                'description' => 'Learn full-stack web development using HTML, CSS, JavaScript, and modern frameworks for building responsive web applications.'
            ],
            [
                'name' => 'Data Structures and Algorithms',
                'code' => 'CS204',
                'description' => 'Study of fundamental data structures and algorithms for efficient data processing and problem solving.'
            ],
            [
                'name' => 'Artificial Intelligence',
                'code' => 'CS405',
                'description' => 'Introduction to the principles and techniques of artificial intelligence, including machine learning and neural networks.'
            ]
        ];
        
        // Insert courses into database if they don't already exist
        foreach ($courses as $courseData) {
            Course::firstOrCreate(
                ['code' => $courseData['code']], // Check if course with this code exists
                $courseData // Create with all data if it doesn't exist
            );
        }
        
        // Output message
        $this->command->info('Courses table seeded successfully!');
    }
}
