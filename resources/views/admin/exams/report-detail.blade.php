@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.reports') }}">تقارير الاختبارات</a></li>
                    <li class="breadcrumb-item active" aria-current="page">التقرير التفصيلي للاختبار</li>
                </ol>
            </nav>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-chart-line"></i> تقرير تفصيلي: {{ $exam->title }}
                    </h4>
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
                                            <th>مدة الاختبار:</th>
                                            <td>{{ $exam->duration }} دقيقة</td>
                                        </tr>
                                        <tr>
                                            <th>مجموع الدرجات:</th>
                                            <td>{{ $exam->total_marks }} درجة</td>
                                        </tr>
                                        <tr>
                                            <th>عدد الأسئلة:</th>
                                            <td>{{ $exam->questions->count() }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">إحصائيات المشاركة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 mb-3">
                                                <h3 class="text-primary mb-0">{{ $exam->total_attempts }}</h3>
                                                <small class="text-muted">إجمالي المحاولات</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 mb-3">
                                                <h3 class="text-success mb-0">{{ $exam->submitted_count }}</h3>
                                                <small class="text-muted">المحاولات المكتملة</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 mb-3">
                                                <h3 class="text-info mb-0">{{ $exam->submitted_count > 0 ? round(($exam->submitted_count / $exam->total_attempts) * 100) : 0 }}%</h3>
                                                <small class="text-muted">معدل الإكمال</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h6 class="mt-4 mb-3">توزيع الدرجات</h6>
                                    <canvas id="scoreDistributionChart" height="150"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- تحليل الأسئلة -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">تحليل الأسئلة</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>نص السؤال</th>
                                            <th>نوع السؤال</th>
                                            <th>الدرجة</th>
                                            <th>عدد الإجابات</th>
                                            <th>الإجابات الصحيحة</th>
                                            <th>نسبة الصحة</th>
                                            <th>مستوى الصعوبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($exam->questions as $index => $question)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ Str::limit($question->question_text, 60) }}</td>
                                                <td>
                                                    @if($question->type == 'multiple_choice')
                                                        <span class="badge bg-primary">اختيار من متعدد</span>
                                                    @elseif($question->type == 'true_false')
                                                        <span class="badge bg-success">صح/خطأ</span>
                                                    @elseif($question->type == 'open_ended')
                                                        <span class="badge bg-warning">مقالي</span>
                                                    @endif
                                                </td>
                                                <td>{{ $question->pivot->marks }}</td>
                                                <td>{{ $questionStats[$question->id]['total_answers'] }}</td>
                                                <td>{{ $questionStats[$question->id]['correct_answers'] }}</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar {{ $questionStats[$question->id]['correct_percentage'] >= 70 ? 'bg-success' : ($questionStats[$question->id]['correct_percentage'] >= 40 ? 'bg-warning' : 'bg-danger') }}" 
                                                            role="progressbar" 
                                                            style="width: {{ $questionStats[$question->id]['correct_percentage'] }}%;" 
                                                            aria-valuenow="{{ $questionStats[$question->id]['correct_percentage'] }}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100">
                                                            {{ round($questionStats[$question->id]['correct_percentage']) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($questionStats[$question->id]['difficulty_level'] == 'سهل')
                                                        <span class="badge bg-success">سهل</span>
                                                    @elseif($questionStats[$question->id]['difficulty_level'] == 'متوسط')
                                                        <span class="badge bg-warning">متوسط</span>
                                                    @else
                                                        <span class="badge bg-danger">صعب</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            
                                            @if($question->type == 'multiple_choice' && isset($questionStats[$question->id]['option_stats']))
                                                <tr>
                                                    <td colspan="8" class="bg-light">
                                                        <div class="p-3">
                                                            <h6 class="mb-3">تحليل خيارات السؤال #{{ $index + 1 }}</h6>
                                                            <div class="row">
                                                                @foreach($question->options as $option)
                                                                    <div class="col-md-6 mb-2">
                                                                        <div class="card">
                                                                            <div class="card-body p-2">
                                                                                <small class="d-block">{{ $option->option_text }}</small>
                                                                                <div class="progress mt-2" style="height: 20px;">
                                                                                    <div class="progress-bar {{ $option->is_correct ? 'bg-success' : 'bg-secondary' }}" 
                                                                                         role="progressbar" 
                                                                                         style="width: {{ $questionStats[$question->id]['option_stats'][$option->id]['selected_percentage'] }}%;" 
                                                                                         aria-valuenow="{{ $questionStats[$question->id]['option_stats'][$option->id]['selected_percentage'] }}" 
                                                                                         aria-valuemin="0" 
                                                                                         aria-valuemax="100">
                                                                                        {{ round($questionStats[$question->id]['option_stats'][$option->id]['selected_percentage']) }}% ({{ $questionStats[$question->id]['option_stats'][$option->id]['selected_count'] }})
                                                                                    </div>
                                                                                </div>
                                                                                @if($option->is_correct)
                                                                                    <span class="badge bg-success mt-1">الإجابة الصحيحة</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- الإجراءات -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.exams.reports') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة إلى التقارير
                        </a>
                        <div>
                            <a href="{{ route('admin.exams.show', $exam->id) }}" class="btn btn-primary">
                                <i class="fas fa-eye"></i> عرض الاختبار
                            </a>
                            <button class="btn btn-success" onclick="window.print()">
                                <i class="fas fa-print"></i> طباعة التقرير
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Score Distribution Chart
        const scoreCtx = document.getElementById('scoreDistributionChart').getContext('2d');
        new Chart(scoreCtx, {
            type: 'bar',
            data: {
                labels: ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'],
                datasets: [{
                    label: 'عدد الطلاب',
                    data: [
                        {{ $scoreDistribution['0-20'] }},
                        {{ $scoreDistribution['21-40'] }},
                        {{ $scoreDistribution['41-60'] }},
                        {{ $scoreDistribution['61-80'] }},
                        {{ $scoreDistribution['81-100'] }}
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(255, 205, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(54, 162, 235, 0.5)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endsection 