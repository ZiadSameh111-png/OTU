@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4">إدارة الدرجات</h2>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="m-0">المقررات الدراسية</h5>
                </div>
                <div class="card-body">
                    @if(count($courses) > 0)
                        <div class="row">
                            @foreach($courses as $course)
                                @php
                                    // Calculate submission stats
                                    $totalStudents = 0;
                                    $submittedGrades = 0;
                                    
                                    foreach($course->groups as $group) {
                                        $totalStudents += $group->students->count();
                                    }
                                    
                                    $submittedGrades = App\Models\Grade::where('course_id', $course->id)
                                        ->where('submitted', true)
                                        ->count();
                                    
                                    $completionPercentage = $totalStudents > 0 ? 
                                        round(($submittedGrades / $totalStudents) * 100) : 0;
                                @endphp
                                
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $course->name }}</h5>
                                            <h6 class="card-subtitle mb-3 text-muted">{{ $course->code }}</h6>
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>عدد الطلاب:</span>
                                                <span>{{ $totalStudents }}</span>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>الدرجات المثبتة:</span>
                                                <span>{{ $submittedGrades }} / {{ $totalStudents }}</span>
                                            </div>
                                            
                                            <div class="progress mb-3">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $completionPercentage }}%;" 
                                                    aria-valuenow="{{ $completionPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                    {{ $completionPercentage }}%
                                                </div>
                                            </div>
                                            
                                            <a href="{{ route('teacher.grades.manage', $course->id) }}" class="btn btn-primary">
                                                <i class="fas fa-edit me-2"></i>إدارة الدرجات
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> لا توجد مقررات دراسية مسجلة لك حالياً.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0">تعليمات إدارة الدرجات</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>معلومات هامة</h6>
                        <hr>
                        <ul class="mb-0">
                            <li>يمكنك إدخال وتخزين الدرجات بشكل مؤقت باستخدام زر "حفظ التغييرات".</li>
                            <li>بعد الانتهاء من إدخال جميع الدرجات لمجموعة، يمكنك تثبيت الدرجات باستخدام زر "تثبيت الدرجات".</li>
                            <li>بعد تثبيت الدرجات، لا يمكن تعديلها إلا من قبل الإدارة.</li>
                            <li>يجب إدخال جميع الدرجات قبل تثبيتها.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 