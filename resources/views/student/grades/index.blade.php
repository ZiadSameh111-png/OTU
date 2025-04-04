@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1 text-primary fw-bold">
                <i class="fas fa-graduation-cap me-2"></i>درجاتي
            </h1>
            <p class="text-muted fs-5">عرض الدرجات لجميع المقررات الدراسية الخاصة بك</p>
        </div>
    </div>

    @if(!$group)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            أنت غير مسجل في أي مجموعة دراسية. يرجى التواصل مع إدارة النظام.
        </div>
    @endif

    @if(count($courses) > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list me-2 text-primary"></i>درجات المقررات
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">م</th>
                                <th width="15%">كود المقرر</th>
                                <th width="25%">اسم المقرر</th>
                                <th width="15%">الأعمال الفصلية</th>
                                <th width="15%">الاختبار النهائي</th>
                                <th width="10%">المجموع</th>
                                <th width="15%">التقدير</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalPoints = 0; $totalMaxPoints = 0; @endphp
                            @foreach($courses as $i => $course)
                                @php
                                    $grade = $grades->where('course_id', $course->id)->first();
                                    $assignmentGrade = $grade && $grade->submitted ? $grade->assignment_grade : '-';
                                    $finalGrade = $grade && $grade->submitted ? $grade->final_grade : '-';
                                    
                                    $total = '-';
                                    $letterGrade = '-';
                                    
                                    if($grade && $grade->submitted) {
                                        $total = $assignmentGrade + $finalGrade;
                                        $maxTotal = $course->assignment_grade + $course->final_grade;
                                        $percentage = ($total / $maxTotal) * 100;
                                        
                                        // Calculate letter grade based on percentage
                                        if($percentage >= 90) {
                                            $letterGrade = 'A';
                                        } elseif($percentage >= 80) {
                                            $letterGrade = 'B';
                                        } elseif($percentage >= 70) {
                                            $letterGrade = 'C';
                                        } elseif($percentage >= 60) {
                                            $letterGrade = 'D';
                                        } else {
                                            $letterGrade = 'F';
                                        }
                                        
                                        $totalPoints += $total;
                                        $totalMaxPoints += $maxTotal;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $course->code ?? 'بدون كود' }}</td>
                                    <td>{{ $course->name }}</td>
                                    <td>
                                        @if($assignmentGrade !== '-')
                                            <span class="badge bg-light text-dark fs-6">
                                                {{ $assignmentGrade }} / {{ $course->assignment_grade }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary fs-6">غير متاح</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($finalGrade !== '-')
                                            <span class="badge bg-light text-dark fs-6">
                                                {{ $finalGrade }} / {{ $course->final_grade }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary fs-6">غير متاح</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($total !== '-')
                                            <span class="badge bg-light text-dark fs-6">
                                                {{ $total }} / {{ $course->assignment_grade + $course->final_grade }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary fs-6">غير متاح</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($letterGrade !== '-')
                                            @php
                                                $badgeClass = 'bg-success';
                                                if($letterGrade == 'C') $badgeClass = 'bg-info';
                                                if($letterGrade == 'D') $badgeClass = 'bg-warning text-dark';
                                                if($letterGrade == 'F') $badgeClass = 'bg-danger';
                                            @endphp
                                            <span class="badge {{ $badgeClass }} fs-6">{{ $letterGrade }}</span>
                                        @else
                                            <span class="badge bg-secondary fs-6">غير متاح</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-3">المعدل التراكمي</h6>
                                @php
                                    $gpa = '-';
                                    if($totalMaxPoints > 0) {
                                        $percentage = ($totalPoints / $totalMaxPoints) * 100;
                                        $gpa = number_format($percentage / 20, 2);
                                    }
                                @endphp
                                <h1 class="display-4 fw-bold text-primary">{{ $gpa }}</h1>
                                <p class="text-muted">من 5.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">بيان المقررات</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>عدد المقررات المسجلة:</span>
                                    <span class="fw-bold">{{ count($courses) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>عدد المقررات المكتملة:</span>
                                    <span class="fw-bold">{{ $grades->where('submitted', true)->count() }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>عدد المقررات المتبقية:</span>
                                    <span class="fw-bold">{{ count($courses) - $grades->where('submitted', true)->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/2866/2866906.png" alt="لا توجد مقررات" style="width: 120px; opacity: 0.5;">
                    <p class="mt-4 text-muted">لا توجد مقررات دراسية مسجلة لك حاليًا</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 