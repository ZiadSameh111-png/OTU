@extends('layouts.app')

@section('title', 'إدارة درجات ' . $course->name)

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    .grade-input {
        width: 70px;
    }
    .grade-total {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .student-row:hover {
        background-color: rgba(0,123,255,0.05);
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
    .edit-grades-btn {
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>إدارة درجات {{ $course->name }}</h2>
            <p class="text-muted">{{ $course->code }}</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('teacher.grades.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i> العودة للمقررات
            </a>
            <a href="{{ route('teacher.grades.update-online', ['course_id' => $course->id]) }}" class="btn btn-info">
                <i class="fas fa-sync me-2"></i> تحديث درجات الاختبارات الإلكترونية
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">فلترة الطلاب</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row">
                <div class="col-md-4 mb-3">
                    <label for="group" class="form-label">المجموعة</label>
                    <select class="form-select" id="group">
                        <option value="">جميع المجموعات</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">حالة الدرجات</label>
                    <select class="form-select" id="status">
                        <option value="">جميع الحالات</option>
                        <option value="finalized">المؤكدة</option>
                        <option value="not-finalized">غير مؤكدة</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="button" id="filterBtn" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i> تطبيق الفلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form id="gradesForm">
        @csrf
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">درجات الطلاب</h5>
                <div>
                    <button type="button" id="saveBtn" class="btn btn-success me-2">
                        <i class="fas fa-save me-2"></i> حفظ الدرجات
                    </button>
                    <button type="button" id="finalizeBtn" class="btn btn-danger">
                        <i class="fas fa-check-circle me-2"></i> تأكيد وإرسال الدرجات
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 20%">اسم الطالب</th>
                                <th style="width: 10%">رقم الطالب</th>
                                <th style="width: 10%">المجموعة</th>
                                <th style="width: 15%">درجة الاختبارات الإلكترونية</th>
                                <th style="width: 15%">درجة الاختبارات الورقية</th>
                                <th style="width: 15%">الدرجة العملية</th>
                                <th style="width: 10%">الإجمالي</th>
                                <th style="width: 10%">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTable">
                            @foreach($students as $index => $student)
                            @php
                                $grade = $gradesCollection[$student->id] ?? null;
                                $isFinalized = $grade && $grade->is_final;
                                $totalGrade = $grade ? $grade->total_grade : 0;
                                $totalPossible = $grade ? $grade->total_possible : 0;
                                
                                // Online exam grades
                                $onlineExamGrade = $grade ? $grade->online_exam_grade : null;
                                $onlineExamTotal = $grade ? $grade->online_exam_total : null;
                                
                                // Paper exam grades
                                $paperExamGrade = $grade ? $grade->paper_exam_grade : null;
                                $paperExamTotal = $grade ? $grade->paper_exam_total : null;
                                
                                // Practical grades
                                $practicalGrade = $grade ? $grade->practical_grade : null;
                                $practicalTotal = $grade ? $grade->practical_total : null;
                            @endphp
                            <tr class="student-row {{ $isFinalized ? 'table-success' : '' }}" data-student-id="{{ $student->id }}" data-group-id="{{ $student->group_id }}" data-status="{{ $isFinalized ? 'finalized' : 'not-finalized' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->id }}</td>
                                <td>{{ $student->group->name ?? 'غير محدد' }}</td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" min="0" step="0.5" class="form-control grade-input online-grade" name="grades[{{ $student->id }}][online_exam_grade]" value="{{ $onlineExamGrade }}" {{ $isFinalized ? 'disabled' : '' }}>
                                        <span class="input-group-text">/</span>
                                        <input type="number" min="0" step="0.5" class="form-control grade-input online-total" name="grades[{{ $student->id }}][online_exam_total]" value="{{ $onlineExamTotal ?? $totalOnlineMarks }}" {{ $isFinalized ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" min="0" step="0.5" class="form-control grade-input paper-grade" name="grades[{{ $student->id }}][paper_exam_grade]" value="{{ $paperExamGrade }}" {{ $isFinalized ? 'disabled' : '' }}>
                                        <span class="input-group-text">/</span>
                                        <input type="number" min="0" step="0.5" class="form-control grade-input paper-total" name="grades[{{ $student->id }}][paper_exam_total]" value="{{ $paperExamTotal }}" {{ $isFinalized ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" min="0" step="0.5" class="form-control grade-input practical-grade" name="grades[{{ $student->id }}][practical_grade]" value="{{ $practicalGrade }}" {{ $isFinalized ? 'disabled' : '' }}>
                                        <span class="input-group-text">/</span>
                                        <input type="number" min="0" step="0.5" class="form-control grade-input practical-total" name="grades[{{ $student->id }}][practical_total]" value="{{ $practicalTotal }}" {{ $isFinalized ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="total-grade">{{ $totalGrade }}</span> / <span class="total-possible">{{ $totalPossible }}</span>
                                    @if($totalPossible > 0)
                                    <div class="progress mt-1" style="height: 5px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ ($totalGrade / $totalPossible) * 100 }}%"></div>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('teacher.grades.student-detail', ['studentId' => $student->id, 'courseId' => $course->id]) }}" class="btn btn-sm btn-primary edit-grades-btn w-100">
                                        <i class="fas fa-edit me-1"></i> التفاصيل
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <div class="loading d-none">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <div class="loading-text">جاري حفظ الدرجات...</div>
    </div>
</div>

<!-- Modal de confirmación para finalización -->
<div class="modal fade" id="finalizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">تأكيد إرسال الدرجات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>تنبيه هام:</strong> بعد تأكيد الدرجات وإرسالها، لن يمكنك تعديلها بعد ذلك. هل أنت متأكد من رغبتك في تأكيد وإرسال الدرجات؟
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="confirmFinalizeBtn" class="btn btn-danger">تأكيد وإرسال</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to filter students
        document.getElementById('filterBtn').addEventListener('click', function() {
            const groupId = document.getElementById('group').value;
            const status = document.getElementById('status').value;
            
            const rows = document.querySelectorAll('#studentsTable tr');
            
            rows.forEach(row => {
                const rowGroupId = row.getAttribute('data-group-id');
                const rowStatus = row.getAttribute('data-status');
                let showRow = true;
                
                if (groupId && rowGroupId !== groupId) {
                    showRow = false;
                }
                
                if (status && rowStatus !== status) {
                    showRow = false;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        });
        
        // Calculate total grade for a row
        function calculateTotal(row) {
            const onlineGrade = parseFloat(row.querySelector('.online-grade').value) || 0;
            const paperGrade = parseFloat(row.querySelector('.paper-grade').value) || 0;
            const practicalGrade = parseFloat(row.querySelector('.practical-grade').value) || 0;
            
            const onlineTotal = parseFloat(row.querySelector('.online-total').value) || 0;
            const paperTotal = parseFloat(row.querySelector('.paper-total').value) || 0;
            const practicalTotal = parseFloat(row.querySelector('.practical-total').value) || 0;
            
            const totalGrade = onlineGrade + paperGrade + practicalGrade;
            const totalPossible = onlineTotal + paperTotal + practicalTotal;
            
            row.querySelector('.total-grade').textContent = totalGrade.toFixed(1);
            row.querySelector('.total-possible').textContent = totalPossible.toFixed(1);
            
            if (totalPossible > 0) {
                row.querySelector('.progress-bar').style.width = `${(totalGrade / totalPossible) * 100}%`;
            }
        }
        
        // Add event listeners to recalculate totals when grades change
        document.querySelectorAll('.grade-input').forEach(input => {
            input.addEventListener('change', function() {
                const row = this.closest('tr');
                calculateTotal(row);
            });
        });
        
        // Calculate initial totals
        document.querySelectorAll('.student-row').forEach(row => {
            calculateTotal(row);
        });
        
        // Save grades
        document.getElementById('saveBtn').addEventListener('click', function() {
            const loading = document.querySelector('.loading');
            loading.classList.remove('d-none');
            
            const formData = new FormData(document.getElementById('gradesForm'));
            
            fetch("{{ route('teacher.grades.store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                loading.classList.add('d-none');
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم الحفظ بنجاح',
                        text: data.success,
                        confirmButtonText: 'موافق'
                    });
                } else if (data.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: data.error,
                        confirmButtonText: 'موافق'
                    });
                }
            })
            .catch(error => {
                loading.classList.add('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء حفظ البيانات. يرجى المحاولة مرة أخرى.',
                    confirmButtonText: 'موافق'
                });
            });
        });
        
        // Finalize grades
        document.getElementById('finalizeBtn').addEventListener('click', function() {
            const finalizeModal = new bootstrap.Modal(document.getElementById('finalizeModal'));
            finalizeModal.show();
        });
        
        document.getElementById('confirmFinalizeBtn').addEventListener('click', function() {
            const loading = document.querySelector('.loading');
            loading.classList.remove('d-none');
            
            const formData = new FormData(document.getElementById('gradesForm'));
            
            fetch("{{ route('teacher.grades.finalize') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                loading.classList.add('d-none');
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم التأكيد بنجاح',
                        text: data.success,
                        confirmButtonText: 'موافق'
                    }).then(() => {
                        window.location.reload();
                    });
                } else if (data.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: data.error,
                        confirmButtonText: 'موافق'
                    });
                }
            })
            .catch(error => {
                loading.classList.add('d-none');
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء تأكيد الدرجات. يرجى المحاولة مرة أخرى.',
                    confirmButtonText: 'موافق'
                });
            });
            
            const finalizeModal = bootstrap.Modal.getInstance(document.getElementById('finalizeModal'));
            finalizeModal.hide();
        });
    });
</script>
@endsection 