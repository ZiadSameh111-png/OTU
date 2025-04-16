@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.reports') }}">تقارير الاختبارات</a></li>
                    <li class="breadcrumb-item active" aria-current="page">نتائج الاختبار</li>
                </ol>
            </nav>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-clipboard-list"></i> نتائج الاختبار: {{ $exam->title }}
                        </h4>
                        <div>
                            <a href="{{ route('admin.exams.report.detail', $exam->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-chart-bar"></i> تقرير تفصيلي
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- معلومات الاختبار -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">معلومات الاختبار</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-striped">
                                        <tr>
                                            <th width="40%">عنوان الاختبار:</th>
                                            <td>{{ $exam->title }}</td>
                                        </tr>
                                        <tr>
                                            <th>المقرر:</th>
                                            <td>{{ $exam->course->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>المجموعة:</th>
                                            <td>{{ $exam->group->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>المدرس:</th>
                                            <td>{{ $exam->teacher->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ النشر:</th>
                                            <td>{{ $exam->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                        <tr>
                                            <th>عدد الأسئلة:</th>
                                            <td>{{ $exam->questions->count() }}</td>
                                        </tr>
                                        <tr>
                                            <th>الدرجة الكلية:</th>
                                            <td>{{ $exam->total_marks }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">ملخص النتائج</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h5 class="text-muted">إجمالي المحاولات</h5>
                                                <h2 class="mb-0 text-primary">{{ $exam->total_attempts }}</h2>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h5 class="text-muted">المحاولات المكتملة</h5>
                                                <h2 class="mb-0 text-success">{{ $exam->submitted_count }}</h2>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h5 class="text-muted">متوسط الدرجات</h5>
                                                <h2 class="mb-0 text-info">{{ number_format($statistics['avg_score'], 1) }}</h2>
                                                <small class="text-muted">من {{ $exam->total_marks }}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h5 class="text-muted">معدل النجاح</h5>
                                                <h2 class="mb-0 text-warning">{{ number_format($statistics['pass_rate'], 1) }}%</h2>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong>أعلى درجة:</strong>
                                            <span>{{ number_format($statistics['max_score'], 1) }} ({{ number_format(($statistics['max_score'] / $exam->total_marks) * 100, 1) }}%)</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <strong>أدنى درجة:</strong>
                                            <span>{{ number_format($statistics['min_score'], 1) }} ({{ number_format(($statistics['min_score'] / $exam->total_marks) * 100, 1) }}%)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- جدول نتائج الطلاب -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">نتائج الطلاب</h5>
                                <button class="btn btn-sm btn-success" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> طباعة النتائج
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>اسم الطالب</th>
                                            <th>المجموعة</th>
                                            <th>وقت التسليم</th>
                                            <th>الدرجة</th>
                                            <th>النسبة المئوية</th>
                                            <th>التقدير</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attempts as $index => $attempt)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $attempt->student->name }}</td>
                                                <td>{{ $attempt->student->group->name ?? 'غير محدد' }}</td>
                                                <td>{{ $attempt->submit_time->format('Y-m-d H:i') }}</td>
                                                <td>{{ number_format($attempt->total_marks_obtained, 1) }} / {{ $attempt->total_possible_marks }}</td>
                                                <td>
                                                    @php
                                                        $percentage = ($attempt->total_marks_obtained / $attempt->total_possible_marks) * 100;
                                                    @endphp
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar {{ $percentage >= 90 ? 'bg-success' : ($percentage >= 70 ? 'bg-info' : ($percentage >= 60 ? 'bg-warning' : 'bg-danger')) }}" 
                                                            role="progressbar" 
                                                            style="width: {{ $percentage }}%;" 
                                                            aria-valuenow="{{ $percentage }}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100">
                                                            {{ number_format($percentage, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($percentage >= 90)
                                                        <span class="badge bg-success">ممتاز</span>
                                                    @elseif($percentage >= 80)
                                                        <span class="badge bg-primary">جيد جداً</span>
                                                    @elseif($percentage >= 70)
                                                        <span class="badge bg-info">جيد</span>
                                                    @elseif($percentage >= 60)
                                                        <span class="badge bg-warning">مقبول</span>
                                                    @else
                                                        <span class="badge bg-danger">راسب</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- الإجراءات -->
                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('admin.exams.reports') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للتقارير
                        </a>
                        <div>
                            <a href="{{ route('admin.exams.show', $exam->id) }}" class="btn btn-primary">
                                <i class="fas fa-eye"></i> عرض الاختبار
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 