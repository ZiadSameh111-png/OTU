@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">تفاصيل المقرر</h2>
            <a href="{{ route('courses.student') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right ml-1"></i> العودة للمقررات
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-4">
                        <h3 class="text-primary mb-2">{{ $course->name }}</h3>
                        <h5><span class="badge bg-primary">{{ $course->code }}</span></h5>
                    </div>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title">وصف المقرر</h5>
                            <p class="card-text">{{ $course->description ?: 'لا يوجد وصف متاح.' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">معلومات المقرر</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong><i class="fas fa-user text-primary me-2"></i> المدرس:</strong>
                                <p class="mt-1">{{ $course->teacher ? $course->teacher->name : 'غير معين' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong><i class="fas fa-users text-primary me-2"></i> المجموعة:</strong>
                                <p class="mt-1">{{ $group ? $group->name : 'غير مسجل في مجموعة' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card bg-light">
                        <div class="card-header">
                            <h5 class="mb-0">جدول المحاضرات</h5>
                        </div>
                        <div class="card-body">
                            @if($schedules && $schedules->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($schedules as $schedule)
                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-calendar-day text-primary me-2"></i>
                                                    {{ $schedule->day_name }}
                                                </div>
                                                <div>
                                                    {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                                </div>
                                            </div>
                                            @if($schedule->room)
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i> 
                                                    {{ $schedule->room }}
                                                </small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">لا توجد محاضرات مجدولة لهذا المقرر حالياً.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 