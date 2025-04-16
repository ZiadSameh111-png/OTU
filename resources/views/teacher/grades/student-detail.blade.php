@extends('layouts.app')

@section('title', 'تفاصيل درجات الطالب ' . $student->name)

@section('styles')
<style>
    .grade-card {
        transition: all 0.3s ease;
    }
    .grade-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .grade-input {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 0.5rem;
        width: 80px;
        text-align: center;
    }
    .grade-input:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    .grade-label {
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    .separator {
        display: flex;
        align-items: center;
        color: #6c757d;
    }
    .separator:before, .separator:after {
        content: "";
        flex: 1;
        border-bottom: 1px solid #dee2e6;
    }
    .separator:before {
        margin-right: 1rem;
    }
    .separator:after {
        margin-left: 1rem;
    }
    .loading {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255,255,255,0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
    }
    .loading-text {
        margin-top: 1rem;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>تفاصيل درجات الطالب</h2>
            <p class="text-muted">{{ $student->name }} - {{ $course->name }}</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('teacher.grades.manage', ['courseId' => $course->id]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i> العودة لجدول الدرجات
            </a>
            @if($examAttempts->count() > 0)
            <a href="{{ route('teacher.grades.update-online', ['course_id' => $course->id, 'student_id' => $student->id]) }}" class="btn btn-info">
                <i class="fas fa-sync me-2"></i> تحديث درجات الاختبارات الإلكترونية
            </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">معلومات الطالب</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-wrapper bg-light rounded-circle me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $student->name }}</h5>
                            <p class="text-muted mb-0">{{ $student->id }}</p>
                        </div>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span>المجموعة:</span>
                            <span class="fw-bold">{{ $student->group->name ?? 'غير محدد' }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span>البريد الإلكتروني:</span>
                            <span class="fw-bold">{{ $student->email }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span>الهاتف:</span>
                            <span class="fw-bold">{{ $student->phone ?? 'غير متوفر' }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span>عدد محاولات الاختبار:</span>
                            <span class="fw-bold">{{ $examAttempts->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <form id="gradesForm" method="POST" action="{{ route('teacher.grades.update-student', ['studentId' => $student->id, 'courseId' => $course->id]) }}">
                @csrf
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">درجات المقرر</h5>
                        <div>
                            <button type="submit" id="saveBtn" class="btn btn-success">
                                <i class="fas fa-save me-2"></i> حفظ التغييرات
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($grade && $grade->is_final)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>ملاحظة:</strong> تم تأكيد وإرسال درجات هذا الطالب، ولا يمكن تعديلها.
                            </div>
                        @endif

                        <div class="separator mb-4">الاختبارات الإلكترونية</div>

                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <div class="grade-label">درجة الاختبارات الإلكترونية</div>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="online_exam_grade" value="{{ $grade ? $grade->online_exam_grade : 0 }}" {{ $grade && $grade->is_final ? 'disabled' : '' }} min="0" step="0.5">
                                    <span class="input-group-text">/</span>
                                    <input type="number" class="form-control" name="online_exam_total" value="{{ $grade ? $grade->online_exam_total : $totalOnlineMarks }}" {{ $grade && $grade->is_final ? 'disabled' : '' }} min="0" step="0.5">
                                </div>
                            </div>

                            @if($examAttempts->count() > 0)
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>اسم الاختبار</th>
                                                    <th>تاريخ المحاولة</th>
                                                    <th>الدرجة</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($examAttempts as $attempt)
                                                <tr>
                                                    <td>{{ $attempt->exam->title }}</td>
                                                    <td>{{ $attempt->created_at->format('Y-m-d H:i') }}</td>
                                                    <td>
                                                        {{ $attempt->score }} / {{ $attempt->exam->total_marks }}
                                                        <div class="progress mt-1" style="height: 5px;">
                                                            <div class="progress-bar" role="progressbar" style="width: {{ ($attempt->score / $attempt->exam->total_marks) * 100 }}%"></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('grades.exam-detail', ['attemptId' => $attempt->id]) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        لم يقم الطالب بأداء أي اختبارات إلكترونية بعد.
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="separator mb-4">الاختبارات الورقية والعملية</div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="grade-label">درجة الاختبارات الورقية</div>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="paper_exam_grade" value="{{ $grade ? $grade->paper_exam_grade : 0 }}" {{ $grade && $grade->is_final ? 'disabled' : '' }} min="0" step="0.5">
                                    <span class="input-group-text">/</span>
                                    <input type="number" class="form-control" name="paper_exam_total" value="{{ $grade ? $grade->paper_exam_total : 0 }}" {{ $grade && $grade->is_final ? 'disabled' : '' }} min="0" step="0.5">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="grade-label">الدرجة العملية</div>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="practical_grade" value="{{ $grade ? $grade->practical_grade : 0 }}" {{ $grade && $grade->is_final ? 'disabled' : '' }} min="0" step="0.5">
                                    <span class="input-group-text">/</span>
                                    <input type="number" class="form-control" name="practical_total" value="{{ $grade ? $grade->practical_total : 0 }}" {{ $grade && $grade->is_final ? 'disabled' : '' }} min="0" step="0.5">
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="grade-label">ملاحظات</div>
                                <textarea class="form-control" name="comments" rows="3" {{ $grade && $grade->is_final ? 'disabled' : '' }}>{{ $grade ? $grade->comments : '' }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">الدرجة الإجمالية</h5>
                                            <div>
                                                <span class="h4">
                                                    @php
                                                        $totalGrade = 0;
                                                        $totalPossible = 0;
                                                        
                                                        if ($grade) {
                                                            $totalGrade = $grade->total_grade;
                                                            $totalPossible = $grade->total_possible;
                                                        }
                                                        
                                                        $percentage = $totalPossible > 0 ? round(($totalGrade / $totalPossible) * 100) : 0;
                                                    @endphp
                                                    {{ $totalGrade }} / {{ $totalPossible }}
                                                </span>
                                                <span class="ms-2 badge {{ $percentage >= 60 ? 'bg-success' : 'bg-danger' }}">{{ $percentage }}%</span>
                                            </div>
                                        </div>
                                        <div class="progress mt-2" style="height: 10px;">
                                            <div class="progress-bar {{ $percentage >= 60 ? 'bg-success' : 'bg-danger' }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="loading d-none">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    <div class="loading-text">جاري حفظ البيانات...</div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show loading spinner when form is submitted
        document.getElementById('gradesForm').addEventListener('submit', function() {
            document.querySelector('.loading').classList.remove('d-none');
        });
    });
</script>
@endsection 