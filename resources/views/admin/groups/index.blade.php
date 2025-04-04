@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">إدارة المجموعات</h2>
            <a href="{{ route('groups.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> إضافة مجموعة جديدة
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

            @if(count($groups) > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>الوصف</th>
                                <th>الحالة</th>
                                <th>عدد الطلاب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $group)
                                <tr>
                                    <td>
                                        <strong>{{ $group->name }}</strong>
                                    </td>
                                    <td>{{ Str::limit($group->description, 50) }}</td>
                                    <td>
                                        @if($group->active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-danger">غير نشط</span>
                                        @endif
                                    </td>
                                    <td>{{ $group->students->count() }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('groups.show', $group->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('groups.destroy', $group->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذه المجموعة؟')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <img src="{{ asset('images/empty-data.svg') }}" alt="No Groups" class="img-fluid mb-3" style="max-height: 150px;">
                    <h3>لا توجد مجموعات</h3>
                    <p class="text-muted">لم يتم إضافة أي مجموعات بعد.</p>
                    <a href="{{ route('groups.create') }}" class="btn btn-primary mt-3">إضافة مجموعة جديدة</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 