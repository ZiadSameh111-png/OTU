@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0 text-primary fw-bold">
                <i class="fas fa-bell me-2"></i>الإشعارات
            </h1>
            <p class="text-muted">عرض جميع الإشعارات والتنبيهات الخاصة بك</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>جميع الإشعارات
            </h5>
        </div>
        <div class="card-body p-0">
            @if(count($notifications) > 0)
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="notification-icon me-3">
                                        @if($notification['type'] == 'schedule')
                                            <span class="badge rounded-circle bg-primary p-3"><i class="fas fa-calendar-alt"></i></span>
                                        @elseif($notification['type'] == 'message')
                                            <span class="badge rounded-circle bg-danger p-3"><i class="fas fa-envelope"></i></span>
                                        @elseif($notification['type'] == 'attendance')
                                            <span class="badge rounded-circle bg-success p-3"><i class="fas fa-user-check"></i></span>
                                        @else
                                            <span class="badge rounded-circle bg-secondary p-3"><i class="fas fa-info-circle"></i></span>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-1">
                                            {{ $notification['message'] }}
                                            @if(!$notification['is_read'])
                                                <span class="badge bg-danger">جديد</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">{{ $notification['date']->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <a href="{{ $notification['link'] }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-external-link-alt me-1"></i> عرض
                                </a>
                            </div>
                        </div>
                    @endforeach
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