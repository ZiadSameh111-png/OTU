@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">المقررات الدراسية</h2>
                @if(auth()->user()->hasRole('Admin'))
                <a href="{{ route('courses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i> إضافة مقرر جديد
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(count($courses) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الرمز</th>
                                    <th>اسم المقرر</th>
                                    <th>الوصف</th>
                                    @if(auth()->user()->hasRole('Admin'))
                                    <th>الإجراءات</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $course)
                                <tr>
                                    <td><span class="badge bg-primary-soft text-primary">{{ $course->code }}</span></td>
                                    <td>
                                        <strong>{{ $course->name }}</strong>
                                    </td>
                                    <td>{{ Str::limit($course->description, 100) }}</td>
                                    @if(auth()->user()->hasRole('Admin'))
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المقرر؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <img src="{{ asset('images/empty-courses.svg') }}" alt="No Courses" class="img-fluid mb-3" style="max-height: 150px;">
                        <h3>لا توجد مقررات</h3>
                        <p class="text-muted">لم يتم إضافة أي مقررات دراسية بعد.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 