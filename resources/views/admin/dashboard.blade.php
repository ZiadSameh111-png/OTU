@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1 text-primary fw-bold">
                <i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم
            </h1>
            <p class="text-muted fs-5">مرحباً بك في لوحة التحكم، {{ Auth::user()->name }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 1. قسم الإحصائيات العامة - Statistics Overview -->
    <div class="row mb-4">
        <!-- عدد الطلاب -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-user-graduate text-primary fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['studentsCount'] }}</h3>
                            <p class="text-muted mb-0">طالب</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('admin.students') }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- عدد الدكاترة -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-chalkboard-teacher text-success fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['teachersCount'] }}</h3>
                            <p class="text-muted mb-0">دكتور</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('admin.teachers') }}" class="btn btn-sm btn-outline-success w-100">
                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- عدد المقررات -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-book text-warning fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['coursesCount'] }}</h3>
                            <p class="text-muted mb-0">مقرر</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('admin.courses') }}" class="btn btn-sm btn-outline-warning w-100">
                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- عدد المجموعات -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon bg-orange bg-opacity-10 p-3 rounded-circle me-3" style="--bs-bg-opacity: .1;">
                            <i class="fas fa-users text-orange fs-3" style="color: #fd7e14;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['groupsCount'] }}</h3>
                            <p class="text-muted mb-0">مجموعة</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('admin.groups') }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- 2. قسم الطلبات الإدارية المعلقة - Pending Administrative Requests -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2 text-primary"></i>الطلبات الإدارية المعلقة
                    </h5>
                    <a href="{{ route('admin.requests') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> عرض جميع الطلبات
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($pendingRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">اسم الطالب</th>
                                        <th scope="col">نوع الطلب</th>
                                        <th scope="col">الأولوية</th>
                                        <th scope="col">تاريخ التقديم</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequests as $request)
                                        <tr>
                                            <td>{{ $request->student->name }}</td>
                                            <td>{{ $request->getTypeNameAttribute() }}</td>
                                            <td>
                                                <span class="badge rounded-pill
                                                    @if($request->priority == 'urgent') bg-danger
                                                    @elseif($request->priority == 'high') bg-warning
                                                    @elseif($request->priority == 'normal') bg-info
                                                    @else bg-secondary
                                                    @endif
                                                ">
                                                    {{ $request->getPriorityNameAttribute() }}
                                                </span>
                                            </td>
                                            <td>{{ $request->request_date->format('Y-m-d') }}</td>
                                            <td>
                                                <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-tasks me-1"></i> معالجة الطلب
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1355/1355961.png" alt="لا توجد طلبات" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لا توجد طلبات معلقة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. قسم حضور الدكاترة اليوم - Today's Teacher Attendance -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2 text-primary"></i>حضور الدكاترة اليوم ({{ \Carbon\Carbon::today()->format('Y-m-d') }})
                    </h5>
                    <a href="{{ route('admin.attendance') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> عرض سجل الحضور الكامل
                    </a>
                </div>
                <div class="card-body p-0">
                    @if(count($teachersAttendance) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">اسم الدكتور</th>
                                        <th scope="col">حالة الحضور</th>
                                        <th scope="col">الملاحظات</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teachersAttendance as $attendance)
                                        <tr>
                                            <td>{{ $attendance['teacher']->name }}</td>
                                            <td>
                                                @if($attendance['status'] == 'present')
                                                    <span class="badge bg-success">حاضر</span>
                                                @elseif($attendance['status'] == 'absent')
                                                    <span class="badge bg-danger">غائب</span>
                                                @else
                                                    <span class="badge bg-secondary">غير مسجل</span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance['notes'] ?? '-' }}</td>
                                            <td>
                                                @if($attendance['status'] == 'not_recorded')
                                                    <a href="{{ route('admin.attendance.create', ['teacher_id' => $attendance['teacher']->id]) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-user-check me-1"></i> تسجيل حضور
                                                    </a>
                                                @else
                                                    <a href="{{ route('admin.attendance.edit', $attendance['attendance_id']) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-edit me-1"></i> تعديل
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2790/2790445.png" alt="لا يوجد تسجيل" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لم يتم تسجيل حضور اليوم بعد</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4. قسم الرسوم الدراسية المستحقة - Due Fees Overview -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-check-alt me-2 text-primary"></i>الرسوم الدراسية المستحقة
                    </h5>
                    <a href="{{ route('admin.fees') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> عرض جميع الرسوم
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($dueFeesStudents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">اسم الطالب</th>
                                        <th scope="col">المبلغ المستحق</th>
                                        <th scope="col">المبلغ المدفوع</th>
                                        <th scope="col">المبلغ المتبقي</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dueFeesStudents as $fee)
                                        <tr>
                                            <td>{{ $fee->student_name }}</td>
                                            <td>{{ number_format($fee->total_amount, 2) }} ريال</td>
                                            <td>{{ number_format($fee->paid_amount, 2) }} ريال</td>
                                            <td class="text-danger fw-bold">{{ number_format($fee->remaining_amount, 2) }} ريال</td>
                                            <td>
                                                <a href="{{ route('admin.fees.payments.create', $fee->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-money-bill-wave me-1"></i> تسجيل دفعة
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" alt="لا توجد رسوم مستحقة" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لا توجد رسوم مستحقة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- 5. قسم الرسائل الأخيرة - Recent Messages -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>الرسائل الأخيرة
                    </h5>
                    <div>
                        <a href="{{ route('admin.messages.create') }}" class="btn btn-sm btn-success me-2">
                            <i class="fas fa-paper-plane me-1"></i> إرسال رسالة جديدة
                        </a>
                        <a href="{{ route('admin.messages') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> عرض جميع الرسائل
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentMessages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">المستلم</th>
                                        <th scope="col">نوع المستلم</th>
                                        <th scope="col">عنوان الرسالة</th>
                                        <th scope="col">تاريخ الإرسال</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMessages as $message)
                                        <tr>
                                            <td>{{ $message->receiver_name }}</td>
                                            <td>
                                                @if($message->receiver_type == 'Student')
                                                    <span class="badge bg-primary">طالب</span>
                                                @elseif($message->receiver_type == 'Teacher')
                                                    <span class="badge bg-success">دكتور</span>
                                                @elseif($message->receiver_type == 'Admin')
                                                    <span class="badge bg-danger">إداري</span>
                                                @else
                                                    <span class="badge bg-secondary">مستخدم</span>
                                                @endif
                                            </td>
                                            <td>{{ $message->subject }}</td>
                                            <td>{{ \Carbon\Carbon::parse($message->created_at)->format('Y-m-d H:i A') }}</td>
                                            <td>
                                                <a href="{{ route('admin.messages.show', $message->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3829/3829933.png" alt="لا توجد رسائل" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لم ترسل أي رسائل بعد</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- 6. قسم الإشعارات السريعة - Quick Notifications -->
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 100;">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2 text-primary"></i>الإشعارات السريعة
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(count($quickNotifications) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($quickNotifications as $notification)
                                <li class="list-group-item d-flex align-items-start">
                                    <div class="notification-icon me-3 mt-1">
                                        @if($notification['type'] == 'request')
                                            <i class="fas fa-clipboard-list fs-5 text-primary"></i>
                                        @elseif($notification['type'] == 'attendance')
                                            <i class="fas fa-user-clock fs-5 text-danger"></i>
                                        @elseif($notification['type'] == 'fee')
                                            <i class="fas fa-money-bill-wave fs-5 text-success"></i>
                                        @else
                                            <i class="fas fa-bell fs-5 text-secondary"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1">{{ $notification['message'] }}</p>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">{{ $notification['date']->diffForHumans() }}</small>
                                            <a href="{{ $notification['link'] }}" class="btn btn-sm btn-link p-0">عرض التفاصيل</a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="p-3">
                            <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-external-link-alt me-1"></i> عرض جميع الإشعارات
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2098/2098565.png" alt="لا توجد إشعارات" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لا توجد إشعارات جديدة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Other scripts can go here
</script>
@endpush

@push('styles')
<style>
    /* Basic styling improvements */
    .stats-icon {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
        transition: all 0.3s ease;
    }
    
    .text-orange {
        color: #fd7e14;
    }
    
    .bg-orange {
        background-color: #fd7e14;
    }

    /* Font size improvements */
    h1.fw-bold {
        font-size: 2.5rem;
        margin-bottom: 0.75rem !important;
    }
    
    p.fs-5 {
        font-size: 1.15rem !important;
        opacity: 0.8;
    }
    
    .card h5 {
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .card h3 {
        font-size: 2.2rem;
    }

    /* Enhanced spacing and layout */
    .card {
        margin-bottom: 1.8rem;
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05) !important;
    }
    
    .card:hover {
        transform: translateY(-7px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12) !important;
    }
    
    .card-header {
        border-bottom: none;
        padding: 1.5rem 1.75rem;
        border-radius: 15px 15px 0 0 !important;
        background-color: rgba(248, 249, 250, 0.5) !important;
    }
    
    .card-body {
        padding: 1.75rem;
    }
    
    .card-body.p-0 {
        padding: 0 !important;
    }
    
    .card-body.d-flex {
        padding: 1.75rem;
    }
    
    .table th {
        font-weight: 700;
        padding: 1.2rem 1.5rem;
        font-size: 0.95rem;
        text-transform: uppercase;
        color: #495057;
        background-color: rgba(248, 249, 250, 0.7);
    }
    
    .table td {
        padding: 1.2rem 1.5rem;
        vertical-align: middle;
        font-size: 1rem;
    }
    
    /* Button styling */
    .btn {
        font-weight: 500;
        border-radius: 10px;
        padding: 0.5rem 1.25rem;
        transition: all 0.3s ease;
    }
    
    .btn-sm {
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
        border-radius: 8px;
    }
    
    .btn-outline-primary {
        border-width: 2px;
    }
    
    .btn-outline-primary:hover {
        background-color: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .btn-primary {
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
    }
    
    .btn-primary:hover {
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.3);
    }
    
    /* Improved responsive layout */
    @media (max-width: 991.98px) {
        .col-lg-8, .col-lg-4 {
            width: 100%;
        }
        
        .sticky-top {
            position: relative !important;
            top: 0 !important;
        }
    }
    
    @media (max-width: 767.98px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
            padding: 1.25rem;
        }
        
        .card-header > div, 
        .card-header > a {
            margin-top: 0.75rem;
            width: 100%;
        }
        
        .table-responsive {
            border-radius: 0 0 15px 15px;
        }
        
        h1.fw-bold {
            font-size: 2rem;
        }
        
        .col-md-3 {
            width: 50%;
        }
        
        .card-body.d-flex {
            padding: 1.25rem;
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
        }
    }
    
    @media (max-width: 575.98px) {
        .col-md-3 {
            width: 100%;
        }
        
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }
    
    /* Enhanced notification styling */
    .list-group-item {
        padding: 1.25rem 1.75rem;
        border-left: none;
        border-right: none;
        transition: all 0.3s ease;
        border-color: rgba(0, 0, 0, 0.05);
    }
    
    .list-group-item:hover {
        background-color: rgba(13, 110, 253, 0.04);
    }
    
    .notification-icon {
        min-width: 40px;
        text-align: center;
        font-size: 1.4rem;
    }
    
    .notification-icon i {
        filter: drop-shadow(0 3px 5px rgba(0, 0, 0, 0.1));
    }
    
    /* Improved card section spacing */
    .row > [class*="col-"] {
        margin-bottom: 1.5rem;
    }
    
    /* Animated elements */
    .stats-icon i {
        transition: all 0.3s ease;
        font-size: 1.75rem !important;
    }
    
    .card:hover .stats-icon i {
        transform: scale(1.2);
    }
    
    .card:hover .stats-icon {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    /* Enhanced modal styling */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.5rem 1.75rem;
    }
    
    .modal-body {
        padding: 1.75rem;
    }
    
    .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.5rem 1.75rem;
    }
    
    /* Additional enhancements */
    .badge {
        padding: 0.5em 0.8em;
        font-weight: 500;
    }
    
    .rounded-pill {
        border-radius: 50rem !important;
    }
    
    .py-5 {
        padding-top: 3.5rem !important;
        padding-bottom: 3.5rem !important;
    }
    
    .alert {
        border-radius: 12px;
        padding: 1rem 1.25rem;
        border: none;
        margin-bottom: 1.5rem;
    }
    
    .bg-light {
        background-color: rgba(248, 249, 250, 0.7) !important;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.04);
    }
    
    .text-muted {
        color: #6c757d !important;
    }
</style>
@endpush 