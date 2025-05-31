<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Course;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing schedules with foreign key handling
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schedule::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $courses = Course::all();
        $groups = Group::where('active', true)->get();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timeSlots = [
            ['08:00', '09:30'],
            ['09:45', '11:15'],
            ['11:30', '13:00'],
            ['14:00', '15:30'],
            ['15:45', '17:15']
        ];

        $rooms = [
            'قاعة 101', 'قاعة 102', 'قاعة 103', 'قاعة 201', 'قاعة 202',
            'مختبر الحاسوب 1', 'مختبر الحاسوب 2', 'قاعة المحاضرات الكبرى',
            'قاعة الاجتماعات', 'مختبر الشبكات'
        ];

        foreach ($groups as $group) {
            // Get courses for this group
            $groupCourses = $group->courses()->take(5)->get();
            
            if ($groupCourses->isEmpty()) {
                // If no courses assigned to group, assign some random courses
                $randomCourses = $courses->random(min(5, $courses->count()));
                foreach ($randomCourses as $course) {
                    $group->courses()->attach($course->id);
                }
                $groupCourses = $randomCourses;
            }

            $scheduleIndex = 0;
            foreach ($groupCourses as $course) {
                $dayIndex = $scheduleIndex % count($days);
                $timeIndex = $scheduleIndex % count($timeSlots);
                $roomIndex = $scheduleIndex % count($rooms);

                Schedule::create([
                    'course_id' => $course->id,
                    'group_id' => $group->id,
                    'day' => $days[$dayIndex],
                    'start_time' => $timeSlots[$timeIndex][0],
                    'end_time' => $timeSlots[$timeIndex][1],
                    'room' => $rooms[$roomIndex],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $scheduleIndex++;
            }
        }

        $this->command->info('تم إنشاء الجداول الدراسية بنجاح');
    }
} 