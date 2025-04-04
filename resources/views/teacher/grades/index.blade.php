@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1 text-primary fw-bold">
                <i class="fas fa-clipboard-check me-2"></i>إدارة الدرجات
            </h1>
            <p class="text-muted fs-5">إدخال وإدارة درجات الطلاب للمقررات التي تقوم بتدريسها</p>
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">
                <i class="fas fa-book me-2 text-primary"></i>المقررات الدراسية
            </h5>
        </div>
        <div class="card-body">
            @if($courses->count() > 0)
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @foreach($courses as $course)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold mb-2">{{ $course->name }}</h5>
                                    <p class="card-text text-muted small mb-3">{{ $course->code ?? 'بدون كود' }}</p>
                                    
                                    @if($course->description)
                                        <p class="card-text small text-truncate mb-3" title="{{ $course->description }}">
                                            {{ $course->description }}
                                        </p>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted small">
                                            <i class="fas fa-users me-1 text-primary"></i>
                                            @php
                                                $studentsCount = 0;
                                                foreach($course->groups as $group) {
                                                    $studentsCount += $group->students->count();
                                                }
                                            @endphp
                                            عدد الطلاب: {{ $studentsCount }}
                                        </span>
                                        <span class="text-muted small">
                                            <i class="fas fa-layer-group me-1 text-primary"></i>
                                            عدد المجموعات: {{ $course->groups->count() }}
                                        </span>
                                    </div>
                                    
                                    @php
                                        $submittedGrades = $course->grades()->where('submitted', true)->count();
                                        $totalStudents = $studentsCount;
                                        $percentage = $totalStudents > 0 ? round(($submittedGrades / $totalStudents) * 100) : 0;
                                    @endphp
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted small fw-bold">تقدم إدخال الدرجات</span>
                                            <span class="text-muted small">{{ $percentage }}%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%;" 
                                                aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    
                                    <a href="{{ route('teacher.grades.manage', $course->id) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-pen-alt me-1"></i> إدارة الدرجات
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/2866/2866906.png" alt="لا توجد مقررات" style="width: 120px; opacity: 0.5;">
                    <p class="mt-4 text-muted">لا توجد مقررات دراسية مسندة لك حاليًا</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 