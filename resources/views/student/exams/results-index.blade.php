@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-poll"></i> نتائج الاختبارات
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

                    @if(count($attempts) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>عنوان الاختبار</th>
                                        <th>المقرر</th>
                                        <th>تاريخ التقديم</th>
                                        <th>الدرجة</th>
                                        <th>النسبة المئوية</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attempts as $attempt)
                                        <tr>
                                            <td>{{ $attempt->exam->title }}</td>
                                            <td>{{ $attempt->exam->course->name }}</td>
                                            <td>{{ $attempt->submit_time ? $attempt->submit_time->format('Y-m-d h:i A') : 'غير محدد' }}</td>
                                            <td>{{ $attempt->total_marks_obtained ?? 0 }} / {{ $attempt->total_possible_marks ?? $attempt->exam->total_marks }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    @php
                                                        $percentage = $attempt->scorePercentage();
                                                        $colorClass = 'bg-danger';
                                                        
                                                        if ($percentage >= 90) {
                                                            $colorClass = 'bg-success';
                                                        } elseif ($percentage >= 70) {
                                                            $colorClass = 'bg-info';
                                                        } elseif ($percentage >= 50) {
                                                            $colorClass = 'bg-warning';
                                                        }
                                                    @endphp
                                                    
                                                    <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percentage }}%;" 
                                                        aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                                        {{ $percentage }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($attempt->is_graded)
                                                    <span class="badge badge-success">تم التصحيح</span>
                                                @else
                                                    <span class="badge badge-warning">قيد التصحيح</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('student.exams.results.view', $attempt->exam_id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> عرض التفاصيل
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-lg mb-3"></i>
                            <p>لم تقم بإجراء أي اختبارات بعد.</p>
                            <a href="{{ route('student.exams.index') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-list"></i> استعراض الاختبارات المتاحة
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Performance Summary Section -->
            @if(count($attempts) > 0)
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> ملخص الأداء
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3 mb-md-0">
                                <div class="h5">عدد الاختبارات</div>
                                <div class="display-4">{{ count($attempts) }}</div>
                            </div>
                            <div class="col-md-4 text-center mb-3 mb-md-0">
                                <div class="h5">متوسط الدرجات</div>
                                <div class="display-4">
                                    @php
                                        $avgPercentage = $attempts->avg(function($attempt) {
                                            return $attempt->scorePercentage();
                                        });
                                    @endphp
                                    {{ number_format($avgPercentage, 1) }}%
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="h5">أعلى درجة</div>
                                <div class="display-4">
                                    @php
                                        $highestPercentage = $attempts->max(function($attempt) {
                                            return $attempt->scorePercentage();
                                        });
                                    @endphp
                                    {{ number_format($highestPercentage, 1) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="text-center mt-4">
                <a href="{{ route('student.exams.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> العودة إلى قائمة الاختبارات
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-home"></i> الرئيسية
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 