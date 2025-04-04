@extends('layouts.app')

@section('styles')
<style>
    .timer-container {
        position: sticky;
        top: 20px;
        z-index: 100;
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
    .option-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    .option-card:hover {
        border-color: #007bff;
    }
    .option-card.selected {
        border-color: #007bff;
        background-color: #f0f8ff;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Timer Container -->
            <div class="timer-container">
                <div class="alert alert-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong><i class="fas fa-clock"></i> الوقت المتبقي:</strong>
                        <div id="exam-timer" class="h4 mb-0"></div>
                        <form id="submit-form" action="{{ route('student.exams.submit', $exam->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" onclick="return confirm('هل أنت متأكد من رغبتك في إنهاء الاختبار وتسليم الإجابات؟')">
                                <i class="fas fa-check-circle"></i> تسليم الاختبار
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Exam Info -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ $exam->title }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>المقرر:</strong> {{ $exam->course->name }}</p>
                            <p><strong>المجموعة:</strong> {{ $exam->group->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>بداية الاختبار:</strong> {{ $exam->start_time->format('Y-m-d h:i A') }}</p>
                            <p><strong>نهاية الاختبار:</strong> {{ $exam->end_time->format('Y-m-d h:i A') }}</p>
                            <p><strong>مدة الاختبار:</strong> {{ $exam->duration }} دقيقة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Section -->
            <div class="questions-container">
                @foreach($exam->questions as $index => $question)
                <div class="card question-card shadow-sm mb-4" id="question-{{ $question->id }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="question-number">{{ $index + 1 }}</span>
                            <span class="question-type-badge badge badge-info">
                                {{ $question->getQuestionTypeArabic() }} - {{ $question->marks }} درجة
                            </span>
                        </div>
                        <div class="answer-status" id="status-{{ $question->id }}">
                            @if(isset($answers[$question->id]))
                                <span class="badge badge-success"><i class="fas fa-check"></i> تم الإجابة</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-times"></i> لم تتم الإجابة بعد</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="question-text mb-4">
                            {!! nl2br(e($question->question_text)) !!}
                        </div>

                        <div class="answer-form">
                            <!-- Multiple Choice Question -->
                            @if($question->question_type === 'multiple_choice')
                                <div class="options-container">
                                    @foreach($question->getOptionsArray() as $key => $optionText)
                                        <div class="card option-card mb-2 {{ isset($answers[$question->id]) && $answers[$question->id] === $key ? 'selected' : '' }}" 
                                            onclick="selectOption('{{ $question->id }}', '{{ $key }}')">
                                            <div class="card-body py-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="question-{{ $question->id }}" 
                                                        id="option-{{ $question->id }}-{{ $key }}" value="{{ $key }}"
                                                        {{ isset($answers[$question->id]) && $answers[$question->id] === $key ? 'checked' : '' }}
                                                        onchange="saveAnswer('{{ $exam->id }}', '{{ $question->id }}', '{{ $key }}')">
                                                    <label class="form-check-label w-100" for="option-{{ $question->id }}-{{ $key }}">
                                                        {{ $optionText }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            
                            <!-- True/False Question -->
                            @elseif($question->question_type === 'true_false')
                                <div class="options-container">
                                    <div class="card option-card mb-2 {{ isset($answers[$question->id]) && $answers[$question->id] === 'true' ? 'selected' : '' }}" 
                                        onclick="selectOption('{{ $question->id }}', 'true')">
                                        <div class="card-body py-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="question-{{ $question->id }}" 
                                                    id="option-{{ $question->id }}-true" value="true"
                                                    {{ isset($answers[$question->id]) && $answers[$question->id] === 'true' ? 'checked' : '' }}
                                                    onchange="saveAnswer('{{ $exam->id }}', '{{ $question->id }}', 'true')">
                                                <label class="form-check-label w-100" for="option-{{ $question->id }}-true">
                                                    صح
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card option-card mb-2 {{ isset($answers[$question->id]) && $answers[$question->id] === 'false' ? 'selected' : '' }}" 
                                        onclick="selectOption('{{ $question->id }}', 'false')">
                                        <div class="card-body py-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="question-{{ $question->id }}" 
                                                    id="option-{{ $question->id }}-false" value="false"
                                                    {{ isset($answers[$question->id]) && $answers[$question->id] === 'false' ? 'checked' : '' }}
                                                    onchange="saveAnswer('{{ $exam->id }}', '{{ $question->id }}', 'false')">
                                                <label class="form-check-label w-100" for="option-{{ $question->id }}-false">
                                                    خطأ
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                            <!-- Open-Ended Question -->
                            @elseif($question->question_type === 'open_ended')
                                <div class="form-group">
                                    <label for="answer-{{ $question->id }}">إجابتك:</label>
                                    <textarea class="form-control" id="answer-{{ $question->id }}" rows="5" 
                                        onchange="saveAnswer('{{ $exam->id }}', '{{ $question->id }}', this.value)"
                                        onblur="saveAnswer('{{ $exam->id }}', '{{ $question->id }}', this.value)">{{ $answers[$question->id] ?? '' }}</textarea>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Final Submit -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <form action="{{ route('student.exams.submit', $exam->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-lg btn-primary" onclick="return confirm('هل أنت متأكد من رغبتك في إنهاء الاختبار وتسليم الإجابات؟')">
                            <i class="fas fa-paper-plane"></i> تسليم الاختبار
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Timer functionality
    let timeRemaining = {{ $attempt->timeRemaining() * 60 }}; // Convert to seconds
    const timerElement = document.getElementById('exam-timer');
    
    function updateTimer() {
        const hours = Math.floor(timeRemaining / 3600);
        const minutes = Math.floor((timeRemaining % 3600) / 60);
        const seconds = timeRemaining % 60;
        
        timerElement.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        if (timeRemaining <= 300) { // 5 minutes remaining
            timerElement.style.color = 'red';
        }
        
        if (timeRemaining <= 0) {
            // Auto-submit the exam
            document.getElementById('submit-form').submit();
        } else {
            timeRemaining--;
            setTimeout(updateTimer, 1000);
        }
    }
    
    updateTimer();
    
    // Save answers via AJAX
    function saveAnswer(examId, questionId, answer) {
        document.getElementById(`status-${questionId}`).innerHTML = '<span class="badge badge-warning"><i class="fas fa-spinner fa-spin"></i> جاري الحفظ...</span>';
        
        fetch("{{ route('student.exams.save-answer') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                exam_id: examId,
                question_id: questionId,
                answer: answer
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById(`status-${questionId}`).innerHTML = '<span class="badge badge-success"><i class="fas fa-check"></i> تم الحفظ</span>';
                setTimeout(() => {
                    document.getElementById(`status-${questionId}`).innerHTML = '<span class="badge badge-success"><i class="fas fa-check"></i> تم الإجابة</span>';
                }, 1500);
            } else {
                document.getElementById(`status-${questionId}`).innerHTML = '<span class="badge badge-danger"><i class="fas fa-times"></i> فشل الحفظ</span>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById(`status-${questionId}`).innerHTML = '<span class="badge badge-danger"><i class="fas fa-times"></i> فشل الحفظ</span>';
        });
    }
    
    // Handle option selection for radio buttons
    function selectOption(questionId, optionKey) {
        // Select the radio button
        document.getElementById(`option-${questionId}-${optionKey}`).checked = true;
        
        // Update the card styling
        const optionsContainer = document.querySelectorAll(`[name="question-${questionId}"]`);
        optionsContainer.forEach(option => {
            const card = option.closest('.option-card');
            if (option.value === optionKey) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
        
        // Save the answer
        saveAnswer('{{ $exam->id }}', questionId, optionKey);
    }
    
    // Warn the user before leaving the page
    window.addEventListener('beforeunload', function (e) {
        e.preventDefault();
        e.returnValue = 'هل أنت متأكد من رغبتك في مغادرة الصفحة؟ قد تفقد إجاباتك غير المحفوظة.';
    });
</script>
@endsection 