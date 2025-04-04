@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-lg mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">تفاصيل المقرر</h2>
                    <div>
                        <a href="{{ route('admin.courses') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right ml-1"></i> العودة للمقررات
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4 class="text-primary">{{ $course->name }}</h4>
                            <h6 class="text-muted">{{ $course->code }}</h6>
                        </div>
                        <div class="col-md-6 text-md-end">
                            @if(auth()->user()->hasRole('Admin'))
                                <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit ml-1"></i> تعديل المقرر
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">الوصف</h5>
                                    <p class="card-text">{{ $course->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">المدرس</h5>
                                    @if($course->teacher)
                                        <p class="card-text">{{ $course->teacher->name }}</p>
                                    @else
                                        <p class="card-text text-muted">لم يتم تعيين مدرس بعد</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">تاريخ الإضافة</h5>
                                    <p class="card-text">{{ $course->created_at->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-lg mb-4">
                <div class="card-header">
                    <h5 class="mb-0">المجموعات المسجلة</h5>
                </div>
                <div class="card-body">
                    @if($course->groups->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($course->groups as $group)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $group->name }}
                                    <span class="badge bg-primary rounded-pill">{{ $group->students->count() }} طالب</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-3">
                            <img src="{{ asset('images/empty-data.svg') }}" alt="No Groups" class="img-fluid mb-3" style="max-height: 100px;">
                            <p class="mb-0 text-muted">لم يتم تسجيل أي مجموعة لهذا المقرر بعد</p>
                        </div>
                    @endif
                </div>
                @if(auth()->user()->hasRole('Admin'))
                    <div class="card-footer text-center">
                        <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المقرر؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash ml-1"></i> حذف المقرر
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 