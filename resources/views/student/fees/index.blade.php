@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>الرسوم الدراسية
            </h1>
            <p class="text-muted">إدارة الرسوم الدراسية وعمليات الدفع</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">الرسوم المستحقة</h5>
                </div>
                <div class="card-body">
                    @if($fees->isEmpty())
                        <div class="text-center py-5">
                            <img src="{{ asset('images/no-data.svg') }}" alt="لا توجد رسوم" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">لا توجد رسوم مستحقة حالياً</h5>
                            <p class="text-muted small">لم يتم العثور على أي رسوم دراسية مستحقة</p>
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
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fees as $fee)
                                        @php
                                            $paid = $fee->payments->where('user_id', auth()->id())->where('status', 'completed')->sum('amount');
                                            $remaining = $fee->amount - $paid;
                                            $status = $remaining <= 0 ? 'مدفوع' : ($remaining == $fee->amount ? 'غير مدفوع' : 'مدفوع جزئياً');
                                            $statusClass = $remaining <= 0 ? 'success' : ($remaining == $fee->amount ? 'danger' : 'warning');
                                            $dueDate = $fee->due_date ? $fee->due_date->format('Y-m-d') : 'غير محدد';
                                            $isOverdue = $fee->due_date && $fee->due_date->isPast() && $remaining > 0;
                                        @endphp
                                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $fee->description }}</td>
                                            <td>
                                                {{ $dueDate }}
                                                @if($isOverdue)
                                                    <span class="badge bg-danger">متأخر</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($fee->amount, 2) }} ر.س</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                        <div class="progress-bar bg-{{ $statusClass }}" role="progressbar" style="width: {{ ($paid / $fee->amount) * 100 }}%"></div>
                                                    </div>
                                                    <span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('student.fees.show', $fee->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($remaining > 0)
                                                    <a href="{{ route('student.fees.pay', $fee->id) }}" class="btn btn-sm btn-primary">
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
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title mb-4">ملخص الرسوم</h5>
                    <div class="fee-summary">
                        <div class="mb-4">
                            <span class="d-block display-6 text-primary mb-0">{{ number_format($totalFees, 2) }}</span>
                            <span class="text-muted">إجمالي الرسوم (ر.س)</span>
                        </div>
                        <hr>
                        <div class="mb-4">
                            <span class="d-block display-6 text-success mb-0">{{ number_format($totalPaid, 2) }}</span>
                            <span class="text-muted">إجمالي المدفوع (ر.س)</span>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <span class="d-block display-6 {{ $totalFees - $totalPaid > 0 ? 'text-danger' : 'text-success' }} mb-0">{{ number_format($totalFees - $totalPaid, 2) }}</span>
                            <span class="text-muted">المبلغ المتبقي (ر.س)</span>
                        </div>
                    </div>
                    <div class="fee-actions">
                        <a href="{{ route('fees.statement') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-file-alt me-1"></i> عرض كشف حساب تفصيلي
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">معلومات هامة</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            يرجى الالتزام بدفع الرسوم في مواعيدها المحددة
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-credit-card text-primary me-2"></i>
                            يمكنك الدفع باستخدام البطاقة الائتمانية أو التحويل البنكي
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-exclamation-triangle text-primary me-2"></i>
                            التأخر في سداد الرسوم قد يؤثر على الخدمات المتاحة
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            لمزيد من المعلومات، يرجى التواصل مع قسم الشؤون المالية
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 