<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeesController extends Controller
{
    /**
     * إنشاء الكنترولر وضبط إجراءات الصلاحيات
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Admin', ['except' => ['studentFees', 'show']]);
    }
    
    /**
     * عرض قائمة الرسوم (للمسؤول)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fees = Fee::with('user')->get();
        return view('admin.fees.index', compact('fees'));
    }

    /**
     * عرض نموذج إنشاء رسوم جديدة
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();
        
        return view('admin.fees.create', compact('students'));
    }

    /**
     * تخزين الرسوم الجديدة في قاعدة البيانات
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'fee_type' => 'required|string',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'academic_year' => 'required|string',
        ]);
        
        $validated['paid_amount'] = 0;
        $validated['remaining_amount'] = $validated['total_amount'];
        $validated['status'] = 'unpaid';
        
        $fee = Fee::create($validated);
        
        return redirect()->route('fees.index')
            ->with('success', 'تم إنشاء الرسوم بنجاح');
    }

    /**
     * عرض تفاصيل رسوم محددة
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function show(Fee $fee)
    {
        // التحقق من الصلاحيات (يمكن للمسؤول رؤية جميع الرسوم، والطالب يمكنه رؤية رسومه فقط)
        if (Auth::user()->hasRole('Student') && Auth::id() != $fee->user_id) {
            return redirect()->route('student.fees')
                ->with('error', 'لا يمكنك الوصول إلى هذه الرسوم');
        }
        
        $fee->load('payments');
        
        return view('fees.show', compact('fee'));
    }

    /**
     * عرض نموذج تعديل رسوم محددة
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function edit(Fee $fee)
    {
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();
        
        return view('admin.fees.edit', compact('fee', 'students'));
    }

    /**
     * تحديث الرسوم المحددة في قاعدة البيانات
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'fee_type' => 'required|string',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'academic_year' => 'required|string',
        ]);
        
        // تحديث المبلغ المتبقي إذا تغير إجمالي المبلغ
        if ($fee->total_amount != $validated['total_amount']) {
            $validated['remaining_amount'] = $validated['total_amount'] - $fee->paid_amount;
            
            // تحديث حالة الرسوم بناءً على المبلغ المدفوع والمبلغ الجديد
            if ($fee->paid_amount >= $validated['total_amount']) {
                $validated['status'] = 'paid';
            } elseif ($fee->paid_amount > 0) {
                $validated['status'] = 'partial';
            } else {
                $validated['status'] = 'unpaid';
            }
        }
        
        $fee->update($validated);
        
        return redirect()->route('fees.index')
            ->with('success', 'تم تحديث الرسوم بنجاح');
    }

    /**
     * حذف الرسوم المحددة من قاعدة البيانات
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fee $fee)
    {
        // التحقق من وجود مدفوعات مرتبطة بهذه الرسوم
        if ($fee->payments()->count() > 0) {
            return redirect()->route('fees.index')
                ->with('error', 'لا يمكن حذف هذه الرسوم لأنها تحتوي على مدفوعات مرتبطة');
        }
        
        $fee->delete();
        
        return redirect()->route('fees.index')
            ->with('success', 'تم حذف الرسوم بنجاح');
    }
    
    /**
     * عرض الرسوم الخاصة بالطالب الحالي
     *
     * @return \Illuminate\Http\Response
     */
    public function studentFees()
    {
        $fees = Fee::where('user_id', Auth::id())->with('payments')->get();
        
        // Calculate totals
        $totalFees = $fees->sum('total_amount');
        $totalPaid = $fees->sum('paid_amount');
        
        return view('student.fees.index', compact('fees', 'totalFees', 'totalPaid'));
    }
    
    /**
     * عرض نموذج إضافة دفعة جديدة
     *
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function createPayment(Fee $fee)
    {
        return view('admin.fees.payment', compact('fee'));
    }
    
    /**
     * تخزين دفعة جديدة في قاعدة البيانات
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fee  $fee
     * @return \Illuminate\Http\Response
     */
    public function storePayment(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $fee->remaining_amount,
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'payment_date' => 'required|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        // بدء العملية المعاملة
        DB::beginTransaction();
        
        try {
            $validated['user_id'] = $fee->user_id;
            $validated['fee_id'] = $fee->id;
            $validated['status'] = 'completed';
            
            $payment = FeePayment::create($validated);
            
            // تحديث حالة الرسوم
            $fee->updateBalances();
            
            DB::commit();
            
            return redirect()->route('fees.show', $fee)
                ->with('success', 'تم تسجيل الدفعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تسجيل الدفعة: ' . $e->getMessage())
                ->withInput();
        }
    }
}
