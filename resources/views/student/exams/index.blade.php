@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt"></i> الاختبارات الإلكترونية
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
                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if(count($exams) > 0)
                        <!-- Active Exams -->
                        <div class="mb-5">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-play-circle text-success"></i> الاختبارات الجارية
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>عنوان الاختبار</th>
                                            <th>المقرر</th>
                                            <th>المجموعة</th>
                                            <th>تاريخ البدء</th>
                                            <th>تاريخ الانتهاء</th>
                                            <th>المدة (دقيقة)</th>
                                            <th>الحالة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $activeExamFound = false; @endphp
                                        @foreach($exams as $exam)
                                            @if($exam->status === 'active')
                                                @php $activeExamFound = true; @endphp
                                                <tr>
                                                    <td>{{ $exam->title }}</td>
                                                    <td>{{ $exam->course->name }}</td>
                                                    <td>{{ $exam->group->name }}</td>
                                                    <td>{{ $exam->start_time->format('Y-m-d h:i A') }}</td>
                                                    <td>{{ $exam->end_time->format('Y-m-d h:i A') }}</td>
                                                    <td>{{ $exam->duration }}</td>
                                                    <td>
                                                        <span class="badge badge-success">متاح الآن</span>
                                                    </td>
                                                    <td>
                                                        @if(isset($attempts[$exam->id]) && in_array($attempts[$exam->id], ['submitted', 'graded']))
                                                            <a href="{{ route('student.exams.results.view', $exam->id) }}" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> عرض النتائج
                                                            </a>
                                                        @elseif(isset($attempts[$exam->id]) && in_array($attempts[$exam->id], ['started', 'in_progress']))
                                                            <a href="{{ route('student.exams.take', $exam->id) }}" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-pencil-alt"></i> إكمال الاختبار
                                                            </a>
                                                        @else
                                                            <a href="{{ route('student.exams.start', $exam->id) }}" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-play"></i> بدء الاختبار
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        @if(!$activeExamFound)
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">لا توجد اختبارات متاحة حالياً</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Upcoming Exams -->
                        <div class="mb-5">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-clock text-info"></i> الاختبارات القادمة
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>عنوان الاختبار</th>
                                            <th>المقرر</th>
                                            <th>المجموعة</th>
                                            <th>تاريخ البدء</th>
                                            <th>تاريخ الانتهاء</th>
                                            <th>المدة (دقيقة)</th>
                                            <th>الوقت المتبقي</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $upcomingExamFound = false; @endphp
                                        @foreach($exams as $exam)
                                            @if($exam->status === 'pending')
                                                @php $upcomingExamFound = true; @endphp
                                                <tr>
                                                    <td>{{ $exam->title }}</td>
                                                    <td>{{ $exam->course->name }}</td>
                                                    <td>{{ $exam->group->name }}</td>
                                                    <td>{{ $exam->start_time->format('Y-m-d h:i A') }}</td>
                                                    <td>{{ $exam->end_time->format('Y-m-d h:i A') }}</td>
                                                    <td>{{ $exam->duration }}</td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            {{ now()->diffInDays($exam->start_time) > 0 ? now()->diffInDays($exam->start_time) . ' يوم' : now()->diffInHours($exam->start_time) . ' ساعة' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        @if(!$upcomingExamFound)
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">لا توجد اختبارات قادمة</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Completed Exams -->
                        <div>
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-check-circle text-secondary"></i> الاختبارات المنتهية
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>عنوان الاختبار</th>
                                            <th>المقرر</th>
                                            <th>المجموعة</th>
                                            <th>تاريخ البدء</th>
                                            <th>تاريخ الانتهاء</th>
                                            <th>الحالة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $completedExamFound = false; @endphp
                                        @foreach($exams as $exam)
                                            @if($exam->status === 'completed')
                                                @php $completedExamFound = true; @endphp
                                                <tr>
                                                    <td>{{ $exam->title }}</td>
                                                    <td>{{ $exam->course->name }}</td>
                                                    <td>{{ $exam->group->name }}</td>
                                                    <td>{{ $exam->start_time->format('Y-m-d h:i A') }}</td>
                                                    <td>{{ $exam->end_time->format('Y-m-d h:i A') }}</td>
                                                    <td>
                                                        @if(isset($attempts[$exam->id]) && in_array($attempts[$exam->id], ['submitted', 'graded']))
                                                            <span class="badge badge-success">تم التقديم</span>
                                                        @elseif(isset($attempts[$exam->id]) && in_array($attempts[$exam->id], ['started', 'in_progress']))
                                                            <span class="badge badge-danger">غير مكتمل</span>
                                                        @else
                                                            <span class="badge badge-danger">لم يتم التقديم</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(isset($attempts[$exam->id]) && in_array($attempts[$exam->id], ['submitted', 'graded']))
                                                            <a href="{{ route('student.exams.results.view', $exam->id) }}" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> عرض النتائج
                                                            </a>
                                                        @else
                                                            <span class="text-muted">غير متاح</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        @if(!$completedExamFound)
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">لا توجد اختبارات منتهية</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-lg mb-3"></i>
                            <p>لم يتم تعيين أي اختبارات لمجموعتك بعد.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 