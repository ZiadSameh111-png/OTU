@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">جدولي الدراسي - {{ $groupName }}</h2>
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
                    @if(count($schedules) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>المقرر</th>
                                    <th>اليوم</th>
                                    <th>وقت البداية</th>
                                    <th>وقت النهاية</th>
                                    <th>القاعة</th>
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
                                    <td>{{ date('h:i A', strtotime($schedule->start_time)) }}</td>
                                    <td>{{ date('h:i A', strtotime($schedule->end_time)) }}</td>
                                    <td>{{ $schedule->room ?? 'غير محدد' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <img src="{{ asset('images/empty-schedule.svg') }}" alt="No Schedules" class="img-fluid mb-3" style="max-height: 150px;">
                        <h3>لا توجد جداول دراسية</h3>
                        <p class="text-muted">لا توجد محاضرات مجدولة لمجموعتك بعد.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 