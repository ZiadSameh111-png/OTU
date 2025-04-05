@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0 text-primary fw-bold">
                <i class="fas fa-bell me-2"></i>الإشعارات
            </h1>
            <p class="text-muted">عرض جميع الإشعارات والتنبيهات الخاصة بالنظام</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>جميع الإشعارات 
                <span class="badge bg-danger">{{ $unread_count }}</span>
            </h5>
            <div>
                <a href="{{ route('notifications.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> إنشاء إشعار جديد
                </a>
                @if($unread_count > 0)
                <a href="{{ route('notifications.markAllAsRead') }}" class="btn btn-sm btn-secondary ms-2">
                    <i class="fas fa-check-double me-1"></i> تحديد الكل كمقروء
                </a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if(count($notifications) > 0)
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action {{ is_null($notification->read_at) ? 'bg-light' : '' }}">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="notification-icon me-3">
                                        @if($notification->notification_type == 'general')
                                            <span class="badge rounded-circle bg-secondary p-3"><i class="fas fa-info-circle"></i></span>
                                        @elseif($notification->notification_type == 'academic')
                                            <span class="badge rounded-circle bg-primary p-3"><i class="fas fa-graduation-cap"></i></span>
                                        @elseif($notification->notification_type == 'announcement')
                                            <span class="badge rounded-circle bg-success p-3"><i class="fas fa-bullhorn"></i></span>
                                        @elseif($notification->notification_type == 'exam')
                                            <span class="badge rounded-circle bg-warning p-3"><i class="fas fa-file-alt"></i></span>
                                        @else
                                            <span class="badge rounded-circle bg-info p-3"><i class="fas fa-bell"></i></span>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-1">
                                            {{ $notification->title }}
                                            @if(is_null($notification->read_at))
                                                <span class="badge bg-danger">جديد</span>
                                            @endif
                                        </h6>
                                        <p class="mb-1">{{ Str::limit($notification->description, 100) }}</p>
                                        <div class="text-muted d-flex align-items-center">
                                            <small>{{ $notification->created_at->diffForHumans() }}</small>
                                            @if($notification->sender)
                                                <span class="mx-2">•</span>
                                                <small>المرسل: {{ $notification->sender->name }}</small>
                                            @endif
                                            @if($notification->receiver_type == 'role')
                                                <span class="mx-2">•</span>
                                                <small>الدور: {{ $notification->role }}</small>
                                            @elseif($notification->receiver_type == 'group')
                                                <span class="mx-2">•</span>
                                                <small>المجموعة: {{ optional($notification->group)->name ?? 'غير محدد' }}</small>
                                            @elseif($notification->receiver_type == 'user' && $notification->receiver)
                                                <span class="mx-2">•</span>
                                                <small>المستلم: {{ $notification->receiver->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    @if($notification->url)
                                        <a href="{{ $notification->url }}" class="btn btn-sm btn-primary me-2">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                    
                                    @if(is_null($notification->read_at))
                                        <a href="{{ route('notifications.markAsRead', $notification->id) }}" class="btn btn-sm btn-success me-2">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif
                                    
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإشعار؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center pt-3 pb-3">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center p-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/2098/2098565.png" alt="لا توجد إشعارات" style="width: 120px; opacity: 0.5;">
                    <p class="mt-4 text-muted">لا توجد إشعارات جديدة</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 