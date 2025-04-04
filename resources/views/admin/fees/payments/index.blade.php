@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.fees') }}">إدارة الرسوم</a></li>
                    <li class="breadcrumb-item active" aria-current="page">سجل مدفوعات الطالب {{ $student->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>معلومات الطالب ورسومه
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-2">معلومات الطالب</h6>
                            <p class="mb-1"><strong>الاسم:</strong> {{ $student->name }}</p>
                            <p class="mb-1"><strong>البريد الإلكتروني:</strong> {{ $student->email }}</p>
                            @if($student->group)
                                <p class="mb-0"><strong>المجموعة:</strong> {{ $student->group->name }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">تفاصيل الرسوم</h6>
                            <div class="d-flex mb-2">
                                <div class="me-4">
                                    <p class="mb-0"><strong>إجمالي الرسوم:</strong> {{ number_format($fee->total_amount, 2) }} ريال</p>
                                </div>
                                <div class="me-4">
                                    <p class="mb-0"><strong>المدفوع:</strong> {{ number_format($fee->paid_amount, 2) }} ريال</p>
                                </div>
                                <div>
                                    <p class="mb-0"><strong>المتبقي:</strong> {{ number_format($fee->remaining_amount, 2) }} ريال</p>
                                </div>
                            </div>
                            
                            <!-- Progress bar for payment percentage -->
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                    style="width: {{ $fee->payment_percentage }}%;" 
                                    aria-valuenow="{{ $fee->payment_percentage }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    {{ round($fee->payment_percentage) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>سجل المدفوعات
                    </h5>
                    
                    @if($fee->remaining_amount > 0)
                    <a href="{{ route('admin.fees.payments.create', $fee) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> تسجيل دفعة جديدة
                    </a>
                    @endif
                </div>
                
                <div class="card-body p-0">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">المبلغ</th>
                                        <th scope="col">تاريخ الدفع</th>
                                        <th scope="col">المسؤول</th>
                                        <th scope="col">تاريخ التسجيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $index => $payment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ number_format($payment->amount, 2) }} ريال</td>
                                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                            <td>{{ $payment->admin->name }}</td>
                                            <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/438/438567.png" alt="لا توجد مدفوعات" style="width: 120px; opacity: 0.6;">
                            <p class="mt-3 text-muted">لا توجد أي مدفوعات مسجلة حتى الآن</p>
                            
                            @if($fee->remaining_amount > 0)
                                <a href="{{ route('admin.fees.payments.create', $fee) }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i> تسجيل دفعة جديدة
                                </a>
                            @else
                                <div class="alert alert-success mt-3 mx-auto" style="max-width: 400px;">
                                    <i class="fas fa-check-circle me-1"></i> تم دفع كامل الرسوم المطلوبة
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 