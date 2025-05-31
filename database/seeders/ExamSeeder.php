<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing exams with foreign key handling
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Exam::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $courses = Course::all();
        $groups = Group::where('active', true)->get();
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();

        $questionTypes = ['multiple_choice', 'open_ended', 'true_false', 'mixed'];

        foreach ($courses as $course) {
            // Get groups for this course
            $courseGroups = $course->groups;
            if ($courseGroups->isEmpty()) {
                $courseGroups = $groups->random(rand(1, 2));
                foreach ($courseGroups as $group) {
                    $course->groups()->attach($group->id);
                }
            }

            // Get teachers for this course
            $courseTeachers = $course->teachers;
            if ($courseTeachers->isEmpty()) {
                $randomTeacher = $teachers->random();
                $course->teachers()->attach($randomTeacher->id);
                $courseTeachers = collect([$randomTeacher]);
            }

            foreach ($courseGroups as $group) {
                // Create midterm exam
                $midtermStartTime = Carbon::now()->addWeeks(rand(2, 6))->setTime(rand(8, 14), 0);
                $midtermDuration = rand(60, 120);
                
                Exam::create([
                    'title' => 'امتحان منتصف الفصل - ' . $course->name,
                    'description' => 'امتحان منتصف الفصل الدراسي لمقرر ' . $course->name,
                    'course_id' => $course->id,
                    'group_id' => $group->id,
                    'teacher_id' => $courseTeachers->first()->id,
                    'start_time' => $midtermStartTime,
                    'end_time' => $midtermStartTime->copy()->addMinutes($midtermDuration),
                    'duration' => $midtermDuration,
                    'question_type' => $questionTypes[array_rand($questionTypes)],
                    'is_published' => rand(0, 1),
                    'status' => rand(0, 1) ? 'active' : 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create final exam
                $finalStartTime = Carbon::now()->addWeeks(rand(8, 12))->setTime(rand(8, 14), 0);
                $finalDuration = rand(120, 180);
                
                Exam::create([
                    'title' => 'الامتحان النهائي - ' . $course->name,
                    'description' => 'الامتحان النهائي لمقرر ' . $course->name,
                    'course_id' => $course->id,
                    'group_id' => $group->id,
                    'teacher_id' => $courseTeachers->first()->id,
                    'start_time' => $finalStartTime,
                    'end_time' => $finalStartTime->copy()->addMinutes($finalDuration),
                    'duration' => $finalDuration,
                    'question_type' => $questionTypes[array_rand($questionTypes)],
                    'is_published' => rand(0, 1),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create some quizzes
                for ($i = 1; $i <= rand(2, 4); $i++) {
                    $quizStartTime = Carbon::now()->addWeeks($i * 2)->setTime(rand(8, 16), 0);
                    $quizDuration = rand(15, 45);
                    
                    Exam::create([
                        'title' => 'اختبار قصير ' . $i . ' - ' . $course->name,
                        'description' => 'اختبار قصير رقم ' . $i . ' لمقرر ' . $course->name,
                        'course_id' => $course->id,
                        'group_id' => $group->id,
                        'teacher_id' => $courseTeachers->first()->id,
                        'start_time' => $quizStartTime,
                        'end_time' => $quizStartTime->copy()->addMinutes($quizDuration),
                        'duration' => $quizDuration,
                        'question_type' => $questionTypes[array_rand($questionTypes)],
                        'is_published' => 1,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('تم إنشاء الامتحانات بنجاح');
    }
} 