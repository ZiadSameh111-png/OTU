@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1 text-primary fw-bold">
                    <i class="fas fa-chart-line me-2"></i>تفاصيل درجات المقرر
                </h1>
                <p class="text-muted fs-5">{{ $course->name }} - {{ $course->code ?? 'بدون كود' }}</p>
            </div>
            <a href="{{ route('admin.grades.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i> العودة لقائمة المقررات
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

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>معلومات المقرر
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                            <i class="fas fa-book text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $course->name }}</h5>
                            <p class="text-muted mb-0">{{ $course->code ?? 'بدون كود' }}</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-tie text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">أستاذ المقرر</small>
                            <span>{{ $course->teacher->name ?? 'غير محدد' }}</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-layer-group text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">عدد المجموعات</small>
                            <span>{{ $course->groups->count() }}</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-users text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">عدد الطلاب</small>
                            @php
                                $studentCount = 0;
                                foreach($course->groups as $group) {
                                    $studentCount += $group->students->count();
                                }
                            @endphp
                            <span>{{ $studentCount }}</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clipboard-check text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">توزيع الدرجات</small>
                            <span>أعمال فصلية: {{ $course->assignment_grade }} | نهائي: {{ $course->final_grade }}</span>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4 mb-0">
                        <h6 class="alert-heading mb-2"><i class="fas fa-chart-pie me-2"></i>ملخص تسليم الدرجات</h6>
                        @php
                            $submittedCount = $grades->where('submitted', true)->count();
                            $percentage = $studentCount > 0 ? round(($submittedCount / $studentCount) * 100) : 0;
                        @endphp
                        <div class="progress mb-2" style="height: 8px;">
                            @php
                                $progressClass = 'bg-danger';
                                if($percentage >= 70) $progressClass = 'bg-success';
                                elseif($percentage >= 30) $progressClass = 'bg-warning';
                            @endphp
                            <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                style="width: {{ $percentage }}%;" 
                                aria-valuenow="{{ $percentage }}" 
                                aria-valuemin="0" 
                                aria-valuemax="100"></div>
                        </div>
                        <p class="mb-0 small">تم تسليم {{ $submittedCount }} من أصل {{ $studentCount }} ({{ $percentage }}%)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>إحصائيات الدرجات
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" id="exportPdf">
                            <i class="fas fa-file-pdf me-1"></i> تصدير PDF
                        </button>
                        <button class="btn btn-sm btn-outline-success" id="exportExcel">
                            <i class="fas fa-file-excel me-1"></i> تصدير Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $submittedGrades = $grades->where('submitted', true);
                        if(count($submittedGrades) > 0) {
                            $assignmentTotal = 0;
                            $finalTotal = 0;
                            $overallTotal = 0;
                            
                            foreach($submittedGrades as $grade) {
                                $assignmentTotal += $grade->assignment_grade;
                                $finalTotal += $grade->final_grade;
                                $overallTotal += $grade->assignment_grade + $grade->final_grade;
                            }
                            
                            $assignmentAvg = count($submittedGrades) > 0 ? round($assignmentTotal / count($submittedGrades), 1) : 0;
                            $finalAvg = count($submittedGrades) > 0 ? round($finalTotal / count($submittedGrades), 1) : 0;
                            $overallAvg = count($submittedGrades) > 0 ? round($overallTotal / count($submittedGrades), 1) : 0;
                            
                            $maxTotal = $course->assignment_grade + $course->final_grade;
                            $overallPercentage = $maxTotal > 0 ? round(($overallAvg / $maxTotal) * 100) : 0;
                            
                            // Grade distribution
                            $aPlusCount = $aCount = $bPlusCount = $bCount = $cPlusCount = $cCount = $dCount = $fCount = 0;
                            
                            foreach($submittedGrades as $grade) {
                                $total = $grade->assignment_grade + $grade->final_grade;
                                $percentage = ($total / $maxTotal) * 100;
                                
                                if($percentage >= 95) $aPlusCount++;
                                elseif($percentage >= 90) $aCount++;
                                elseif($percentage >= 85) $bPlusCount++;
                                elseif($percentage >= 80) $bCount++;
                                elseif($percentage >= 75) $cPlusCount++;
                                elseif($percentage >= 70) $cCount++;
                                elseif($percentage >= 60) $dCount++;
                                else $fCount++;
                            }
                        }
                    @endphp
                    
                    @if(count($submittedGrades) > 0)
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 text-center h-100">
                                    <h6 class="text-muted mb-2">متوسط الأعمال الفصلية</h6>
                                    <div class="display-6 text-primary mb-1">{{ $assignmentAvg }}</div>
                                    <small class="text-muted">من {{ $course->assignment_grade }}</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 text-center h-100">
                                    <h6 class="text-muted mb-2">متوسط الاختبار النهائي</h6>
                                    <div class="display-6 text-primary mb-1">{{ $finalAvg }}</div>
                                    <small class="text-muted">من {{ $course->final_grade }}</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3 text-center h-100">
                                    <h6 class="text-muted mb-2">المتوسط الإجمالي</h6>
                                    <div class="display-6 text-primary mb-1">{{ $overallAvg }}</div>
                                    <small class="text-muted">{{ $overallPercentage }}%</small>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">توزيع التقديرات</h6>
                        <div class="mb-4">
                            <div class="row g-2">
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-success me-2">A+</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: {{ count($submittedGrades) > 0 ? ($aPlusCount / count($submittedGrades) * 100) : 0 }}%;" 
                                                aria-valuenow="{{ $aPlusCount }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ count($submittedGrades) }}"></div>
                                        </div>
                                        <span class="ms-2 small">{{ $aPlusCount }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-success-subtle text-success me-2">A</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-success-subtle" role="progressbar" 
                                                style="width: {{ count($submittedGrades) > 0 ? ($aCount / count($submittedGrades) * 100) : 0 }}%;" 
                                                aria-valuenow="{{ $aCount }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ count($submittedGrades) }}"></div>
                                        </div>
                                        <span class="ms-2 small">{{ $aCount }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-info me-2">B+</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                style="width: {{ count($submittedGrades) > 0 ? ($bPlusCount / count($submittedGrades) * 100) : 0 }}%;" 
                                                aria-valuenow="{{ $bPlusCount }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ count($submittedGrades) }}"></div>
                                        </div>
                                        <span class="ms-2 small">{{ $bPlusCount }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-info-subtle text-info me-2">B</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-info-subtle" role="progressbar" 
                                                style="width: {{ count($submittedGrades) > 0 ? ($bCount / count($submittedGrades) * 100) : 0 }}%;" 
                                                aria-valuenow="{{ $bCount }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ count($submittedGrades) }}"></div>
                                        </div>
                                        <span class="ms-2 small">{{ $bCount }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-warning me-2">C+</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                style="width: {{ count($submittedGrades) > 0 ? ($cPlusCount / count($submittedGrades) * 100) : 0 }}%;" 
                                                aria-valuenow="{{ $cPlusCount }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ count($submittedGrades) }}"></div>
                                        </div>
                                        <span class="ms-2 small">{{ $cPlusCount }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-warning-subtle text-warning me-2">C</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning-subtle" role="progressbar" 
                                                style="width: {{ count($submittedGrades) > 0 ? ($cCount / count($submittedGrades) * 100) : 0 }}%;" 
                                                aria-valuenow="{{ $cCount }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ count($submittedGrades) }}"></div>
                                        </div>
                                        <span class="ms-2 small">{{ $cCount }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-secondary me-2">D</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-secondary" role="progressbar" 
                                                style="width: {{ count($submittedGrades) > 0 ? ($dCount / count($submittedGrades) * 100) : 0 }}%;" 
                                                aria-valuenow="{{ $dCount }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ count($submittedGrades) }}"></div>
                                        </div>
                                        <span class="ms-2 small">{{ $dCount }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-danger me-2">F</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                style="width: {{ count($submittedGrades) > 0 ? ($fCount / count($submittedGrades) * 100) : 0 }}%;" 
                                                aria-valuenow="{{ $fCount }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ count($submittedGrades) }}"></div>
                                        </div>
                                        <span class="ms-2 small">{{ $fCount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2866/2866906.png" alt="لا توجد درجات" style="width: 120px; opacity: 0.5;">
                            <p class="mt-4 text-muted">لم يتم تسليم أي درجات لهذا المقرر بعد</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2 text-primary"></i>درجات الطلاب
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showSubmittedOnly">
                        <label class="form-check-label" for="showSubmittedOnly">عرض المؤكدة فقط</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">م</th>
                                    <th width="15%">رقم الطالب</th>
                                    <th width="25%">اسم الطالب</th>
                                    <th width="15%">المجموعة</th>
                                    <th width="10%">الأعمال الفصلية</th>
                                    <th width="10%">الاختبار النهائي</th>
                                    <th width="10%">المجموع</th>
                                    <th width="10%">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @foreach($course->groups as $group)
                                    @foreach($group->students as $student)
                                        @php
                                            $grade = $grades->where('student_id', $student->id)->first();
                                            $assignmentGrade = $grade ? $grade->assignment_grade : null;
                                            $finalGrade = $grade ? $grade->final_grade : null;
                                            $total = ($assignmentGrade !== null && $finalGrade !== null) ? ($assignmentGrade + $finalGrade) : null;
                                            $submitted = $grade ? $grade->submitted : false;
                                        @endphp
                                        <tr class="{{ $submitted ? 'submitted-row' : 'non-submitted-row' }}">
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $student->student_id }}</td>
                                            <td>{{ $student->name }}</td>
                                            <td>{{ $group->name }}</td>
                                            <td>
                                                @if($assignmentGrade !== null)
                                                    <span class="badge bg-light text-dark fs-6">
                                                        {{ $assignmentGrade }} / {{ $course->assignment_grade }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary fs-6">غير متاح</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($finalGrade !== null)
                                                    <span class="badge bg-light text-dark fs-6">
                                                        {{ $finalGrade }} / {{ $course->final_grade }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary fs-6">غير متاح</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($total !== null)
                                                    <span class="badge bg-light text-dark fs-6">
                                                        {{ $total }} / {{ $course->assignment_grade + $course->final_grade }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary fs-6">غير متاح</span>
                                                @endif
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle submitted only rows
        $('#showSubmittedOnly').on('change', function() {
            if($(this).is(':checked')) {
                $('.non-submitted-row').hide();
            } else {
                $('.non-submitted-row').show();
            }
        });
        
        // Export buttons (just placeholders - would need server-side implementation)
        $('#exportPdf').on('click', function() {
            alert('سيتم تصدير البيانات بتنسيق PDF');
        });
        
        $('#exportExcel').on('click', function() {
            alert('سيتم تصدير البيانات بتنسيق Excel');
        });
    });
</script>
@endsection

@endsection 