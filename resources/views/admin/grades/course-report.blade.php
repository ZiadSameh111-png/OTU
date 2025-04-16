@extends('layouts.app')

@section('title', 'تقرير المقرر - ' . $course->name)

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
        height: 300px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">تقرير المقرر: {{ $course->name }} ({{ $course->code }})</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.grades.reports') }}">تقارير الدرجات</a></li>
                    <li class="breadcrumb-item active">{{ $course->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.grades.export_course_report', $course->id) }}" class="btn btn-primary">
                <i class="fas fa-file-export me-1"></i> تصدير التقرير
            </a>
            <a href="{{ route('admin.grades.reports') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-1"></i> العودة
            </a>
        </div>
    </div>

    <!-- Course Info -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="card-title">معلومات المقرر</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>اسم المقرر:</strong></td>
                            <td>{{ $course->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>رمز المقرر:</strong></td>
                            <td>{{ $course->code }}</td>
                        </tr>
                        <tr>
                            <td><strong>عدد الوحدات:</strong></td>
                            <td>{{ $course->credits ?? 'غير محدد' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <h5 class="card-title">معلومات التدريس</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>أستاذ المقرر:</strong></td>
                            <td>{{ $course->teacher->name ?? 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <td><strong>الفصل الدراسي:</strong></td>
                            <td>{{ $course->semester ?? 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <td><strong>العام الدراسي:</strong></td>
                            <td>{{ $course->academic_year ?? 'غير محدد' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <h5 class="card-title">توزيع الدرجات</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>الاختبارات الشهرية:</strong></td>
                            <td>{{ $course->midterm_grade ?? 0 }} درجة</td>
                        </tr>
                        <tr>
                            <td><strong>الأعمال العملية:</strong></td>
                            <td>{{ $course->assignment_grade ?? 0 }} درجة</td>
                        </tr>
                        <tr>
                            <td><strong>الاختبار النهائي:</strong></td>
                            <td>{{ $course->final_grade ?? 0 }} درجة</td>
                        </tr>
                    </table>
                </div>
            </div>
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
                        <p class="text-muted mb-0">عدد الطلاب</p>
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
                        <p class="text-muted mb-0">متوسط الدرجة</p>
                        <h3 class="mb-0">{{ number_format($stats['avg_score'], 1) }}</h3>
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
                        <h3 class="mb-0">{{ $stats['at_risk_count'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm performance-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">توزيع الدرجات</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="gradeDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm performance-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">مكونات الدرجة</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="gradeComponentsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Distribution Table -->
    <div class="card shadow-sm performance-card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">توزيع التقديرات</h5>
            <div>
                <a href="#" class="btn btn-sm btn-outline-primary" id="toggleChart">
                    <i class="fas fa-chart-pie me-1"></i> عرض الرسم البياني
                </a>
            </div>
        </div>
        <div class="card-body pb-0">
            <div class="row d-none" id="gradesPieChartContainer">
                <div class="col-md-8 mx-auto mb-4">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="gradesPieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>التقدير</th>
                                    <th>النطاق</th>
                                    <th>عدد الطلاب</th>
                                    <th>النسبة المئوية</th>
                                    <th>التوزيع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $gradeRanges = [
                                        'A+' => ['min' => 95, 'max' => 100, 'color' => 'bg-success'],
                                        'A' => ['min' => 90, 'max' => 94.99, 'color' => 'bg-success'],
                                        'A-' => ['min' => 85, 'max' => 89.99, 'color' => 'bg-success'],
                                        'B+' => ['min' => 80, 'max' => 84.99, 'color' => 'bg-info'],
                                        'B' => ['min' => 75, 'max' => 79.99, 'color' => 'bg-info'],
                                        'B-' => ['min' => 70, 'max' => 74.99, 'color' => 'bg-info'],
                                        'C+' => ['min' => 65, 'max' => 69.99, 'color' => 'bg-warning'],
                                        'C' => ['min' => 60, 'max' => 64.99, 'color' => 'bg-warning'],
                                        'C-' => ['min' => 55, 'max' => 59.99, 'color' => 'bg-warning'],
                                        'D+' => ['min' => 50, 'max' => 54.99, 'color' => 'bg-warning'],
                                        'D' => ['min' => 40, 'max' => 49.99, 'color' => 'bg-warning'],
                                        'F' => ['min' => 0, 'max' => 39.99, 'color' => 'bg-danger'],
                                    ];
                                @endphp

                                @foreach($gradeRanges as $grade => $range)
                                    @php
                                        $count = $gradeDistribution[$grade] ?? 0;
                                        $percentage = $stats['total_students'] > 0 ? ($count / $stats['total_students']) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge {{ $range['color'] }} px-2 py-1">{{ $grade }}</span>
                                        </td>
                                        <td>{{ $range['min'] }} - {{ $range['max'] }}</td>
                                        <td>{{ $count }}</td>
                                        <td>{{ number_format($percentage, 1) }}%</td>
                                        <td width="30%">
                                            <div class="progress progress-sm">
                                                <div class="progress-bar {{ $range['color'] }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Scores -->
    <div class="card shadow-sm performance-card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">قائمة درجات الطلاب</h5>
            <div class="d-flex">
                <select class="form-select form-select-sm me-2" id="sortOption">
                    <option value="name">ترتيب حسب الاسم</option>
                    <option value="grade_desc">ترتيب حسب الدرجة (تنازلي)</option>
                    <option value="grade_asc">ترتيب حسب الدرجة (تصاعدي)</option>
                </select>
                <input type="text" class="form-control form-control-sm" placeholder="بحث..." id="searchInput">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>اسم الطالب</th>
                            <th>رقم الطالب</th>
                            <th>المجموعة</th>
                            <th>أعمال السنة</th>
                            <th>الاختبار النهائي</th>
                            <th>المجموع</th>
                            <th>التقدير</th>
                            <th>الحالة</th>
                            <th>خيارات</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        @forelse($students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student['name'] }}</td>
                                <td>{{ $student['id'] }}</td>
                                <td>{{ $student['group'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar {{ getScoreColor($student['assignment_percentage']) }}" role="progressbar" style="width: {{ $student['assignment_percentage'] }}%"></div>
                                        </div>
                                        <span>{{ $student['assignment_score'] }}/{{ $student['assignment_max'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar {{ getScoreColor($student['final_percentage']) }}" role="progressbar" style="width: {{ $student['final_percentage'] }}%"></div>
                                        </div>
                                        <span>{{ $student['final_score'] }}/{{ $student['final_max'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar {{ getScoreColor($student['total_percentage']) }}" role="progressbar" style="width: {{ $student['total_percentage'] }}%"></div>
                                        </div>
                                        <span>{{ $student['total_score'] }}/{{ $student['total_max'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ getGradeBadgeColor($student['grade']) }} px-2 py-1">{{ $student['grade'] }}</span>
                                </td>
                                <td>
                                    @if($student['passed'])
                                        <span class="badge bg-success px-2 py-1">ناجح</span>
                                    @else
                                        <span class="badge bg-danger px-2 py-1">راسب</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.grades.edit', $student['grade_id']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i> تعديل
                                    </a>
                                    <a href="{{ route('admin.grades.student_report', $student['id']) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-user me-1"></i> ملف الطالب
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">لا توجد بيانات للعرض</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
            labels: @json(array_keys($gradeDistribution)),
            datasets: [{
                label: 'عدد الطلاب',
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

    // Grade Components Chart
    const componentData = {
        avgMidterm: {{ $stats['avg_midterm'] ?? 0 }},
        maxMidterm: {{ $course->midterm_grade ?? 0 }},
        avgAssignment: {{ $stats['avg_assignment'] ?? 0 }},
        maxAssignment: {{ $course->assignment_grade ?? 0 }},
        avgFinal: {{ $stats['avg_final'] ?? 0 }},
        maxFinal: {{ $course->final_grade ?? 0 }},
    };

    const gradeComponentsCtx = document.getElementById('gradeComponentsChart').getContext('2d');
    const gradeComponentsChart = new Chart(gradeComponentsCtx, {
        type: 'radar',
        data: {
            labels: ['الاختبارات الشهرية', 'الأعمال العملية', 'الاختبار النهائي'],
            datasets: [
                {
                    label: 'متوسط درجات الطلاب',
                    data: [
                        componentData.avgMidterm,
                        componentData.avgAssignment,
                        componentData.avgFinal
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(54, 162, 235, 1)'
                },
                {
                    label: 'الدرجة القصوى',
                    data: [
                        componentData.maxMidterm,
                        componentData.maxAssignment,
                        componentData.maxFinal
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: {
                        display: true
                    },
                    suggestedMin: 0,
                    suggestedMax: Math.max(
                        componentData.maxMidterm,
                        componentData.maxAssignment,
                        componentData.maxFinal
                    )
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

    // Grades Pie Chart
    const gradesPieCtx = document.getElementById('gradesPieChart').getContext('2d');
    const gradesPieChart = new Chart(gradesPieCtx, {
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

    // Toggle Chart Display
    document.getElementById('toggleChart').addEventListener('click', function(e) {
        e.preventDefault();
        const chartContainer = document.getElementById('gradesPieChartContainer');
        const isHidden = chartContainer.classList.contains('d-none');
        
        if (isHidden) {
            chartContainer.classList.remove('d-none');
            this.innerHTML = '<i class="fas fa-table me-1"></i> عرض الجدول فقط';
        } else {
            chartContainer.classList.add('d-none');
            this.innerHTML = '<i class="fas fa-chart-pie me-1"></i> عرض الرسم البياني';
        }
    });

    // Sorting and Searching
    const studentsData = @json($students);
    const studentsTableBody = document.getElementById('studentsTableBody');
    
    document.getElementById('sortOption').addEventListener('change', function() {
        sortAndRenderStudents();
    });

    document.getElementById('searchInput').addEventListener('input', function() {
        sortAndRenderStudents();
    });

    function sortAndRenderStudents() {
        const sortOption = document.getElementById('sortOption').value;
        const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
        
        let filteredStudents = [...studentsData];
        
        // Filter by search query
        if (searchQuery) {
            filteredStudents = filteredStudents.filter(student => 
                student.name.toLowerCase().includes(searchQuery) || 
                student.id.toLowerCase().includes(searchQuery) ||
                student.group.toLowerCase().includes(searchQuery)
            );
        }
        
        // Sort based on selected option
        switch(sortOption) {
            case 'name':
                filteredStudents.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'grade_desc':
                filteredStudents.sort((a, b) => b.total_score - a.total_score);
                break;
            case 'grade_asc':
                filteredStudents.sort((a, b) => a.total_score - b.total_score);
                break;
        }
        
        // Render sorted students
        renderStudents(filteredStudents);
    }

    function renderStudents(students) {
        studentsTableBody.innerHTML = '';
        
        if (students.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="10" class="text-center py-4">لا توجد بيانات للعرض</td>';
            studentsTableBody.appendChild(emptyRow);
            return;
        }
        
        students.forEach((student, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${student.name}</td>
                <td>${student.id}</td>
                <td>${student.group}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                            <div class="progress-bar ${getScoreColorJS(student.assignment_percentage)}" role="progressbar" style="width: ${student.assignment_percentage}%"></div>
                        </div>
                        <span>${student.assignment_score}/${student.assignment_max}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                            <div class="progress-bar ${getScoreColorJS(student.final_percentage)}" role="progressbar" style="width: ${student.final_percentage}%"></div>
                        </div>
                        <span>${student.final_score}/${student.final_max}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                            <div class="progress-bar ${getScoreColorJS(student.total_percentage)}" role="progressbar" style="width: ${student.total_percentage}%"></div>
                        </div>
                        <span>${student.total_score}/${student.total_max}</span>
                    </div>
                </td>
                <td>
                    <span class="badge ${getGradeBadgeColorJS(student.grade)} px-2 py-1">${student.grade}</span>
                </td>
                <td>
                    ${student.passed 
                        ? '<span class="badge bg-success px-2 py-1">ناجح</span>' 
                        : '<span class="badge bg-danger px-2 py-1">راسب</span>'
                    }
                </td>
                <td>
                    <a href="${getEditUrl(student.grade_id)}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    <a href="${getStudentReportUrl(student.id)}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-user me-1"></i> ملف الطالب
                    </a>
                </td>
            `;
            studentsTableBody.appendChild(row);
        });
    }

    function getScoreColorJS(score) {
        if (score >= 90) return 'bg-success';
        if (score >= 70) return 'bg-info';
        if (score >= 50) return 'bg-warning';
        return 'bg-danger';
    }

    function getGradeBadgeColorJS(grade) {
        if (['A+', 'A', 'A-'].includes(grade)) return 'bg-success';
        if (['B+', 'B', 'B-'].includes(grade)) return 'bg-info';
        if (['C+', 'C', 'C-', 'D+', 'D'].includes(grade)) return 'bg-warning';
        return 'bg-danger';
    }

    function getEditUrl(gradeId) {
        return "{{ route('admin.grades.edit', '') }}/" + gradeId;
    }

    function getStudentReportUrl(studentId) {
        return "{{ route('admin.grades.student_report', '') }}/" + studentId;
    }
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
@endphp 