@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">إدارة الحضور</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-user-check me-2"></i>إدارة الحضور
            </h1>
            <p class="text-muted">تسجيل وإدارة حضور الطلاب في المقررات الدراسية</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تسجيل الحضور</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.attendance.select') }}" method="GET" id="course-selection-form">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="course_id" class="form-label">المقرر الدراسي <span class="text-danger">*</span></label>
                                <select class="form-select @error('course_id') is-invalid @enderror" id="course_id" name="course_id" required>
                                    <option value="" selected disabled>اختر المقرر...</option>
                                    @foreach($courses ?? [] as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }} ({{ $course->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="group_id" class="form-label">المجموعة <span class="text-danger">*</span></label>
                                <select class="form-select @error('group_id') is-invalid @enderror" id="group_id" name="group_id" {{ empty($groups) ? 'disabled' : '' }} required>
                                    <option value="" selected disabled>اختر المجموعة...</option>
                                    @foreach($groups ?? [] as $group)
                                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="attendance_date" class="form-label">تاريخ المحاضرة <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" id="attendance_date" name="attendance_date" value="{{ request('attendance_date', date('Y-m-d')) }}" required>
                                @error('attendance_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-1 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> عرض
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($students) && $courseId && $groupId && $attendanceDate)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            سجل حضور طلاب {{ $courseName }} 
                            <span class="text-muted">({{ $groupName }})</span> - 
                            <span class="text-primary">{{ \Carbon\Carbon::parse($attendanceDate)->format('Y-m-d') }}</span>
                        </h5>
                        <span class="badge bg-dark">{{ $students->count() }} طالب</span>
                    </div>
                    <div class="card-body">
                        @if(count($students) > 0)
                            <form action="{{ route('teacher.attendance.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="course_id" value="{{ $courseId }}">
                                <input type="hidden" name="group_id" value="{{ $groupId }}">
                                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;" class="text-center">#</th>
                                                <th>اسم الطالب</th>
                                                <th style="width: 180px;" class="text-center">الحالة</th>
                                                <th>ملاحظات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $index => $student)
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                @if($student->profile_photo)
                                                                    <img src="{{ asset('storage/' . $student->profile_photo) }}" class="rounded-circle" width="40" alt="{{ $student->name }}">
                                                                @else
                                                                    <div class="avatar-initials rounded-circle bg-primary text-white">{{ substr($student->name, 0, 2) }}</div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $student->name }}</h6>
                                                                <small class="text-muted">{{ $student->student_id }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                                                        <div class="text-center">
                                                            <div class="btn-group" role="group">
                                                                <input type="radio" class="btn-check" name="status[{{ $student->id }}]" value="present" id="present{{ $student->id }}" {{ isset($attendanceData[$student->id]) && $attendanceData[$student->id]['status'] == 'present' ? 'checked' : '' }} {{ !isset($attendanceData[$student->id]) ? 'checked' : '' }} autocomplete="off">
                                                                <label class="btn btn-outline-success" for="present{{ $student->id }}">حاضر</label>
                                                                
                                                                <input type="radio" class="btn-check" name="status[{{ $student->id }}]" value="late" id="late{{ $student->id }}" {{ isset($attendanceData[$student->id]) && $attendanceData[$student->id]['status'] == 'late' ? 'checked' : '' }} autocomplete="off">
                                                                <label class="btn btn-outline-warning" for="late{{ $student->id }}">متأخر</label>
                                                                
                                                                <input type="radio" class="btn-check" name="status[{{ $student->id }}]" value="absent" id="absent{{ $student->id }}" {{ isset($attendanceData[$student->id]) && $attendanceData[$student->id]['status'] == 'absent' ? 'checked' : '' }} autocomplete="off">
                                                                <label class="btn btn-outline-danger" for="absent{{ $student->id }}">غائب</label>
                                                                
                                                                <input type="radio" class="btn-check" name="status[{{ $student->id }}]" value="excused" id="excused{{ $student->id }}" {{ isset($attendanceData[$student->id]) && $attendanceData[$student->id]['status'] == 'excused' ? 'checked' : '' }} autocomplete="off">
                                                                <label class="btn btn-outline-secondary" for="excused{{ $student->id }}">معذور</label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" name="notes[{{ $student->id }}]" placeholder="ملاحظات (اختياري)" value="{{ isset($attendanceData[$student->id]) ? $attendanceData[$student->id]['notes'] : '' }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-success mark-all" data-status="present">
                                            <i class="fas fa-check-circle me-1"></i> تعليم الكل حاضر
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2 mark-all" data-status="absent">
                                            <i class="fas fa-times-circle me-1"></i> تعليم الكل غائب
                                        </button>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> حفظ سجل الحضور
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا يوجد طلاب">
                                <p class="text-muted">لا يوجد طلاب مسجلين في هذه المجموعة</p>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif(request()->has('course_id'))
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    يرجى اختيار المقرر والمجموعة وتاريخ المحاضرة لعرض سجل الحضور.
                </div>
            @endif
        </div>
    </div>
    
    <!-- تقارير الحضور -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تقارير الحضور</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#reportsCollapse">
                        <i class="fas fa-chart-bar me-1"></i> عرض التقارير
                    </button>
                </div>
                <div class="card-body collapse" id="reportsCollapse">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="mb-3">تقرير الحضور حسب المقرر</h6>
                                    <form action="{{ route('teacher.attendance.report.course') }}" method="GET">
                                        <div class="mb-3">
                                            <label for="report_course_id" class="form-label">المقرر الدراسي</label>
                                            <select class="form-select" id="report_course_id" name="course_id" required>
                                                <option value="" selected disabled>اختر المقرر...</option>
                                                @foreach($courses ?? [] as $course)
                                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="report_group_id" class="form-label">المجموعة (اختياري)</label>
                                            <select class="form-select" id="report_group_id" name="group_id">
                                                <option value="">جميع المجموعات</option>
                                            </select>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-chart-line me-1"></i> عرض التقرير
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="mb-3">تقرير حضور الطالب</h6>
                                    <form action="{{ route('teacher.attendance.report.student') }}" method="GET">
                                        <div class="mb-3">
                                            <label for="student_id" class="form-label">الطالب</label>
                                            <select class="form-select" id="student_id" name="student_id" required>
                                                <option value="" selected disabled>اختر الطالب...</option>
                                                @foreach($allStudents ?? [] as $student)
                                                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="report_course_id2" class="form-label">المقرر (اختياري)</label>
                                            <select class="form-select" id="report_course_id2" name="course_id">
                                                <option value="">جميع المقررات</option>
                                                @foreach($courses ?? [] as $course)
                                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-chart-line me-1"></i> عرض التقرير
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="mb-3">تقرير حسب التاريخ</h6>
                                    <form action="{{ route('teacher.attendance.report.date') }}" method="GET">
                                        <div class="mb-3">
                                            <label for="date_from" class="form-label">من تاريخ</label>
                                            <input type="date" class="form-control" id="date_from" name="date_from" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="date_to" class="form-label">إلى تاريخ</label>
                                            <input type="date" class="form-control" id="date_to" name="date_to" required>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-chart-line me-1"></i> عرض التقرير
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        يمكنك تصدير جميع التقارير إلى ملف Excel أو PDF من خلال صفحة التقرير.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar-initials {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .btn-check:checked + .btn-outline-success {
        background-color: #198754;
        color: white;
    }
    
    .btn-check:checked + .btn-outline-warning {
        background-color: #ffc107;
        color: #000;
    }
    
    .btn-check:checked + .btn-outline-danger {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-check:checked + .btn-outline-secondary {
        background-color: #6c757d;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Course selection - update groups dropdown
        const courseSelect = document.getElementById('course_id');
        const groupSelect = document.getElementById('group_id');
        
        if (courseSelect && groupSelect) {
            courseSelect.addEventListener('change', function() {
                const courseId = this.value;
                
                if (!courseId) {
                    groupSelect.innerHTML = '<option value="" selected disabled>اختر المجموعة...</option>';
                    groupSelect.disabled = true;
                    return;
                }
                
                // Fetch groups for selected course
                fetch(`/teacher/courses/${courseId}/groups`)
                    .then(response => response.json())
                    .then(data => {
                        groupSelect.innerHTML = '<option value="" selected disabled>اختر المجموعة...</option>';
                        
                        data.forEach(group => {
                            const option = document.createElement('option');
                            option.value = group.id;
                            option.textContent = group.name;
                            groupSelect.appendChild(option);
                        });
                        
                        groupSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching groups:', error);
                    });
            });
        }
        
        // Report course selection - update groups dropdown
        const reportCourseSelect = document.getElementById('report_course_id');
        const reportGroupSelect = document.getElementById('report_group_id');
        
        if (reportCourseSelect && reportGroupSelect) {
            reportCourseSelect.addEventListener('change', function() {
                const courseId = this.value;
                
                if (!courseId) {
                    reportGroupSelect.innerHTML = '<option value="">جميع المجموعات</option>';
                    return;
                }
                
                // Fetch groups for selected course
                fetch(`/teacher/courses/${courseId}/groups`)
                    .then(response => response.json())
                    .then(data => {
                        reportGroupSelect.innerHTML = '<option value="">جميع المجموعات</option>';
                        
                        data.forEach(group => {
                            const option = document.createElement('option');
                            option.value = group.id;
                            option.textContent = group.name;
                            reportGroupSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching groups:', error);
                    });
            });
        }
        
        // Mark all students with same status
        const markAllButtons = document.querySelectorAll('.mark-all');
        
        markAllButtons.forEach(button => {
            button.addEventListener('click', function() {
                const status = this.getAttribute('data-status');
                const radioButtons = document.querySelectorAll(`input[type="radio"][value="${status}"]`);
                
                radioButtons.forEach(radio => {
                    radio.checked = true;
                });
            });
        });
        
        // Set min date for attendance_date to beginning of semester
        const attendanceDateInput = document.getElementById('attendance_date');
        if (attendanceDateInput) {
            // Set min date to beginning of current semester (you can adjust this as needed)
            const currentDate = new Date();
            let semesterStart;
            
            // If current month is between January and May, set semester start to January 1
            // If current month is between June and August, set semester start to June 1
            // If current month is between September and December, set semester start to September 1
            const currentMonth = currentDate.getMonth();
            
            if (currentMonth >= 0 && currentMonth <= 4) {
                semesterStart = new Date(currentDate.getFullYear(), 0, 1);
            } else if (currentMonth >= 5 && currentMonth <= 7) {
                semesterStart = new Date(currentDate.getFullYear(), 5, 1);
            } else {
                semesterStart = new Date(currentDate.getFullYear(), 8, 1);
            }
            
            const formattedDate = semesterStart.toISOString().split('T')[0];
            attendanceDateInput.min = formattedDate;
            
            // Set max date to today
            const today = new Date().toISOString().split('T')[0];
            attendanceDateInput.max = today;
        }
        
        // Set min and max dates for report date inputs
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        
        if (dateFromInput && dateToInput) {
            // Set default values: date_from to 30 days ago, date_to to today
            const today = new Date();
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(today.getDate() - 30);
            
            dateFromInput.value = thirtyDaysAgo.toISOString().split('T')[0];
            dateToInput.value = today.toISOString().split('T')[0];
            
            // Set min date to beginning of academic year
            const academicYearStart = new Date(today.getFullYear(), 8, 1); // September 1st
            if (today < academicYearStart) {
                academicYearStart.setFullYear(academicYearStart.getFullYear() - 1);
            }
            
            dateFromInput.min = academicYearStart.toISOString().split('T')[0];
            dateToInput.min = academicYearStart.toISOString().split('T')[0];
            dateToInput.max = today.toISOString().split('T')[0];
            
            // Update dateToInput min when dateFromInput changes
            dateFromInput.addEventListener('change', function() {
                dateToInput.min = this.value;
                
                // If date_to is before date_from, update it
                if (dateToInput.value < this.value) {
                    dateToInput.value = this.value;
                }
            });
            
            // Update dateFromInput max when dateToInput changes
            dateToInput.addEventListener('change', function() {
                dateFromInput.max = this.value;
                
                // If date_from is after date_to, update it
                if (dateFromInput.value > this.value) {
                    dateFromInput.value = this.value;
                }
            });
        }
    });
</script>
@endpush

@endsection 