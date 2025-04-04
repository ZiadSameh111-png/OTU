@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">التقارير</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-chart-bar me-2"></i>التقارير الإحصائية
            </h1>
            <p class="text-muted">عرض وتحليل البيانات والإحصائيات المختلفة للنظام</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- تقارير رئيسية -->
    <div class="row g-4">
        <!-- تقارير الحضور -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2 text-primary"></i>تقارير الحضور
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.reports.attendance.teachers') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">حضور المعلمين</h6>
                                <p class="text-muted small mb-0">تقرير حضور المعلمين خلال الفترة المحددة</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="{{ route('admin.reports.attendance.monthly') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">التقرير الشهري للحضور</h6>
                                <p class="text-muted small mb-0">تقرير إحصائي شهري لمعدلات الحضور</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="{{ route('admin.reports.attendance.comparative') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">تقرير المقارنة</h6>
                                <p class="text-muted small mb-0">مقارنة معدلات الحضور بين الأشهر المختلفة</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- تقارير الرسوم -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>تقارير الرسوم
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.reports.fees.collection') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">تحصيل الرسوم</h6>
                                <p class="text-muted small mb-0">تقرير تحصيل الرسوم خلال فترة محددة</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="{{ route('admin.reports.fees.remaining') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">تقرير المتأخرات</h6>
                                <p class="text-muted small mb-0">قائمة بالطلاب المتأخرين في سداد الرسوم</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="{{ route('admin.reports.fees.monthly') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">الإيرادات الشهرية</h6>
                                <p class="text-muted small mb-0">تقرير الإيرادات الشهرية خلال العام</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- تقارير الطلاب -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2 text-primary"></i>تقارير الطلاب
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.reports.students.distribution') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">توزيع الطلاب</h6>
                                <p class="text-muted small mb-0">توزيع الطلاب حسب المجموعات</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="{{ route('admin.reports.students.activity') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">نشاط الطلاب</h6>
                                <p class="text-muted small mb-0">تقرير نشاط الطلاب ومشاركتهم</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="{{ route('admin.reports.students.requests') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">تقرير الطلبات</h6>
                                <p class="text-muted small mb-0">إحصائيات الطلبات المقدمة من الطلاب</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- تقارير المقررات -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2 text-primary"></i>تقارير المقررات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.reports.courses.distribution') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">توزيع المقررات</h6>
                                <p class="text-muted small mb-0">توزيع المقررات على المعلمين والمجموعات</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="{{ route('admin.reports.courses.schedules') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">جدول المقررات</h6>
                                <p class="text-muted small mb-0">تقرير الجدول الزمني للمقررات</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                        <a href="{{ route('admin.reports.courses.statistics') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">إحصائيات المقررات</h6>
                                <p class="text-muted small mb-0">إحصائيات عامة حول المقررات الدراسية</p>
                            </div>
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- إنشاء تقرير مخصص -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2 text-primary"></i>إنشاء تقرير مخصص
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.reports.custom') }}" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label for="report_type" class="form-label">نوع التقرير</label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="" selected disabled>-- اختر نوع التقرير --</option>
                                <option value="attendance">تقرير الحضور</option>
                                <option value="fees">تقرير الرسوم</option>
                                <option value="students">تقرير الطلاب</option>
                                <option value="courses">تقرير المقررات</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">تاريخ البداية</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ now()->subMonths(1)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">تاريخ النهاية</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="group_id" class="form-label">المجموعة (اختياري)</label>
                            <select class="form-select" id="group_id" name="group_id">
                                <option value="">جميع المجموعات</option>
                                @foreach($groups ?? [] as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="format" class="form-label">تنسيق التقرير</label>
                            <select class="form-select" id="format" name="format" required>
                                <option value="web" selected>عرض على المتصفح</option>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="chart_type" class="form-label">نوع الرسم البياني (اختياري)</label>
                            <select class="form-select" id="chart_type" name="chart_type">
                                <option value="">بدون رسم بياني</option>
                                <option value="bar">أعمدة</option>
                                <option value="line">خط بياني</option>
                                <option value="pie">دائري</option>
                            </select>
                        </div>
                        <div class="col-12 mt-4 text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-export me-1"></i> إنشاء التقرير
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 