@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">إدارة الجداول الدراسية</h2>
            <a href="{{ route('schedules.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> إضافة جدول جديد
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

            @if(count($schedules) > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المجموعة</th>
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
                                        <strong>{{ $schedule->group->name }}</strong><br>
                                        <small class="text-muted">{{ $schedule->group->description }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $schedule->course->name }}</strong><br>
                                        <small class="text-muted">{{ $schedule->course->code }}</small>
                                    </td>
                                    <td>{{ $schedule->day }}</td>
                                    <td>{{ date('h:i A', strtotime($schedule->start_time)) }} - {{ date('h:i A', strtotime($schedule->end_time)) }}</td>
                                    <td>{{ $schedule->room ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('schedules.edit', $schedule->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذا الجدول؟')">
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
                    <img src="{{ asset('images/empty-schedule.svg') }}" alt="No Schedules" class="img-fluid mb-3" style="max-height: 150px;">
                    <h3>لا توجد جداول دراسية</h3>
                    <p class="text-muted">لم يتم إضافة أي جداول دراسية بعد.</p>
                    <a href="{{ route('schedules.create') }}" class="btn btn-primary mt-3">إضافة جدول دراسي جديد</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 