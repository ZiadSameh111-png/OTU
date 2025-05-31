<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing fee payments with foreign key handling
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        FeePayment::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $fees = Fee::where('paid_amount', '>', 0)->get();
        $admins = User::whereHas('roles', function($query) {
            $query->where('name', 'Admin');
        })->get();

        $paymentMethods = ['cash', 'bank_transfer', 'credit_card', 'check'];

        foreach ($fees as $fee) {
            $remainingAmount = $fee->paid_amount;
            $paymentCount = rand(1, 3); // Number of payments for this fee
            
            for ($i = 0; $i < $paymentCount && $remainingAmount > 0; $i++) {
                $paymentAmount = $i == $paymentCount - 1 ? $remainingAmount : rand(100, min($remainingAmount, $remainingAmount / 2));
                $remainingAmount -= $paymentAmount;

                FeePayment::create([
                    'user_id' => $fee->user_id,
                    'fee_id' => $fee->id,
                    'amount' => $paymentAmount,
                    'payment_date' => Carbon::now()->subDays(rand(1, 90)),
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    'status' => 'completed',
                    'description' => $i == 0 ? 'دفعة أولى' : ($i == $paymentCount - 1 ? 'دفعة أخيرة' : 'دفعة جزئية'),
                    'admin_id' => $admins->random()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create some pending payments
        $unpaidFees = Fee::where('paid_amount', 0)->take(5)->get();
        foreach ($unpaidFees as $fee) {
            FeePayment::create([
                'user_id' => $fee->user_id,
                'fee_id' => $fee->id,
                'amount' => rand(500, min(2000, $fee->total_amount)),
                'payment_date' => Carbon::now()->addDays(rand(1, 7)),
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'transaction_id' => 'PENDING-' . strtoupper(uniqid()),
                'status' => 'pending',
                'description' => 'دفعة معلقة في انتظار التأكيد',
                'admin_id' => $admins->random()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('تم إنشاء مدفوعات الرسوم بنجاح');
    }
} 