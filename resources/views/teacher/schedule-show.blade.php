@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-day"></i> تفاصيل الجدول الدراسي
                    </h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 text-dark fw-bold">معلومات الجدول</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">المقرر الدراسي</th>
                                            <td>{{ $schedule->course->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>رمز المقرر</th>
                                            <td>{{ $schedule->course->code ?? 'غير محدد' }}</td>
                                        </tr>
                                        <tr>
                                            <th>المجموعة</th>
                                            <td>{{ $schedule->group->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>اليوم</th>
                                            <td>{{ $schedule->day }}</td>
                                        </tr>
                                        <tr>
                                            <th>وقت البدء</th>
                                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>وقت الانتهاء</th>
                                            <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>القاعة</th>
                                            <td>{{ $schedule->room ?? 'غير محدد' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 text-dark fw-bold">معلومات المجموعة</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">اسم المجموعة</th>
                                            <td>{{ $schedule->group->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>عدد الطلاب</th>
                                            <td>{{ $schedule->group->students->count() }} طالب</td>
                                        </tr>
                                        <tr>
                                            <th>الحالة</th>
                                            <td>
                                                @if($schedule->group->active)
                                                    <span class="badge badge-success fw-bold text-dark">نشطة</span>
                                                @else
                                                    <span class="badge badge-secondary fw-bold text-dark">غير نشطة</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <a href="{{ route('teacher.groups') }}" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-users"></i> عرض معلومات المجموعة
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 text-dark fw-bold">معلومات المقرر</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>وصف المقرر:</strong> {{ $schedule->course->description ?? 'لا يوجد وصف متاح' }}</p>
                                    
                                    <a href="{{ route('courses.teacher') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-book"></i> عرض معلومات المقرر
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('teacher.schedule') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> عودة إلى الجدول
                        </a>
                        <a href="{{ route('teacher.attendance.create') }}" class="btn btn-success">
                            <i class="fas fa-user-check"></i> تسجيل الحضور
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 