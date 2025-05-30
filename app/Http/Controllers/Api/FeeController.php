<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\User;
use App\Models\Group;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FeeController extends Controller
{
    /**
     * Display a listing of the fees for admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $fees = Fee::with(['user', 'payments'])->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $fees
        ]);
    }

    /**
     * Get a list of students for fee assignment.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudents()
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get(['id', 'name', 'email']);
        
        return response()->json([
            'status' => 'success',
            'data' => $students
        ]);
    }

    /**
     * Store a newly created fee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'academic_year' => 'nullable|string',
            'fee_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

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
            
            return response()->json([
                'status' => 'success',
                'message' => 'Fee created successfully',
                'data' => $fee->load(['user', 'payments'])
            ], 201);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating fee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified fee.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $fee = Fee::with(['user', 'payments'])->findOrFail($id);
        
        // Check if user is authorized to view this fee
        $user = Auth::user();
        if (!$user->hasRole('Admin') && $fee->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $fee
        ]);
    }

    /**
     * Update the specified fee in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'academic_year' => 'required|string',
            'fee_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $fee = Fee::findOrFail($id);
        
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

        return response()->json([
            'status' => 'success',
            'message' => 'Fee updated successfully',
            'data' => $fee->load(['user', 'payments'])
        ]);
    }

    /**
     * Remove the specified fee from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $fee = Fee::findOrFail($id);
        
        // Check if there are any payments associated with this fee
        if ($fee->payments()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete fee with existing payments',
                'data' => [
                    'payment_count' => $fee->payments()->count()
                ]
            ], 400);
        }
        
        $fee->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Fee deleted successfully'
        ]);
    }

    /**
     * Display a listing of the fees for the authenticated student.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function studentFees()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Student')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $fees = Fee::where('user_id', $user->id)
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $totalFees = $fees->sum('total_amount');
        $totalPaid = $fees->sum('paid_amount');
        $totalRemaining = $fees->sum('remaining_amount');
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'fees' => $fees,
                'summary' => [
                    'total_fees' => $totalFees,
                    'total_paid' => $totalPaid,
                    'total_remaining' => $totalRemaining
                ]
            ]
        ]);
    }

    /**
     * Get payment statement for the authenticated student.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statement()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Student')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $fees = Fee::where('user_id', $user->id)->with('payments')->get();
        $payments = FeePayment::where('user_id', $user->id)
            ->with('fee')
            ->orderBy('payment_date', 'desc')
            ->get();
            
        $totalFees = $fees->sum('total_amount');
        $totalPaid = $fees->sum('paid_amount');
        $totalRemaining = $fees->sum('remaining_amount');
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'fees' => $fees,
                'payments' => $payments,
                'summary' => [
                    'total_fees' => $totalFees,
                    'total_paid' => $totalPaid,
                    'total_remaining' => $totalRemaining
                ]
            ]
        ]);
    }

    /**
     * Create a payment transaction for a fee.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPaymentTransaction(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Student')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:credit_card,bank_transfer,digital_wallet',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $fee = Fee::findOrFail($id);
        
        // Check if the fee belongs to the authenticated user
        if ($fee->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this fee'
            ], 403);
        }
        
        // Check if the fee is already paid
        if ($fee->status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'This fee is already paid'
            ], 400);
        }
        
        // Check if payment amount is valid
        if ($request->amount > $fee->remaining_amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment amount exceeds the remaining balance',
                'data' => [
                    'remaining_amount' => $fee->remaining_amount,
                    'requested_amount' => $request->amount
                ]
            ], 400);
        }
        
        // Create a new payment transaction
        $transaction = new PaymentTransaction();
        $transaction->user_id = $user->id;
        $transaction->fee_id = $fee->id;
        $transaction->amount = $request->amount;
        $transaction->payment_method = $request->payment_method;
        $transaction->transaction_id = 'TRANS-' . time() . '-' . $user->id;
        $transaction->status = 'pending';
        $transaction->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Payment transaction created',
            'data' => [
                'transaction' => $transaction,
                'payment_url' => '/api/student/fees/payment-gateway/' . $transaction->transaction_id
            ]
        ], 201);
    }

    /**
     * Process a payment transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $transactionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request, $transactionId)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Student')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $transaction = PaymentTransaction::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        if ($transaction->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'This transaction has already been processed',
                'data' => [
                    'transaction_status' => $transaction->status
                ]
            ], 400);
        }
        
        // In a real-world scenario, you would process the payment with a payment gateway
        // For this example, we'll simulate a successful payment
        
        DB::beginTransaction();
        try {
            // Update transaction status
            $transaction->status = 'completed';
            $transaction->completed_at = now();
            $transaction->save();
            
            // Create payment record
            $payment = new FeePayment();
            $payment->user_id = $user->id;
            $payment->fee_id = $transaction->fee_id;
            $payment->amount = $transaction->amount;
            $payment->payment_method = $transaction->payment_method;
            $payment->transaction_id = $transaction->transaction_id;
            $payment->payment_date = now();
            $payment->status = 'completed';
            $payment->description = 'Online payment via API';
            $payment->save();
            
            // Update fee balance
            $fee = Fee::findOrFail($transaction->fee_id);
            $fee->paid_amount += $transaction->amount;
            $fee->remaining_amount -= $transaction->amount;
            
            // Update fee status
            if ($fee->remaining_amount <= 0) {
                $fee->status = 'paid';
            } else {
                $fee->status = 'partially_paid';
            }
            
            $fee->save();
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment' => $payment,
                    'fee' => $fee,
                    'receipt_id' => $payment->id
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment receipt details.
     *
     * @param  int  $paymentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReceipt($paymentId)
    {
        $user = Auth::user();
        $payment = FeePayment::with(['user', 'fee'])->findOrFail($paymentId);
        
        // Check if the user is authorized to view this receipt
        if (!$user->hasRole('Admin') && $payment->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this receipt'
            ], 403);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'receipt' => $payment,
                'fee' => $payment->fee,
                'student' => $payment->user,
                'receipt_date' => $payment->payment_date,
                'receipt_number' => 'RCP-' . $payment->id
            ]
        ]);
    }

    /**
     * Get payment history for student.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentHistory()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Student')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        $payments = FeePayment::where('user_id', $user->id)
            ->with('fee')
            ->orderBy('payment_date', 'desc')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }
} 