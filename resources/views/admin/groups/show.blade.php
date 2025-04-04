@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">تفاصيل المجموعة: {{ $group->name }}</h2>
            <div>
                <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> تعديل المجموعة
                </a>
                <a href="{{ route('groups.index') }}" class="btn btn-secondary ms-2">
                    <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-lg h-100">
                <div class="card-header">
                    <h4 class="mb-0">معلومات المجموعة</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5 class="text-muted mb-1">اسم المجموعة</h5>
                        <h4>{{ $group->name }}</h4>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="text-muted mb-1">الحالة</h5>
                        @if($group->active)
                            <span class="badge bg-success">نشط</span>
                        @else
                            <span class="badge bg-danger">غير نشط</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <h5 class="text-muted mb-1">تاريخ الإنشاء</h5>
                        <p>{{ $group->created_at->format('Y-m-d') }}</p>
                    </div>

                    <div>
                        <h5 class="text-muted mb-1">الوصف</h5>
                        <p>{{ $group->description ?: 'لا يوجد وصف' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 mb-4">
            <div class="card shadow-lg">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">طلاب المجموعة</h4>
                    <span class="badge bg-primary">{{ $students->count() }} طالب</span>
                </div>
                <div class="card-body">
                    @if($students->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>الاسم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>تاريخ الانضمام</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                        <tr>
                                            <td>{{ $student->name }}</td>
                                            <td>{{ $student->email }}</td>
                                            <td>{{ $student->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="mb-0 text-muted">لا يوجد طلاب في هذه المجموعة حتى الآن</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">الجدول الدراسي للمجموعة</h4>
                    <a href="{{ route('schedules.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> إضافة محاضرة
                    </a>
                </div>
                <div class="card-body">
                    @if($schedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>المقرر</th>
                                        <th>اليوم</th>
                                        <th>الوقت</th>
                                        <th>القاعة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                        <tr>
                                            <td>
                                                <strong>{{ $schedule->course->name }}</strong><br>
                                                <small class="text-muted">{{ $schedule->course->code }}</small>
                                            </td>
                                            <td>{{ $schedule->day }}</td>
                                            <td>{{ date('h:i A', strtotime($schedule->start_time)) }} - {{ date('h:i A', strtotime($schedule->end_time)) }}</td>
                                            <td>{{ $schedule->room ?? 'غير محدد' }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('schedules.edit', $schedule->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذه المحاضرة؟')">
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
                        <div class="text-center py-4">
                            <p class="mb-0 text-muted">لا يوجد محاضرات لهذه المجموعة حتى الآن</p>
                            <a href="{{ route('schedules.create') }}" class="btn btn-primary mt-3">إضافة محاضرة جديدة</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 