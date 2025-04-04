@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">تفاصيل الدرجات - {{ $course->name }}</h4>
                    <a href="{{ route('student.grades.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-right ml-1"></i> العودة للدرجات
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box bg-light shadow-sm">
                                <div class="info-box-content">
                                    <h5 class="info-box-text text-muted mb-2">معلومات المقرر</h5>
                                    <p class="mb-1"><strong>اسم المقرر:</strong> {{ $course->name }}</p>
                                    <p class="mb-1"><strong>رمز المقرر:</strong> {{ $course->code }}</p>
                                    <p class="mb-1"><strong>أستاذ المقرر:</strong> {{ $course->teacher ? $course->teacher->name : 'غير محدد' }}</p>
                                    <p class="mb-0"><strong>عدد الساعات:</strong> {{ $course->credit_hours }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light shadow-sm">
                                <div class="info-box-content">
                                    <h5 class="info-box-text text-muted mb-2">حالة الدرجات</h5>
                                    <p class="mb-1">
                                        <strong>حالة الدرجات:</strong> 
                                        @if($grade->submitted)
                                            <span class="badge badge-success">تم تقديم الدرجات</span>
                                        @else
                                            <span class="badge badge-warning">لم يتم تقديم الدرجات بعد</span>
                                        @endif
                                    </p>
                                    @if($grade->submission_date)
                                        <p class="mb-1"><strong>تاريخ التقديم:</strong> {{ $grade->submission_date->format('Y-m-d') }}</p>
                                    @endif
                                    
                                    @if($grade->submitted)
                                        <p class="mb-0">
                                            <strong>التقدير النهائي:</strong> 
                                            <span class="badge badge-{{ $grade->getLetterGradeColor() }}">
                                                {{ $grade->getLetterGradeAttribute() }}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3">تفاصيل الدرجات</h5>
                    
                    @if($grade->submitted)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="25%">عنصر التقييم</th>
                                                <th width="25%">الدرجة القصوى</th>
                                                <th width="25%">درجتك</th>
                                                <th width="25%">النسبة المئوية</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>أعمال الفصل (اختبارات شهرية)</td>
                                                <td>{{ $course->midterm_grade }}</td>
                                                <td>{{ $grade->midterm_grade }}</td>
                                                <td>
                                                    @if($course->midterm_grade > 0)
                                                        {{ round(($grade->midterm_grade / $course->midterm_grade) * 100) }}%
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>أعمال الفصل (عملي/واجبات)</td>
                                                <td>{{ $course->assignment_grade }}</td>
                                                <td>{{ $grade->assignment_grade }}</td>
                                                <td>
                                                    @if($course->assignment_grade > 0)
                                                        {{ round(($grade->assignment_grade / $course->assignment_grade) * 100) }}%
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>الاختبار النهائي</td>
                                                <td>{{ $course->final_grade }}</td>
                                                <td>{{ $grade->final_grade }}</td>
                                                <td>
                                                    @if($course->final_grade > 0)
                                                        {{ round(($grade->final_grade / $course->final_grade) * 100) }}%
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr class="bg-light font-weight-bold">
                                                <td>المجموع</td>
                                                <td>{{ $course->midterm_grade + $course->assignment_grade + $course->final_grade }}</td>
                                                <td>{{ $grade->midterm_grade + $grade->assignment_grade + $grade->final_grade }}</td>
                                                <td>
                                                    @php
                                                        $totalMax = $course->midterm_grade + $course->assignment_grade + $course->final_grade;
                                                        $totalGrade = $grade->midterm_grade + $grade->assignment_grade + $grade->final_grade;
                                                        $percentage = $totalMax > 0 ? round(($totalGrade / $totalMax) * 100) : 0;
                                                    @endphp
                                                    {{ $percentage }}%
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        @if($grade->comments)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">ملاحظات المعلم</h5>
                                        </div>
                                        <div class="card-body">
                                            {{ $grade->comments }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">تفسير التقديرات</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><span class="badge badge-success">A+</span> - ممتاز مرتفع (95% فأعلى)</li>
                                                    <li><span class="badge badge-success">A</span> - ممتاز (90% - 94.9%)</li>
                                                    <li><span class="badge badge-primary">B+</span> - جيد جداً مرتفع (85% - 89.9%)</li>
                                                    <li><span class="badge badge-primary">B</span> - جيد جداً (80% - 84.9%)</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><span class="badge badge-info">C+</span> - جيد مرتفع (75% - 79.9%)</li>
                                                    <li><span class="badge badge-info">C</span> - جيد (70% - 74.9%)</li>
                                                    <li><span class="badge badge-warning">D+</span> - مقبول مرتفع (65% - 69.9%)</li>
                                                    <li><span class="badge badge-warning">D</span> - مقبول (60% - 64.9%)</li>
                                                    <li><span class="badge badge-danger">F</span> - راسب (أقل من 60%)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            لم يتم تقديم الدرجات لهذا المقرر بعد. يرجى التحقق لاحقًا.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 