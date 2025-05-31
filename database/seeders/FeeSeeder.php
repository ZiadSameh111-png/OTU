<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing fees with foreign key handling
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Fee::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();

        $feeTypes = [
            'tuition' => 'رسوم دراسية',
            'registration' => 'رسوم تسجيل',
            'exam' => 'رسوم امتحانات',
            'other' => 'رسوم أخرى'
        ];

        $academicYears = ['2023-2024', '2024-2025'];
        $currentYear = '2024-2025';

        foreach ($students as $student) {
            // Create tuition fees for current academic year
            $tuitionAmount = rand(8000, 15000);
            $paidAmount = rand(0, $tuitionAmount);
            
            Fee::create([
                'user_id' => $student->id,
                'title' => 'رسوم دراسية للفصل الدراسي الحالي',
                'total_amount' => $tuitionAmount,
                'paid_amount' => $paidAmount,
                'due_date' => Carbon::now()->addMonths(rand(1, 6)),
                'description' => 'رسوم دراسية للفصل الدراسي الحالي',
                'status' => $paidAmount >= $tuitionAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                'academic_year' => $currentYear,
                'fee_type' => 'tuition',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create registration fees
            $registrationAmount = rand(500, 1000);
            $registrationPaid = rand(0, $registrationAmount);
            
            Fee::create([
                'user_id' => $student->id,
                'title' => 'رسوم تسجيل للفصل الدراسي',
                'total_amount' => $registrationAmount,
                'paid_amount' => $registrationPaid,
                'due_date' => Carbon::now()->addMonths(rand(1, 3)),
                'description' => 'رسوم تسجيل للفصل الدراسي',
                'status' => $registrationPaid >= $registrationAmount ? 'paid' : ($registrationPaid > 0 ? 'partial' : 'unpaid'),
                'academic_year' => $currentYear,
                'fee_type' => 'registration',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create exam fees (sometimes)
            if (rand(1, 3) == 1) {
                $examAmount = rand(200, 500);
                $examPaid = rand(0, $examAmount);
                
                Fee::create([
                    'user_id' => $student->id,
                    'title' => 'رسوم امتحانات نهاية الفصل',
                    'total_amount' => $examAmount,
                    'paid_amount' => $examPaid,
                    'due_date' => Carbon::now()->addWeeks(rand(2, 8)),
                    'description' => 'رسوم امتحانات نهاية الفصل',
                    'status' => $examPaid >= $examAmount ? 'paid' : ($examPaid > 0 ? 'partial' : 'unpaid'),
                    'academic_year' => $currentYear,
                    'fee_type' => 'exam',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create some overdue fees for testing
            if (rand(1, 4) == 1) {
                $overdueAmount = rand(1000, 3000);
                Fee::create([
                    'user_id' => $student->id,
                    'title' => 'رسوم متأخرة من الفصل السابق',
                    'total_amount' => $overdueAmount,
                    'paid_amount' => 0,
                    'due_date' => Carbon::now()->subMonths(rand(1, 3)),
                    'description' => 'رسوم متأخرة من الفصل السابق',
                    'status' => 'overdue',
                    'academic_year' => '2023-2024',
                    'fee_type' => 'tuition',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('تم إنشاء الرسوم الدراسية بنجاح');
    }
} 