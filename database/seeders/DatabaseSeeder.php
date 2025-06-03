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
        $this->command->info('🚀 بدء عملية إنشاء البيانات التجريبية...');

        // Base data (roles, groups, admin user)
        $this->command->info('📋 إنشاء الأدوار والمجموعات الأساسية...');
        $this->call([
            RoleSeeder::class,
            GroupSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Users (teachers and students)
        $this->command->info('👥 إنشاء المستخدمين (مدرسين وطلاب)...');
        $this->call([
            UsersTableSeeder::class,
        ]);

        // Academic structure (courses, schedules)
        $this->command->info('📚 إنشاء المقررات والجداول الدراسية...');
        $this->call([
            CourseSeeder::class,
            ScheduleSeeder::class,
        ]);

        // Academic content (exams, grades)
        $this->command->info('📝 إنشاء الامتحانات والدرجات...');
        $this->call([
            ExamSeeder::class,
            ExamQuestionSeeder::class,
            GradeSeeder::class,
        ]);

        // Financial data (fees and payments)
        $this->command->info('💰 إنشاء الرسوم والمدفوعات...');
        $this->call([
            FeeSeeder::class,
            FeePaymentSeeder::class,
        ]);

        // Attendance records
        $this->command->info('📅 إنشاء سجلات الحضور...');
        $this->call([
            AttendanceSeeder::class,
        ]);

        // Administrative data (requests, messages, notifications)
        $this->command->info('📨 إنشاء الطلبات الإدارية والرسائل والإشعارات...');
        $this->call([
            AdminRequestSeeder::class,
            MessageSeeder::class,
            NotificationSeeder::class,
        ]);

        $this->command->info('✅ تم إنشاء جميع البيانات التجريبية بنجاح!');
        $this->command->info('');
        $this->command->info('🔑 بيانات تسجيل الدخول:');
        $this->command->info('👨‍💼 المدير: admin@otu.edu / password123');
        $this->command->info('👨‍🏫 مدرس تجريبي: teacher@test.com / password');
        $this->command->info('👨‍🎓 طالب تجريبي: student@test.com / password');
        $this->command->info('');
        $this->command->info('📊 إحصائيات البيانات المُنشأة:');
        $this->command->info('• الأدوار: 3 (Admin, Teacher, Student)');
        $this->command->info('• المجموعات: 4 مجموعات دراسية');
        $this->command->info('• المستخدمين: ~35 مستخدم (مدراء، مدرسين، طلاب)');
        $this->command->info('• المقررات: 12 مقرر دراسي');
        $this->command->info('• الجداول الدراسية: جداول لجميع المجموعات');
        $this->command->info('• الامتحانات: امتحانات متنوعة لكل مقرر');
        $this->command->info('• الدرجات: درجات شاملة لجميع الطلاب');
        $this->command->info('• الرسوم: رسوم متنوعة مع مدفوعات');
        $this->command->info('• الحضور: سجلات حضور للمدرسين والطلاب');
        $this->command->info('• الطلبات الإدارية: طلبات متنوعة من الطلاب');
        $this->command->info('• الرسائل والإشعارات: تواصل شامل');
        $this->command->info('');
        $this->command->info('🎯 يمكنك الآن اختبار جميع وظائف النظام!');
    }
}
