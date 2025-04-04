<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\User;
use App\Models\Group;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PDF;

class FeeController extends Controller
{
    /**
     * Display a listing of the fees for admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fees = Fee::with(['user', 'payments'])->paginate(10);
        
        // إضافة قائمة الطلاب للعرض في نموذج إضافة دفعة جديدة
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();
        
        return view('admin.fees.index', compact('fees', 'students'));
    }

    /**
     * Show the form for creating a new fee.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $students = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Student');
        })->get();
        
        return view('admin.fees.create', compact('students'));
    }

    /**
     * Store a newly created fee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'academic_year' => 'nullable|string',
            'fee_type' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $fee = new Fee();
            $fee->user_id = $request->student_id;
            $fee->total_amount = $request->total_amount;
            $fee->paid_amount = $request->paid_amount ?? 0;
            $fee->remaining_amount = $request->total_amount - ($request->paid_amount ?? 0);
            $fee->due_date = $request->due_date ?? now()->addMonths(1);
            $fee->description = $request->description ?? 'رسوم دراسية';
            $fee->academic_year = $request->academic_year ?? date('Y') . '/' . (date('Y') + 1);
            $fee->fee_type = $request->fee_type ?? 'tuition';
            
            // تحديد حالة الدفع
            if ($fee->remaining_amount <= 0) {
                $fee->status = 'paid';
            } elseif ($fee->paid_amount > 0) {
                $fee->status = 'partially_paid';
            } else {
                $fee->status = 'unpaid';
            }
            
            $fee->save();
            
            // إذا كان هناك مبلغ مدفوع، قم بإنشاء سجل دفع
            if ($request->paid_amount > 0) {
                $payment = new FeePayment();
                $payment->user_id = $fee->user_id;
                $payment->fee_id = $fee->id;
                $payment->amount = $request->paid_amount;
                $payment->payment_method = 'cash';
                $payment->transaction_id = 'MANUAL-' . time();
                $payment->payment_date = now();
                $payment->status = 'completed';
                $payment->description = 'دفعة أولية عند التسجيل';
                $payment->save();
            }
            
            DB::commit();
            
            return redirect()->route('admin.fees')
                ->with('success', 'تم إضافة الرسوم بنجاح');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.fees')
                ->with('error', 'حدث خطأ أثناء تسجيل الرسوم: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified fee for admin.
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function show(Fee $fee)
    {
        $fee->load(['user', 'payments']);
        return view('admin.fees.show', compact('fee'));
    }

    /**
     * Show the form for editing the specified fee.
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function edit(Fee $fee)
    {
        $students = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Student');
        })->get();
        
        return view('admin.fees.edit', compact('fee', 'students'));
    }

    /**
     * Update the specified fee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fee $fee)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'academic_year' => 'required|string',
            'fee_type' => 'required|string',
        ]);

        // Store old amount to adjust remaining amount correctly
        $oldTotalAmount = $fee->total_amount;
        
        $fee->user_id = $request->user_id;
        $fee->total_amount = $request->total_amount;
        
        // Recalculate remaining amount
        $fee->remaining_amount = $fee->remaining_amount + ($request->total_amount - $oldTotalAmount);
        
        $fee->due_date = $request->due_date;
        $fee->description = $request->description;
        $fee->academic_year = $request->academic_year;
        $fee->fee_type = $request->fee_type;
        
        // Update status based on payment
        if ($fee->remaining_amount <= 0) {
            $fee->status = 'paid';
        } elseif ($fee->paid_amount > 0) {
            $fee->status = 'partially_paid';
        } else {
            $fee->status = 'unpaid';
        }
        
        $fee->save();

        return redirect()->route('admin.fees')
            ->with('success', 'تم تحديث الرسوم بنجاح');
    }

    /**
     * Remove the specified fee from storage.
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fee $fee)
    {
        // Check if there are payments before deleting
        if ($fee->payments->count() > 0) {
            return redirect()->route('admin.fees')
                ->with('error', 'لا يمكن حذف الرسوم لوجود مدفوعات مرتبطة بها');
        }
        
        $fee->delete();
        
        return redirect()->route('admin.fees')
            ->with('success', 'تم حذف الرسوم بنجاح');
    }
    
    /**
     * Display a listing of the fees for the student.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentIndex()
    {
        $user = Auth::user();
        // Temporary fix to avoid using the fee_group table
        $fees = Fee::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('applies_to_all', true);
        })
        ->with('payments')
        ->get();
        
        $totalFees = $fees->sum('amount');
        $totalPaid = FeePayment::where('user_id', $user->id)
                                ->where('status', 'completed')
                                ->sum('amount');
        
        return view('student.fees.index', compact('fees', 'totalFees', 'totalPaid'));
    }
    
    /**
     * Display the specified fee for student.
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function studentShow(Fee $fee)
    {
        $user = Auth::user();
        
        // التحقق من أن الرسوم تنطبق على الطالب
        // Temporary fix to avoid using the fee_group table
        $applies = $fee->applies_to_all || $fee->user_id == $user->id;
        
        if (!$applies) {
            return redirect()->route('student.fees')
                            ->with('error', 'لا يمكنك الوصول إلى هذه الرسوم.');
        }
        
        $payments = FeePayment::where('user_id', $user->id)
                            ->where('fee_id', $fee->id)
                            ->orderBy('payment_date', 'desc')
                            ->get();
        
        $totalPaid = $payments->where('status', 'completed')->sum('amount');
        $remaining = max(0, $fee->amount - $totalPaid);
        
        $pendingTransaction = PaymentTransaction::where('user_id', $user->id)
                                              ->where('fee_id', $fee->id)
                                              ->where('status', 'pending')
                                              ->orderBy('created_at', 'desc')
                                              ->first();
        
        return view('student.fees.show', compact('fee', 'payments', 'totalPaid', 'remaining', 'pendingTransaction'));
    }
    
    /**
     * Process a new fee payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pay(Fee $fee)
    {
        $user = Auth::user();
        
        // التحقق من أن الرسوم تنطبق على الطالب
        // Temporary fix to avoid using the fee_group table
        $applies = $fee->applies_to_all || $fee->user_id == $user->id;
        
        if (!$applies) {
            return redirect()->route('student.fees')
                            ->with('error', 'لا يمكنك الوصول إلى هذه الرسوم.');
        }
        
        $payments = FeePayment::where('user_id', $user->id)
                            ->where('fee_id', $fee->id)
                            ->where('status', 'completed')
                            ->get();
        
        $totalPaid = $payments->sum('amount');
        $remaining = max(0, $fee->amount - $totalPaid);
        
        if ($remaining <= 0) {
            return redirect()->route('fees.show', $fee->id)
                            ->with('info', 'تم دفع هذه الرسوم بالكامل.');
        }
        
        return view('student.fees.pay', compact('fee', 'remaining'));
    }
    
    /**
     * Update fee balances after a successful payment.
     *
     * @param  int  $userId
     * @param  float  $paymentAmount
     * @return void
     */
    private function updateFeeBalances($userId, $paymentAmount)
    {
        $remainingAmount = $paymentAmount;
        
        // Get unpaid fees ordered by due date (oldest first)
        $fees = Fee::where('user_id', $userId)
                ->where('remaining_amount', '>', 0)
                ->orderBy('due_date')
                ->get();
        
        foreach ($fees as $fee) {
            if ($remainingAmount <= 0) {
                break;
            }
            
            $amountToApply = min($remainingAmount, $fee->remaining_amount);
            
            $fee->paid_amount += $amountToApply;
            $fee->remaining_amount -= $amountToApply;
            
            if ($fee->remaining_amount <= 0) {
                $fee->status = 'paid';
            } else {
                $fee->status = 'partially_paid';
            }
            
            $fee->save();
            
            $remainingAmount -= $amountToApply;
        }
    }
    
