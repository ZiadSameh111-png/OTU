@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-chart-bar"></i> تقارير الاختبارات
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

                    <!-- Filter Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">تصفية النتائج</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.exams.reports') }}">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="course_id">المقرر</label>
                                        <select class="form-control" id="course_id" name="course_id">
                                            <option value="">الكل</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                                    {{ $course->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="group_id">المجموعة</label>
                                        <select class="form-control" id="group_id" name="group_id">
                                            <option value="">الكل</option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                                    {{ $group->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="date_from">من تاريخ</label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="date_to">إلى تاريخ</label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> تصفية
                                        </button>
                                        <a href="{{ route('admin.exams.reports') }}" class="btn btn-secondary">
                                            <i class="fas fa-sync"></i> إعادة تعيين
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Analytics Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">إجمالي الاختبارات</h5>
                                    <p class="card-text display-4">{{ $stats['total_exams'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">الاختبارات النشطة</h5>
                                    <p class="card-text display-4">{{ $stats['active_exams'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">عدد الطلاب المشاركين</h5>
                                    <p class="card-text display-4">{{ $stats['total_students'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">متوسط الدرجات</h5>
                                    <p class="card-text display-4">{{ number_format($stats['average_score'], 1) }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Exams Table -->
                    @if(count($exams) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>عنوان الاختبار</th>
                                        <th>المقرر</th>
                                        <th>المجموعة</th>
                                        <th>المدرس</th>
                                        <th>عدد المشاركين</th>
                                        <th>متوسط الدرجات</th>
                                        <th>أعلى درجة</th>
                                        <th>أدنى درجة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exams as $exam)
                                        <tr>
                                            <td>{{ $exam->title }}</td>
                                            <td>{{ $exam->course->name }}</td>
                                            <td>{{ $exam->group->name }}</td>
                                            <td>{{ $exam->teacher->name }}</td>
                                            <td>{{ $exam->participants_count }}</td>
                                            <td>{{ number_format($exam->average_score, 1) }}%</td>
                                            <td>{{ number_format($exam->highest_score, 1) }}%</td>
                                            <td>{{ number_format($exam->lowest_score, 1) }}%</td>
                                            <td>
                                                <a href="{{ route('admin.exams.report.detail', $exam->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> التفاصيل
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $exams->appends(request()->except('page'))->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-lg mb-3"></i>
                            <p>لا توجد اختبارات مطابقة لمعايير البحث.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">الرسوم البيانية للأداء</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">توزيع الدرجات</div>
                                <div class="card-body">
                                    <canvas id="gradesDistributionChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">متوسط الدرجات حسب المقرر</div>
                                <div class="card-body">
                                    <canvas id="courseAveragesChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">توزيع الاختبارات عبر الوقت</div>
                                <div class="card-body">
                                    <canvas id="examTimelineChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
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
        // Grades Distribution Chart
        const gradesCtx = document.getElementById('gradesDistributionChart').getContext('2d');
        new Chart(gradesCtx, {
            type: 'bar',
            data: {
                labels: ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'],
                datasets: [{
                    label: 'عدد الطلاب',
                    data: {{ json_encode($stats['grade_distribution']) }},
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(255, 205, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(54, 162, 235, 0.5)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Course Averages Chart
        const courseCtx = document.getElementById('courseAveragesChart').getContext('2d');
        new Chart(courseCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($stats['course_names']) !!},
                datasets: [{
                    label: 'متوسط الدرجات (%)',
                    data: {{ json_encode($stats['course_averages']) }},
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Exam Timeline Chart
        const timelineCtx = document.getElementById('examTimelineChart').getContext('2d');
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($stats['timeline_labels']) !!},
                datasets: [{
                    label: 'عدد الاختبارات',
                    data: {{ json_encode($stats['timeline_data']) }},
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endsection
