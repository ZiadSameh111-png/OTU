@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Row -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0 text-primary fw-bold">
                <i class="fas fa-user-graduate me-2"></i>لوحة التحكم
            </h1>
            <p class="text-muted">مرحباً بك {{ Auth::user()->name }}</p>
        </div>
    </div>

    <div class="row mb-4">
        <!-- البطاقات الإحصائية الخاصة بالطالب -->
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
                        <a href="{{ route('courses.student') }}" class="btn btn-sm btn-outline-primary w-100">
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
                            <i class="fas fa-calendar-alt text-success fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['sessionsCount'] }}</h3>
                            <p class="text-muted mb-0">المحاضرات (اليوم)</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('student.schedule') }}" class="btn btn-sm btn-outline-success w-100">
                            <i class="fas fa-external-link-alt me-1"></i> عرض الجدول
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
                            <i class="fas fa-clipboard-list text-warning fs-3"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['requestsCount'] }}</h3>
                            <p class="text-muted mb-0">الطلبات</p>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <a href="{{ route('student.requests') }}" class="btn btn-sm btn-outline-warning w-100">
                            <i class="fas fa-external-link-alt me-1"></i> عرض الطلبات
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
                        <a href="{{ route('student.messages') }}" class="btn btn-sm btn-outline-danger w-100">
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
                    <a href="{{ route('student.schedule') }}" class="btn btn-sm btn-outline-primary">
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
                                        <th scope="col">الدكتور</th>
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
                                            <td>{{ $session->course->teacher->name ?? 'غير محدد' }}</td>
                                            <td>{{ $session->classroom ?? 'غير محدد' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}</td>
                                            <td>
                                                <a href="{{ route('student.courses.show', $session->course->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-info-circle me-1"></i> تفاصيل المقرر
                                                </a>
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

            <!-- 2. قسم مقرراتك الدراسية - Your Courses -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book-open me-2 text-primary"></i>مقرراتك الدراسية
                    </h5>
                    <a href="{{ route('courses.student') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> عرض جميع المقررات
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($courses->count() > 0)
                        <div class="row row-cols-1 row-cols-md-2 g-4 p-3">
                            @foreach($courses as $course)
                                <div class="col">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold mb-1">{{ $course->name }}</h5>
                                            <p class="card-text text-muted small mb-3">{{ $course->code }}</p>
                                            
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="me-2 text-primary">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">الدكتور:</small>
                                                    <span class="ms-1">{{ $course->teacher->name ?? 'غير محدد' }}</span>
                                                </div>
                                            </div>
                                            
                                            @if($course->description)
                                                <p class="card-text text-truncate mb-3" title="{{ $course->description }}">{{ $course->description }}</p>
                                            @endif
                                            
                                            <a href="{{ route('student.courses.show', $course->id) }}" class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-info-circle me-1"></i> عرض التفاصيل
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" alt="لا توجد مقررات" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لا توجد مقررات مسجلة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. قسم طلباتك الإدارية - Your Administrative Requests -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2 text-primary"></i>طلباتك الإدارية
                    </h5>
                    <div>
                        <a href="{{ route('student.requests.create') }}" class="btn btn-sm btn-success me-2">
                            <i class="fas fa-plus-circle me-1"></i> طلب جديد
                        </a>
                        <a href="{{ route('student.requests') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> عرض جميع الطلبات
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">رقم الطلب</th>
                                        <th scope="col">نوع الطلب</th>
                                        <th scope="col">تاريخ التقديم</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequests as $request)
                                        <tr>
                                            <td>#{{ $request->id }}</td>
                                            <td>{{ $request->getTypeNameAttribute() }}</td>
                                            <td>{{ $request->request_date->format('Y-m-d') }}</td>
                                            <td>
                                                @if($request->status == 'pending')
                                                    <span class="badge bg-warning">قيد المراجعة</span>
                                                @elseif($request->status == 'approved')
                                                    <span class="badge bg-success">تمت الموافقة</span>
                                                @elseif($request->status == 'rejected')
                                                    <span class="badge bg-danger">مرفوض</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $request->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('student.requests.show', $request->id) }}" class="btn btn-sm btn-primary">
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
                            <img src="https://cdn-icons-png.flaticon.com/512/3342/3342137.png" alt="لا توجد طلبات" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لم تقم بتقديم أي طلبات بعد</p>
                            <a href="{{ route('student.requests.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-1"></i> تقديم طلب جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- 4. قسم الرسوم الدراسية - Your Fees Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-money-check-alt me-2 text-primary"></i>حالة الرسوم الدراسية
                    </h5>
                </div>
                <div class="card-body">
                    @if($feeStatus)
                        <div class="text-center mb-4">
                            @if($feeStatus['paid_percentage'] == 100)
                                <div class="fee-icon bg-success bg-opacity-10 p-4 rounded-circle mx-auto mb-3" style="width: 120px; height: 120px;">
                                    <i class="fas fa-check-circle text-success fa-4x"></i>
                                </div>
                                <h4 class="text-success mb-1">تم سداد الرسوم بالكامل</h4>
                                <p class="text-muted">لقد قمت بسداد جميع الرسوم المستحقة</p>
                            @else
                                <div class="fee-icon bg-warning bg-opacity-10 p-4 rounded-circle mx-auto mb-3" style="width: 120px; height: 120px;">
                                    <i class="fas fa-exclamation-circle text-warning fa-4x"></i>
                                </div>
                                <h4 class="text-warning mb-1">رسوم مستحقة</h4>
                                <p class="text-muted">يوجد رسوم مستحقة عليك</p>
                            @endif
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">إجمالي الرسوم</h6>
                                        <h5 class="mb-0">{{ number_format($feeStatus['total_amount'], 2) }} ريال</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center p-3">
                                        <h6 class="mb-2">المبلغ المدفوع</h6>
                                        <h5 class="mb-0 text-success">{{ number_format($feeStatus['paid_amount'], 2) }} ريال</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="mb-2">نسبة السداد:</label>
                            <div class="progress" style="height: 15px;">
                                <div class="progress-bar {{ $feeStatus['paid_percentage'] < 50 ? 'bg-danger' : ($feeStatus['paid_percentage'] < 100 ? 'bg-warning' : 'bg-success') }}" 
                                    role="progressbar" 
                                    style="width: {{ $feeStatus['paid_percentage'] }}%;" 
                                    aria-valuenow="{{ $feeStatus['paid_percentage'] }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    {{ $feeStatus['paid_percentage'] }}%
                                </div>
                            </div>
                        </div>

                        @if($feeStatus['paid_percentage'] < 100)
                            <div class="alert alert-warning mb-0">
                                <strong>المبلغ المتبقي:</strong> {{ number_format($feeStatus['remaining_amount'], 2) }} ريال
                                <br>
                                <strong>الموعد النهائي للسداد:</strong> {{ $feeStatus['due_date'] ? \Carbon\Carbon::parse($feeStatus['due_date'])->format('Y-m-d') : 'غير محدد' }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2037/2037510.png" alt="لا توجد رسوم" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لا توجد بيانات رسوم متاحة</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 5. قسم الرسائل الأخيرة - Recent Messages -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>آخر الرسائل
                    </h5>
                    <a href="{{ route('student.messages') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> عرض الكل
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentMessages->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($recentMessages as $message)
                                <li class="list-group-item {{ $message->read_at ? '' : 'bg-light' }}">
                                    <div class="d-flex w-100 justify-content-between mb-1">
                                        <h6 class="mb-0 {{ $message->read_at ? '' : 'fw-bold' }}">
                                            {{ $message->subject }}
                                            @if(!$message->read_at)
                                                <span class="badge bg-danger ms-1">جديد</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">{{ $message->created_at->format('Y-m-d') }}</small>
                                    </div>
                                    <p class="mb-1 text-muted small">من: {{ $message->sender->name }}</p>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-primary view-message-btn" 
                                                data-bs-toggle="modal" data-bs-target="#viewMessageModal"
                                                data-subject="{{ $message->subject }}" 
                                                data-body="{{ $message->body }}"
                                                data-date="{{ $message->created_at->format('Y-m-d H:i A') }}"
                                                data-sender="{{ $message->sender->name }}"
                                                data-message-id="{{ $message->id }}">
                                            <i class="fas fa-eye me-1"></i> عرض
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3917/3917032.png" alt="لا توجد رسائل" style="width: 100px; opacity: 0.5;">
                            <p class="mt-3 text-muted">لا توجد رسائل واردة</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 6. قسم الإشعارات - Recent Notifications -->
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 10;">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2 text-primary"></i>الإشعارات الأخيرة
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
                                        @elseif($notification['type'] == 'request')
                                            <i class="fas fa-clipboard-list fs-5 text-warning"></i>
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
                            <a href="{{ route('student.notifications') }}" class="btn btn-outline-primary btn-sm w-100">
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
                markAsReadForm.action = `/student/messages/${messageId}/read`;
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
    
    .fee-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush 