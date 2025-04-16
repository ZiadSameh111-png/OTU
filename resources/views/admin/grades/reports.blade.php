@extends('layouts.app')

@section('title', 'تقارير الدرجات')

@section('styles')
<style>
    .filter-card {
        border-radius: 10px;
        overflow: hidden;
    }
    .stats-card {
        border-radius: 10px;
        transition: all 0.3s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .performance-card {
        border-radius: 10px;
        overflow: hidden;
    }
    .grade-distribution {
        height: 300px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">تقارير الدرجات</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.grades.index') }}">إدارة الدرجات</a></li>
                    <li class="breadcrumb-item active">التقارير</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.grades.export_report') }}" class="btn btn-primary">
                <i class="fas fa-file-export me-1"></i> تصدير التقرير
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm filter-card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.grades.reports') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="course_id" class="form-label">المقرر</label>
                    <select name="course_id" id="course_id" class="form-select">
                        <option value="">جميع المقررات</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="group_id" class="form-label">المجموعة</label>
                    <select name="group_id" id="group_id" class="form-select">
                        <option value="">جميع المجموعات</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="semester" class="form-label">الفصل الدراسي</label>
                    <select name="semester" id="semester" class="form-select">
                        <option value="">جميع الفصول</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester }}" {{ request('semester') == $semester ? 'selected' : '' }}>
                                {{ $semester }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> تطبيق الفلتر
                    </button>
                    <a href="{{ route('admin.grades.reports') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i> إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm stats-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">إجمالي الطلاب</p>
                        <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm stats-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">معدل النجاح</p>
                        <h3 class="mb-0">{{ $stats['pass_rate'] }}%</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm stats-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">متوسط المعدل التراكمي</p>
                        <h3 class="mb-0">{{ $stats['avg_gpa'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm stats-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">الطلاب المتعثرين</p>
                        <h3 class="mb-0">{{ $stats['at_risk_students'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Distribution Chart -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm performance-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">توزيع التقديرات</h5>
                </div>
                <div class="card-body">
                    <canvas id="gradeDistributionChart" class="grade-distribution"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm performance-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">نسبة النجاح والرسوب</h5>
                </div>
                <div class="card-body">
                    <canvas id="passFailChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Performance -->
    <div class="card shadow-sm performance-card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">أداء المقررات</h5>
            <span class="badge bg-primary">{{ count($coursePerformance) }} مقرر</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>كود المقرر</th>
                            <th>اسم المقرر</th>
                            <th>عدد الطلاب</th>
                            <th>متوسط الدرجة</th>
                            <th>نسبة النجاح</th>
                            <th>أعلى درجة</th>
                            <th>أقل درجة</th>
                            <th>الانحراف المعياري</th>
                            <th>خيارات</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($coursePerformance as $course)
                        <tr>
                            <td>{{ $course['code'] }}</td>
                            <td>{{ $course['name'] }}</td>
                            <td>{{ $course['student_count'] }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar {{ getScoreColor($course['avg_score']) }}" role="progressbar" style="width: {{ $course['avg_score'] }}%"></div>
                                    </div>
                                    <span>{{ number_format($course['avg_score'], 1) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar {{ getPassRateColor($course['pass_rate']) }}" role="progressbar" style="width: {{ $course['pass_rate'] }}%"></div>
                                    </div>
                                    <span>{{ number_format($course['pass_rate'], 1) }}%</span>
                                </div>
                            </td>
                            <td>{{ number_format($course['max_score'], 1) }}</td>
                            <td>{{ number_format($course['min_score'], 1) }}</td>
                            <td>{{ number_format($course['std_deviation'], 2) }}</td>
                            <td>
                                <a href="{{ route('admin.grades.course_report', $course['id']) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i> التفاصيل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">لا توجد بيانات للعرض</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm performance-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">الطلاب المتفوقين</h5>
                    <span class="badge bg-success">أعلى 5 طلاب</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>المجموعة</th>
                                    <th>المعدل التراكمي</th>
                                    <th>خيارات</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($topPerformers as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <p class="mb-0 fw-bold">{{ $student['name'] }}</p>
                                                <small class="text-muted">{{ $student['id'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $student['group'] }}</td>
                                    <td>
                                        <span class="badge bg-success px-2 py-1">{{ number_format($student['gpa'], 2) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.grades.student_report', $student['id']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-user me-1"></i> ملف الطالب
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">لا توجد بيانات للعرض</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm performance-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">الطلاب المتعثرين</h5>
                    <span class="badge bg-danger">أقل 5 طلاب</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>المجموعة</th>
                                    <th>المعدل التراكمي</th>
                                    <th>خيارات</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($lowPerformers as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <p class="mb-0 fw-bold">{{ $student['name'] }}</p>
                                                <small class="text-muted">{{ $student['id'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $student['group'] }}</td>
                                    <td>
                                        <span class="badge bg-danger px-2 py-1">{{ number_format($student['gpa'], 2) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.grades.student_report', $student['id']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-user me-1"></i> ملف الطالب
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">لا توجد بيانات للعرض</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grade Distribution Chart
    const gradeDistributionCtx = document.getElementById('gradeDistributionChart').getContext('2d');
    const gradeDistributionChart = new Chart(gradeDistributionCtx, {
        type: 'bar',
        data: {
            labels: ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F'],
            datasets: [{
                label: 'عدد الطلاب',
                data: {{ json_encode($gradeDistribution) }},
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)', // A+
                    'rgba(40, 167, 69, 0.7)', // A
                    'rgba(40, 167, 69, 0.6)', // A-
                    'rgba(23, 162, 184, 0.8)', // B+
                    'rgba(23, 162, 184, 0.7)', // B
                    'rgba(23, 162, 184, 0.6)', // B-
                    'rgba(255, 193, 7, 0.8)', // C+
                    'rgba(255, 193, 7, 0.7)', // C
                    'rgba(255, 193, 7, 0.6)', // C-
                    'rgba(255, 193, 7, 0.5)', // D+
                    'rgba(255, 193, 7, 0.4)', // D
                    'rgba(220, 53, 69, 0.7)'  // F
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)', // A+
                    'rgba(40, 167, 69, 1)', // A
                    'rgba(40, 167, 69, 1)', // A-
                    'rgba(23, 162, 184, 1)', // B+
                    'rgba(23, 162, 184, 1)', // B
                    'rgba(23, 162, 184, 1)', // B-
                    'rgba(255, 193, 7, 1)', // C+
                    'rgba(255, 193, 7, 1)', // C
                    'rgba(255, 193, 7, 1)', // C-
                    'rgba(255, 193, 7, 1)', // D+
                    'rgba(255, 193, 7, 1)', // D
                    'rgba(220, 53, 69, 1)'  // F
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 14
                    },
                    callbacks: {
                        label: function(context) {
                            return `عدد الطلاب: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });

    // Pass/Fail Chart
    const passFailCtx = document.getElementById('passFailChart').getContext('2d');
    const passFailChart = new Chart(passFailCtx, {
        type: 'doughnut',
        data: {
            labels: ['ناجح', 'راسب'],
            datasets: [{
                data: [{{ $stats['pass_count'] }}, {{ $stats['fail_count'] }}],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 14
                    },
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection

@php
// Helper functions
function getScoreColor($score) {
    if ($score >= 90) return 'bg-success';
    if ($score >= 70) return 'bg-info';
    if ($score >= 50) return 'bg-warning';
    return 'bg-danger';
}

function getPassRateColor($rate) {
    if ($rate >= 90) return 'bg-success';
    if ($rate >= 70) return 'bg-info';
    if ($rate >= 50) return 'bg-warning';
    return 'bg-danger';
}
@endphp 