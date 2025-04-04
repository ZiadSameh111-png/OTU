@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم
            </h1>
            <p class="text-muted">مرحباً {{ auth()->user()->name }}، هذه لوحة التحكم الخاصة بالإدارة</p>
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

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-user-graduate text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">الطلاب</h6>
                        <h3 class="mb-0">{{ $studentsCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-chalkboard-teacher text-success fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">المعلمين</h6>
                        <h3 class="mb-0">{{ $teachersCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-book text-warning fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">المقررات</h6>
                        <h3 class="mb-0">{{ $coursesCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-users text-info fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">المجموعات</h6>
                        <h3 class="mb-0">{{ $groupsCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Requests -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2 text-primary"></i>الطلبات المعلقة
                    </h5>
                    <a href="{{ route('admin.requests') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    @if(isset($pendingRequests) && $pendingRequests->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($pendingRequests as $request)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $request->type_name }}</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $request->student->name }} - {{ $request->request_date->format('Y-m-d') }}
                                            </p>
                                        </div>
                                        <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i> عرض
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/3342/3342137.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد طلبات">
                            <p class="text-muted">لا توجد طلبات معلقة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day me-2 text-primary"></i>جدول اليوم
                    </h5>
                    <a href="{{ route('admin.schedules') }}" class="btn btn-sm btn-outline-primary">الجدول الكامل</a>
                </div>
                <div class="card-body">
                    @if(isset($todaySchedule) && $todaySchedule->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>المقرر</th>
                                        <th>المعلم</th>
                                        <th>المجموعة</th>
                                        <th>الوقت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaySchedule as $schedule)
                                        <tr>
                                            <td>{{ $schedule->course->name }}</td>
                                            <td>{{ $schedule->course->teacher->name ?? 'غير محدد' }}</td>
                                            <td>{{ $schedule->group->name ?? 'غير محدد' }}</td>
                                            <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/3176/3176395.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد محاضرات">
                            <p class="text-muted">لا توجد محاضرات مجدولة لهذا اليوم</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Attendance Summary -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2 text-primary"></i>إحصائيات الحضور
                    </h5>
                    <a href="{{ route('admin.attendance') }}" class="btn btn-sm btn-outline-primary">التفاصيل</a>
                </div>
                <div class="card-body">
                    @if(isset($attendanceSummary) && count($attendanceSummary) > 0)
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">حضور المعلمين</h6>
                                        <div class="d-flex justify-content-around">
                                            <div>
                                                <h5 class="mb-0 text-success">{{ $attendanceSummary['present'] ?? 0 }}</h5>
                                                <small class="text-muted">حاضر</small>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 text-danger">{{ $attendanceSummary['absent'] ?? 0 }}</h5>
                                                <small class="text-muted">غائب</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">معدل الحضور</h6>
                                        <div class="progress mt-2" style="height: 15px;">
                                            <div class="progress-bar bg-success" 
                                                role="progressbar" 
                                                style="width: {{ $attendanceRate ?? 0 }}%;" 
                                                aria-valuenow="{{ $attendanceRate ?? 0 }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                                {{ $attendanceRate ?? 0 }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <a href="{{ route('admin.attendance.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-1"></i> تسجيل حضور جديد
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/1791/1791961.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد بيانات">
                            <p class="text-muted mb-3">لا توجد بيانات حضور مسجلة حالياً</p>
                            <a href="{{ route('admin.attendance.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-1"></i> تسجيل حضور جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Fees Summary -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>ملخص الرسوم
                    </h5>
                    <a href="{{ route('admin.fees') }}" class="btn btn-sm btn-outline-primary">التفاصيل</a>
                </div>
                <div class="card-body">
                    @if(isset($feesSummary) && count($feesSummary) > 0)
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">إجمالي المستحقات</h6>
                                        <h5 class="mb-0">{{ number_format($feesSummary['total'] ?? 0, 2) }} ريال</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">المبالغ المحصلة</h6>
                                        <h5 class="mb-0 text-success">{{ number_format($feesSummary['collected'] ?? 0, 2) }} ريال</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">المبالغ المتبقية</h6>
                                        <h5 class="mb-0 text-danger">{{ number_format($feesSummary['remaining'] ?? 0, 2) }} ريال</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">نسبة التحصيل</h6>
                                        <h5 class="mb-0">{{ $feesSummary['collection_rate'] ?? 0 }}%</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <a href="{{ route('admin.fees.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-1"></i> تسجيل رسوم جديدة
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/2037/2037510.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد بيانات">
                            <p class="text-muted mb-3">لا توجد بيانات رسوم مسجلة حالياً</p>
                            <a href="{{ route('admin.fees.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-1"></i> تسجيل رسوم جديدة
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 