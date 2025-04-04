@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>كشف حساب الرسوم الدراسية
            </h1>
            <p class="text-muted">عرض تفصيلي لحالة الرسوم الدراسية والمدفوعات</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="card-title">معلومات الطالب</h5>
                            <p class="mb-1"><strong>الاسم:</strong> {{ $user->name }}</p>
                            <p class="mb-1"><strong>الرقم الجامعي:</strong> {{ $user->student_id ?? 'غير متوفر' }}</p>
                            <p class="mb-0"><strong>المجموعة:</strong> {{ $user->group->name ?? 'غير مسجل' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5 class="card-title">ملخص الحساب</h5>
                            <p class="mb-1"><strong>إجمالي الرسوم:</strong> {{ number_format($totalFees, 2) }} ر.س</p>
                            <p class="mb-1"><strong>إجمالي المدفوع:</strong> {{ number_format($totalPaid, 2) }} ر.س</p>
                            <p class="mb-0"><strong>المتبقي:</strong> 
                                <span class="{{ $totalFees - $totalPaid > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($totalFees - $totalPaid, 2) }} ر.س
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5 class="card-title">الرسوم المستحقة</h5>
                            @if($fees->isEmpty())
                                <div class="alert alert-info">
                                    لا توجد رسوم مستحقة حاليًا.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>الوصف</th>
                                                <th>تاريخ الاستحقاق</th>
                                                <th>المبلغ</th>
                                                <th>المدفوع</th>
                                                <th>المتبقي</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($fees as $fee)
                                                @php
                                                    $paid = $fee->payments->sum('amount');
                                                    $remaining = $fee->amount - $paid;
                                                    $status = $remaining <= 0 ? 'مدفوع' : ($remaining == $fee->amount ? 'غير مدفوع' : 'مدفوع جزئياً');
                                                    $statusClass = $remaining <= 0 ? 'success' : ($remaining == $fee->amount ? 'danger' : 'warning');
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $fee->description }}</td>
                                                    <td>{{ $fee->due_date->format('Y-m-d') }}</td>
                                                    <td>{{ number_format($fee->amount, 2) }} ر.س</td>
                                                    <td>{{ number_format($paid, 2) }} ر.س</td>
                                                    <td>{{ number_format($remaining, 2) }} ر.س</td>
                                                    <td>
                                                        <span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('fees.show', $fee->id) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if($remaining > 0)
                                                            <a href="{{ route('fees.pay', $fee->id) }}" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-money-bill-wave"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="card-title">سجل المدفوعات</h5>
                            @if($payments->isEmpty())
                                <div class="alert alert-info">
                                    لا توجد مدفوعات مسجلة حتى الآن.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>رقم المعاملة</th>
                                                <th>تاريخ الدفع</th>
                                                <th>طريقة الدفع</th>
                                                <th>المبلغ</th>
                                                <th>الوصف</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payments as $payment)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $payment->transaction_id ?? '-' }}</td>
                                                    <td>{{ $payment->payment_date->format('Y-m-d H:i') }}</td>
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
                                                    <td>{{ $payment->description }}</td>
                                                    <td>
                                                        <a href="{{ route('fees.receipt', $payment->id) }}" class="btn btn-sm btn-success">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12 text-center">
            <a href="{{ route('student.fees') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> العودة للرسوم
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> طباعة الكشف
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            box-shadow: none !important;
        }
        .btn, .actions {
            display: none !important;
        }
    }
</style>
@endpush

@endsection 