@extends('layouts.app')

@section('title', 'تقرير الدرجات - ' . $course->name)

@section('styles')
<style>
    .grade-chart-container {
        height: 300px;
    }
    .stat-card {
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .student-performance-table th, 
    .student-performance-table td {
        vertical-align: middle;
    }
    .performance-indicator {
        width: 10px;
        height: 10px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 5px;
    }
    .grade-distribution-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-top: 15px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-right: 15px;
    }
    .legend-color {
        width: 15px;
        height: 15px;
        display: inline-block;
        margin-right: 5px;
        border-radius: 3px;
    }
    .exam-score-chart {
        height: 250px;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.grades.index') }}">إدارة الدرجات</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.grades.students', $course->id) }}">{{ $course->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">تقرير الدرجات</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">تقرير الدرجات: {{ $course->name }}</h2>
            <p class="text-muted mb-0">{{ $course->code }} | {{ $course->semester }}</p>
        </div>
        <div class="d-flex">
            <a href="{{ route('teacher.grades.export.report', $course->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-file-export me-1"></i> تصدير التقرير
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-users text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">إجمالي الطلاب</h6>
                            <h3 class="mb-0">{{ $statistics['total_students'] }}</h3>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">متوسط الدرجات</h6>
                            <h3 class="mb-0">{{ number_format($statistics['average_score'], 1) }}%</h3>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $statistics['average_score'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-medal text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">أعلى درجة</h6>
                            <h3 class="mb-0">{{ number_format($statistics['highest_score'], 1) }}%</h3>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $statistics['highest_score'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">معدل النجاح</h6>
                            <h3 class="mb-0">{{ number_format($statistics['pass_rate'], 1) }}%</h3>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $statistics['pass_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Grade Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">توزيع التقديرات</h5>
                </div>
                <div class="card-body">
                    <div class="grade-chart-container">
                        <canvas id="gradeDistributionChart"></canvas>
                    </div>
                    <div class="grade-distribution-legend">
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: rgba(40, 167, 69, 0.8)"></span>
                            <span>A ({{ $gradeDistribution['A'] ?? 0 }} طالب)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: rgba(23, 162, 184, 0.8)"></span>
                            <span>B ({{ $gradeDistribution['B'] ?? 0 }} طالب)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: rgba(255, 193, 7, 0.8)"></span>
                            <span>C ({{ $gradeDistribution['C'] ?? 0 }} طالب)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: rgba(255, 153, 0, 0.8)"></span>
                            <span>D ({{ $gradeDistribution['D'] ?? 0 }} طالب)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: rgba(220, 53, 69, 0.8)"></span>
                            <span>F ({{ $gradeDistribution['F'] ?? 0 }} طالب)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Component Scores Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">متوسط درجات المكونات</h5>
                </div>
                <div class="card-body">
                    <div class="exam-score-chart">
                        <canvas id="componentScoresChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Students -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">أفضل 5 طلاب</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table student-performance-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>الطالب</th>
                                    <th>الدرجة الكلية</th>
                                    <th>التقدير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topStudents as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle text-primary me-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $student->name }}</h6>
                                                <small class="text-muted">{{ $student->student_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <strong>{{ number_format($student->total_percentage, 1) }}%</strong>
                                            <div class="progress ms-2 flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $student->total_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $gradeColors[$student->grade] ?? 'bg-secondary' }}">{{ $student->grade }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">لا توجد بيانات متاحة</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students at Risk -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">الطلاب المعرضون للرسوب</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table student-performance-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>الطالب</th>
                                    <th>الدرجة الكلية</th>
                                    <th>التقدير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($studentsAtRisk as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-danger bg-opacity-10 rounded-circle text-danger me-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $student->name }}</h6>
                                                <small class="text-muted">{{ $student->student_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <strong>{{ number_format($student->total_percentage, 1) }}%</strong>
                                            <div class="progress ms-2 flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $student->total_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $gradeColors[$student->grade] ?? 'bg-secondary' }}">{{ $student->grade }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">لا يوجد طلاب معرضون للرسوب</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Performance -->
    @if(count($groupPerformance) > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">أداء المجموعات</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-8">
                    <canvas id="groupPerformanceChart" height="250"></canvas>
                </div>
                <div class="col-lg-4">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>المجموعة</th>
                                    <th>عدد الطلاب</th>
                                    <th>متوسط الدرجات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupPerformance as $group)
                                <tr>
                                    <td>{{ $group['name'] }}</td>
                                    <td>{{ $group['student_count'] }}</td>
                                    <td>{{ number_format($group['average_score'], 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grade Distribution Chart
    const gradeDistributionCtx = document.getElementById('gradeDistributionChart').getContext('2d');
    new Chart(gradeDistributionCtx, {
        type: 'pie',
        data: {
            labels: ['A', 'B', 'C', 'D', 'F'],
            datasets: [{
                data: [
                    {{ $gradeDistribution['A'] ?? 0 }}, 
                    {{ $gradeDistribution['B'] ?? 0 }}, 
                    {{ $gradeDistribution['C'] ?? 0 }}, 
                    {{ $gradeDistribution['D'] ?? 0 }}, 
                    {{ $gradeDistribution['F'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(255, 153, 0, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(255, 153, 0, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Component Scores Chart
    const componentScoresCtx = document.getElementById('componentScoresChart').getContext('2d');
    new Chart(componentScoresCtx, {
        type: 'bar',
        data: {
            labels: ['أعمال المقرر', 'منتصف الفصل', 'النهائي', 'العملي'],
            datasets: [{
                label: 'متوسط الدرجات (%)',
                data: [
                    {{ $componentAverages['coursework_avg'] }}, 
                    {{ $componentAverages['midterm_avg'] }}, 
                    {{ $componentAverages['final_avg'] }},
                    {{ $componentAverages['practical_avg'] }}
                ],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(23, 162, 184, 0.7)',
                    'rgba(52, 58, 64, 0.7)',
                    'rgba(40, 167, 69, 0.7)'
                ],
                borderColor: [
                    'rgba(13, 110, 253, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(52, 58, 64, 1)',
                    'rgba(40, 167, 69, 1)'
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
                    max: 100,
                    title: {
                        display: true,
                        text: 'النسبة المئوية (%)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Group Performance Chart
    @if(count($groupPerformance) > 0)
    const groupPerformanceCtx = document.getElementById('groupPerformanceChart').getContext('2d');
    new Chart(groupPerformanceCtx, {
        type: 'bar',
        data: {
            labels: [
                @foreach($groupPerformance as $group)
                '{{ $group['name'] }}',
                @endforeach
            ],
            datasets: [{
                label: 'متوسط الدرجات (%)',
                data: [
                    @foreach($groupPerformance as $group)
                    {{ $group['average_score'] }},
                    @endforeach
                ],
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'متوسط الدرجات (%)'
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endsection 