@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.requests') }}">الطلبات الإدارية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">الرد على الطلب</li>
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

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2 text-primary"></i>تفاصيل الطلب
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">اسم الطالب:</p>
                            <p class="fw-bold">{{ $request->student->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">تاريخ التقديم:</p>
                            <p class="fw-bold">{{ $request->request_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">نوع الطلب:</p>
                            <p class="fw-bold">{{ $request->type_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted">الأولوية:</p>
                            <p class="fw-bold">
                                @if($request->priority == 'urgent')
                                    <span class="badge bg-danger">عاجل</span>
                                @elseif($request->priority == 'high')
                                    <span class="badge bg-warning">مرتفع</span>
                                @elseif($request->priority == 'normal')
                                    <span class="badge bg-info">عادي</span>
                                @else
                                    <span class="badge bg-secondary">منخفض</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <p class="mb-1 text-muted">تفاصيل الطلب:</p>
                            <div class="p-3 bg-light rounded">
                                <span class="text-dark fw-bold">{{ $request->details }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($request->attachment_path)
                    <div class="row mt-3">
                        <div class="col-12">
                            <p class="mb-1 text-muted">المرفقات:</p>
                            <div class="p-3 bg-light rounded">
                                <a href="{{ asset('storage/' . $request->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> تحميل المرفق
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <p class="mb-1 text-muted">الحالة الحالية:</p>
                            <p class="fw-bold">
                                @if($request->status == 'pending')
                                    <span class="badge bg-warning">قيد المعالجة</span>
                                @elseif($request->status == 'approved')
                                    <span class="badge bg-success">مقبول</span>
                                @elseif($request->status == 'rejected')
                                    <span class="badge bg-danger">مرفوض</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-reply me-2 text-primary"></i>الرد على الطلب
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.requests.update', $request) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">تغيير الحالة</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>قيد المعالجة</option>
                                <option value="approved" {{ $request->status == 'approved' ? 'selected' : '' }}>مقبول</option>
                                <option value="rejected" {{ $request->status == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="admin_comment" class="form-label fw-bold">الرد</label>
                            <textarea class="form-control" id="admin_comment" name="admin_comment" rows="5" placeholder="أدخل ردك على الطلب هنا...">{{ $request->admin_comment }}</textarea>
                            <small class="text-muted">سيتم إرسال هذا الرد إلى الطالب</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.requests') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-1"></i> العودة للطلبات
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> حفظ وإرسال الرد
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 