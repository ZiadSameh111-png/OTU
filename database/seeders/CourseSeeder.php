<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;
use App\Models\Group;
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
        // Clear existing courses and relationships
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('course_teacher')->truncate();
        DB::table('course_group')->truncate();
        Course::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $courses = [
            [
                'code' => 'CS101',
                'name' => 'مقدمة في البرمجة',
                'description' => 'مقرر تمهيدي في أساسيات البرمجة باستخدام لغة Python',
                'credit_hours' => 3,
                'semester' => 'الفصل الأول',
                'active' => true
            ],
            [
                'code' => 'CS102',
                'name' => 'هياكل البيانات والخوارزميات',
                'description' => 'دراسة هياكل البيانات الأساسية والخوارزميات المختلفة',
                'credit_hours' => 4,
                'semester' => 'الفصل الثاني',
                'active' => true
            ],
            [
                'code' => 'CS201',
                'name' => 'البرمجة الشيئية',
                'description' => 'مفاهيم البرمجة الشيئية باستخدام Java',
                'credit_hours' => 3,
                'semester' => 'الفصل الأول',
                'active' => true
            ],
            [
                'code' => 'CS202',
                'name' => 'قواعد البيانات',
                'description' => 'تصميم وإدارة قواعد البيانات العلائقية',
                'credit_hours' => 3,
                'semester' => 'الفصل الثاني',
                'active' => true
            ],
            [
                'code' => 'CS301',
                'name' => 'تطوير تطبيقات الويب',
                'description' => 'تطوير تطبيقات الويب باستخدام HTML, CSS, JavaScript, PHP',
                'credit_hours' => 4,
                'semester' => 'الفصل الأول',
                'active' => true
            ],
            [
                'code' => 'CS302',
                'name' => 'هندسة البرمجيات',
                'description' => 'مبادئ وممارسات هندسة البرمجيات ودورة حياة التطوير',
                'credit_hours' => 3,
                'semester' => 'الفصل الثاني',
                'active' => true
            ],
            [
                'code' => 'CS401',
                'name' => 'الذكاء الاصطناعي',
                'description' => 'مقدمة في الذكاء الاصطناعي وتعلم الآلة',
                'credit_hours' => 3,
                'semester' => 'الفصل الأول',
                'active' => true
            ],
            [
                'code' => 'CS402',
                'name' => 'أمن المعلومات',
                'description' => 'أساسيات أمن المعلومات والحماية السيبرانية',
                'credit_hours' => 3,
                'semester' => 'الفصل الثاني',
                'active' => true
            ],
            [
                'code' => 'MATH101',
                'name' => 'الرياضيات للحاسوب',
                'description' => 'الرياضيات الأساسية المطلوبة لعلوم الحاسوب',
                'credit_hours' => 3,
                'semester' => 'الفصل الأول',
                'active' => true
            ],
            [
                'code' => 'ENG101',
                'name' => 'اللغة الإنجليزية التقنية',
                'description' => 'تطوير مهارات اللغة الإنجليزية في المجال التقني',
                'credit_hours' => 2,
                'semester' => 'الفصل الأول',
                'active' => true
            ],
            [
                'code' => 'CS501',
                'name' => 'مشروع التخرج',
                'description' => 'مشروع تطبيقي شامل في مجال تخصص الطالب',
                'credit_hours' => 6,
                'semester' => 'الفصل الثاني',
                'active' => true
            ],
            [
                'code' => 'CS303',
                'name' => 'الشبكات والاتصالات',
                'description' => 'أساسيات الشبكات وبروتوكولات الاتصال',
                'credit_hours' => 3,
                'semester' => 'الفصل الأول',
                'active' => true
            ]
        ];

        // Create courses
        foreach ($courses as $courseData) {
            Course::create($courseData);
        }

        // Get all created courses, teachers, and groups
        $allCourses = Course::all();
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();
        $groups = Group::where('active', true)->get();

        // Assign teachers to courses
        foreach ($allCourses as $course) {
            // Assign 1-2 teachers per course
            $courseTeachers = $teachers->random(rand(1, min(2, $teachers->count())));
            foreach ($courseTeachers as $teacher) {
                $course->teachers()->attach($teacher->id);
            }

            // Assign 1-3 groups per course
            $courseGroups = $groups->random(rand(1, min(3, $groups->count())));
            foreach ($courseGroups as $group) {
                $course->groups()->attach($group->id);
            }
        }

        $this->command->info('تم إنشاء المقررات الدراسية بنجاح!');
        $this->command->info('عدد المقررات: ' . $allCourses->count());
    }
}
