<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeacherAttendance;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Models\Course;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing attendance records with foreign key handling
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        TeacherAttendance::truncate();
        StudentAttendance::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->seedTeacherAttendance();
        $this->seedStudentAttendance();

        $this->command->info('تم إنشاء سجلات الحضور بنجاح');
    }

    private function seedTeacherAttendance()
    {
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();

        $statuses = ['present', 'absent', 'late', 'sick_leave'];
        $statusWeights = [70, 10, 15, 5]; // Probability weights

        // Create attendance records for the last 30 days
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($teachers as $teacher) {
                $status = $this->getWeightedRandomStatus($statuses, $statusWeights);
                
                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'attendance_date' => $date->format('Y-m-d'),
                    'check_in' => $status === 'present' ? 
                        $date->copy()->setTime(rand(7, 9), rand(0, 59)) : 
                        ($status === 'late' ? $date->copy()->setTime(rand(9, 11), rand(0, 59)) : null),
                    'check_out' => $status === 'present' || $status === 'late' ? 
                        $date->copy()->setTime(rand(15, 17), rand(0, 59)) : null,
                    'status' => $status,
                    'notes' => $this->getAttendanceNotes($status),
                    'recorded_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedStudentAttendance()
    {
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();

        $courses = Course::all();
        $statuses = ['present', 'absent'];
        $statusWeights = [80, 20]; // Probability weights

        // Create attendance records for the last 60 days
        for ($i = 60; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($students as $student) {
                // Get courses for this student's group
                $studentCourses = $student->group ? $student->group->courses : $courses->random(rand(2, 4));

                foreach ($studentCourses as $course) {
                    // Check if there's a schedule for this course on this day
                    $dayOfWeek = $date->format('l'); // Monday, Tuesday, etc.
                    $schedule = Schedule::where('course_id', $course->id)
                        ->where('group_id', $student->group_id)
                        ->where('day', $dayOfWeek)
                        ->first();

                    if ($schedule) {
                        $status = $this->getWeightedRandomStatus($statuses, $statusWeights);
                        
                        StudentAttendance::create([
                            'schedule_id' => $schedule->id,
                            'student_id' => $student->id,
                            'teacher_id' => $course->teachers->first()->id ?? null,
                            'attendance_date' => $date->format('Y-m-d'),
                            'status' => $status,
                            'notes' => $this->getStudentAttendanceNotes($status),
                            'recorded_by' => $course->teachers->first()->id ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    private function getWeightedRandomStatus($statuses, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        for ($i = 0; $i < count($statuses); $i++) {
            $currentWeight += $weights[$i];
            if ($random <= $currentWeight) {
                return $statuses[$i];
            }
        }
        
        return $statuses[0]; // fallback
    }

    private function getAttendanceNotes($status)
    {
        $notes = [
            'present' => ['حضور منتظم', 'وصل في الوقت المحدد', null],
            'absent' => ['غياب بدون عذر', 'لم يحضر اليوم', 'غياب غير مبرر'],
            'late' => ['تأخر عن الموعد', 'وصل متأخراً', 'تأخير بسبب الزحام'],
            'sick_leave' => ['إجازة مرضية', 'عذر مرضي', 'إجازة صحية']
        ];

        $statusNotes = $notes[$status] ?? [null];
        return $statusNotes[array_rand($statusNotes)];
    }

    private function getStudentAttendanceNotes($status)
    {
        $notes = [
            'present' => ['حضور منتظم', 'مشاركة فعالة', null],
            'absent' => ['غياب بدون عذر', 'لم يحضر المحاضرة', null],
        ];

        $statusNotes = $notes[$status] ?? [null];
        return $statusNotes[array_rand($statusNotes)];
    }
} 