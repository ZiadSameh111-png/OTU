<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert groups without timestamps
        DB::table('groups')->insert([
            [
                'name' => 'مجموعة هندسة البرمجيات',
                'description' => 'مجموعة متخصصة في هندسة البرمجيات والتطوير',
                'active' => true,
            ],
            [
                'name' => 'مجموعة تطوير الويب',
                'description' => 'مجموعة متخصصة في تطوير تطبيقات الويب',
                'active' => true,
            ],
            [
                'name' => 'مجموعة الذكاء الاصطناعي',
                'description' => 'مجموعة متخصصة في الذكاء الاصطناعي وتعلم الآلة',
                'active' => true,
            ],
            [
                'name' => 'مجموعة تحليل البيانات',
                'description' => 'مجموعة متخصصة في علم البيانات وتحليلها',
                'active' => false,
            ],
        ]);
    }
}
