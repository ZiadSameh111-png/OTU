@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between">
                <h1 class="mb-0 text-primary fw-bold">
                    <i class="fas fa-file-alt me-2"></i>تفاصيل الطلب #{{ $adminRequest->id }}
                </h1>
                <div>
                    <a href="{{ route('student.requests') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i> العودة للطلبات
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">معلومات الطلب</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">رقم الطلب:</h6>
                            <p>#{{ $adminRequest->id }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">نوع الطلب:</h6>
                            <p>{{ $adminRequest->type }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">تاريخ التقديم:</h6>
                            <p>{{ $adminRequest->request_date->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">الأولوية:</h6>
                            <p>
                                @switch($adminRequest->priority)
                                    @case('normal')
                                        <span class="badge bg-info">عادية</span>
                                        @break
                                    @case('medium')
                                        <span class="badge bg-warning">متوسطة</span>
                                        @break
                                    @case('high')
                                        <span class="badge bg-danger">عالية</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">غير محدد</span>
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-muted">الحالة:</h6>
                            <p>
                                @switch($adminRequest->status)
                                    @case('pending')
                                        <span class="badge bg-warning">قيد الإنتظار</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-success">تمت الموافقة</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">مرفوض</span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-primary">مكتمل</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">غير محدد</span>
                                @endswitch
                            </p>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold text-muted">تفاصيل الطلب:</h6>
                            <div class="p-3 bg-light rounded">
                                <span class="text-dark fw-bold">{{ $adminRequest->details }}</span>
                            </div>
                        </div>
                    </div>

                    @if($adminRequest->attachment)
                    <div class="row mt-3">
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold text-muted">المرفقات:</h6>
                            <div class="p-3 bg-light rounded">
                                <a href="{{ asset('storage/' . $adminRequest->attachment) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-download me-1"></i> عرض/تحميل المرفق
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($adminRequest->admin_comment)
                    <div class="row mt-3">
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold text-muted">تعليق الإدارة:</h6>
                            <div class="p-3 bg-light rounded">
                                <span class="text-dark fw-bold">{{ $adminRequest->admin_comment }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($adminRequest->status === 'pending')
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('student.admin-requests.destroy', $adminRequest->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> إلغاء الطلب
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($adminRequest->type === 'certificate_request' && $adminRequest->status === 'completed')
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="fas fa-check-circle me-2"></i> يمكنك الآن تحميل شهادتك
                    </h5>
                    <a href="{{ route('student.admin-requests.download-certificate', $adminRequest->id) }}" class="btn btn-light">
                        <i class="fas fa-download me-1"></i> تحميل الشهادة
                    </a>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">تتبع حالة الطلب</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-paper-plane text-primary me-2"></i> تم تقديم الطلب
                            </div>
                            <small class="text-muted">{{ $adminRequest->request_date->format('Y-m-d') }}</small>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas {{ in_array($adminRequest->status, ['pending', 'approved', 'rejected', 'completed']) ? 'fa-check text-success' : 'fa-hourglass-half text-muted' }} me-2"></i> 
                                تمت المراجعة
                            </div>
                            @if($adminRequest->status !== 'pending')
                                <small class="text-muted">{{ $adminRequest->updated_at->format('Y-m-d') }}</small>
                            @else
                                <small class="text-muted">قيد الإنتظار</small>
                            @endif
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas {{ $adminRequest->status === 'completed' ? 'fa-check text-success' : 'fa-hourglass-half text-muted' }} me-2"></i> 
                                تم إكمال الطلب
                            </div>
                            @if($adminRequest->status === 'completed')
                                <small class="text-muted">{{ $adminRequest->updated_at->format('Y-m-d') }}</small>
                            @else
                                <small class="text-muted">قيد الإنتظار</small>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">تعليمات</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">يمكنك متابعة حالة طلبك من خلال هذه الصفحة.</li>
                        <li class="mb-2">في حال وجود أي استفسار، يرجى التواصل مع الإدارة.</li>
                        <li class="mb-2">يمكنك إلغاء الطلب فقط إذا كان في حالة الانتظار.</li>
                        <li>عند اكتمال الطلب، ستظهر لك الإجراءات المناسبة.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 