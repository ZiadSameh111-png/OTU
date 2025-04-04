@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="m-0">تعديل الدرجات</h2>
                <a href="{{ route('admin.grades.reports') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i> العودة للتقارير
                </a>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="m-0">معلومات الطالب والمقرر</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>اسم الطالب:</strong> {{ $grade->student->name }}</p>
                            <p><strong>رقم الطالب:</strong> {{ $grade->student->student_id }}</p>
                            <p><strong>المجموعة:</strong> {{ $grade->student->group->name ?? 'غير محدد' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>اسم المقرر:</strong> {{ $grade->course->name }}</p>
                            <p><strong>رمز المقرر:</strong> {{ $grade->course->code }}</p>
                            <p><strong>أستاذ المقرر:</strong> {{ $grade->course->teacher->name ?? 'غير محدد' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0">نموذج تعديل الدرجات</h5>
                </div>
                <div class="card-body">
                    <form id="editGradeForm" action="{{ route('admin.grades.update', $grade->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="midterm_grade" class="form-label">درجة الاختبارات الشهرية</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="midterm_grade" name="midterm_grade" 
                                        value="{{ $grade->midterm_grade }}" min="0" max="{{ $grade->course->midterm_grade }}" step="0.5" required>
                                    <span class="input-group-text">/ {{ $grade->course->midterm_grade }}</span>
                                </div>
                                @error('midterm_grade')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="assignment_grade" class="form-label">درجة الأعمال العملية</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="assignment_grade" name="assignment_grade" 
                                        value="{{ $grade->assignment_grade }}" min="0" max="{{ $grade->course->assignment_grade }}" step="0.5" required>
                                    <span class="input-group-text">/ {{ $grade->course->assignment_grade }}</span>
                                </div>
                                @error('assignment_grade')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="final_grade" class="form-label">الدرجة النهائية</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="final_grade" name="final_grade" 
                                        value="{{ $grade->final_grade }}" min="0" max="{{ $grade->course->final_grade }}" step="0.5" required>
                                    <span class="input-group-text">/ {{ $grade->course->final_grade }}</span>
                                </div>
                                @error('final_grade')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comments" class="form-label">ملاحظات</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3">{{ $grade->comments }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_reason" class="form-label">سبب التعديل <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_reason" name="edit_reason" rows="3" required></textarea>
                            <div class="form-text">يرجى ذكر سبب تعديل الدرجات، سيتم تسجيل هذا في سجل التعديلات.</div>
                            @error('edit_reason')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">إلغاء</button>
                            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                        </div>
                    </form>
                </div>
            </div>
            
            @if(isset($grade->edit_logs) && count($grade->edit_logs) > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="m-0">سجل التعديلات</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>بواسطة</th>
                                        <th>الدرجات القديمة</th>
                                        <th>الدرجات الجديدة</th>
                                        <th>سبب التعديل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grade->edit_logs as $log)
                                        <tr>
                                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $log->user->name ?? 'غير معروف' }}</td>
                                            <td>
                                                الاختبارات: {{ $log->old_midterm_grade }}<br>
                                                الأعمال: {{ $log->old_assignment_grade }}<br>
                                                النهائي: {{ $log->old_final_grade }}
                                            </td>
                                            <td>
                                                الاختبارات: {{ $log->new_midterm_grade }}<br>
                                                الأعمال: {{ $log->new_assignment_grade }}<br>
                                                النهائي: {{ $log->new_final_grade }}
                                            </td>
                                            <td>{{ $log->reason }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const midtermGradeInput = document.getElementById('midterm_grade');
        const assignmentGradeInput = document.getElementById('assignment_grade');
        const finalGradeInput = document.getElementById('final_grade');
        
        function validateGrades() {
            const midtermGrade = parseFloat(midtermGradeInput.value) || 0;
            const assignmentGrade = parseFloat(assignmentGradeInput.value) || 0;
            const finalGrade = parseFloat(finalGradeInput.value) || 0;
            
            const midtermMax = parseFloat('{{ $grade->course->midterm_grade }}');
            const assignmentMax = parseFloat('{{ $grade->course->assignment_grade }}');
            const finalMax = parseFloat('{{ $grade->course->final_grade }}');
            
            let isValid = true;
            
            if (midtermGrade < 0 || midtermGrade > midtermMax) {
                midtermGradeInput.classList.add('is-invalid');
                isValid = false;
            } else {
                midtermGradeInput.classList.remove('is-invalid');
            }
            
            if (assignmentGrade < 0 || assignmentGrade > assignmentMax) {
                assignmentGradeInput.classList.add('is-invalid');
                isValid = false;
            } else {
                assignmentGradeInput.classList.remove('is-invalid');
            }
            
            if (finalGrade < 0 || finalGrade > finalMax) {
                finalGradeInput.classList.add('is-invalid');
                isValid = false;
            } else {
                finalGradeInput.classList.remove('is-invalid');
            }
            
            return isValid;
        }
        
        document.getElementById('editGradeForm').addEventListener('submit', function(event) {
            if (!validateGrades()) {
                event.preventDefault();
                alert('يرجى التأكد من صحة الدرجات المدخلة.');
            }
        });
        
        [midtermGradeInput, assignmentGradeInput, finalGradeInput].forEach(input => {
            input.addEventListener('input', validateGrades);
        });
    });
</script>
@endpush 