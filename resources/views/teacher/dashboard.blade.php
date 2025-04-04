@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم
            </h1>
            <p class="text-muted">مرحباً {{ auth()->user()->name }}، هذه لوحة التحكم الخاصة بك</p>
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-book text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">المقررات</h6>
                        <h3 class="mb-0">{{ $coursesCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-calendar-alt text-success fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">المحاضرات اليوم</h6>
                        <h3 class="mb-0">{{ $todayLecturesCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-user-check text-warning fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">نسبة الحضور</h6>
                        <h3 class="mb-0">{{ $attendanceRate ?? 0 }}%</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-envelope text-info fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">رسائل غير مقروءة</h6>
                        <h3 class="mb-0">{{ $unreadMessagesCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- My Courses -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book-open me-2 text-primary"></i>مقرراتي الدراسية
                    </h5>
                    <a href="{{ route('courses.teacher') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    @if(isset($courses) && $courses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($courses as $course)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $course->name }}</h6>
                                            <p class="text-muted small mb-0">
                                                @if($course->groups->count() > 0)
                                                    المجموعات: 
                                                    @foreach($course->groups as $group)
                                                        <span class="badge bg-light text-dark">{{ $group->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">لا توجد مجموعات مسجلة</span>
                                                @endif
                                            </p>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-light">التفاصيل</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد مقررات">
                            <p class="text-muted">لا توجد مقررات مسجلة حالياً</p>
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
                    <a href="{{ route('teacher.schedule') }}" class="btn btn-sm btn-outline-primary">الجدول الكامل</a>
                </div>
                <div class="card-body">
                    @if(isset($todaySchedule) && $todaySchedule->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>المقرر</th>
                                        <th>المجموعة</th>
                                        <th>الوقت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaySchedule as $schedule)
                                        <tr>
                                            <td>{{ $schedule->course->name }}</td>
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
                        <i class="fas fa-user-check me-2 text-primary"></i>ملخص الحضور
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($attendanceSummary) && count($attendanceSummary) > 0)
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">أيام الحضور</h6>
                                        <h5 class="mb-0 text-success">{{ $attendanceSummary['present'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">أيام الغياب</h6>
                                        <h5 class="mb-0 text-danger">{{ $attendanceSummary['absent'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-2">نسبة الحضور</h6>
                        <div class="progress" style="height: 15px;">
                            <div class="progress-bar bg-success" 
                                role="progressbar" 
                                style="width: {{ $attendanceRate ?? 0 }}%;" 
                                aria-valuenow="{{ $attendanceRate ?? 0 }}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                {{ $attendanceRate ?? 0 }}%
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/1791/1791961.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد بيانات">
                            <p class="text-muted">لا توجد بيانات حضور مسجلة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>آخر الرسائل الواردة
                    </h5>
                    <a href="{{ route('teacher.messages') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    @if(isset($recentMessages) && $recentMessages->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentMessages as $message)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 {{ $message->is_read ? '' : 'fw-bold' }}">
                                                {{ $message->subject }}
                                                @if(!$message->is_read)
                                                    <span class="badge bg-danger ms-2">جديد</span>
                                                @endif
                                            </h6>
                                            <p class="text-muted small mb-0">{{ $message->sender->name }} - {{ $message->created_at->format('Y-m-d') }}</p>
                                        </div>
                                        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#viewModal{{ $message->id }}">
                                            قراءة
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/3917/3917032.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد رسائل">
                            <p class="text-muted">لا توجد رسائل واردة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 