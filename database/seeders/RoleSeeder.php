<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // مسح البيانات الحالية من جدول الأدوار
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // إنشاء الأدوار الافتراضية
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'مدير النظام مع صلاحيات كاملة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teacher',
                'description' => 'معلم مع صلاحيات إدارة المقررات والطلاب',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Student',
                'description' => 'طالب مع صلاحيات عرض المقررات وتقديم الواجبات',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // إضافة الأدوار إلى قاعدة البيانات
        DB::table('roles')->insert($roles);

        $this->command->info('تم إنشاء الأدوار بنجاح');
    }
}
