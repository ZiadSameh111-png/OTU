@extends('layouts.app')

@section('title', 'إدارة درجات الطلاب - ' . $course->name)

@section('styles')
<style>
    .student-row:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .grade-input {
        width: 60px;
        text-align: center;
    }
    .grade-cell {
        width: 100px;
    }
    .badge-grade {
        min-width: 36px;
    }
    .grade-status-icon {
        width: 18px;
        height: 18px;
    }
    .action-buttons {
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.grades.index') }}">إدارة الدرجات</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $course->name }}</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $course->name }}</h2>
            <p class="text-muted mb-0">{{ $course->code }} | {{ $course->semester }}</p>
        </div>
        <div class="d-flex">
            <a href="{{ route('teacher.grades.exams', $course->id) }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-file-alt me-1"></i> إدارة درجات الاختبارات
            </a>
            <a href="{{ route('teacher.grades.report', $course->id) }}" class="btn btn-outline-info">
                <i class="fas fa-chart-bar me-1"></i> عرض تقرير الدرجات
            </a>
        </div>
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

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">درجات الطلاب</h5>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#importGradesModal">
                    <i class="fas fa-file-import me-1"></i> استيراد الدرجات
                </button>
                <a href="{{ route('teacher.grades.export', $course->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-export me-1"></i> تصدير الدرجات
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>المجموعة</th>
                            <th>أعمال المقرر ({{ $courseSettings->coursework_percentage }}%)</th>
                            <th>منتصف الفصل ({{ $courseSettings->midterm_percentage }}%)</th>
                            <th>نهائي ({{ $courseSettings->final_percentage }}%)</th>
                            <th>عملي ({{ $courseSettings->practical_percentage }}%)</th>
                            <th>المجموع</th>
                            <th>التقدير</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                            <tr class="student-row">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light rounded-circle text-primary me-3 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $student->name }}</h6>
                                            <small class="text-muted">{{ $student->student_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->group->name ?? 'غير محدد' }}</td>
                                <td class="grade-cell">
                                    @if(isset($grades[$student->id]))
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark badge-grade me-2">{{ $grades[$student->id]->coursework_score ?? 0 }}</span>
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" 
                                                    style="width: {{ ($grades[$student->id]->coursework_score / $courseSettings->max_coursework) * 100 }}%" aria-valuenow="{{ $grades[$student->id]->coursework_score ?? 0 }}" 
                                                    aria-valuemin="0" aria-valuemax="{{ $courseSettings->max_coursework }}">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">غير متوفر</span>
                                    @endif
                                </td>
                                <td class="grade-cell">
                                    @if(isset($grades[$student->id]))
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark badge-grade me-2">{{ $grades[$student->id]->midterm_score ?? 0 }}</span>
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-info" role="progressbar" 
                                                    style="width: {{ ($grades[$student->id]->midterm_score / $courseSettings->max_midterm) * 100 }}%" aria-valuenow="{{ $grades[$student->id]->midterm_score ?? 0 }}" 
                                                    aria-valuemin="0" aria-valuemax="{{ $courseSettings->max_midterm }}">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">غير متوفر</span>
                                    @endif
                                </td>
                                <td class="grade-cell">
                                    @if(isset($grades[$student->id]))
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark badge-grade me-2">{{ $grades[$student->id]->final_score ?? 0 }}</span>
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-dark" role="progressbar" 
                                                    style="width: {{ ($grades[$student->id]->final_score / $courseSettings->max_final) * 100 }}%" aria-valuenow="{{ $grades[$student->id]->final_score ?? 0 }}" 
                                                    aria-valuemin="0" aria-valuemax="{{ $courseSettings->max_final }}">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">غير متوفر</span>
                                    @endif
                                </td>
                                <td class="grade-cell">
                                    @if(isset($grades[$student->id]))
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark badge-grade me-2">{{ $grades[$student->id]->practical_score ?? 0 }}</span>
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                    style="width: {{ ($grades[$student->id]->practical_score / $courseSettings->max_practical) * 100 }}%" aria-valuenow="{{ $grades[$student->id]->practical_score ?? 0 }}" 
                                                    aria-valuemin="0" aria-valuemax="{{ $courseSettings->max_practical }}">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">غير متوفر</span>
                                    @endif
                                </td>
                                <td class="fw-bold">
                                    @if(isset($grades[$student->id]))
                                        {{ $grades[$student->id]->total_percentage }}%
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($grades[$student->id]))
                                        <span class="badge {{ $gradeColors[$grades[$student->id]->grade] ?? 'bg-secondary' }}">
                                            {{ $grades[$student->id]->grade }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($grades[$student->id]))
                                        @if($grades[$student->id]->is_finalized)
                                            <i class="fas fa-lock text-secondary grade-status-icon" title="تم اعتماد الدرجات"></i>
                                        @else
                                            <i class="fas fa-unlock text-warning grade-status-icon" title="لم يتم الاعتماد بعد"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-circle text-muted grade-status-icon" title="لم يتم إدخال الدرجات"></i>
                                    @endif
                                </td>
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editGradeModal" 
                                        data-student-id="{{ $student->id }}"
                                        data-student-name="{{ $student->name }}"
                                        data-coursework="{{ isset($grades[$student->id]) ? $grades[$student->id]->coursework_score : 0 }}"
                                        data-midterm="{{ isset($grades[$student->id]) ? $grades[$student->id]->midterm_score : 0 }}"
                                        data-final="{{ isset($grades[$student->id]) ? $grades[$student->id]->final_score : 0 }}"
                                        data-practical="{{ isset($grades[$student->id]) ? $grades[$student->id]->practical_score : 0 }}"
                                        data-comments="{{ isset($grades[$student->id]) ? $grades[$student->id]->comments : '' }}"
                                        data-is-finalized="{{ isset($grades[$student->id]) ? $grades[$student->id]->is_finalized : 0 }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="fas fa-user-graduate text-muted fa-3x mb-3"></i>
                                    <p class="text-muted">لا يوجد طلاب مسجلين في هذا المقرر.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-muted small">
            <div class="row">
                <div class="col-md-6">
                    <i class="fas fa-info-circle me-1"></i> 
                    الدرجات النهائية: A (90-100%), B (80-89%), C (70-79%), D (60-69%), F (0-59%)
                </div>
                <div class="col-md-6 text-md-end">
                    <i class="fas fa-lock me-1"></i> بعد اعتماد الدرجات لا يمكن تعديلها إلا بإذن من الإدارة
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Grade Modal -->
<div class="modal fade" id="editGradeModal" tabindex="-1" aria-labelledby="editGradeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teacher.grades.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="student_id" id="student_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editGradeModalLabel">تعديل درجات الطالب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الطالب:</label>
                        <div id="studentName" class="form-control-plaintext"></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="coursework" class="form-label">أعمال المقرر ({{ $courseSettings->max_coursework }} درجة)</label>
                            <input type="number" class="form-control" id="coursework" name="coursework_score" min="0" max="{{ $courseSettings->max_coursework }}" step="0.5" required>
                        </div>
                        <div class="col-md-6">
                            <label for="midterm" class="form-label">منتصف الفصل ({{ $courseSettings->max_midterm }} درجة)</label>
                            <input type="number" class="form-control" id="midterm" name="midterm_score" min="0" max="{{ $courseSettings->max_midterm }}" step="0.5" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="final" class="form-label">نهائي ({{ $courseSettings->max_final }} درجة)</label>
                            <input type="number" class="form-control" id="final" name="final_score" min="0" max="{{ $courseSettings->max_final }}" step="0.5" required>
                        </div>
                        <div class="col-md-6">
                            <label for="practical" class="form-label">عملي ({{ $courseSettings->max_practical }} درجة)</label>
                            <input type="number" class="form-control" id="practical" name="practical_score" min="0" max="{{ $courseSettings->max_practical }}" step="0.5" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comments" class="form-label">ملاحظات</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_finalized" name="is_finalized" value="1">
                        <label class="form-check-label" for="is_finalized">
                            اعتماد الدرجات (بعد الاعتماد لا يمكن تعديل الدرجات)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ الدرجات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Grades Modal -->
