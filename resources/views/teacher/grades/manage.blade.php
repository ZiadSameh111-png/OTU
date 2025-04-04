@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1 text-primary fw-bold">
                    <i class="fas fa-clipboard-check me-2"></i>إدارة درجات المقرر
                </h1>
                <p class="text-muted fs-5">{{ $course->name }} - {{ $course->code ?? 'بدون كود' }}</p>
            </div>
            <a href="{{ route('teacher.grades.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i> العودة للمقررات
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($groups->count() > 0)
        <div class="row">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-layer-group me-2 text-primary"></i>المجموعات
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="list-tab" role="tablist">
                            @foreach($groups as $index => $group)
                                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $index == 0 ? 'active' : '' }}" 
                                   id="list-{{ $group->id }}-list" 
                                   data-bs-toggle="list" 
                                   href="#list-{{ $group->id }}" 
                                   role="tab" 
                                   aria-controls="{{ $group->id }}">
                                    {{ $group->name }}
                                    <span class="badge bg-primary rounded-pill">{{ $group->students->count() }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="tab-content" id="nav-tabContent">
                    @foreach($groups as $index => $group)
                        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" 
                             id="list-{{ $group->id }}" 
                             role="tabpanel" 
                             aria-labelledby="list-{{ $group->id }}-list">
                            
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-users me-2 text-primary"></i>طلاب {{ $group->name }}
                                    </h5>
                                    <div>
                                        <button class="btn btn-sm btn-success me-2" id="saveAllBtn-{{ $group->id }}">
                                            <i class="fas fa-save me-1"></i> حفظ جميع الدرجات
                                        </button>
                                        <button class="btn btn-sm btn-primary" id="submitAllBtn-{{ $group->id }}">
                                            <i class="fas fa-check-circle me-1"></i> تأكيد وإرسال الدرجات
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($group->students->count() > 0)
                                        <form id="grades-form-{{ $group->id }}" action="{{ route('teacher.grades.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                                            <input type="hidden" name="group_id" value="{{ $group->id }}">
                                            
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="5%">م</th>
                                                            <th width="15%">رقم الطالب</th>
                                                            <th width="30%">اسم الطالب</th>
                                                            <th width="15%">درجة الأعمال الفصلية ({{ $course->assignment_grade }})</th>
                                                            <th width="15%">درجة الاختبار النهائي ({{ $course->final_grade }})</th>
                                                            <th width="10%">المجموع</th>
                                                            <th width="10%">الحالة</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($group->students as $i => $student)
                                                            @php
                                                                $grade = $grades->where('student_id', $student->id)->first();
                                                                $assignmentGrade = $grade ? $grade->assignment_grade : '';
                                                                $finalGrade = $grade ? $grade->final_grade : '';
                                                                $total = $grade ? ($assignmentGrade + $finalGrade) : '';
                                                                $submitted = $grade ? $grade->submitted : false;
                                                                $inputDisabled = $submitted ? 'disabled' : '';
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $i + 1 }}</td>
                                                                <td>{{ $student->student_id }}</td>
                                                                <td>{{ $student->name }}</td>
                                                                <td>
                                                                    <input type="number" 
                                                                           class="form-control assignment-grade" 
                                                                           name="grades[{{ $student->id }}][assignment_grade]" 
                                                                           min="0" 
                                                                           max="{{ $course->assignment_grade }}" 
                                                                           value="{{ $assignmentGrade }}"
                                                                           data-student-id="{{ $student->id }}"
                                                                           data-group-id="{{ $group->id }}"
                                                                           {{ $inputDisabled }}>
                                                                </td>
                                                                <td>
                                                                    <input type="number" 
                                                                           class="form-control final-grade" 
                                                                           name="grades[{{ $student->id }}][final_grade]" 
                                                                           min="0" 
                                                                           max="{{ $course->final_grade }}" 
                                                                           value="{{ $finalGrade }}"
                                                                           data-student-id="{{ $student->id }}"
                                                                           data-group-id="{{ $group->id }}"
                                                                           {{ $inputDisabled }}>
                                                                </td>
                                                                <td>
                                                                    <span class="total-grade badge bg-light text-dark fs-6">
                                                                        {{ $total }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    @if($submitted)
                                                                        <span class="badge bg-success">تم التأكيد</span>
                                                                    @else
                                                                        <span class="badge bg-warning text-dark">غير مؤكد</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    @else
                                        <div class="text-center py-5">
                                            <img src="https://cdn-icons-png.flaticon.com/512/2866/2866906.png" alt="لا يوجد طلاب" style="width: 120px; opacity: 0.5;">
                                            <p class="mt-4 text-muted">لا يوجد طلاب مسجلين في هذه المجموعة</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/2866/2866906.png" alt="لا توجد مجموعات" style="width: 120px; opacity: 0.5;">
                    <p class="mt-4 text-muted">لا توجد مجموعات مسجلة لهذا المقرر</p>
                </div>
            </div>
        </div>
    @endif
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // Calculate total grade when assignment or final grade changes
        $('.assignment-grade, .final-grade').on('change', function() {
            const row = $(this).closest('tr');
            const assignmentGrade = parseFloat(row.find('.assignment-grade').val()) || 0;
            const finalGrade = parseFloat(row.find('.final-grade').val()) || 0;
            const total = assignmentGrade + finalGrade;
            row.find('.total-grade').text(total);
        });

        // Save all grades for a group
        $('[id^="saveAllBtn-"]').on('click', function() {
            const groupId = $(this).attr('id').split('-')[1];
            const form = $(`#grades-form-${groupId}`);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    alert('تم حفظ الدرجات بنجاح');
                },
                error: function(error) {
                    alert('حدث خطأ أثناء حفظ الدرجات');
                    console.error(error);
                }
            });
        });

        // Submit all grades for a group
        $('[id^="submitAllBtn-"]').on('click', function() {
            const groupId = $(this).attr('id').split('-')[1];
            const form = $(`#grades-form-${groupId}`);
            
            if(confirm('هل أنت متأكد من تأكيد وإرسال جميع الدرجات؟ لن تتمكن من تعديلها بعد الإرسال.')) {
                $.ajax({
                    url: "{{ route('teacher.grades.submit') }}",
                    type: 'POST',
                    data: form.serialize() + '&submit=1',
                    success: function(response) {
                        alert('تم تأكيد وإرسال الدرجات بنجاح');
                        location.reload();
                    },
                    error: function(error) {
                        alert('حدث خطأ أثناء إرسال الدرجات');
                        console.error(error);
                    }
                });
            }
        });
    });
</script>
@endsection

@endsection 