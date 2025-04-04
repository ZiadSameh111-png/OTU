<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // تحقق من وجود مستخدم مدير قبل الإنشاء
        $adminEmail = 'admin@otu.edu';
        $existingAdmin = User::where('email', $adminEmail)->first();
        
        if (!$existingAdmin) {
            // الحصول على دور المدير
            $adminRole = Role::where('name', 'Admin')->first();
            
            if (!$adminRole) {
                $this->command->error('دور المدير (Admin) غير موجود في النظام!');
                return;
            }
            
            // إنشاء مستخدم مدير جديد
            $admin = User::create([
                'name' => 'مدير النظام',
                'email' => $adminEmail,
                'password' => Hash::make('password123'),
            ]);
            
            // تعيين دور المدير للمستخدم
            $admin->roles()->attach($adminRole);
            
            $this->command->info('تم إنشاء حساب المدير بنجاح:');
            $this->command->info('البريد الإلكتروني: ' . $adminEmail);
            $this->command->info('كلمة المرور: password123');
        } else {
            $this->command->line('حساب المدير موجود بالفعل: ' . $adminEmail);
        }
    }
} 