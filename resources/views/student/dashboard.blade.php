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
                        <h6 class="mb-1">جدول هذا الأسبوع</h6>
                        <h3 class="mb-0">{{ $weeklyScheduleCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-file-alt text-warning fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">الطلبات</h6>
                        <h3 class="mb-0">{{ $pendingRequestsCount ?? 0 }}</h3>
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
        <!-- Latest Courses -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book-open me-2 text-primary"></i>المقررات الدراسية
                    </h5>
                    <a href="{{ route('courses.student') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    @if(isset($courses) && $courses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($courses as $course)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $course->name }}</h6>
                                            <p class="text-muted small mb-0">{{ $course->teacher->name ?? 'غير محدد' }}</p>
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
                    <a href="{{ route('student.schedule') }}" class="btn btn-sm btn-outline-primary">الجدول الكامل</a>
                </div>
                <div class="card-body">
                    @if(isset($todaySchedule) && $todaySchedule->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>المقرر</th>
                                        <th>الوقت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaySchedule as $schedule)
                                        <tr>
                                            <td>{{ $schedule->course->name }}</td>
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
        
        <!-- Fees Summary -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>ملخص الرسوم
                    </h5>
                    <a href="{{ route('student.fees') }}" class="btn btn-sm btn-outline-primary">التفاصيل</a>
                </div>
                <div class="card-body">
                    @if(isset($fee))
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">المبلغ المستحق</h6>
                                        <h5 class="mb-0">{{ number_format($fee->total_amount, 2) }} ريال</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">المبلغ المتبقي</h6>
                                        <h5 class="mb-0 {{ $fee->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($fee->remaining_amount, 2) }} ريال
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6 class="mb-2">نسبة الدفع</h6>
                            <div class="progress" style="height: 15px;">
                                <div class="progress-bar {{ $fee->payment_percentage < 100 ? 'bg-warning' : 'bg-success' }}" 
                                    role="progressbar" 
                                    style="width: {{ $fee->payment_percentage }}%;" 
                                    aria-valuenow="{{ $fee->payment_percentage }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    {{ round($fee->payment_percentage) }}%
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/2037/2037510.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد بيانات">
                            <p class="text-muted">لا توجد بيانات رسوم متاحة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Requests -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2 text-primary"></i>طلباتي الأخيرة
                    </h5>
                    <a href="{{ route('student.requests') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    @if(isset($requests) && $requests->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($requests as $request)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $request->type_name }}</h6>
                                            <p class="text-muted small mb-0">{{ $request->request_date->format('Y-m-d') }}</p>
                                        </div>
                                        <span class="badge 
                                            @if($request->status == 'pending') bg-warning
                                            @elseif($request->status == 'approved') bg-success
                                            @elseif($request->status == 'rejected') bg-danger
                                            @endif">
                                            @if($request->status == 'pending') قيد المعالجة
                                            @elseif($request->status == 'approved') مقبول
                                            @elseif($request->status == 'rejected') مرفوض
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/3342/3342137.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد طلبات">
                            <p class="text-muted">لم تقم بتقديم أي طلبات بعد</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 