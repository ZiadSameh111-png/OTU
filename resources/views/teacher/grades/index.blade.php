@extends('layouts.app')

@section('title', 'إدارة الدرجات')

@section('styles')
<style>
    .card-course {
        transition: all 0.3s ease;
        border-top: 3px solid #dee2e6;
    }
    .card-course:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .stats-box {
        border-radius: 10px;
        padding: 15px;
        transition: all 0.3s;
    }
    .stats-box:hover {
        transform: translateY(-3px);
    }
    .card-header-tabs .nav-link {
        font-weight: 500;
        color: #6c757d;
        padding: 0.75rem 1rem;
        border-bottom: 2px solid transparent;
    }
    .card-header-tabs .nav-link.active {
        color: #0d6efd;
        background: transparent;
        border-bottom: 2px solid #0d6efd;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>إدارة الدرجات</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stats-box bg-primary bg-opacity-10">
                <h3 class="text-primary mb-1">{{ $totalCourses }}</h3>
                <p class="text-muted mb-0">المقررات الدراسية</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-box bg-success bg-opacity-10">
                <h3 class="text-success mb-1">{{ $totalStudents }}</h3>
                <p class="text-muted mb-0">إجمالي الطلاب</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-box bg-info bg-opacity-10">
                <h3 class="text-info mb-1">{{ $pendingGrades }}</h3>
                <p class="text-muted mb-0">درجات في انتظار الاعتماد</p>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="courseTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-courses" type="button" role="tab">
                        <i class="fas fa-book me-1"></i> المقررات الحالية
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="archived-tab" data-bs-toggle="tab" data-bs-target="#archived-courses" type="button" role="tab">
                        <i class="fas fa-archive me-1"></i> المقررات المؤرشفة
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="courseTabContent">
                <div class="tab-pane fade show active" id="active-courses" role="tabpanel">
                    @if(count($activeCourses) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المقرر</th>
                                        <th>رمز المقرر</th>
                                        <th>المستوى</th>
                                        <th>عدد الطلاب</th>
                                        <th>حالة الدرجات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeCourses as $course)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-book-open"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $course->name }}</h6>
                                                        <small class="text-muted">{{ $course->semester }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $course->code }}</td>
                                            <td>{{ $course->level }}</td>
                                            <td>{{ $course->students_count }}</td>
                                            <td>
                                                @php
                                                    $completedPercentage = $course->students_count > 0 ? 
                                                        round(($course->final_grades_count / $course->students_count) * 100) : 0;
                                                @endphp
                                                
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar {{ $completedPercentage == 100 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ $completedPercentage }}%"></div>
                                                    </div>
                                                    <span class="text-muted small">{{ $completedPercentage }}%</span>
                                                </div>
                                                
                                                @if($completedPercentage == 100)
                                                    <small class="text-success"><i class="fas fa-check-circle me-1"></i> مكتملة</small>
                                                @else
                                                    <small class="text-warning"><i class="fas fa-clock me-1"></i> قيد التقدم</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('teacher.grades.students', $course->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-users me-1"></i> إدارة درجات الطلاب
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('teacher.grades.exams', $course->id) }}">
                                                                <i class="fas fa-file-alt me-1"></i> إدارة درجات الاختبارات
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('teacher.grades.practicals', $course->id) }}">
                                                                <i class="fas fa-flask me-1"></i> إدارة الدرجات العملية
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('teacher.grades.report', $course->id) }}">
                                                                <i class="fas fa-chart-bar me-1"></i> تقرير الدرجات
                                                            </a>
                                                        </li>
                                                        @if($completedPercentage < 100)
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('teacher.grades.finalize', $course->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من اعتماد جميع الدرجات النهائية لهذا المقرر؟')">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <button class="dropdown-item text-success">
                                                                        <i class="fas fa-check-double me-1"></i> اعتماد جميع الدرجات
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="{{ asset('images/empty-state.svg') }}" alt="No courses" class="img-fluid mb-3" style="max-width: 200px;">
                            <h5>لا توجد مقررات حالية</h5>
                            <p class="text-muted">لم يتم تعيين أي مقررات دراسية لك في الفصل الدراسي الحالي.</p>
                        </div>
                    @endif
                </div>
                
                <div class="tab-pane fade" id="archived-courses" role="tabpanel">
                    @if(count($archivedCourses) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المقرر</th>
                                        <th>رمز المقرر</th>
                                        <th>الفصل الدراسي</th>
                                        <th>حالة الدرجات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($archivedCourses as $course)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-secondary bg-opacity-10 text-secondary rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-book"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $course->name }}</h6>
                                                        <small class="text-muted">{{ $course->level }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $course->code }}</td>
                                            <td>{{ $course->semester }}</td>
                                            <td>
                                                @if($course->is_grading_completed)
                                                    <span class="badge bg-success">مكتملة</span>
                                                @else
                                                    <span class="badge bg-secondary">مؤرشفة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('teacher.grades.report', $course->id) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-chart-bar me-1"></i> عرض تقرير الدرجات
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="{{ asset('images/empty-archive.svg') }}" alt="No archived courses" class="img-fluid mb-3" style="max-width: 200px;">
                            <h5>لا توجد مقررات مؤرشفة</h5>
                            <p class="text-muted">ستظهر هنا المقررات السابقة التي قمت بتدريسها.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">أحدث تحديثات الدرجات</h5>
                </div>
                <div class="card-body p-0">
                    @if(count($recentUpdates) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentUpdates as $update)
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light rounded-circle text-primary me-3 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-pen"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $update->course->name }} - {{ $update->student->name }}</h6>
                                            <p class="text-muted small mb-0">
                                                تم تحديث درجات {{ $update->assessment_type }} بتاريخ {{ $update->updated_at->format('Y-m-d H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history text-muted fa-3x mb-3"></i>
                            <p class="text-muted">لا توجد تحديثات حديثة للدرجات</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">إحصائيات الدرجات</h5>
                </div>
                <div class="card-body">
                    @if($totalGrades > 0)
                        <canvas id="gradesChart" width="400" height="300"></canvas>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-pie text-muted fa-3x mb-3"></i>
                            <p class="text-muted">لا توجد بيانات كافية لعرض الإحصائيات</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($totalGrades > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('gradesChart').getContext('2d');
        const gradesChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['ممتاز (A)', 'جيد جداً (B)', 'جيد (C)', 'مقبول (D)', 'راسب (F)'],
                datasets: [{
                    data: [
                        {{ $gradeDistribution['A'] ?? 0 }},
                        {{ $gradeDistribution['B'] ?? 0 }},
                        {{ $gradeDistribution['C'] ?? 0 }},
                        {{ $gradeDistribution['D'] ?? 0 }},
                        {{ $gradeDistribution['F'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(253, 126, 20, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(253, 126, 20, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endif
@endsection 