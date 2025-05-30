<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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

        // Insert roles without timestamps
        DB::table('roles')->insert([
            [
                'name' => 'Admin',
                'description' => 'مدير النظام مع صلاحيات كاملة',
            ],
            [
                'name' => 'Teacher',
                'description' => 'معلم مع صلاحيات إدارة المقررات والطلاب',
            ],
            [
                'name' => 'Student',
                'description' => 'طالب مع صلاحيات عرض المقررات وتقديم الواجبات',
            ],
        ]);

        $this->command->info('تم إنشاء الأدوار بنجاح');
    }
}
