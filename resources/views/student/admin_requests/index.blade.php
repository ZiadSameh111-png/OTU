@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">الطلبات الإدارية</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-file-alt me-2"></i>الطلبات الإدارية
            </h1>
            <p class="text-muted">تقديم ومتابعة الطلبات الإدارية</p>
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
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تقديم طلب جديد</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#newRequestForm">
                        <i class="fas fa-plus-circle me-1"></i> طلب جديد
                    </button>
                </div>
                <div class="card-body collapse" id="newRequestForm">
                    <form action="{{ route('student.admin-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">نوع الطلب <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="" selected disabled>اختر نوع الطلب...</option>
                                    <option value="absence_excuse">عذر غياب</option>
                                    <option value="certificate_request">طلب شهادة</option>
                                    <option value="schedule_change">تغيير الجدول الدراسي</option>
                                    <option value="personal_info_update">تحديث البيانات الشخصية</option>
                                    <option value="financial_aid">مساعدة مالية</option>
                                    <option value="other">طلب آخر</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">الأولوية</label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                    <option value="normal" selected>عادية</option>
                                    <option value="medium">متوسطة</option>
                                    <option value="high">عالية</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="details" class="form-label">تفاصيل الطلب <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="5" required></textarea>
                            @error('details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="attachment" class="form-label">مرفقات (اختياري)</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror" id="attachment" name="attachment">
                            <div class="form-text">يمكنك إرفاق ملف PDF أو صورة متعلقة بالطلب (الحد الأقصى 5 ميجابايت)</div>
                            @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> تقديم الطلب
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">قائمة الطلبات المقدمة</h5>
                    <div class="d-flex">
                        <select class="form-select form-select-sm me-2" id="status-filter" style="width: auto">
                            <option value="all" selected>جميع الحالات</option>
                            <option value="pending">قيد المراجعة</option>
                            <option value="in_progress">قيد المعالجة</option>
                            <option value="completed">مكتمل</option>
                            <option value="rejected">مرفوض</option>
                        </select>
                        <div class="input-group" style="width: 200px;">
                            <input type="text" class="form-control form-control-sm" placeholder="بحث..." id="request-search">
                            <button class="btn btn-sm btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(isset($requests) && $requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>نوع الطلب</th>
                                        <th>تاريخ التقديم</th>
                                        <th>الحالة</th>
                                        <th>آخر تحديث</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr>
                                            <td>#{{ $request->id }}</td>
                                            <td>
                                                @if($request->type == 'absence_excuse')
                                                    <span class="badge bg-info">عذر غياب</span>
                                                @elseif($request->type == 'certificate_request')
                                                    <span class="badge bg-secondary">طلب شهادة</span>
                                                @elseif($request->type == 'schedule_change')
                                                    <span class="badge bg-warning text-dark">تغيير الجدول</span>
                                                @elseif($request->type == 'personal_info_update')
                                                    <span class="badge bg-primary">تحديث البيانات</span>
                                                @elseif($request->type == 'financial_aid')
                                                    <span class="badge bg-success">مساعدة مالية</span>
                                                @else
                                                    <span class="badge bg-dark">طلب آخر</span>
                                                @endif
                                                {{ $request->type_name }}
                                            </td>
                                            <td>{{ $request->request_date->format('Y-m-d') }}</td>
                                            <td>
                                                @if($request->status == 'pending')
                                                    <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                                @elseif($request->status == 'in_progress')
                                                    <span class="badge bg-info">قيد المعالجة</span>
                                                @elseif($request->status == 'completed')
                                                    <span class="badge bg-success">مكتمل</span>
                                                @elseif($request->status == 'rejected')
                                                    <span class="badge bg-danger">مرفوض</span>
                                                @endif
                                            </td>
                                            <td>{{ $request->updated_at->diffForHumans() }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal{{ $request->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                @if($request->status == 'pending')
                                                    <form action="{{ route('student.admin-requests.destroy', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal for Viewing Request Details -->
                                        <div class="modal fade" id="viewModal{{ $request->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $request->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewModalLabel{{ $request->id }}">
                                                            تفاصيل الطلب #{{ $request->id }}
                                                            @if($request->status == 'pending')
                                                                <span class="badge bg-warning text-dark ms-2">قيد المراجعة</span>
                                                            @elseif($request->status == 'in_progress')
                                                                <span class="badge bg-info ms-2">قيد المعالجة</span>
                                                            @elseif($request->status == 'completed')
                                                                <span class="badge bg-success ms-2">مكتمل</span>
                                                            @elseif($request->status == 'rejected')
                                                                <span class="badge bg-danger ms-2">مرفوض</span>
                                                            @endif
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-4">
                                                            <div class="col-md-6">
                                                                <p class="mb-1"><strong>نوع الطلب:</strong> {{ $request->type_name }}</p>
                                                                <p class="mb-1"><strong>تاريخ التقديم:</strong> {{ $request->request_date->format('Y-m-d') }}</p>
                                                                <p class="mb-1"><strong>الأولوية:</strong> 
                                                                    @if($request->priority == 'high')
                                                                        <span class="text-danger">عالية</span>
                                                                    @elseif($request->priority == 'medium')
                                                                        <span class="text-warning">متوسطة</span>
                                                                    @else
                                                                        <span class="text-secondary">عادية</span>
                                                                    @endif
                                                                </p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1"><strong>الحالة:</strong> {{ $request->status_name }}</p>
                                                                <p class="mb-1"><strong>تاريخ آخر تحديث:</strong> {{ $request->updated_at->format('Y-m-d H:i') }}</p>
                                                                @if($request->admin)
                                                                    <p class="mb-1"><strong>تمت المعالجة بواسطة:</strong> {{ $request->admin->name }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="card mb-3">
                                                            <div class="card-header bg-light">
                                                                <h6 class="mb-0">تفاصيل الطلب</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <p class="mb-0">{{ $request->details }}</p>
                                                            </div>
                                                        </div>
                                                        
                                                        @if($request->attachment)
                                                            <div class="card mb-3">
                                                                <div class="card-header bg-light">
                                                                    <h6 class="mb-0">المرفقات</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <a href="{{ asset('storage/' . $request->attachment) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                        <i class="fas fa-download me-1"></i> عرض المرفق
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        
                                                        @if($request->admin_comment)
                                                            <div class="card mb-3">
                                                                <div class="card-header bg-light">
                                                                    <h6 class="mb-0">تعليق الإدارة</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <p class="mb-0">{{ $request->admin_comment }}</p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Status Timeline -->
                                                        <div class="timeline mt-4">
                                                            <div class="timeline-item {{ $request->status == 'pending' || $request->status == 'in_progress' || $request->status == 'completed' || $request->status == 'rejected' ? 'done' : '' }}">
                                                                <div class="timeline-marker"></div>
                                                                <div class="timeline-content">
                                                                    <h6 class="timeline-title">تم تقديم الطلب</h6>
                                                                    <p class="timeline-text">{{ $request->request_date->format('Y-m-d H:i') }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="timeline-item {{ $request->status == 'in_progress' || $request->status == 'completed' || $request->status == 'rejected' ? 'done' : '' }}">
                                                                <div class="timeline-marker"></div>
                                                                <div class="timeline-content">
                                                                    <h6 class="timeline-title">تمت المراجعة</h6>
                                                                    <p class="timeline-text">
                                                                        @if($request->status == 'in_progress' || $request->status == 'completed' || $request->status == 'rejected')
                                                                            تمت مراجعة الطلب
                                                                        @else
                                                                            قيد الانتظار
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="timeline-item {{ $request->status == 'completed' || $request->status == 'rejected' ? 'done' : '' }}">
                                                                <div class="timeline-marker"></div>
                                                                <div class="timeline-content">
                                                                    <h6 class="timeline-title">
                                                                        @if($request->status == 'rejected')
                                                                            تم رفض الطلب
                                                                        @else
                                                                            معالجة الطلب
                                                                        @endif
                                                                    </h6>
                                                                    <p class="timeline-text">
                                                                        @if($request->status == 'completed' || $request->status == 'rejected')
                                                                            تمت معالجة الطلب
                                                                        @else
                                                                            قيد الانتظار
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            @if($request->status != 'rejected')
                                                                <div class="timeline-item {{ $request->status == 'completed' ? 'done' : '' }}">
                                                                    <div class="timeline-marker"></div>
                                                                    <div class="timeline-content">
                                                                        <h6 class="timeline-title">اكتمال الطلب</h6>
                                                                        <p class="timeline-text">
                                                                            @if($request->status == 'completed')
                                                                                تم الانتهاء من معالجة الطلب بنجاح
                                                                            @else
                                                                                قيد الانتظار
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                        @if($request->status == 'completed' && $request->type == 'certificate_request')
                                                            <a href="{{ route('student.admin-requests.download-certificate', $request->id) }}" class="btn btn-primary">
                                                                <i class="fas fa-download me-1"></i> تحميل الشهادة
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center p-3">
                            {{ $requests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076445.png" alt="لا توجد طلبات" style="width: 100px; opacity: 0.6;">
                            <p class="mt-3 text-muted">لا توجد طلبات مقدمة حالياً</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#newRequestForm">
                                <i class="fas fa-plus-circle me-1"></i> تقديم طلب جديد
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 15px;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 25px;
    }
    
    .timeline-marker {
        position: absolute;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #e9ecef;
        background: #fff;
        left: -22px;
        top: 4px;
    }
    
    .timeline-item.done .timeline-marker {
        border-color: #28a745;
        background: #28a745;
    }
    
    .timeline-content {
        padding-bottom: 10px;
    }
    
    .timeline-title {
        margin-top: 0;
        margin-bottom: 5px;
        font-weight: 500;
    }
    
    .timeline-text {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status Filter
        const statusFilter = document.getElementById('status-filter');
        const requestRows = document.querySelectorAll('tbody tr');
        
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value;
            
            requestRows.forEach(row => {
                const statusCell = row.querySelector('td:nth-child(4)');
                const statusText = statusCell.textContent.trim();
                
                if (selectedStatus === 'all') {
                    row.style.display = '';
                } else if (selectedStatus === 'pending' && statusText.includes('قيد المراجعة')) {
                    row.style.display = '';
                } else if (selectedStatus === 'in_progress' && statusText.includes('قيد المعالجة')) {
                    row.style.display = '';
                } else if (selectedStatus === 'completed' && statusText.includes('مكتمل')) {
                    row.style.display = '';
                } else if (selectedStatus === 'rejected' && statusText.includes('مرفوض')) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Search Functionality
        const searchInput = document.getElementById('request-search');
        
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            requestRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                
                if (rowText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Type-specific fields
        const requestType = document.getElementById('type');
        
        requestType.addEventListener('change', function() {
            // Here you can add logic to show/hide specific fields based on request type
            // For example:
            // if (this.value === 'absence_excuse') {
            //     document.getElementById('absence-date-field').style.display = 'block';
            // } else {
            //     document.getElementById('absence-date-field').style.display = 'none';
            // }
        });
    });
</script>
@endpush

@endsection 