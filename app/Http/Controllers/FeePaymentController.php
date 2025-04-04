<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeePaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Admin');
    }

    /**
     * Display a listing of the payments for a specific fee.
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function index(Fee $fee)
    {
        $payments = $fee->payments()->with('admin')->latest('payment_date')->get();
        $student = $fee->student;
        
        return view('admin.fees.payments.index', compact('fee', 'payments', 'student'));
    }

    /**
     * Show the form for creating a new payment.
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function create(Fee $fee)
    {
        $student = $fee->student;
        $remainingAmount = $fee->remaining_amount;
        
        return view('admin.fees.payments.create', compact('fee', 'student', 'remainingAmount'));
    }

    /**
     * Store a newly created payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Fee $fee)
    {
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($fee) {
                    if ($value > $fee->remaining_amount) {
                        $fail('مبلغ الدفعة يتجاوز المبلغ المتبقي من الرسوم.');
                    }
                },
            ],
            'payment_date' => 'required|date|before_or_equal:today',
        ]);

        try {
            DB::beginTransaction();

            // إنشاء سجل الدفعة
            $payment = FeePayment::create([
                'fee_id' => $fee->id,
                'user_id' => $fee->user_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'admin_id' => Auth::id(),
                'payment_method' => 'cash',
                'status' => 'completed',
            ]);

            // تحديث إجمالي المبلغ المدفوع في سجل الرسوم
            $fee->update([
                'paid_amount' => $fee->paid_amount + $request->amount
            ]);

            DB::commit();

            return redirect()->route('admin.fees.payments', $fee)
                ->with('success', 'تم تسجيل الدفعة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'حدث خطأ أثناء تسجيل الدفعة: ' . $e->getMessage());
        }
    }
}
