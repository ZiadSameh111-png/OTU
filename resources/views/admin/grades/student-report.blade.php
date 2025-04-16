@extends('layouts.app')

@section('title', 'تقرير الطالب - ' . $student->name)

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
    .student-profile {
        border-radius: 15px;
        overflow: hidden;
    }
    .student-profile-header {
        padding: 30px;
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
    }
    .profile-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.2);
        object-fit: cover;
    }
    .semester-card {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .semester-card:hover {
        transform: translateY(-5px);
    }
    .semester-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background-color: #2575fc;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">ملف الطالب: {{ $student->name }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.grades.reports') }}">تقارير الدرجات</a></li>
                    <li class="breadcrumb-item active">{{ $student->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.grades.export_student_report', $student->id) }}" class="btn btn-primary">
                <i class="fas fa-file-export me-1"></i> تصدير التقرير
            </a>
            <a href="{{ route('admin.grades.reports') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-1"></i> العودة
            </a>
        </div>
    </div>

    <!-- Student Profile Card -->
    <div class="card shadow-sm student-profile mb-4">
        <div class="student-profile-header">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img src="{{ $student->profile_image ?? asset('assets/img/default-avatar.png') }}" alt="{{ $student->name }}" class="profile-img">
                </div>
                <div class="col-md-5">
                    <h3 class="mb-1">{{ $student->name }}</h3>
                    <p class="mb-1"><i class="fas fa-id-card me-2"></i>{{ $student->id }}</p>
                    <p class="mb-1"><i class="fas fa-users me-2"></i>{{ $student->group ?? 'غير محدد' }}</p>
                    <p class="mb-0"><i class="fas fa-graduation-cap me-2"></i>{{ $student->program ?? 'غير محدد' }}</p>
                </div>
                <div class="col-md-5 text-md-end">
                    <div class="mb-3">
                        <h5 class="mb-1">المعدل التراكمي</h5>
                        <div class="d-flex align-items-center justify-content-md-end">
                            <h2 class="mb-0 me-2">{{ number_format($stats['gpa'], 2) }}</h2>
                            <span class="badge {{ getGpaBadgeColor($stats['gpa']) }} px-3 py-2">{{ getGpaText($stats['gpa']) }}</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-md-end">
                        <div class="me-4">
                            <h6 class="mb-1">الساعات المكتسبة</h6>
                            <p class="mb-0 h5">{{ $stats['earned_credits'] }}</p>
                        </div>
                        <div>
                            <h6 class="mb-1">الساعات المسجلة</h6>
                            <p class="mb-0 h5">{{ $stats['registered_credits'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">ملخص الأداء العام</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <td>عدد المقررات المجتازة</td>
                                <td><span class="badge bg-success">{{ $stats['passed_courses'] }}</span></td>
                            </tr>
                            <tr>
                                <td>عدد المقررات الغير مجتازة</td>
                                <td><span class="badge bg-danger">{{ $stats['failed_courses'] }}</span></td>
                            </tr>
                            <tr>
                                <td>عدد المقررات الحالية</td>
                                <td><span class="badge bg-info">{{ $stats['current_courses'] }}</span></td>
                            </tr>
                            <tr>
                                <td>معدل الحضور</td>
                                <td><span class="badge {{ getAttendanceBadgeColor($stats['attendance_rate']) }}">{{ $stats['attendance_rate'] }}%</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="card-title">الإحصائيات الأكاديمية</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <h6 class="text-muted mb-1">المعدل هذا الفصل</h6>
                                    <h3 class="mb-0">{{ number_format($stats['current_semester_gpa'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <h6 class="text-muted mb-1">متوسط الدرجات</h6>
                                    <h3 class="mb-0">{{ number_format($stats['overall_score'], 1) }}%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <h6 class="text-muted mb-1">متوسط النجاح</h6>
                                    <h3 class="mb-0">{{ number_format($stats['success_rate'], 1) }}%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <h6 class="text-muted mb-1">ترتيب الدفعة</h6>
                                    <h3 class="mb-0">{{ $stats['class_rank'] }} / {{ $stats['class_size'] }}</h3>
                                </div>
                            </div>
                        </div>
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
            <div class="card shadow-sm performance-card h-100">
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

    <!-- Current Semester Courses -->
    <div class="card shadow-sm performance-card mb-4">
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

    <!-- Academic History -->
    <div class="card shadow-sm performance-card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">السجل الأكاديمي</h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="academicHistoryAccordion">
                @forelse($academicHistory as $semester => $courses)
                    <div class="accordion-item semester-card mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#semester{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="semester{{ $loop->index }}">
                                <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                    <span>{{ $semester }}</span>
                                    <div>
                                        <span class="badge bg-primary me-2">المعدل: {{ number_format($semesterGPA[$semester] ?? 0, 2) }}</span>
                                        <span class="badge bg-secondary">الساعات: {{ $semesterCredits[$semester] ?? 0 }}</span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="semester{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#academicHistoryAccordion">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>المقرر</th>
                                                <th>الرمز</th>
                                                <th>الساعات</th>
                                                <th>الدرجة</th>
                                                <th>التقدير</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($courses as $course)
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
                                                    <td>{{ $course['total_score'] }}/{{ $course['total_max'] }}</td>
                                                    <td>
                                                        <span class="badge {{ getGradeBadgeColor($course['grade']) }} px-2 py-1">{{ $course['grade'] }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <p class="mb-0">لا يوجد سجل أكاديمي سابق</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Additional Notes -->
    <div class="card shadow-sm performance-card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">ملاحظات إضافية</h5>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="fas fa-plus me-1"></i> إضافة ملاحظة
            </button>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($notes as $note)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $note['title'] }}</h6>
                                <p class="text-muted mb-1">{{ $note['date'] }} - بواسطة {{ $note['author'] }}</p>
                                <p class="mb-0">{{ $note['content'] }}</p>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editNoteModal" data-note-id="{{ $note['id'] }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteNoteModal" data-note-id="{{ $note['id'] }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">لا توجد ملاحظات إضافية</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">إضافة ملاحظة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.student.add_note', $student->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="noteTitle" class="form-label">العنوان</label>
                        <input type="text" class="form-control" id="noteTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">المحتوى</label>
                        <textarea class="form-control" id="noteContent" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNoteModalLabel">تعديل الملاحظة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.student.edit_note') }}" method="POST" id="editNoteForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="note_id" id="editNoteId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editNoteTitle" class="form-label">العنوان</label>
                        <input type="text" class="form-control" id="editNoteTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editNoteContent" class="form-label">المحتوى</label>
                        <textarea class="form-control" id="editNoteContent" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Note Modal -->
<div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-labelledby="deleteNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteNoteModalLabel">حذف الملاحظة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من رغبتك في حذف هذه الملاحظة؟ هذا الإجراء لا يمكن التراجع عنه.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.student.delete_note') }}" method="POST" id="deleteNoteForm">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="note_id" id="deleteNoteId">
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
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

    // Note modals event handlers
    document.querySelectorAll('[data-bs-target="#editNoteModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-note-id');
            // Fetch note data via AJAX and populate the form
            // This is a placeholder, you would need to implement the actual data fetching
            document.getElementById('editNoteId').value = noteId;
            // Placeholder values
            document.getElementById('editNoteTitle').value = "عنوان الملاحظة";
            document.getElementById('editNoteContent').value = "محتوى الملاحظة...";
        });
    });

    document.querySelectorAll('[data-bs-target="#deleteNoteModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.getAttribute('data-note-id');
            document.getElementById('deleteNoteId').value = noteId;
        });
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

function getAttendanceBadgeColor($rate) {
    if ($rate >= 90) return 'bg-success';
    if ($rate >= 75) return 'bg-info';
    if ($rate >= 60) return 'bg-warning';
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