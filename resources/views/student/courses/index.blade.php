@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">مقرراتي الدراسية</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success animate__animated animate__fadeIn" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger animate__animated animate__fadeIn" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if(!Auth::user()->group)
                <div class="alert alert-warning animate__animated animate__fadeIn" role="alert">
                    أنت غير منضم إلى أي مجموعة. يرجى التواصل مع إدارة النظام.
                </div>
            @elseif(count($courses) == 0)
                <div class="text-center py-5">
                    <img src="{{ asset('images/empty-data.svg') }}" alt="No Courses" class="img-fluid mb-3" style="max-height: 150px;">
                    <h3>لا توجد مقررات مسجلة</h3>
                    <p class="text-muted">لم يتم تسجيلك في أي مقررات دراسية بعد.</p>
                </div>
            @else
                <div class="row">
                    @foreach($courses as $course)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <h5 class="card-title mb-0">{{ $course->name }}</h5>
                                        <span class="badge bg-primary rounded-pill">{{ $course->code }}</span>
                                    </div>
                                    <p class="card-text text-muted mb-3">{{ Str::limit($course->description, 100) }}</p>
                                    
                                    <div class="mt-auto">
                                        @if($course->teacher)
                                            <p class="small mb-1">
                                                <i class="fas fa-chalkboard-teacher me-1 text-primary"></i>
                                                المدرس: {{ $course->teacher->name }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 p-3">
                                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 