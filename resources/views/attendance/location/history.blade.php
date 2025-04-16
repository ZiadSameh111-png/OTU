@extends('layouts.app')

@section('title', 'سجل الحضور المكاني')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 fw-bold">
                        <i class="fas fa-history text-primary"></i> سجل الحضور المكاني
                    </h1>
                    <p class="text-muted">سجل حضورك في المواقع المختلفة</p>
                </div>
                <a href="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.index') : route('teacher.location-attendance.index') }}" class="btn btn-primary">
                    <i class="fas fa-map-marker-alt me-2"></i> تسجيل حضور جديد
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2 text-primary"></i> تصفية السجل
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.by-date') : route('teacher.location-attendance.by-date') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">حسب التاريخ</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ request('date', now()->toDateString()) }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i> بحث
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
                        <i class="fas fa-clipboard-list me-2 text-primary"></i> قائمة سجلات الحضور
                    </h5>
                </div>
                <div class="card-body">
                    @if($attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
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
                                            <td>{{ $attendance->attendance_date->format('Y-m-d') }}</td>
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
                        
                        <div class="mt-4">
                            {{ $attendances->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1054/1054870.png" alt="لا توجد سجلات" width="80" class="mb-3 opacity-50">
                            <p class="text-muted">لا توجد سجلات حضور</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 