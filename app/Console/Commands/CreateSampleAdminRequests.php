<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AdminRequest;
use Illuminate\Support\Carbon;

class CreateSampleAdminRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-admin-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إضافة طلبات إدارية تجريبية';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('جاري إضافة طلبات إدارية تجريبية...');

        // Get students to create requests for
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->take(5)->get();

        if ($students->isEmpty()) {
            $this->warn('لا يوجد طلاب في النظام! جاري إنشاء طالب تجريبي...');
            
            // Create a test student if none exists
            $studentRole = \App\Models\Role::where('name', 'Student')->first();
            if (!$studentRole) {
                $this->error('دور الطالب غير موجود في النظام!');
                return Command::FAILURE;
            }
            
            $student = User::create([
                'name' => 'طالب تجريبي',
                'email' => 'student@example.com',
                'password' => bcrypt('password123'),
            ]);
            
            $student->roles()->attach($studentRole);
            $students = collect([$student]);
            
            $this->info('تم إنشاء طالب تجريبي بنجاح!');
        }

        // Request types
        $types = [
            'leave' => 'طلب إجازة لظروف طارئة',
            'certificate_request' => 'طلب شهادة للتقديم على وظيفة',
            'group_transfer' => 'طلب نقل إلى مجموعة أخرى',
            'course_withdrawal' => 'طلب انسحاب من مقرر بسبب تعارض',
            'absence_excuse' => 'طلب عذر غياب لظروف صحية',
        ];

        // Priorities
        $priorities = ['low', 'normal', 'high', 'urgent'];

        // Create 10 sample requests
        $count = 0;
        foreach ($students as $student) {
            for ($i = 0; $i < 3; $i++) {
                $type = array_rand($types);
                
                AdminRequest::create([
                    'user_id' => $student->id,
                    'type' => $type,
                    'details' => $types[$type] . ' - تفاصيل إضافية للطلب رقم ' . ($count + 1),
                    'priority' => $priorities[array_rand($priorities)],
                    'request_date' => Carbon::now()->subDays(rand(0, 10)),
                    'status' => 'pending',
                ]);
                
                $count++;
            }
        }

        $this->info('تم إضافة ' . $count . ' طلب إداري تجريبي بنجاح!');
        return Command::SUCCESS;
    }
} 