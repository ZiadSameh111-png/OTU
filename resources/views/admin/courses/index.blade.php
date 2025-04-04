@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">إدارة المقررات</h2>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus ml-1"></i> إضافة مقرر جديد
            </a>
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

            @if(count($courses) == 0)
                <div class="text-center py-5">
                    <img src="{{ asset('images/empty-data.svg') }}" alt="No Courses" class="img-fluid mb-3" style="max-height: 150px;">
                    <h3>لا توجد مقررات مسجلة</h3>
                    <p class="text-muted">قم بإضافة مقررات جديدة باستخدام زر "إضافة مقرر جديد"</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المقرر</th>
                                <th>الرمز</th>
                                <th>المدرس</th>
                                <th>المجموعات</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $index => $course)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $course->name }}</td>
                                    <td><span class="badge bg-primary">{{ $course->code }}</span></td>
                                    <td>
                                        @if($course->teacher)
                                            {{ $course->teacher->name }}
                                        @else
                                            <span class="text-muted">غير معين</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->groups->count() > 0)
                                            <span class="badge bg-info">{{ $course->groups->count() }} مجموعة</span>
                                        @else
                                            <span class="badge bg-secondary">0 مجموعة</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.courses.show', $course->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $course->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal Delete -->
                                <div class="modal fade" id="deleteModal{{ $course->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $course->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $course->id }}">تأكيد الحذف</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                هل أنت متأكد من حذف المقرر <strong>{{ $course->name }}</strong>؟
                                                <p class="text-danger mt-2 mb-0"><small>سيتم حذف جميع البيانات المرتبطة بهذا المقرر.</small></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">تأكيد الحذف</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 