@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.fees') }}">إدارة الرسوم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.fees.payments', $fee) }}">مدفوعات الطالب {{ $student->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تسجيل دفعة جديدة</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>تسجيل دفعة جديدة
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <!-- بيانات الطالب والرسوم -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-2"><i class="fas fa-user me-2"></i>الطالب: {{ $student->name }}</h6>
                                <p class="mb-0 text-muted small">{{ $student->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2"><i class="fas fa-receipt me-2"></i>تفاصيل الرسوم</h6>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <p class="mb-0 small">إجمالي الرسوم: <strong>{{ number_format($fee->total_amount, 2) }} ريال</strong></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <p class="mb-0 small">المدفوع: <strong>{{ number_format($fee->paid_amount, 2) }} ريال</strong></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <p class="mb-0 small">المتبقي: <strong>{{ number_format($remainingAmount, 2) }} ريال</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.fees.payments.store', $fee) }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="amount" class="form-label">مبلغ الدفعة (ريال) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" step="0.01" min="1" max="{{ $remainingAmount }}" value="{{ old('amount') }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">أقصى مبلغ يمكن دفعه هو {{ number_format($remainingAmount, 2) }} ريال</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="payment_date" class="form-label">تاريخ الدفع <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('admin.fees.payments', $fee) }}" class="btn btn-light me-2">
                                <i class="fas fa-times me-1"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> تسجيل الدفعة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 