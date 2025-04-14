<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateTeacherUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:teacher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء حساب مدرس للنظام';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('جاري إنشاء حساب مدرس للنظام...');

        // التحقق من وجود دور المدرس
        $teacherRole = Role::where('name', 'Teacher')->first();
        if (!$teacherRole) {
            $this->error('دور المدرس (Teacher) غير موجود في قاعدة البيانات!');
            $this->info('جاري إنشاء دور المدرس...');
            $teacherRole = Role::create([
                'name' => 'Teacher',
                'description' => 'دور المدرس في النظام'
            ]);
        }

        // حذف المدرس السابق إذا وجد
        $existingTeacher = User::where('email', 'teacher@otu.edu')->first();
        if ($existingTeacher) {
            $this->info('تم العثور على حساب مدرس سابق. جاري حذفه...');
            $existingTeacher->roles()->detach();
            $existingTeacher->delete();
        }

        // إنشاء حساب مدرس جديد
        $teacher = User::create([
            'name' => 'مدرس تجريبي',
            'email' => 'teacher@otu.edu',
            'password' => Hash::make('password123'),
        ]);

        // تعيين دور المدرس للمستخدم
        $teacher->roles()->attach($teacherRole);

        $this->info('تم إنشاء حساب المدرس بنجاح!');
        $this->info('بيانات الدخول:');
        $this->info('البريد الإلكتروني: teacher@otu.edu');
        $this->info('كلمة المرور: password123');

        return Command::SUCCESS;
    }
} 