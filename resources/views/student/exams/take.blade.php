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
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Exam Header -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">{{ $exam->title }}</h5>
                    <div class="timer-container d-flex align-items-center">
                        <div class="spinner-grow spinner-grow-sm text-light me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span id="timer" class="fw-bold"></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">المقرر الدراسي:</small>
                            <p class="mb-0 fw-bold">{{ $exam->course->name }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">المجموعة:</small>
                            <p class="mb-0 fw-bold">{{ $exam->group->name }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">إجمالي الدرجات:</small>
                            <p class="mb-0 fw-bold">{{ $exam->total_marks }} درجة</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">مدة الاختبار:</small>
                            <p class="mb-0 fw-bold">{{ $exam->duration }} دقيقة</p>
                        </div>
                    </div>
                    
                    <div class="progress mt-3" style="height: 10px;">
                        <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Exam Questions -->
            <div id="questions-container">
                @foreach($questions as $index => $question)
                    <div class="question-card card shadow-sm border-0 mb-4 {{ $index > 0 ? 'd-none' : '' }}" data-question-id="{{ $question->id }}" data-question-index="{{ $index }}">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <span class="badge bg-primary me-2">{{ $index + 1 }}/{{ count($questions) }}</span>
                                {{ $question->question_text }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form class="answer-form" data-question-id="{{ $question->id }}">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                <input type="hidden" name="question_id" value="{{ $question->id }}">
                                
                                @if($question->question_type == 'multiple_choice')
                                    <div class="mb-3">
                                        @foreach($question->options as $key => $option)
                                            <div class="form-check mb-2 p-0">
                                                <input class="btn-check" type="radio" name="answer" id="option{{ $question->id }}_{{ $key }}" value="{{ $key }}" 
                                                    {{ isset($answers[$question->id]) && $answers[$question->id] == $key ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary w-100 text-start" for="option{{ $question->id }}_{{ $key }}">
                                                    {{ $option }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($question->question_type == 'true_false')
                                    <div class="mb-3">
                                        <div class="form-check mb-2 p-0">
                                            <input class="btn-check" type="radio" name="answer" id="true{{ $question->id }}" value="true" 
                                                {{ isset($answers[$question->id]) && $answers[$question->id] == 'true' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success w-100 text-start" for="true{{ $question->id }}">
                                                <i class="fas fa-check me-2"></i> صح
                                            </label>
                                        </div>
                                        <div class="form-check mb-2 p-0">
                                            <input class="btn-check" type="radio" name="answer" id="false{{ $question->id }}" value="false" 
                                                {{ isset($answers[$question->id]) && $answers[$question->id] == 'false' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-danger w-100 text-start" for="false{{ $question->id }}">
                                                <i class="fas fa-times me-2"></i> خطأ
                                            </label>
                                        </div>
                                    </div>
                                @elseif($question->question_type == 'open_ended')
                                    <div class="mb-3">
                                        <textarea class="form-control" name="answer" rows="6" placeholder="اكتب إجابتك هنا...">{{ isset($answers[$question->id]) ? $answers[$question->id] : '' }}</textarea>
                                        <small class="text-muted mt-1 d-block">أجب بشكل واضح ومختصر في حدود المساحة المتاحة.</small>
                                    </div>
                                @endif
                                
                                <div class="alert alert-success save-status d-none">
                                    <i class="fas fa-check-circle me-1"></i> تم حفظ إجابتك بنجاح
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary prev-question-btn" {{ $index == 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-arrow-right me-1"></i> السؤال السابق
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-primary save-answer-btn">
                                        <i class="fas fa-save me-1"></i> حفظ الإجابة
                                    </button>
                                    
                                    @if($index < count($questions) - 1)
                                        <button type="button" class="btn btn-primary next-question-btn">
                                            السؤال التالي <i class="fas fa-arrow-left ms-1"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitExamModal">
                                            <i class="fas fa-check-circle me-1"></i> إنهاء الاختبار
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Exam Navigation Sidebar (Mobile/Footer) -->
            <div class="d-lg-none card shadow-sm border-0 mb-4 position-sticky bottom-0">
                <div class="card-body py-2">
                    <div class="scroll-container overflow-auto">
                        <div class="d-flex flex-nowrap p-1">
                            @foreach($questions as $index => $question)
                                <button type="button" class="btn btn-sm me-1 mb-0 question-nav-btn {{ isset($answers[$question->id]) ? 'btn-success' : 'btn-outline-secondary' }}" 
                                    data-target-index="{{ $index }}">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                            <button type="button" class="btn btn-danger btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#submitExamModal">
                                <i class="fas fa-check-circle me-1"></i> إنهاء
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Exam Navigation Sidebar (Desktop) -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card shadow-sm border-0 mb-4 position-sticky" style="top: 1rem;">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold">الأسئلة</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($questions as $index => $question)
                            <div class="col-3">
                                <button type="button" class="btn btn-sm w-100 question-nav-btn {{ isset($answers[$question->id]) ? 'btn-success' : 'btn-outline-secondary' }}" 
                                    data-target-index="{{ $index }}">
                                    {{ $index + 1 }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="btn btn-sm btn-outline-secondary me-2 disabled" style="width: 30px;"></div>
                            <span class="small">لم تتم الإجابة</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="btn btn-sm btn-success me-2 disabled" style="width: 30px;"></div>
                            <span class="small">تمت الإجابة</span>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#submitExamModal">
                            <i class="fas fa-check-circle me-1"></i> إنهاء الاختبار
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Exam Confirmation Modal -->
<div class="modal fade" id="submitExamModal" tabindex="-1" aria-labelledby="submitExamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="submitExamModalLabel">تأكيد إنهاء الاختبار</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>تنبيه هام:</strong> هل أنت متأكد من إنهاء الاختبار وتسليم إجاباتك؟
                </div>
                <p>بعد التسليم، لن تتمكن من العودة لتعديل إجاباتك.</p>
                
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>الأسئلة التي تمت الإجابة عليها:</strong>
                                <span id="answered-count">{{ count($answers) }}</span> من {{ count($questions) }}
                            </div>
                            <div class="progress" style="width: 100px; height: 10px;">
                                <div id="answered-progress" class="progress-bar bg-success" role="progressbar" 
                                    style="width: {{ count($questions) > 0 ? (count($answers) / count($questions) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="text-danger fw-bold mb-0">أسئلة لم تتم الإجابة عليها:</p>
                <div id="unanswered-questions-list">
                    @php
                        $unansweredCount = 0;
                    @endphp
                    
                    @foreach($questions as $index => $question)
                        @if(!isset($answers[$question->id]))
                            <div class="badge bg-danger m-1">السؤال {{ $index + 1 }}</div>
                            @php $unansweredCount++; @endphp
                        @endif
                    @endforeach
                    
                    @if($unansweredCount == 0)
                        <p class="text-success mt-2">
                            <i class="fas fa-check-circle me-1"></i> لقد أجبت على جميع الأسئلة
                        </p>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> العودة للاختبار
                </button>
                <form action="{{ route('student.exams.submit', $exam->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> تسليم الاختبار
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Timer setup
        const timerDisplay = document.getElementById('timer');
        const progressBar = document.getElementById('progress-bar');
        let timeLeft = {{ $timeRemaining * 60 }}; // Convert minutes to seconds
        const totalTime = {{ $exam->duration * 60 }}; // Total exam time in seconds
        
        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            
            if (hours > 0) {
                return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            } else {
                return `${minutes}:${secs.toString().padStart(2, '0')}`;
            }
        }
        
        function updateTimer() {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timerDisplay.classList.add('text-danger');
                timerDisplay.textContent = 'انتهى الوقت!';
                
                // Auto-submit the exam
                setTimeout(() => {
                    alert('انتهى وقت الاختبار! سيتم تسليم إجاباتك تلقائيًا.');
                    document.querySelector('#submitExamModal form').submit();
                }, 1000);
                
                return;
            }
            
            timeLeft--;
            timerDisplay.textContent = formatTime(timeLeft);
            
            // Update progress bar
            const percentComplete = 100 - ((timeLeft / totalTime) * 100);
            progressBar.style.width = `${percentComplete}%`;
            
            // Change progress bar color as time gets lower
            if (timeLeft < totalTime * 0.25) {
                progressBar.classList.remove('bg-success', 'bg-warning');
                progressBar.classList.add('bg-danger');
            } else if (timeLeft < totalTime * 0.5) {
                progressBar.classList.remove('bg-success', 'bg-danger');
                progressBar.classList.add('bg-warning');
            }
        }
        
        // Initialize the timer
        timerDisplay.textContent = formatTime(timeLeft);
        const timerInterval = setInterval(updateTimer, 1000);
        
        // Question navigation
        const questionCards = document.querySelectorAll('.question-card');
        const navButtons = document.querySelectorAll('.question-nav-btn');
        
        navButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetIndex = parseInt(this.getAttribute('data-target-index'));
                
                // Hide all questions and show the target
                questionCards.forEach(card => card.classList.add('d-none'));
                questionCards[targetIndex].classList.remove('d-none');
            });
        });
        
        // Next and Previous buttons
        const nextButtons = document.querySelectorAll('.next-question-btn');
        const prevButtons = document.querySelectorAll('.prev-question-btn');
        
        nextButtons.forEach(button => {
            button.addEventListener('click', function() {
                const currentCard = this.closest('.question-card');
                const currentIndex = parseInt(currentCard.getAttribute('data-question-index'));
                const nextIndex = currentIndex + 1;
                
                if (nextIndex < questionCards.length) {
                    currentCard.classList.add('d-none');
                    questionCards[nextIndex].classList.remove('d-none');
                }
            });
        });
        
        prevButtons.forEach(button => {
            button.addEventListener('click', function() {
                const currentCard = this.closest('.question-card');
                const currentIndex = parseInt(currentCard.getAttribute('data-question-index'));
                const prevIndex = currentIndex - 1;
                
                if (prevIndex >= 0) {
                    currentCard.classList.add('d-none');
                    questionCards[prevIndex].classList.remove('d-none');
                }
            });
        });
        
        // Save answers
        const saveButtons = document.querySelectorAll('.save-answer-btn');
        const answeredCount = document.getElementById('answered-count');
        const answeredProgress = document.getElementById('answered-progress');
        const unansweredList = document.getElementById('unanswered-questions-list');
        
        saveButtons.forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                const questionId = form.getAttribute('data-question-id');
                const formData = new FormData(form);
                const statusAlert = form.querySelector('.save-status');
                const navBtn = document.querySelectorAll(`.question-nav-btn[data-target-index="${form.closest('.question-card').getAttribute('data-question-index')}"]`);
                
                // Check if an answer is provided
                const answer = formData.get('answer');
                if (!answer) {
                    alert('يرجى اختيار إجابة قبل الحفظ');
                    return;
                }
                
                // Send the answer via AJAX
                fetch('{{ route("student.exams.save-answer") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        statusAlert.textContent = 'تم حفظ إجابتك بنجاح';
                        statusAlert.classList.remove('d-none');
                        setTimeout(() => statusAlert.classList.add('d-none'), 2000);
                        
                        // Update navigation button status
                        navBtn.forEach(btn => btn.classList.remove('btn-outline-secondary'));
                        navBtn.forEach(btn => btn.classList.add('btn-success'));
                        
                        // Update answered questions count and progress
                        const totalAnswered = parseInt(data.answeredCount);
                        answeredCount.textContent = totalAnswered;
                        const percent = (totalAnswered / {{ count($questions) }}) * 100;
                        answeredProgress.style.width = `${percent}%`;
                        
                        // Update unanswered questions list
                        updateUnansweredList();
                    } else {
                        alert('حدث خطأ أثناء حفظ إجابتك. يرجى المحاولة مرة أخرى');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء حفظ إجابتك. يرجى المحاولة مرة أخرى');
                });
            });
        });
        
        // Auto-save on radio button change
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const saveBtn = this.closest('form').querySelector('.save-answer-btn');
                if (saveBtn) {
                    saveBtn.click();
                }
            });
        });
        
        // Auto-save on textarea change (debounced)
        let textareaTimeout;
        document.querySelectorAll('textarea[name="answer"]').forEach(textarea => {
            textarea.addEventListener('input', function() {
                clearTimeout(textareaTimeout);
                textareaTimeout = setTimeout(() => {
                    if (textarea.value.trim().length > 0) {
                        const saveBtn = this.closest('form').querySelector('.save-answer-btn');
                        if (saveBtn) {
                            saveBtn.click();
                        }
                    }
                }, 2000); // 2 seconds delay
            });
        });
        
        // Update unanswered questions list
        function updateUnansweredList() {
            // Collect answered question IDs
            const answeredQuestions = [];
            document.querySelectorAll('.question-nav-btn.btn-success').forEach(btn => {
                answeredQuestions.push(parseInt(btn.getAttribute('data-target-index')));
            });
            
            // Update the list in the modal
            let unansweredHTML = '';
            let unansweredCount = 0;
            
            for (let i = 0; i < {{ count($questions) }}; i++) {
                if (!answeredQuestions.includes(i)) {
                    unansweredHTML += `<div class="badge bg-danger m-1">السؤال ${i + 1}</div>`;
                    unansweredCount++;
                }
            }
            
            if (unansweredCount === 0) {
                unansweredHTML = '<p class="text-success mt-2"><i class="fas fa-check-circle me-1"></i> لقد أجبت على جميع الأسئلة</p>';
            }
            
            unansweredList.innerHTML = unansweredHTML;
        }
        
        // Initial call to setup the page
        updateUnansweredList();
    });
</script>
@endsection 