<div class="modal fade" id="importGradesModal" tabindex="-1" aria-labelledby="importGradesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teacher.grades.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="importGradesModalLabel">استيراد درجات الطلاب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="grades_file" class="form-label">ملف الدرجات (Excel)</label>
                        <input type="file" class="form-control" id="grades_file" name="grades_file" required accept=".xlsx,.xls,.csv">
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i> ملاحظات:</h6>
                        <ul class="mb-0">
                            <li>يجب أن يحتوي الملف على أعمدة: الرقم الجامعي، أعمال المقرر، منتصف الفصل، النهائي، العملي.</li>
                            <li>يمكنك <a href="{{ route('teacher.grades.template', $course->id) }}" class="alert-link">تحميل نموذج Excel</a> لتعبئة الدرجات.</li>
                            <li>سيتم تحديث الدرجات الموجودة للطلاب، وإضافة الدرجات الجديدة.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">استيراد</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Populate edit modal with student data
        const editGradeModal = document.getElementById('editGradeModal');
        if (editGradeModal) {
            editGradeModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const studentId = button.getAttribute('data-student-id');
                const studentName = button.getAttribute('data-student-name');
                const coursework = button.getAttribute('data-coursework');
                const midterm = button.getAttribute('data-midterm');
                const final = button.getAttribute('data-final');
                const practical = button.getAttribute('data-practical');
                const comments = button.getAttribute('data-comments');
                const isFinalized = button.getAttribute('data-is-finalized') === '1';
                
                // Set values in the modal
                document.getElementById('student_id').value = studentId;
                document.getElementById('studentName').textContent = studentName;
                document.getElementById('coursework').value = coursework;
                document.getElementById('midterm').value = midterm;
                document.getElementById('final').value = final;
                document.getElementById('practical').value = practical;
                document.getElementById('comments').value = comments;
                document.getElementById('is_finalized').checked = isFinalized;
                
                // Disable fields if grades are finalized
                const formElements = [
                    document.getElementById('coursework'),
                    document.getElementById('midterm'),
                    document.getElementById('final'),
                    document.getElementById('practical'),
                    document.getElementById('comments'),
                    document.getElementById('is_finalized')
                ];
                
                if (isFinalized) {
                    // Show finalized message
                    const modalBody = editGradeModal.querySelector('.modal-body');
                    
                    // Check if notification already exists
                    let notification = modalBody.querySelector('.finalized-notification');
                    if (!notification) {
                        notification = document.createElement('div');
                        notification.className = 'alert alert-warning finalized-notification mb-3';
                        notification.innerHTML = '<i class="fas fa-lock me-2"></i> تم اعتماد درجات هذا الطالب. لا يمكن تعديلها إلا بإذن من الإدارة.';
                        modalBody.insertBefore(notification, modalBody.firstChild);
                    }
                    
                    // Disable form elements
                    formElements.forEach(el => {
                        if (el) el.disabled = true;
                    });
                } else {
                    // Remove finalized message if exists
                    const notification = editGradeModal.querySelector('.finalized-notification');
                    if (notification) {
                        notification.remove();
                    }
                    
                    // Enable form elements
                    formElements.forEach(el => {
                        if (el) el.disabled = false;
                    });
                }
            });
        }
    });
</script>
@endsection 