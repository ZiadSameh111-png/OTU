@extends('layouts.app')

@section('title', 'سجل الحضور ليوم ' . $date)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 fw-bold">
                        <i class="fas fa-calendar-day text-primary"></i> سجل الحضور ليوم {{ $date }}
                    </h1>
                    <p class="text-muted">عرض جميع سجلات الحضور لهذا اليوم</p>
                </div>
                <div>
                    <a href="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.history') : route('teacher.location-attendance.history') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-history me-2"></i> السجل الكامل
                    </a>
                    <a href="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.index') : route('teacher.location-attendance.index') }}" class="btn btn-primary">
                        <i class="fas fa-map-marker-alt me-2"></i> تسجيل حضور جديد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2 text-primary"></i> تغيير التاريخ
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.by-date') : route('teacher.location-attendance.by-date') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">التاريخ</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ $date }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i> عرض
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-list me-2 text-primary"></i> قائمة سجلات الحضور ليوم {{ $date }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الوقت</th>
                                        <th>الموقع</th>
                                        <th>المسافة</th>
                                        <th>الحالة</th>
                                        <th>ملاحظات</th>
                                        <th>الجهاز</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $attendance)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($attendance->attendance_time)->format('h:i A') }}</td>
                                            <td>{{ $attendance->locationSetting->name }}</td>
                                            <td>{{ $attendance->distance_meters }} متر</td>
                                            <td>
                                                @if($attendance->is_within_range)
                                                    <span class="badge bg-success">ضمن النطاق</span>
                                                @else
                                                    <span class="badge bg-warning">خارج النطاق</span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance->notes }}</td>
                                            <td><small class="text-muted">{{ $attendance->device_info }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1054/1054870.png" alt="لا توجد سجلات" width="80" class="mb-3 opacity-50">
                            <p class="text-muted">لا توجد سجلات حضور لهذا اليوم</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 