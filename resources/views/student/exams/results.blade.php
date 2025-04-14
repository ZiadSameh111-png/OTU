@extends('layouts.app')

@section('styles')
<style>
    .result-summary {
        font-size: 1.2em;
    }
    .result-score {
        font-size: 2em;
        font-weight: bold;
    }
    .question-card {
        border-right: 4px solid #007bff;
        margin-bottom: 20px;
    }
    .question-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        background-color: #007bff;
        color: white;
        border-radius: 50%;
        margin-left: 10px;
    }
    .question-type-badge {
        font-size: 0.8em;
        margin-right: 10px;
    }
    .correct-answer {
        border-right: 4px solid #28a745;
        background-color: rgba(40, 167, 69, 0.05);
    }
    .incorrect-answer {
        border-right: 4px solid #dc3545;
        background-color: rgba(220, 53, 69, 0.05);
    }
    .neutral-answer {
        border-right: 4px solid #17a2b8;
        background-color: rgba(23, 162, 184, 0.05);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-poll"></i> نتائج الاختبار: {{ $exam->title }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>المقرر:</strong> {{ $exam->course->name }}</p>
                            <p><strong>المجموعة:</strong> {{ $exam->group->name }}</p>
                            <p><strong>تاريخ التقديم:</strong> {{ $attempt->submit_time ? $attempt->submit_time->format('Y-m-d h:i A') : 'غير محدد' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>حالة الاختبار:</strong> 
                                @if($exam->is_open)
                                    <span class="badge badge-success">مفتوح</span>
                                @else
                                    <span class="badge badge-secondary">مغلق</span>
                                @endif
                            </p>
                            <p><strong>مدة الاختبار:</strong> {{ $exam->duration }} دقيقة</p>
                            <p><strong>المدة المستغرقة:</strong> {{ $attempt->duration() }} دقيقة</p>
                        </div>
                    </div>
                    
                    <!-- Result Summary -->
                    <div class="card bg-light mb-4">
                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold text-dark">ملخص النتائج</h5>
                            <div class="result-summary mb-3">
                                <div class="result-score fw-bold text-dark">
                                    {{ $attempt->total_marks_obtained ?? 0 }} / {{ $attempt->total_possible_marks ?? $exam->total_marks }}
                                </div>
                                <div class="result-percentage fw-bold text-dark">
                                    {{ $attempt->scorePercentage() }}%
                                </div>
                            </div>
                            
                            @if($attempt->is_graded)
                                <div class="badge badge-success p-2 fw-bold">تم التصحيح بالكامل</div>
                            @else
                                <div class="badge badge-warning p-2">جاري التصحيح</div>
                                <p class="text-muted mt-2 small">بعض الأسئلة قد تحتاج إلى تصحيح يدوي من المدرس.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Questions and Answers Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">تفاصيل الإجابات</h5>
                </div>
                <div class="card-body">
                    @foreach($exam->questions as $index => $question)
                        @php
                            $answer = $answers[$question->id] ?? null;
                            $answerClass = '';
                            
                            if ($question->question_type !== 'open_ended') {
                                if ($answer && isset($answer->is_correct)) {
                                    $answerClass = $answer->is_correct ? 'correct-answer' : 'incorrect-answer';
                                }
                            } else {
                                $answerClass = 'neutral-answer';
                            }
                        @endphp
                        
                        <div class="card question-card {{ $answerClass }} shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="question-number">{{ $index + 1 }}</span>
                                    <span class="question-type-badge badge badge-info">
                                        {{ $question->getQuestionTypeArabic() }} - {{ $question->marks }} درجة
                                    </span>
                                </div>
                                <div>
                                    @if($answer)
                                        @if($question->question_type === 'open_ended')
                                            @if($answer->marks_obtained !== null)
                                                <span class="badge badge-primary">{{ $answer->marks_obtained }} / {{ $question->marks }}</span>
                                            @else
                                                <span class="badge badge-warning">في انتظار التصحيح</span>
                                            @endif
                                        @else
                                            @if($answer->is_correct)
                                                <span class="badge badge-success">إجابة صحيحة</span>
                                            @else
                                                <span class="badge badge-danger">إجابة خاطئة</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">لم تتم الإجابة</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="question-text mb-4">
                                    {!! nl2br(e($question->question_text)) !!}
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>إجابتك:</h6>
                                        @if($answer)
                                            @if($question->question_type === 'multiple_choice')
                                                <p>{{ $question->getOptionsArray()[$answer->answer] ?? $answer->answer }}</p>
                                            @elseif($question->question_type === 'true_false')
                                                <p>{{ $answer->answer === 'true' ? 'صح' : 'خطأ' }}</p>
                                            @else
                                                <div class="card">
                                                    <div class="card-body bg-light">
                                                        {!! nl2br(e($answer->answer)) !!}
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <p class="text-muted">لم تقم بالإجابة على هذا السؤال</p>
                                        @endif
                                    </div>
                                    
                                    <div class="col-md-6">
                                        @if($question->question_type !== 'open_ended' || $attempt->is_graded)
                                            <h6>الإجابة الصحيحة:</h6>
                                            @if($question->question_type === 'multiple_choice')
                                                <p>{{ $question->getOptionsArray()[$question->correct_answer] ?? $question->correct_answer }}</p>
                                            @elseif($question->question_type === 'true_false')
                                                <p>{{ $question->correct_answer === 'true' ? 'صح' : 'خطأ' }}</p>
                                            @else
                                                <p class="text-muted">سؤال مفتوح، لا توجد إجابة محددة.</p>
                                            @endif
                                        @endif
                                        
                                        @if($answer && $answer->feedback)
                                            <h6 class="mt-3">تعليق المدرس:</h6>
                                            <div class="alert alert-info">
                                                {{ $answer->feedback }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="text-center mb-4">
                <a href="{{ route('student.exams.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> العودة إلى قائمة الاختبارات
                </a>
                <a href="{{ route('student.exams.results') }}" class="btn btn-info">
                    <i class="fas fa-poll"></i> جميع نتائج الاختبارات
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 