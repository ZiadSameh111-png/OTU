@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">تفاصيل المقرر</h2>
                <a href="{{ route('courses.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i> العودة إلى المقررات
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <h3 class="text-primary">{{ $course->name }}</h3>
                        <span class="badge bg-primary-soft text-primary mb-3">{{ $course->code }}</span>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>الوصف</h5>
                            <p>{{ $course->description ?? 'لا يوجد وصف متاح' }}</p>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasRole('Admin'))
                    <div class="mt-4">
                        <a href="{{ route('courses.edit', $course) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> تعديل المقرر
                        </a>
                        <form action="{{ route('courses.destroy', $course) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('هل أنت متأكد من حذف هذا المقرر؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i> حذف المقرر
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 