@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="m-0">إدارة درجات - {{ $course->name }}</h2>
                <a href="{{ route('teacher.grades.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
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
                    <h5 class="m-0">معلومات المقرر</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>اسم المقرر:</strong> {{ $course->name }}</p>
                            <p><strong>رمز المقرر:</strong> {{ $course->code }}</p>
                            <p><strong>توزيع الدرجات:</strong> 
                                الاختبارات الشهرية ({{ $course->midterm_grade }})، 
                                الأعمال العملية ({{ $course->assignment_grade }})، 
                                النهائي ({{ $course->final_grade }})
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>عدد المجموعات:</strong> {{ $groups->count() }}</p>
                            <p><strong>إجمالي عدد الطلاب:</strong> 
                                @php
                                    $totalStudents = 0;
                                    foreach($groups as $group) {
                                        $totalStudents += $group->students->count();
                                    }
                                    echo $totalStudents;
                                @endphp
                            </p>
                            <p><strong>نسبة اكتمال الدرجات:</strong> 
                                @php
                                    $submittedGrades = $grades->where('submitted', true)->count();
                                    echo $totalStudents > 0 ? 
                                        round(($submittedGrades / $totalStudents) * 100) . '%' : 
                                        '0%';
                                @endphp
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">جدول الدرجات</h5>
                    
                    <div class="d-flex">
                        <div class="me-3">
                            <select id="groupFilter" class="form-select form-select-sm">
                                <option value="all">جميع المجموعات</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="dropdown me-3">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="statusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-1"></i> تصفية
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="statusDropdown">
                                <li><a class="dropdown-item filter-status" data-status="all" href="#">جميع الطلاب</a></li>
                                <li><a class="dropdown-item filter-status" data-status="submitted" href="#">الدرجات المثبتة</a></li>
                                <li><a class="dropdown-item filter-status" data-status="not_submitted" href="#">الدرجات غير المثبتة</a></li>
                                <li><a class="dropdown-item filter-status" data-status="missing" href="#">بدون درجات</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <form id="gradesForm">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>اسم الطالب</th>
                                        <th>رقم الطالب</th>
                                        <th>المجموعة</th>
                                        <th width="12%">الاختبارات الشهرية ({{ $course->midterm_grade }})</th>
                                        <th width="12%">الأعمال العملية ({{ $course->assignment_grade }})</th>
                                        <th width="12%">النهائي ({{ $course->final_grade }})</th>
                                        <th width="12%">المجموع ({{ $course->midterm_grade + $course->assignment_grade + $course->final_grade }})</th>
                                        <th>ملاحظات</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $studentsFound = false;
                                    @endphp
                                    
                                    @foreach($groups as $group)
                                        @foreach($group->students as $student)
                                            @php
                                                $studentsFound = true;
                                                $grade = $grades->where('student_id', $student->id)->first();
                                                $midtermGrade = $grade ? $grade->midterm_grade : null;
                                                $assignmentGrade = $grade ? $grade->assignment_grade : null;
                                                $finalGrade = $grade ? $grade->final_grade : null;
                                                $comments = $grade ? $grade->comments : '';
                                                $submitted = $grade ? $grade->submitted : false;
                                                $totalGrade = ($midtermGrade !== null ? $midtermGrade : 0) + 
                                                             ($assignmentGrade !== null ? $assignmentGrade : 0) + 
                                                             ($finalGrade !== null ? $finalGrade : 0);
                                            @endphp
                                            
                                            <tr class="student-row" 
                                                data-group-id="{{ $group->id }}" 
                                                data-status="{{ $grade ? ($submitted ? 'submitted' : 'not_submitted') : 'missing' }}">
                                                <td>{{ $student->name }}</td>
                                                <td>{{ $student->student_id }}</td>
                                                <td>{{ $group->name }}</td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control grade-input midterm-grade" 
                                                           name="grades[{{ $student->id }}][midterm_grade]" 
                                                           value="{{ $midtermGrade }}"
                                                           min="0" 
                                                           max="{{ $course->midterm_grade }}" 
                                                           step="0.5"
                                                           {{ $submitted ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control grade-input assignment-grade" 
                                                           name="grades[{{ $student->id }}][assignment_grade]" 
                                                           value="{{ $assignmentGrade }}"
                                                           min="0" 
                                                           max="{{ $course->assignment_grade }}" 
                                                           step="0.5"
                                                           {{ $submitted ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control grade-input final-grade" 
                                                           name="grades[{{ $student->id }}][final_grade]" 
                                                           value="{{ $finalGrade }}"
                                                           min="0" 
                                                           max="{{ $course->final_grade }}" 
                                                           step="0.5"
                                                           {{ $submitted ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <span class="total-grade">{{ $totalGrade }}</span>/{{ $course->midterm_grade + $course->assignment_grade + $course->final_grade }}
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="grades[{{ $student->id }}][comments]" 
                                                           value="{{ $comments }}"
                                                           {{ $submitted ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    @if($submitted)
                                                        <span class="badge bg-success">مثبتة</span>
                                                    @else
                                                        <span class="badge bg-warning">غير مثبتة</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    
                                    @if(!$studentsFound)
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                لا يوجد طلاب مسجلين في هذا المقرر
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" id="saveGradesBtn" class="btn btn-primary me-2">
                                <i class="fas fa-save me-1"></i> حفظ التغييرات
                            </button>
                            <button type="button" id="submitGradesBtn" class="btn btn-success">
                                <i class="fas fa-check-circle me-1"></i> تثبيت الدرجات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-labelledby="submitConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitConfirmModalLabel">تأكيد تثبيت الدرجات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تحذير: بعد تثبيت الدرجات، لن تتمكن من تعديلها إلا بطلب من الإدارة. هل أنت متأكد من تثبيت الدرجات؟
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="confirmSubmitBtn" class="btn btn-success">تأكيد التثبيت</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate total grade when input changes
        document.querySelectorAll('.grade-input').forEach(function(input) {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const midtermGrade = parseFloat(row.querySelector('.midterm-grade').value) || 0;
                const assignmentGrade = parseFloat(row.querySelector('.assignment-grade').value) || 0;
                const finalGrade = parseFloat(row.querySelector('.final-grade').value) || 0;
                
                const totalGrade = midtermGrade + assignmentGrade + finalGrade;
                row.querySelector('.total-grade').textContent = totalGrade;
            });
        });
        
        // Group filter
        document.getElementById('groupFilter').addEventListener('change', function() {
            const groupId = this.value;
            filterStudents();
        });
        
        // Status filter
        document.querySelectorAll('.filter-status').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.filter-status').forEach(item => item.classList.remove('active'));
                this.classList.add('active');
                filterStudents();
            });
        });
        
        function filterStudents() {
            const groupId = document.getElementById('groupFilter').value;
            const statusFilter = document.querySelector('.filter-status.active')?.dataset.status || 'all';
            
            document.querySelectorAll('.student-row').forEach(function(row) {
                const rowGroupId = row.dataset.groupId;
                const rowStatus = row.dataset.status;
                
                let showByGroup = groupId === 'all' || rowGroupId === groupId;
                let showByStatus = statusFilter === 'all' || rowStatus === statusFilter;
                
                row.style.display = (showByGroup && showByStatus) ? '' : 'none';
            });
        }
        
        // Save grades
        document.getElementById('saveGradesBtn').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('gradesForm'));
            
            fetch('{{ route("teacher.grades.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.success);
                } else if(data.error) {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء حفظ الدرجات');
            });
        });
        
        // Submit grades - show confirmation modal
        document.getElementById('submitGradesBtn').addEventListener('click', function() {
            const submitModal = new bootstrap.Modal(document.getElementById('submitConfirmModal'));
            submitModal.show();
        });
        
        // Confirm submit grades
        document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('gradesForm'));
            
            fetch('{{ route("teacher.grades.submit") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.success);
                    location.reload();
                } else if(data.error) {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء تثبيت الدرجات');
            });
            
            bootstrap.Modal.getInstance(document.getElementById('submitConfirmModal')).hide();
        });
    });
</script>
@endpush 