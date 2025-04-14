@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> جدول المواعيد
                    </h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h5 class="mb-4">جدول مواعيد المدرس: {{ $teacherName }}</h5>

                    @if($schedules->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> لا توجد مواعيد مسجلة حالياً.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>اليوم</th>
                                        <th>المقرر</th>
                                        <th>المجموعة</th>
                                        <th>الوقت</th>
                                        <th>القاعة</th>
                                        <th>التفاصيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules->groupBy('day') as $day => $daySchedules)
                                        @foreach($daySchedules as $schedule)
                                            <tr>
                                                <td>{{ $schedule->day }}</td>
                                                <td>{{ $schedule->course->name }}</td>
                                                <td>{{ $schedule->group->name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                                                <td>{{ $schedule->room ?? 'غير محدد' }}</td>
                                                <td>
                                                    <a href="{{ route('teacher.schedule.show', $schedule) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> عرض
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Weekly Schedule View -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-week"></i> الجدول الأسبوعي
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 15%">الوقت</th>
                                    <th>الأحد</th>
                                    <th>الإثنين</th>
                                    <th>الثلاثاء</th>
                                    <th>الأربعاء</th>
                                    <th>الخميس</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $timeSlots = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
                                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
                                    $arabicDays = [
                                        'Sunday' => 'الأحد',
                                        'Monday' => 'الإثنين',
                                        'Tuesday' => 'الثلاثاء',
                                        'Wednesday' => 'الأربعاء',
                                        'Thursday' => 'الخميس',
                                        'Friday' => 'الجمعة',
                                        'Saturday' => 'السبت'
                                    ];
                                @endphp

                                @foreach($timeSlots as $index => $time)
                                    <tr>
                                        <td class="align-middle font-weight-bold">
                                            {{ $time }} - {{ $timeSlots[$index + 1] ?? '17:00' }}
                                        </td>
                                        
                                        @foreach($days as $day)
                                            <td>
                                                @php
                                                    $daySchedules = $schedules->filter(function($schedule) use ($day, $time, $timeSlots, $index, $arabicDays) {
                                                        $startTime = \Carbon\Carbon::parse($schedule->start_time);
                                                        $scheduleTime = \Carbon\Carbon::parse($time);
                                                        $endTimeSlot = \Carbon\Carbon::parse($timeSlots[$index + 1] ?? '17:00');
                                                        
                                                        // Check if this schedule falls within this time slot and day
                                                        $isWithinTimeSlot = $startTime->format('H:i') >= $time && $startTime->format('H:i') < ($timeSlots[$index + 1] ?? '17:00');
                                                        $isCorrectDay = $schedule->day == $day || $schedule->day == $arabicDays[$day];
                                                        
                                                        return $isCorrectDay && $isWithinTimeSlot;
                                                    });
                                                @endphp

                                                @foreach($daySchedules as $schedule)
                                                    <div class="schedule-item p-1 mb-1 bg-light rounded border">
                                                        <strong>{{ $schedule->course->name }}</strong><br>
                                                        <small>{{ $schedule->group->name }}</small><br>
                                                        <small>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</small>
                                                        @if($schedule->room)
                                                            <br><small class="text-muted">{{ $schedule->room }}</small>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 