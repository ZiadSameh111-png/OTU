<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\User;

class CreateSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-sample-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إضافة بيانات تجريبية للنظام';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('جاري إضافة بيانات تجريبية للنظام...');

        // Create sample fees
        $this->createSampleFees();
        
        // Create sample messages
        $this->createSampleMessages();
        
        $this->info('تم إضافة البيانات التجريبية بنجاح!');
        return Command::SUCCESS;
    }
    
    /**
     * Create sample fees
     */
    private function createSampleFees()
    {
        $this->info('جاري إضافة رسوم تجريبية...');
        
        // Get students
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->take(5)->get();
        
        if ($students->isEmpty()) {
            $this->warn('لا يوجد طلاب في النظام! تم تخطي إضافة الرسوم.');
            return;
        }
        
        $feeTypes = ['tuition', 'registration', 'examination', 'other'];
        $count = 0;
        
        foreach ($students as $student) {
            $numFees = rand(1, 3);
            
            for ($i = 0; $i < $numFees; $i++) {
                $totalAmount = rand(1000, 5000);
                $paidAmount = rand(0, $totalAmount);
                $status = $paidAmount === 0 ? 'unpaid' : ($paidAmount < $totalAmount ? 'partially_paid' : 'paid');
                
                \DB::table('fees')->insert([
                    'user_id' => $student->id,
                    'fee_type' => $feeTypes[array_rand($feeTypes)],
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'due_date' => Carbon::now()->addDays(rand(1, 30))->format('Y-m-d'),
                    'payment_date' => $paidAmount > 0 ? Carbon::now()->subDays(rand(1, 10))->format('Y-m-d') : null,
                    'payment_method' => $paidAmount > 0 ? 'online' : null,
                    'payment_reference' => $paidAmount > 0 ? 'REF' . rand(10000, 99999) : null,
                    'status' => $status,
                    'notes' => 'رسوم تجريبية',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                
                $count++;
            }
        }
        
        $this->info("تم إضافة $count رسوم تجريبية بنجاح!");
    }
    
    /**
     * Create sample messages
     */
    private function createSampleMessages()
    {
        $this->info('جاري إضافة رسائل تجريبية...');
        
        // Get some users
        $admin = User::whereHas('roles', function($query) {
            $query->where('name', 'Admin');
        })->first();
        
        $teacher = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->first();
        
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->take(3)->get();
        
        if (!$admin) {
            $this->warn('لا يوجد مدير في النظام! تم تخطي إضافة بعض الرسائل.');
            return;
        }
        
        $subjects = [
            'معلومات مهمة عن الاختبارات القادمة',
            'تذكير بموعد تسليم الواجبات',
            'جدول المحاضرات للأسبوع القادم',
            'دعوة لحضور ورشة عمل',
            'إعلان مهم من إدارة الجامعة',
        ];
        
        $contents = [
            'نود تذكيركم بموعد الاختبارات النصفية والتي ستبدأ من يوم الأحد القادم. نتمنى لكم التوفيق!',
            'نذكر جميع الطلاب بضرورة تسليم الواجبات قبل نهاية الأسبوع. الرجاء الالتزام بالمواعيد المحددة.',
            'تم نشر جدول المحاضرات للأسبوع القادم على الموقع. الرجاء الاطلاع عليه مبكراً.',
            'تدعوكم الجامعة لحضور ورشة عمل بعنوان "مهارات البحث العلمي" يوم الثلاثاء القادم الساعة 10 صباحاً.',
            'إعلان مهم: سيتم إغلاق مبنى الإدارة يوم غد لأعمال الصيانة. نأسف على الإزعاج.',
        ];
        
        $count = 0;
        
        // Admin to all students
        \DB::table('messages')->insert([
            'sender_id' => $admin->id,
            'receiver_id' => null,
            'subject' => $subjects[array_rand($subjects)],
            'content' => $contents[array_rand($contents)],
            'read_at' => null,
            'is_important' => true,
            'receiver_type' => 'role',
            'group_id' => null,
            'role' => 'student',
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);
        $count++;
        
        // Messages between admin and specific students
        foreach ($students as $student) {
            // Admin to student
            \DB::table('messages')->insert([
                'sender_id' => $admin->id,
                'receiver_id' => $student->id,
                'subject' => $subjects[array_rand($subjects)],
                'content' => $contents[array_rand($contents)],
                'read_at' => rand(0, 1) ? Carbon::now() : null,
                'is_important' => rand(0, 1),
                'receiver_type' => 'individual',
                'group_id' => null,
                'role' => null,
                'created_at' => Carbon::now()->subDays(rand(1, 7)),
                'updated_at' => Carbon::now()->subDays(rand(1, 7)),
            ]);
            $count++;
            
            // Student to admin
            \DB::table('messages')->insert([
                'sender_id' => $student->id,
                'receiver_id' => $admin->id,
                'subject' => 'استفسار عن ' . ['الجدول الدراسي', 'المنح الدراسية', 'مواعيد التسجيل'][rand(0, 2)],
                'content' => 'مرحباً، أود الاستفسار عن ' . ['مواعيد التسجيل للفصل القادم', 'كيفية التقديم على المنح الدراسية', 'جدول الامتحانات النهائية'][rand(0, 2)] . '. شكراً لتعاونكم.',
                'read_at' => rand(0, 1) ? Carbon::now() : null,
                'is_important' => false,
                'receiver_type' => 'individual',
                'group_id' => null,
                'role' => null,
                'created_at' => Carbon::now()->subDays(rand(1, 7)),
                'updated_at' => Carbon::now()->subDays(rand(1, 7)),
            ]);
            $count++;
        }
        
        // Add teacher messages if available
        if ($teacher) {
            // Teacher to admin
            \DB::table('messages')->insert([
                'sender_id' => $teacher->id,
                'receiver_id' => $admin->id,
                'subject' => 'طلب معلومات عن القاعات الدراسية',
                'content' => 'مرحباً، أرجو تزويدي بمعلومات عن القاعات المتاحة للمحاضرات الإضافية خلال الأسبوع القادم. مع الشكر.',
                'read_at' => null,
                'is_important' => true,
                'receiver_type' => 'individual',
                'group_id' => null,
                'role' => null,
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ]);
            $count++;
            
            // Teacher to students
            \DB::table('messages')->insert([
                'sender_id' => $teacher->id,
                'receiver_id' => null,
                'subject' => 'تأجيل موعد المحاضرة',
                'content' => 'أعزائي الطلبة، أود إعلامكم بتأجيل محاضرة يوم الثلاثاء إلى يوم الأربعاء في نفس الوقت. شكراً لتفهمكم.',
                'read_at' => null,
                'is_important' => true,
                'receiver_type' => 'role',
                'group_id' => null,
                'role' => 'student',
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(12),
            ]);
            $count++;
        }
        
        $this->info("تم إضافة $count رسائل تجريبية بنجاح!");
    }
} 