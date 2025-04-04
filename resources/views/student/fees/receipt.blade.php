@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-receipt me-2"></i>إيصال دفع
            </h1>
            <p class="text-muted">إيصال رسمي لعملية دفع الرسوم الدراسية</p>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" id="receipt-card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-0">مؤسسة التعليم الأهلي</h3>
                        <p class="text-muted">إيصال دفع رسوم دراسية</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="mb-1"><strong>رقم الإيصال:</strong> #{{ $payment->id }}</p>
                            <p class="mb-1"><strong>تاريخ الدفع:</strong> {{ $payment->payment_date->format('Y-m-d') }}</p>
                            <p class="mb-0"><strong>وقت الدفع:</strong> {{ $payment->payment_date->format('H:i') }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="mb-1"><strong>اسم الطالب:</strong> {{ $payment->user->name }}</p>
                            <p class="mb-1"><strong>الرقم الجامعي:</strong> {{ $payment->user->student_id ?? 'غير متوفر' }}</p>
                            <p class="mb-0"><strong>المجموعة:</strong> {{ $payment->user->group->name ?? 'غير متوفر' }}</p>
                        </div>
                    </div>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>الوصف</th>
                                    <th>طريقة الدفع</th>
                                    <th>المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $payment->description }}</td>
                                    <td>
                                        @if($payment->payment_method == 'credit_card')
                                            بطاقة ائتمان
                                        @elseif($payment->payment_method == 'bank_transfer')
                                            تحويل بنكي
                                        @elseif($payment->payment_method == 'cash')
                                            نقدي
                                        @else
                                            {{ $payment->payment_method }}
                                        @endif
                                    </td>
                                    <td>{{ number_format($payment->amount, 2) }} ر.س</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-start">الإجمالي</th>
                                    <th>{{ number_format($payment->amount, 2) }} ر.س</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <p class="mb-0"><strong>ملاحظات:</strong> {{ $payment->notes ?? 'لا توجد ملاحظات' }}</p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted mb-0 small">الرقم المرجعي: {{ md5($payment->id . $payment->created_at) }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted mb-0 small">تم إصدار هذا الإيصال إلكترونيًا وهو معتمد بدون توقيع</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button class="btn btn-primary me-2" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> طباعة الإيصال
                </button>
                <a href="{{ route('fees') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> العودة للرسوم
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt-card, #receipt-card * {
            visibility: visible;
        }
        #receipt-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: auto;
            margin: 0;
            padding: 15px;
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@endsection 