    /**
     * Show detailed statement of fees and payments.
     *
     * @return \Illuminate\Http\Response
     */
    public function statement()
    {
        $user = Auth::user();
        
        // Temporary fix to avoid using the fee_group table
        $fees = Fee::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('applies_to_all', true);
        })
        ->with('payments')
        ->get();
        
        $payments = FeePayment::where('user_id', $user->id)
                            ->orderBy('payment_date', 'desc')
                            ->get();
        
        $totalFees = $fees->sum('amount');
        $totalPaid = $payments->where('status', 'completed')->sum('amount');
        
        return view('student.fees.statement', compact('user', 'fees', 'payments', 'totalFees', 'totalPaid'));
    }
    
    /**
     * Continue to payment checkout.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkout(Request $request, Fee $fee)
    {
        $user = Auth::user();
        
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:credit_card,bank_transfer',
        ]);
        
        $amount = $request->amount;
        $paymentMethod = $request->payment_method;
        
        // التحقق من أن المبلغ لا يتجاوز المبلغ المتبقي
        $payments = FeePayment::where('user_id', $user->id)
                            ->where('fee_id', $fee->id)
                            ->where('status', 'completed')
                            ->get();
        
        $totalPaid = $payments->sum('amount');
        $remaining = max(0, $fee->amount - $totalPaid);
        
        if ($amount > $remaining) {
            return redirect()->back()
                            ->with('error', 'المبلغ المدخل يتجاوز المبلغ المتبقي.')
                            ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // إنشاء معاملة دفع جديدة
            $transaction = new PaymentTransaction();
            $transaction->user_id = $user->id;
            $transaction->fee_id = $fee->id;
            $transaction->amount = $amount;
            $transaction->payment_method = $paymentMethod;
            $transaction->status = 'pending';
            $transaction->transaction_id = 'TRX' . time() . rand(1000, 9999);
            $transaction->save();
            
            DB::commit();
            
            // توجيه إلى صفحة بوابة الدفع (محاكاة)
            return redirect()->route('fees.payment-gateway', $transaction->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في إنشاء معاملة الدفع: ' . $e->getMessage());
            
            return redirect()->back()
                            ->with('error', 'حدث خطأ أثناء معالجة طلب الدفع. الرجاء المحاولة مرة أخرى.')
                            ->withInput();
        }
    }
    
    /**
     * محاكاة بوابة الدفع
     *
     * @param  int  $transactionId
     * @return \Illuminate\Http\Response
     */
    public function paymentGateway($transactionId)
    {
        $user = Auth::user();
        
        $transaction = PaymentTransaction::where('id', $transactionId)
                                        ->where('user_id', $user->id)
                                        ->where('status', 'pending')
                                        ->firstOrFail();
        
        return view('student.fees.payment-gateway', compact('transaction'));
    }
    
    /**
     * معالجة نتيجة الدفع
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $transactionId
     * @return \Illuminate\Http\Response
     */
    public function processPayment(Request $request, $transactionId)
    {
        $user = Auth::user();
        
        $transaction = PaymentTransaction::where('id', $transactionId)
                                        ->where('user_id', $user->id)
                                        ->where('status', 'pending')
                                        ->firstOrFail();
        
        $status = $request->input('status', 'failed');
        
        DB::beginTransaction();
        
        try {
            // تحديث حالة المعاملة
            $transaction->status = $status;
            $transaction->save();
            
            // إذا كانت العملية ناجحة، قم بإنشاء سجل دفع
            if ($status == 'completed') {
                $payment = new FeePayment();
                $payment->user_id = $user->id;
                $payment->fee_id = $transaction->fee_id;
                $payment->amount = $transaction->amount;
                $payment->payment_method = $transaction->payment_method;
                $payment->transaction_id = $transaction->transaction_id;
                $payment->payment_date = Carbon::now();
                $payment->status = 'completed';
                $payment->description = 'دفع رسوم ' . $transaction->fee->description;
                $payment->save();
            }
            
            DB::commit();
            
            if ($status == 'completed') {
                return redirect()->route('fees.receipt', $payment->id)
                                ->with('success', 'تم إتمام عملية الدفع بنجاح.');
            } else {
                return redirect()->route('fees.show', $transaction->fee_id)
                                ->with('error', 'فشلت عملية الدفع. الرجاء المحاولة مرة أخرى.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في معالجة نتيجة الدفع: ' . $e->getMessage());
            
            return redirect()->route('student.fees')
                            ->with('error', 'حدث خطأ أثناء معالجة نتيجة الدفع. يرجى المحاولة مرة أخرى لاحقاً.');
        }
    }
    
    /**
     * إعادة محاولة الدفع
     *
     * @param  int  $transactionId
     * @return \Illuminate\Http\Response
     */
    public function retry($transactionId)
    {
        $user = Auth::user();
        
        $transaction = PaymentTransaction::where('id', $transactionId)
                                        ->where('user_id', $user->id)
                                        ->where('status', 'failed')
                                        ->firstOrFail();
        
        DB::beginTransaction();
        
        try {
            // إنشاء معاملة دفع جديدة
            $newTransaction = new PaymentTransaction();
            $newTransaction->user_id = $user->id;
            $newTransaction->fee_id = $transaction->fee_id;
            $newTransaction->amount = $transaction->amount;
            $newTransaction->payment_method = $transaction->payment_method;
            $newTransaction->status = 'pending';
            $newTransaction->transaction_id = 'TRX' . time() . rand(1000, 9999);
            $newTransaction->save();
            
            DB::commit();
            
            return redirect()->route('fees.payment-gateway', $newTransaction->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في إعادة محاولة الدفع: ' . $e->getMessage());
            
            return redirect()->back()
                            ->with('error', 'حدث خطأ أثناء إعادة محاولة الدفع. الرجاء المحاولة مرة أخرى.');
        }
    }
    
    /**
     * عرض إيصال الدفع
     *
     * @param  int  $paymentId
     * @return \Illuminate\Http\Response
     */
    public function receipt($paymentId)
    {
        $user = Auth::user();
        
        $payment = FeePayment::where('id', $paymentId)
                            ->where('user_id', $user->id)
                            ->where('status', 'completed')
                            ->firstOrFail();
        
        return view('student.fees.receipt', compact('payment'));
    }
}
