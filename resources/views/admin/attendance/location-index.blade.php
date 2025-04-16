@extends('layouts.app')

@section('title', 'سجل الحضور المكاني')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">سجل الحضور المكاني</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0 fw-bold">
                    <i class="fas fa-map-marker-alt text-primary me-2"></i> سجل الحضور المكاني
                </h1>
                <a href="{{ route('admin.locations') }}" class="btn btn-outline-primary">
                    <i class="fas fa-cog me-1"></i> إدارة المواقع
                </a>
            </div>
            <p class="text-muted">عرض وتصفية سجلات الحضور المكاني للطلاب والمعلمين</p>
        </div>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-2">{{ $totalAttendance }}</div>
                    <h5 class="mb-1">إجمالي التسجيلات</h5>
                    <p class="text-muted small mb-0">بتاريخ {{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-success mb-2">{{ $totalPresentCount }}</div>
                    <h5 class="mb-1">داخل النطاق</h5>
                    <p class="text-muted small mb-0">{{ $totalAttendance > 0 ? round(($totalPresentCount / $totalAttendance) * 100) : 0 }}% من الإجمالي</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-warning mb-2">{{ $totalOutsideRangeCount }}</div>
                    <h5 class="mb-1">خارج النطاق</h5>
                    <p class="text-muted small mb-0">{{ $totalAttendance > 0 ? round(($totalOutsideRangeCount / $totalAttendance) * 100) : 0 }}% من الإجمالي</p>
                </div>
            </div>
        </div>
    </div>

    <!-- فلاتر البحث -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-filter text-primary me-2"></i> فلترة سجلات الحضور
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.location-attendance') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">التاريخ</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ $date }}">
                        </div>
                        <div class="col-md-4">
                            <label for="user_type" class="form-label">نوع المستخدم</label>
                            <select class="form-select" id="user_type" name="user_type">
                                <option value="all" {{ $userType == 'all' ? 'selected' : '' }}>الكل</option>
                                <option value="Student" {{ $userType == 'Student' ? 'selected' : '' }}>الطلاب</option>
                                <option value="Teacher" {{ $userType == 'Teacher' ? 'selected' : '' }}>المعلمين</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> عرض
                            </button>
                            <a href="{{ route('admin.location-attendance') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo-alt me-1"></i> إعادة ضبط
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول سجلات الحضور -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary me-2"></i> سجلات الحضور
                    </h5>
                    <span class="badge bg-light text-dark">
                        {{ $attendanceRecords->total() }} سجل
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($attendanceRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">المستخدم</th>
                                        <th scope="col">الموقع</th>
                                        <th scope="col">التاريخ</th>
                                        <th scope="col">الوقت</th>
                                        <th scope="col">المسافة</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendanceRecords as $index => $record)
                                        <tr>
                                            <td>{{ $attendanceRecords->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-{{ $record->user->hasRole('Student') ? 'info' : 'primary' }} text-white rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-{{ $record->user->hasRole('Student') ? 'user-graduate' : 'chalkboard-teacher' }}"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $record->user->name }}</div>
                                                        <div class="small text-muted">{{ $record->user->hasRole('Student') ? 'طالب' : 'معلم' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.location-attendance.location', $record->locationSetting->id) }}" class="text-decoration-none">
                                                    {{ $record->locationSetting->name }}
                                                </a>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($record->attendance_date)->format('Y-m-d') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($record->attendance_time)->format('h:i A') }}</td>
                                            <td>{{ round($record->distance_meters) }} متر</td>
                                            <td>
                                                @if($record->is_within_range)
                                                    <span class="badge bg-success rounded-pill">
                                                        <i class="fas fa-check-circle me-1"></i> داخل النطاق
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning rounded-pill">
                                                        <i class="fas fa-exclamation-triangle me-1"></i> خارج النطاق
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.location-attendance.user', $record->user->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-user me-1"></i> سجل المستخدم
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center p-3">
                            {{ $attendanceRecords->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" alt="لا توجد بيانات" width="120" class="mb-3 opacity-50">
                            <h5>لا توجد سجلات حضور</h5>
                            <p class="text-muted">لم يتم العثور على سجلات حضور مكانية تطابق معايير البحث</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 