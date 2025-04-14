<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء مستخدم مدير للنظام';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('جاري إنشاء مستخدم مدير للنظام...');

        // التحقق من وجود دور المدير
        $adminRole = Role::where('name', 'Admin')->first();
        if (!$adminRole) {
            $this->error('دور المدير (Admin) غير موجود في قاعدة البيانات!');
            $this->info('جاري إنشاء دور المدير...');
            $adminRole = Role::create([
                'name' => 'Admin',
                'description' => 'مدير النظام مع صلاحيات كاملة'
            ]);
        }

        // حذف المستخدم المدير السابق إذا وجد
        $existingAdmin = User::where('email', 'admin@otu.edu')->first();
        if ($existingAdmin) {
            $this->info('تم العثور على مستخدم مدير سابق. جاري حذفه...');
            $existingAdmin->roles()->detach();
            $existingAdmin->delete();
        }

        // إنشاء مستخدم مدير جديد
        $admin = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@otu.edu',
            'password' => Hash::make('password123'),
        ]);

        // تعيين دور المدير للمستخدم
        $admin->roles()->attach($adminRole);

        $this->info('تم إنشاء حساب المدير بنجاح!');
        $this->info('بيانات الدخول:');
        $this->info('البريد الإلكتروني: admin@otu.edu');
        $this->info('كلمة المرور: password123');

        return Command::SUCCESS;
    }
}
