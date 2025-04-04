@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Row -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0 text-primary fw-bold">
                <i class="fas fa-chalkboard-teacher me-2"></i>لوحة التحكم
            </h1>
            <p class="text-muted">مرحباً بك استاذ {{ Auth::user()->name }}</p>
        </div>
    </div>

    <div class="row mb-4">
        <!-- أقسام البطاقات الإحصائية -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-book text-primary fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['coursesCount'] }}</h3>
                            <p class="text-muted mb-0">المقررات</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('courses.teacher') }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-external-link-alt me-1"></i> عرض المقررات
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-users text-success fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['groupsCount'] }}</h3>
                            <p class="text-muted mb-0">المجموعات</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('teacher.groups') }}" class="btn btn-sm btn-outline-success w-100">
                            <i class="fas fa-external-link-alt me-1"></i> عرض المجموعات
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-calendar-alt text-warning fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['sessionsCount'] }}</h3>
                            <p class="text-muted mb-0">المحاضرات (اليوم)</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('teacher.schedule') }}" class="btn btn-sm btn-outline-warning w-100">
                            <i class="fas fa-external-link-alt me-1"></i> جدول المحاضرات
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-envelope text-danger fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['unreadMessages'] }}</h3>
                            <p class="text-muted mb-0">رسائل جديدة</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('teacher.messages') }}" class="btn btn-sm btn-outline-danger w-100">
                            <i class="fas fa-external-link-alt me-1"></i> عرض الرسائل
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- 1. قسم محاضرات اليوم - Today's Schedule -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day me-2 text-primary"></i>محاضرات اليوم ({{ \Carbon\Carbon::today()->format('Y-m-d') }})
                    </h5>
                    <a href="{{ route('teacher.schedule') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> عرض الجدول الكامل
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($todaySchedule->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">المقرر</th>
                                        <th scope="col">المجموعة</th>
                                        <th scope="col">القاعة</th>
                                        <th scope="col">من</th>
                                        <th scope="col">إلى</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaySchedule as $session)
                                        <tr>
                                            <td>{{ $session->course->name }}</td>
                                            <td>{{ $session->group->name }}</td>
                                            <td>{{ $session->classroom ?? 'غير محدد' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('teacher.attendance.create', ['schedule_id' => $session->id]) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-user-check me-1"></i> تسجيل الحضور
                                                    </a>
                                                    <a href="{{ route('teacher.schedule.show', $session->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-info-circle me-1"></i> التفاصيل
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3176/3176395.png" alt="لا توجد محاضرات" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لا توجد محاضرات مجدولة لهذا اليوم</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. قسم حضور الطلاب - Recent Student Attendance -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2 text-primary"></i>آخر سجلات حضور الطلاب
                    </h5>
                    <a href="{{ route('teacher.attendance') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> عرض جميع السجلات
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentAttendance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">التاريخ</th>
                                        <th scope="col">المقرر</th>
                                        <th scope="col">المجموعة</th>
                                        <th scope="col">عدد الحضور</th>
                                        <th scope="col">عدد الغياب</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAttendance as $attendance)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}</td>
                                            <td>{{ $attendance->schedule->course->name }}</td>
                                            <td>{{ $attendance->schedule->group->name }}</td>
                                            <td><span class="badge bg-success">{{ $attendance->present_count }}</span></td>
                                            <td><span class="badge bg-danger">{{ $attendance->absent_count }}</span></td>
                                            <td>
                                                <a href="{{ route('teacher.attendance.show', $attendance->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3588/3588294.png" alt="لا توجد سجلات" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لم تقم بتسجيل أي حضور بعد</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. قسم الرسائل الأخيرة - Recent Messages -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>آخر الرسائل
                    </h5>
                    <div>
                        <a href="{{ route('teacher.messages.create') }}" class="btn btn-sm btn-success me-2">
                            <i class="fas fa-paper-plane me-1"></i> إرسال رسالة جديدة
                        </a>
                        <a href="{{ route('teacher.messages') }}" class="btn btn-sm btn-outline-primary">
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
                                        <th scope="col">من</th>
                                        <th scope="col">العنوان</th>
                                        <th scope="col">التاريخ</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMessages as $message)
                                        <tr class="{{ $message->read_at ? '' : 'table-light fw-bold' }}">
                                            <td>{{ $message->sender->name }}</td>
                                            <td>{{ $message->subject }}</td>
                                            <td>{{ $message->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                @if($message->read_at)
                                                    <span class="badge bg-secondary">مقروءة</span>
                                                @else
                                                    <span class="badge bg-danger">جديدة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary view-message-btn" 
                                                        data-bs-toggle="modal" data-bs-target="#viewMessageModal"
                                                        data-subject="{{ $message->subject }}" 
                                                        data-body="{{ $message->body }}"
                                                        data-date="{{ $message->created_at->format('Y-m-d H:i A') }}"
                                                        data-sender="{{ $message->sender->name }}"
                                                        data-message-id="{{ $message->id }}">
                                                    <i class="fas fa-eye me-1"></i> عرض
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3829/3829933.png" alt="لا توجد رسائل" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لا توجد رسائل واردة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- 4. قسم حالة الحضور - Attendance Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2 text-primary"></i>حالة حضورك اليوم
                    </h5>
                </div>
                <div class="card-body">
                    @if($attendanceStatus)
                        <div class="text-center mb-4">
                            @if($attendanceStatus == 'present')
                                <div class="attendance-icon bg-success bg-opacity-10 p-4 rounded-circle mx-auto mb-3" style="width: 120px; height: 120px;">
                                    <i class="fas fa-user-check text-success fa-4x"></i>
                                </div>
                                <h4 class="text-success mb-1">تم تسجيل حضورك</h4>
                                <p class="text-muted">لقد تم تسجيل حضورك اليوم بنجاح</p>
                            @elseif($attendanceStatus == 'absent')
                                <div class="attendance-icon bg-danger bg-opacity-10 p-4 rounded-circle mx-auto mb-3" style="width: 120px; height: 120px;">
                                    <i class="fas fa-user-times text-danger fa-4x"></i>
                                </div>
                                <h4 class="text-danger mb-1">تم تسجيل غيابك</h4>
                                <p class="text-muted">لقد تم تسجيل غيابك اليوم من قبل الإدارة</p>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2788/2788733.png" alt="لم يتم التسجيل" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لم يتم تسجيل حضورك اليوم بعد</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 5. قسم المقررات - Your Courses Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2 text-primary"></i>مقرراتك الدراسية
                    </h5>
                    <a href="{{ route('courses.teacher') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> عرض الكل
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($courses->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($courses as $course)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $course->name }}</h6>
                                        <small class="text-muted">{{ $course->code }}</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">{{ $course->groups_count }} مجموعة</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2436/2436874.png" alt="لا توجد مقررات" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لم يتم تخصيص مقررات لك بعد</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 6. قسم الإشعارات - Quick Notifications -->
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 10;">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2 text-primary"></i>الإشعارات العاجلة
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(count($notifications) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <li class="list-group-item d-flex align-items-start">
                                    <div class="notification-icon me-3 mt-1">
                                        @if($notification['type'] == 'schedule')
                                            <i class="fas fa-calendar-alt fs-5 text-primary"></i>
                                        @elseif($notification['type'] == 'message')
                                            <i class="fas fa-envelope fs-5 text-danger"></i>
                                        @elseif($notification['type'] == 'attendance')
                                            <i class="fas fa-user-check fs-5 text-success"></i>
                                        @else
                                            <i class="fas fa-bell fs-5 text-warning"></i>
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
                            <a href="{{ route('teacher.notifications') }}" class="btn btn-outline-primary btn-sm w-100">
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

<!-- Modal for viewing message details -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" aria-labelledby="viewMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMessageModalLabel">عرض الرسالة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold">المرسل:</label>
                    <p id="modal-sender"></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">العنوان:</label>
                    <p id="modal-subject"></p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">المحتوى:</label>
                    <div id="modal-body" class="p-3 bg-light rounded"></div>
                </div>
                <div>
                    <label class="fw-bold">تاريخ الإرسال:</label>
                    <p id="modal-date" class="text-muted"></p>
                </div>
            </div>
            <div class="modal-footer">
                <form id="mark-as-read-form" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-success me-2">
                        <i class="fas fa-check me-1"></i> تأكيد القراءة
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Script for message modal
    document.addEventListener('DOMContentLoaded', function() {
        const viewMessageBtns = document.querySelectorAll('.view-message-btn');
        const markAsReadForm = document.getElementById('mark-as-read-form');
        
        viewMessageBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modal-subject').textContent = this.getAttribute('data-subject');
                document.getElementById('modal-body').textContent = this.getAttribute('data-body');
                document.getElementById('modal-date').textContent = this.getAttribute('data-date');
                document.getElementById('modal-sender').textContent = this.getAttribute('data-sender');
                
                // Set the form action URL with the message ID
                const messageId = this.getAttribute('data-message-id');
                markAsReadForm.action = `/teacher/messages/${messageId}/read`;
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .stats-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .attendance-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush 