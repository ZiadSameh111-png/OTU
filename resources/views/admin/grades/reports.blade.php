@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4">تقارير الدرجات</h2>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">فلاتر البحث</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="course_id" class="form-label">المقرر الدراسي</label>
                                <select name="course_id" id="course_id" class="form-select">
                                    <option value="">جميع المقررات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }} ({{ $course->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="group_id" class="form-label">المجموعة</label>
                                <select name="group_id" id="group_id" class="form-select">
                                    <option value="">جميع المجموعات</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">الحالة</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>مثبتة</option>
                                    <option value="not_submitted" {{ request('status') == 'not_submitted' ? 'selected' : '' }}>غير مثبتة</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">نتائج البحث</h5>
                    
                    <div>
                        <button id="exportExcelBtn" class="btn btn-sm btn-success me-2">
                            <i class="fas fa-file-excel me-1"></i> تصدير إلى Excel
                        </button>
                        <button id="exportPdfBtn" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf me-1"></i> تصدير إلى PDF
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>اسم الطالب</th>
                                    <th>رقم الطالب</th>
                                    <th>اسم المقرر</th>
                                    <th>رمز المقرر</th>
                                    <th>الدرجة الكلية</th>
                                    <th>التقدير</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($grades) && count($grades) > 0)
                                    @foreach($grades as $grade)
                                        @php
                                            $course = $grade->course;
                                            $student = $grade->student;
                                            
                                            // Calculate total grade
                                            $totalGrade = $grade->midterm_grade + $grade->assignment_grade + $grade->final_grade;
                                            $totalPossible = $course->midterm_grade + $course->assignment_grade + $course->final_grade;
                                            $percentage = ($totalPossible > 0) ? ($totalGrade / $totalPossible) * 100 : 0;
                                            
                                            // Get letter grade and badge class from the Grade model
                                            $letterGrade = $grade->getLetterGradeAttribute();
                                            $badgeClass = 'bg-' . $grade->getLetterGradeColor();
                                        @endphp
                                        
                                        <tr>
                                            <td>{{ $student->name }}</td>
                                            <td>{{ $student->student_id }}</td>
                                            <td>{{ $course->name }}</td>
                                            <td>{{ $course->code }}</td>
                                            <td>{{ $totalGrade }}/{{ $totalPossible }}</td>
                                            <td><span class="badge {{ $badgeClass }}">{{ $letterGrade }}</span></td>
                                            <td>
                                                @if($grade->submitted)
                                                    <span class="badge bg-success">مثبتة</span>
                                                @else
                                                    <span class="badge bg-warning">غير مثبتة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#gradeModal{{ $grade->id }}">
                                                    <i class="fas fa-eye"></i> التفاصيل
                                                </button>
                                                
                                                @if($grade->submitted)
                                                    <a href="{{ route('admin.grades.edit', $grade->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> تعديل
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                        
                                        <!-- Grade Details Modal -->
                                        <div class="modal fade" id="gradeModal{{ $grade->id }}" tabindex="-1" aria-labelledby="gradeModalLabel{{ $grade->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="gradeModalLabel{{ $grade->id }}">تفاصيل الدرجات - {{ $student->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-4">
                                                            <div class="col-md-6">
                                                                <h6>معلومات الطالب:</h6>
                                                                <p><strong>اسم الطالب:</strong> {{ $student->name }}</p>
                                                                <p><strong>رقم الطالب:</strong> {{ $student->student_id }}</p>
                                                                <p><strong>المجموعة:</strong> {{ $student->group->name ?? 'غير محدد' }}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>معلومات المقرر:</h6>
                                                                <p><strong>اسم المقرر:</strong> {{ $course->name }}</p>
                                                                <p><strong>رمز المقرر:</strong> {{ $course->code }}</p>
                                                                <p><strong>أستاذ المقرر:</strong> {{ $course->teacher->name ?? 'غير محدد' }}</p>
                                                            </div>
                                                        </div>
                                                        
                                                        <h6>تفاصيل الدرجات:</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>المكون</th>
                                                                        <th>الدرجة</th>
                                                                        <th>الدرجة القصوى</th>
                                                                        <th>النسبة</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>الاختبارات الشهرية</td>
                                                                        <td>{{ $grade->midterm_grade }}</td>
                                                                        <td>{{ $course->midterm_grade }}</td>
                                                                        <td>{{ ($course->midterm_grade > 0) ? number_format(($grade->midterm_grade / $course->midterm_grade) * 100, 2) : 0 }}%</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>الأعمال العملية</td>
                                                                        <td>{{ $grade->assignment_grade }}</td>
                                                                        <td>{{ $course->assignment_grade }}</td>
                                                                        <td>{{ ($course->assignment_grade > 0) ? number_format(($grade->assignment_grade / $course->assignment_grade) * 100, 2) : 0 }}%</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>الاختبار النهائي</td>
                                                                        <td>{{ $grade->final_grade }}</td>
                                                                        <td>{{ $course->final_grade }}</td>
                                                                        <td>{{ ($course->final_grade > 0) ? number_format(($grade->final_grade / $course->final_grade) * 100, 2) : 0 }}%</td>
                                                                    </tr>
                                                                    <tr class="table-active">
                                                                        <td><strong>المجموع</strong></td>
                                                                        <td><strong>{{ $totalGrade }}</strong></td>
                                                                        <td><strong>{{ $totalPossible }}</strong></td>
                                                                        <td><strong>{{ number_format($percentage, 2) }}%</strong></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        
                                                        @if($grade->comments)
                                                            <div class="mt-4">
                                                                <h6>ملاحظات الدكتور:</h6>
                                                                <div class="alert alert-info">
                                                                    {{ $grade->comments }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        
                                                        @if($grade->updated_by)
                                                            <div class="mt-4">
                                                                <h6>معلومات التحديث:</h6>
                                                                <p><strong>تم التحديث بواسطة:</strong> {{ App\Models\User::find($grade->updated_by)->name ?? 'غير معروف' }}</p>
                                                                <p><strong>تاريخ التحديث:</strong> {{ $grade->updated_at->format('Y-m-d H:i:s') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                        @if($grade->submitted)
                                                            <a href="{{ route('admin.grades.edit', $grade->id) }}" class="btn btn-warning">
                                                                <i class="fas fa-edit me-1"></i> تعديل الدرجات
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            لا توجد نتائج مطابقة لمعايير البحث
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    @if(isset($grades) && count($grades) > 0 && method_exists($grades, 'links'))
                        <div class="d-flex justify-content-center mt-4">
                            {{ $grades->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Export to Excel
        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            let url = '{{ route("admin.grades.export", ["format" => "excel"]) }}';
            const filters = getFilters();
            url += '?' + new URLSearchParams(filters).toString();
            window.location.href = url;
        });
        
        // Export to PDF
        document.getElementById('exportPdfBtn').addEventListener('click', function() {
            let url = '{{ route("admin.grades.export", ["format" => "pdf"]) }}';
            const filters = getFilters();
            url += '?' + new URLSearchParams(filters).toString();
            window.location.href = url;
        });
        
        function getFilters() {
            return {
                course_id: document.getElementById('course_id').value,
                group_id: document.getElementById('group_id').value,
                status: document.getElementById('status').value
            };
        }
    });
</script>
@endpush 