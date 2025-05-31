<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\User;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing grades with foreign key handling
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Grade::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();

        $courses = Course::all();

        foreach ($students as $student) {
            // Get courses for this student's group
            $studentCourses = $student->group ? $student->group->courses : $courses->random(rand(3, 5));

            foreach ($studentCourses as $course) {
                // Generate realistic grades
                $midtermGrade = rand(40, 95);
                $assignmentGrade = rand(50, 100);
                $finalGrade = rand(35, 95);
                
                // Calculate total score (weighted average)
                $totalScore = ($midtermGrade * 0.3) + ($assignmentGrade * 0.3) + ($finalGrade * 0.4);
                
                // Calculate letter grade
                $letterGrade = $this->calculateLetterGrade($totalScore);
                
                // Calculate GPA
                $gpa = $this->calculateGPA($letterGrade);
                
                // Determine if submitted (80% chance)
                $isSubmitted = rand(1, 10) <= 8;
                
                Grade::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'midterm_grade' => $midtermGrade,
                    'assignment_grade' => $assignmentGrade,
                    'final_grade' => $finalGrade,
                    'score' => round($totalScore, 2),
                    'gpa' => $gpa,
                    'grade' => $letterGrade,
                    'submitted' => $isSubmitted,
                    'submission_date' => $isSubmitted ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'updated_by' => $course->teachers->first()->id ?? null,
                    'comments' => $this->getRandomComment($letterGrade),
                    'is_final' => $isSubmitted,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('تم إنشاء الدرجات بنجاح');
    }

    /**
     * Calculate letter grade based on score
     */
    private function calculateLetterGrade($score)
    {
        if ($score >= 95) return 'A+';
        if ($score >= 90) return 'A';
        if ($score >= 85) return 'A-';
        if ($score >= 80) return 'B+';
        if ($score >= 75) return 'B';
        if ($score >= 70) return 'B-';
        if ($score >= 65) return 'C+';
        if ($score >= 60) return 'C';
        if ($score >= 55) return 'C-';
        if ($score >= 50) return 'D+';
        if ($score >= 45) return 'D';
        return 'F';
    }

    /**
     * Calculate GPA based on letter grade
     */
    private function calculateGPA($letterGrade)
    {
        $gpaMap = [
            'A+' => 4.00,
            'A' => 4.00,
            'A-' => 3.70,
            'B+' => 3.30,
            'B' => 3.00,
            'B-' => 2.70,
            'C+' => 2.30,
            'C' => 2.00,
            'C-' => 1.70,
            'D+' => 1.30,
            'D' => 1.00,
            'F' => 0.00
        ];

        return $gpaMap[$letterGrade] ?? 0.00;
    }

    /**
     * Get random comment based on grade
     */
    private function getRandomComment($letterGrade)
    {
        $comments = [
            'A+' => ['أداء ممتاز ومتميز', 'عمل رائع واستمر', 'مستوى عالي جداً'],
            'A' => ['أداء ممتاز', 'عمل جيد جداً', 'مستوى متقدم'],
            'A-' => ['أداء جيد جداً', 'عمل مميز', 'أداء فوق المتوسط'],
            'B+' => ['أداء جيد', 'عمل مقبول', 'يمكن التحسن أكثر'],
            'B' => ['أداء مقبول', 'عمل جيد', 'مستوى متوسط جيد'],
            'B-' => ['أداء مقبول', 'يحتاج تحسين طفيف', 'مستوى متوسط'],
            'C+' => ['أداء متوسط', 'يحتاج مزيد من الجهد', 'يمكن التحسن'],
            'C' => ['أداء متوسط', 'يحتاج تحسين', 'مستوى مقبول'],
            'C-' => ['أداء ضعيف نسبياً', 'يحتاج جهد أكبر', 'يحتاج متابعة'],
            'D+' => ['أداء ضعيف', 'يحتاج جهد كبير', 'يحتاج مراجعة شاملة'],
            'D' => ['أداء ضعيف جداً', 'يحتاج دعم إضافي', 'يحتاج متابعة مكثفة'],
            'F' => ['أداء غير مقبول', 'يحتاج إعادة دراسة', 'يحتاج مساعدة فورية']
        ];

        $gradeComments = $comments[$letterGrade] ?? ['يحتاج متابعة'];
        
        // 30% chance of having a comment
        return rand(1, 10) <= 3 ? $gradeComments[array_rand($gradeComments)] : null;
    }
} 