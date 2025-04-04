<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeePaymentsController extends Controller
{
    /**
     * إنشاء الكنترولر وضبط إجراءات الصلاحيات
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Admin', ['except' => ['index', 'show']]);
    }
    
    /**
     * عرض قائمة المدفوعات (للمسؤول)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // الطالب يرى مدفوعاته فقط
        if (Auth::user()->hasRole('Student')) {
            $payments = FeePayment::where('user_id', Auth::id())
                ->with('fee')
                ->orderBy('payment_date', 'desc')
                ->get();
                
            return view('student.payments.index', compact('payments'));
        }
        
        // المسؤول يرى جميع المدفوعات
        $payments = FeePayment::with(['fee', 'user'])
            ->orderBy('payment_date', 'desc')
            ->get();
            
        return view('admin.payments.index', compact('payments'));
    }

    /**
     * عرض نموذج إنشاء دفعة جديدة
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $fees = Fee::where('status', '!=', 'paid')
            ->with('user')
            ->get();
            
        return view('admin.payments.create', compact('fees'));
    }

    /**
     * تخزين دفعة جديدة في قاعدة البيانات
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'payment_date' => 'required|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        // التحقق من أن المبلغ المدفوع لا يتجاوز المبلغ المتبقي
        $fee = Fee::findOrFail($validated['fee_id']);
        if ($validated['amount'] > $fee->remaining_amount) {
            return redirect()->back()
                ->with('error', 'المبلغ المدفوع أكبر من المبلغ المتبقي')
                ->withInput();
        }
        
        // بدء العملية المعاملة
        DB::beginTransaction();
        
        try {
            $validated['user_id'] = $fee->user_id;
            $validated['status'] = 'completed';
            
            $payment = FeePayment::create($validated);
            
            // تحديث حالة الرسوم
            $fee->updateBalances();
            
            DB::commit();
            
            return redirect()->route('payments.index')
                ->with('success', 'تم تسجيل الدفعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تسجيل الدفعة: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض تفاصيل دفعة محددة
     *
     * @param  \App\Models\FeePayment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(FeePayment $payment)
    {
        // التحقق من الصلاحيات (يمكن للمسؤول رؤية جميع المدفوعات، والطالب يمكنه رؤية مدفوعاته فقط)
        if (Auth::user()->hasRole('Student') && Auth::id() != $payment->user_id) {
            return redirect()->route('payments.index')
                ->with('error', 'لا يمكنك الوصول إلى هذه الدفعة');
        }
        
        $payment->load(['fee', 'user']);
        
        return view('payments.show', compact('payment'));
    }

    /**
     * عرض نموذج تعديل دفعة محددة
     *
     * @param  \App\Models\FeePayment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(FeePayment $payment)
    {
        $fees = Fee::where('status', '!=', 'paid')
            ->orWhere('id', $payment->fee_id)
            ->with('user')
            ->get();
            
        return view('admin.payments.edit', compact('payment', 'fees'));
    }

    /**
     * تحديث دفعة محددة في قاعدة البيانات
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FeePayment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FeePayment $payment)
    {
        $validated = $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'payment_date' => 'required|date',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        // التحقق من تغيير الرسوم المرتبطة
        $oldFeeId = $payment->fee_id;
        $newFeeId = $validated['fee_id'];
        
        // بدء العملية المعاملة
        DB::beginTransaction();
        
        try {
            // تحديث المستخدم إذا تغيرت الرسوم المرتبطة
            if ($oldFeeId != $newFeeId) {
                $fee = Fee::findOrFail($newFeeId);
                $validated['user_id'] = $fee->user_id;
            }
            
            $payment->update($validated);
            
            // تحديث حالة الرسوم القديمة والجديدة
            if ($oldFeeId != $newFeeId) {
                Fee::findOrFail($oldFeeId)->updateBalances();
            }
            
            Fee::findOrFail($newFeeId)->updateBalances();
            
            DB::commit();
            
            return redirect()->route('payments.index')
                ->with('success', 'تم تحديث الدفعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث الدفعة: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف دفعة محددة من قاعدة البيانات
     *
     * @param  \App\Models\FeePayment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeePayment $payment)
    {
        // بدء العملية المعاملة
        DB::beginTransaction();
        
        try {
            $feeId = $payment->fee_id;
            
            $payment->delete();
            
            // تحديث حالة الرسوم
            Fee::findOrFail($feeId)->updateBalances();
            
            DB::commit();
            
            return redirect()->route('payments.index')
                ->with('success', 'تم حذف الدفعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الدفعة: ' . $e->getMessage());
        }
    }
}
