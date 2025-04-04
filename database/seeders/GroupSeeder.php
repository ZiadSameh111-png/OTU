<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // إنشاء مجموعات نموذجية
        $groups = [
            [
                'name' => 'مجموعة هندسة البرمجيات',
                'description' => 'مجموعة متخصصة في هندسة البرمجيات والتطوير',
                'active' => true
            ],
            [
                'name' => 'مجموعة تطوير الويب',
                'description' => 'مجموعة متخصصة في تطوير تطبيقات الويب',
                'active' => true
            ],
            [
                'name' => 'مجموعة الذكاء الاصطناعي',
                'description' => 'مجموعة متخصصة في الذكاء الاصطناعي وتعلم الآلة',
                'active' => true
            ],
            [
                'name' => 'مجموعة تحليل البيانات',
                'description' => 'مجموعة متخصصة في علم البيانات وتحليلها',
                'active' => false
            ]
        ];

        foreach ($groups as $groupData) {
            Group::create($groupData);
        }

        $this->command->info('تم إنشاء المجموعات النموذجية بنجاح');
    }
}
