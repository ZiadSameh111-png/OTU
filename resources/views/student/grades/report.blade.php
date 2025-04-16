@extends('layouts.app')

@section('title', 'تقرير الدرجات')

@section('styles')
<style>
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
    .progress-sm {
        height: 5px;
    }
    .chart-container {
        position: relative;
        height: 250px;
    }
    .course-card {
        transition: all 0.3s;
        border-radius: 10px;
        overflow: hidden;
    }
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .semester-divider {
        position: relative;
        text-align: center;
        margin: 30px 0;
    }
    .semester-divider:before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e0e0e0;
        z-index: 1;
    }
    .semester-divider span {
        position: relative;
        background: #fff;
        padding: 0 15px;
        z-index: 5;
        font-weight: 600;
    }
    .grade-badge {
        font-size: 1.5rem;
        display: inline-block;
        width: 45px;
        height: 45px;
        line-height: 45px;
        text-align: center;
        border-radius: 50%;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">تقرير الدرجات</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">تقرير الدرجات</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Overall Performance -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted mb-2">المعدل التراكمي</h5>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0 me-2">{{ number_format($stats['gpa'], 2) }}</h2>
                        <span class="badge {{ getGpaBadgeColor($stats['gpa']) }} px-2 py-1">{{ getGpaText($stats['gpa']) }}</span>
                    </div>
                    <div class="progress progress-sm mt-3">
                        <div class="progress-bar {{ getGpaBadgeColor($stats['gpa']) }}" role="progressbar" style="width: {{ ($stats['gpa'] / 4) * 100 }}%" aria-valuenow="{{ $stats['gpa'] }}" aria-valuemin="0" aria-valuemax="4"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted mb-2">معدل الفصل الحالي</h5>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0 me-2">{{ number_format($stats['current_semester_gpa'], 2) }}</h2>
                        <span class="badge {{ getGpaBadgeColor($stats['current_semester_gpa']) }} px-2 py-1">{{ getGpaText($stats['current_semester_gpa']) }}</span>
                    </div>
                    <div class="progress progress-sm mt-3">
                        <div class="progress-bar {{ getGpaBadgeColor($stats['current_semester_gpa']) }}" role="progressbar" style="width: {{ ($stats['current_semester_gpa'] / 4) * 100 }}%" aria-valuenow="{{ $stats['current_semester_gpa'] }}" aria-valuemin="0" aria-valuemax="4"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted mb-2">الساعات المكتملة</h5>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0 me-2">{{ $stats['earned_credits'] }}</h2>
                        <span class="text-muted">/ {{ $stats['total_program_credits'] }}</span>
                    </div>
                    <div class="progress progress-sm mt-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($stats['earned_credits'] / $stats['total_program_credits']) * 100 }}%" aria-valuenow="{{ $stats['earned_credits'] }}" aria-valuemin="0" aria-valuemax="{{ $stats['total_program_credits'] }}"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted mb-2">معدل النجاح</h5>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0 me-2">{{ number_format($stats['success_rate'], 1) }}%</h2>
                    </div>
                    <div class="progress progress-sm mt-3">
                        <div class="progress-bar {{ getSuccessRateColor($stats['success_rate']) }}" role="progressbar" style="width: {{ $stats['success_rate'] }}%" aria-valuenow="{{ $stats['success_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card performance-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">تطور المعدل التراكمي</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="gpaProgressChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card performance-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">توزيع التقديرات</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="gradesDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Semester -->
    <div class="card performance-card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">مقررات الفصل الحالي</h5>
            <span class="badge bg-info px-3 py-2">{{ $currentSemester ?? 'الفصل الدراسي الحالي' }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>المقرر</th>
                            <th>الرمز</th>
                            <th>الساعات</th>
                            <th>أعمال السنة</th>
                            <th>النهائي</th>
                            <th>المجموع</th>
                            <th>التقدير</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currentCourses as $course)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 text-{{ getCourseTypeColor($course['type']) }}">
                                            <i class="fas {{ getCourseTypeIcon($course['type']) }}"></i>
                                        </span>
                                        <div>
                                            <p class="mb-0 fw-semibold">{{ $course['name'] }}</p>
                                            <small class="text-muted">{{ $course['instructor'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $course['code'] }}</td>
                                <td>{{ $course['credits'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar {{ getScoreColor($course['assignment_percentage']) }}" role="progressbar" style="width: {{ $course['assignment_percentage'] }}%"></div>
                                        </div>
                                        <span>{{ $course['assignment_score'] }}/{{ $course['assignment_max'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar {{ getScoreColor($course['final_percentage']) }}" role="progressbar" style="width: {{ $course['final_percentage'] }}%"></div>
                                        </div>
                                        <span>{{ $course['final_score'] }}/{{ $course['final_max'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar {{ getScoreColor($course['total_percentage']) }}" role="progressbar" style="width: {{ $course['total_percentage'] }}%"></div>
                                        </div>
                                        <span>{{ $course['total_score'] }}/{{ $course['total_max'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ getGradeBadgeColor($course['grade']) }} px-2 py-1">{{ $course['grade'] }}</span>
                                </td>
                                <td>
                                    @if($course['status'] === 'passed')
                                        <span class="badge bg-success px-2 py-1">ناجح</span>
                                    @elseif($course['status'] === 'failed')
                                        <span class="badge bg-danger px-2 py-1">راسب</span>
                                    @else
                                        <span class="badge bg-warning px-2 py-1">جاري</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">لا توجد مقررات مسجلة في الفصل الحالي</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Previous Semesters -->
    <h4 class="my-4">السجل الأكاديمي</h4>

    @forelse($academicHistory as $semester => $courses)
        <div class="semester-divider">
            <span>{{ $semester }} - المعدل: {{ number_format($semesterGPA[$semester] ?? 0, 2) }}</span>
        </div>
        
        <div class="row">
            @foreach($courses as $course)
                <div class="col-md-4 mb-4">
                    <div class="card course-card h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $course['name'] }}</h6>
                            <div class="grade-badge {{ getGradeBadgeColor($course['grade']) }} text-white">
                                {{ $course['grade'] }}
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <p class="text-muted mb-1">رمز المقرر</p>
                                    <p class="mb-0">{{ $course['code'] }}</p>
                                </div>
                                <div>
                                    <p class="text-muted mb-1">الساعات المعتمدة</p>
                                    <p class="mb-0">{{ $course['credits'] }}</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <p class="text-muted mb-1">أستاذ المقرر</p>
                                <p class="mb-0">{{ $course['instructor'] }}</p>
                            </div>
                            <div>
                                <p class="text-muted mb-1">الدرجة النهائية</p>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <div class="progress-bar {{ getScoreColor($course['total_percentage']) }}" role="progressbar" style="width: {{ $course['total_percentage'] }}%"></div>
                                    </div>
                                    <span>{{ $course['total_score'] }}/{{ $course['total_max'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> لا يوجد سجل أكاديمي سابق
        </div>
    @endforelse
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // GPA Progress Chart
    const gpaProgressCtx = document.getElementById('gpaProgressChart').getContext('2d');
    const gpaProgressChart = new Chart(gpaProgressCtx, {
        type: 'line',
        data: {
            labels: @json(array_keys($semesterGPA)),
            datasets: [{
                label: 'المعدل التراكمي',
                data: @json(array_values($semesterGPA)),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 0,
                    max: 4,
                    ticks: {
                        stepSize: 0.5
                    },
                    grid: {
                        display: true,
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 14
                    }
                }
            }
        }
    });

    // Grades Distribution Chart
    const gradesDistributionCtx = document.getElementById('gradesDistributionChart').getContext('2d');
    const gradesDistributionChart = new Chart(gradesDistributionCtx, {
        type: 'pie',
        data: {
            labels: @json(array_keys($gradeDistribution)),
            datasets: [{
                data: @json(array_values($gradeDistribution)),
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
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
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

function getGradeBadgeColor($grade) {
    if (in_array($grade, ['A+', 'A', 'A-'])) return 'bg-success';
    if (in_array($grade, ['B+', 'B', 'B-'])) return 'bg-info';
    if (in_array($grade, ['C+', 'C', 'C-', 'D+', 'D'])) return 'bg-warning';
    return 'bg-danger';
}

function getGpaBadgeColor($gpa) {
    if ($gpa >= 3.5) return 'bg-success';
    if ($gpa >= 2.5) return 'bg-info';
    if ($gpa >= 1.5) return 'bg-warning';
    return 'bg-danger';
}

function getGpaText($gpa) {
    if ($gpa >= 3.5) return 'ممتاز';
    if ($gpa >= 2.5) return 'جيد جداً';
    if ($gpa >= 1.5) return 'جيد';
    if ($gpa >= 1.0) return 'مقبول';
    return 'ضعيف';
}

function getSuccessRateColor($rate) {
    if ($rate >= 90) return 'bg-success';
    if ($rate >= 70) return 'bg-info';
    if ($rate >= 50) return 'bg-warning';
    return 'bg-danger';
}

function getCourseTypeIcon($type) {
    switch ($type) {
        case 'core': return 'fa-book';
        case 'elective': return 'fa-puzzle-piece';
        case 'lab': return 'fa-flask';
        case 'practicum': return 'fa-hands-helping';
        default: return 'fa-book';
    }
}

function getCourseTypeColor($type) {
    switch ($type) {
        case 'core': return 'primary';
        case 'elective': return 'success';
        case 'lab': return 'info';
        case 'practicum': return 'warning';
        default: return 'secondary';
    }
}
@endphp