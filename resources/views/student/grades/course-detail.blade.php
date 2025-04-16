@extends('layouts.app')

@section('title', 'تفاصيل درجات المقرر')

@section('styles')
<style>
    .grade-section {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .exam-card {
        transition: all 0.3s ease;
        border-left: 5px solid #dee2e6;
    }
    .exam-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .exam-card.passed {
        border-left-color: #28a745;
    }
    .exam-card.failed {
        border-left-color: #dc3545;
    }
    .score-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .comment-box {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        font-style: italic;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>تفاصيل درجات المقرر</h2>
            <p class="text-muted">{{ $course->name }} ({{ $course->code }})</p>
        </div>
        <a href="{{ route('student.grades.report') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-right me-1"></i> العودة إلى سجل الدرجات
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 order-lg-1 order-2">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">تفاصيل التقييمات</h5>
                </div>
                <div class="card-body p-0">
                    <div class="grade-section">
                        <h5 class="mb-3"><i class="fas fa-laptop-code me-2 text-primary"></i> الاختبارات الإلكترونية</h5>
                        
                        @if(count($onlineExams) > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>عنوان الاختبار</th>
                                            <th>تاريخ الاختبار</th>
                                            <th>النتيجة</th>
                                            <th>النسبة المئوية</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($onlineExams as $exam)
                                            @php
                                                $attempt = $exam->attempts->where('student_id', auth()->id())->first();
                                                $percentage = $attempt ? ($attempt->score / $exam->total_marks) * 100 : 0;
                                                $statusClass = $percentage >= 50 ? 'text-success' : 'text-danger';
                                            @endphp
                                            <tr>
                                                <td>{{ $exam->title }}</td>
                                                <td>{{ $attempt ? $attempt->created_at->format('Y-m-d') : 'لم يتم الاختبار' }}</td>
                                                <td class="{{ $statusClass }} fw-bold">
                                                    {{ $attempt ? $attempt->score : 0 }} / {{ $exam->total_marks }}
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar {{ $percentage >= 50 ? 'bg-success' : 'bg-danger' }}" role="progressbar" style="width: {{ round($percentage) }}%"></div>
                                                    </div>
                                                    <small class="d-block text-muted mt-1">{{ round($percentage) }}%</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد اختبارات إلكترونية لهذا المقرر.
                            </div>
                        @endif
                    </div>

                    <div class="grade-section">
                        <h5 class="mb-3"><i class="fas fa-file-alt me-2 text-primary"></i> الاختبارات الورقية</h5>
                        
                        @if(count($paperExams) > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>عنوان الاختبار</th>
                                            <th>التاريخ</th>
                                            <th>النتيجة</th>
                                            <th>النسبة المئوية</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paperExams as $exam)
                                            @php
                                                $percentage = $exam->total_marks > 0 ? ($exam->score / $exam->total_marks) * 100 : 0;
                                                $statusClass = $percentage >= 50 ? 'text-success' : 'text-danger';
                                            @endphp
                                            <tr>
                                                <td>{{ $exam->title }}</td>
                                                <td>{{ $exam->exam_date->format('Y-m-d') }}</td>
                                                <td class="{{ $statusClass }} fw-bold">
                                                    {{ $exam->score }} / {{ $exam->total_marks }}
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar {{ $percentage >= 50 ? 'bg-success' : 'bg-danger' }}" role="progressbar" style="width: {{ round($percentage) }}%"></div>
                                                    </div>
                                                    <small class="d-block text-muted mt-1">{{ round($percentage) }}%</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد اختبارات ورقية لهذا المقرر.
                            </div>
                        @endif
                    </div>

                    <div class="grade-section">
                        <h5 class="mb-3"><i class="fas fa-flask me-2 text-primary"></i> التقييمات العملية</h5>
                        
                        @if(count($practicalAssessments) > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>عنوان التقييم</th>
                                            <th>النوع</th>
                                            <th>النتيجة</th>
                                            <th>النسبة المئوية</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($practicalAssessments as $assessment)
                                            @php
                                                $percentage = $assessment->total_marks > 0 ? ($assessment->score / $assessment->total_marks) * 100 : 0;
                                                $statusClass = $percentage >= 50 ? 'text-success' : 'text-danger';
                                            @endphp
                                            <tr>
                                                <td>{{ $assessment->title }}</td>
                                                <td>{{ $assessment->type }}</td>
                                                <td class="{{ $statusClass }} fw-bold">
                                                    {{ $assessment->score }} / {{ $assessment->total_marks }}
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar {{ $percentage >= 50 ? 'bg-success' : 'bg-danger' }}" role="progressbar" style="width: {{ round($percentage) }}%"></div>
                                                    </div>
                                                    <small class="d-block text-muted mt-1">{{ round($percentage) }}%</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد تقييمات عملية لهذا المقرر.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @if($grade->comment)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">تعليقات المدرس</h5>
                    </div>
                    <div class="card-body">
                        <div class="comment-box">
                            <i class="fas fa-quote-left text-muted me-2"></i>
                            {{ $grade->comment }}
                            <i class="fas fa-quote-right text-muted ms-2"></i>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4 order-lg-2 order-1 mb-4">
            <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ملخص الدرجات</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @php
                            $totalPercentage = $grade->total_possible > 0 ? ($grade->total_grade / $grade->total_possible) * 100 : 0;
                            $gradeColor = $totalPercentage >= 50 ? 'success' : 'danger';
                            
                            if ($totalPercentage >= 85) {
                                $gradeText = 'A';
                            } elseif ($totalPercentage >= 75) {
                                $gradeText = 'B';
                            } elseif ($totalPercentage >= 65) {
                                $gradeText = 'C';
                            } elseif ($totalPercentage >= 50) {
                                $gradeText = 'D';
                            } else {
                                $gradeText = 'F';
                            }
                        @endphp
                        
                        <div class="score-circle mx-auto mb-3 border border-{{ $gradeColor }}">
                            <span class="display-6 fw-bold text-{{ $gradeColor }}">{{ $gradeText }}</span>
                            <small class="text-muted">{{ round($totalPercentage) }}%</small>
                        </div>
                        
                        <h4 class="mb-0">{{ $grade->total_grade }} / {{ $grade->total_possible }}</h4>
                        <p class="text-muted">الدرجة الإجمالية</p>
                        
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-{{ $gradeColor }}" role="progressbar" style="width: {{ round($totalPercentage) }}%"></div>
                        </div>
                        
                        @if($grade->is_final)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                تم اعتماد الدرجات النهائية
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                الدرجات غير نهائية وقابلة للتغيير
                            </div>
                        @endif
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3">تفاصيل الدرجات</h6>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>اختبارات إلكترونية</span>
                            <span class="fw-bold">{{ $grade->online_exam_grade }} / {{ $grade->online_exam_total }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php 
                                $onlinePercentage = $grade->online_exam_total > 0 ? ($grade->online_exam_grade / $grade->online_exam_total) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ round($onlinePercentage) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>اختبارات ورقية</span>
                            <span class="fw-bold">{{ $grade->paper_exam_grade }} / {{ $grade->paper_exam_total }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php 
                                $paperPercentage = $grade->paper_exam_total > 0 ? ($grade->paper_exam_grade / $grade->paper_exam_total) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ round($paperPercentage) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>تقييمات عملية</span>
                            <span class="fw-bold">{{ $grade->practical_grade }} / {{ $grade->practical_total }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php 
                                $practicalPercentage = $grade->practical_total > 0 ? ($grade->practical_grade / $grade->practical_total) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ round($practicalPercentage) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2 mb-3">توزيع الدرجات</h6>
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 15px; height: 15px; background-color: #0dcaf0; border-radius: 50%;"></div>
                            <span class="ms-2">اختبارات إلكترونية ({{ round(($grade->online_exam_total / $grade->total_possible) * 100) }}%)</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 15px; height: 15px; background-color: #0d6efd; border-radius: 50%;"></div>
                            <span class="ms-2">اختبارات ورقية ({{ round(($grade->paper_exam_total / $grade->total_possible) * 100) }}%)</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 15px; height: 15px; background-color: #ffc107; border-radius: 50%;"></div>
                            <span class="ms-2">تقييمات عملية ({{ round(($grade->practical_total / $grade->total_possible) * 100) }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 