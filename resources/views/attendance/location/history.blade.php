@extends('layouts.app')

@section('title', 'تاريخ تسجيل الحضور المكاني')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.index') : route('teacher.location-attendance.index') }}">تسجيل الحضور المكاني</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تاريخ التسجيل</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1 fw-bold">
                <i class="fas fa-history text-primary me-2"></i> تاريخ تسجيل الحضور المكاني
            </h1>
            <p class="text-muted">عرض سجل تسجيلات الحضور المكاني الخاصة بك</p>
        </div>
    </div>

    <!-- فلتر التاريخ -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-filter text-primary me-2"></i> تصفية السجلات
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.history') : route('teacher.location-attendance.history') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">التاريخ</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ $date ?? Carbon\Carbon::today()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> عرض
                            </button>
                            <a href="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.history') : route('teacher.location-attendance.history') }}" class="btn btn-outline-secondary">
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
                                        <th scope="col">الموقع</th>
                                        <th scope="col">التاريخ</th>
                                        <th scope="col">الوقت</th>
                                        <th scope="col">المسافة</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col">ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendanceRecords as $index => $record)
                                        <tr>
                                            <td>{{ $attendanceRecords->firstItem() + $index }}</td>
                                            <td>{{ $record->locationSetting->name }}</td>
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
                                            <td>{{ $record->notes ?? '-' }}</td>
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