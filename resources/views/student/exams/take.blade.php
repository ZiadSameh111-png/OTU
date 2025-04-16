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
    .save-status {
        transition: all 0.3s ease;
    }
    .save-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-left: 5px;
    }
    .saving {
        background-color: #ffc107;
    }
    .saved {
        background-color: #28a745;
    }
    .save-error {
        background-color: #dc3545;
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
                        <div class="card-header bg-dark text-white">
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
                                    <i class="fas fa-check-circle me-1"></i> <span class="save-status-text">تم حفظ إجابتك بنجاح</span>
                                    <span class="save-indicator"></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary prev-question-btn" {{ $index == 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-arrow-right me-1"></i> السؤال السابق
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
                                    data-target-index="{{ $index }}" data-question-id="{{ $question->id }}">
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
                    <!-- Will be filled by JavaScript -->
                </div>
                
                <div id="submission-loader" class="text-center mt-3 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري الحفظ...</span>
                    </div>
                    <p class="mt-2 mb-0">جاري حفظ إجاباتك وتجهيز الاختبار للتسليم...</p>
                    <p id="submission-status" class="mt-2"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> العودة للاختبار
                </button>
                <form action="{{ route('student.exams.submit', $exam->id) }}" method="POST" id="submitExamForm">
                    @csrf
                    <button type="button" class="btn btn-primary" id="finalSubmitBtn">
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
        // Globals
        const answersCache = new Map();
        const savedStatus = new Map();
        let isSubmitting = false;
        
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
                    submitAllAnswers();
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
                
                // Save current answer if any
                const form = currentCard.querySelector('form');
                if (form) {
                    saveAnswer(form);
                }
                
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
                
                // Save current answer if any
                const form = currentCard.querySelector('form');
                if (form) {
                    saveAnswer(form);
                }
                
                if (prevIndex >= 0) {
                    currentCard.classList.add('d-none');
                    questionCards[prevIndex].classList.remove('d-none');
                }
            });
        });
        
        // Function to update UI status
        function updateSaveStatus(form, status, message) {
            const statusAlert = form.querySelector('.save-status');
            const statusText = statusAlert.querySelector('.save-status-text');
            const indicator = statusAlert.querySelector('.save-indicator');
            
            statusAlert.classList.remove('d-none', 'alert-success', 'alert-warning', 'alert-danger');
            indicator.classList.remove('saving', 'saved', 'save-error');
            
            switch(status) {
                case 'saving':
                    statusAlert.classList.add('alert-warning');
                    indicator.classList.add('saving');
                    statusAlert.classList.remove('d-none');
                    break;
                case 'saved':
                    statusAlert.classList.add('alert-success');
                    indicator.classList.add('saved');
                    statusAlert.classList.remove('d-none');
                    // Hide after 3 seconds
                    setTimeout(() => statusAlert.classList.add('d-none'), 3000);
                    break;
                case 'error':
                    statusAlert.classList.add('alert-danger');
                    indicator.classList.add('save-error');
                    statusAlert.classList.remove('d-none');
                    break;
                default:
                    statusAlert.classList.add('d-none');
            }
            
            if (message) {
                statusText.textContent = message;
            }
        }
        
        // Function to save answers with verification
        function saveAnswer(form, callback = null) {
            const questionId = form.getAttribute('data-question-id');
            const formData = new FormData(form);
            const answer = formData.get('answer');
            const index = parseInt(form.closest('.question-card').getAttribute('data-question-index'));
            
            // Skip if no answer or if already saving
            if (!answer || savedStatus.get(questionId) === 'saving') {
                if (callback) callback(false);
                return;
            }
            
            // Skip if answer hasn't changed
            if (answersCache.get(questionId) === answer && savedStatus.get(questionId) === 'saved') {
                if (callback) callback(true);
                return;
            }
            
            // Update cache
            answersCache.set(questionId, answer);
            savedStatus.set(questionId, 'saving');
            
            // Update UI - show saving
            updateSaveStatus(form, 'saving', 'جاري حفظ إجابتك...');
            
            // Update navigation button status immediately for better UX
            updateQuestionNavButton(index, true);
            
            // Add question ID to form data explicitly
            formData.set('question_id', questionId);
            formData.set('exam_id', '{{ $exam->id }}');
            
            console.log(`Saving answer for question ${questionId}: ${answer}`);
            
            // Send the answer via AJAX with retry logic
            let retryCount = 0;
            const maxRetries = 3;
            
            function attemptSave() {
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
                        savedStatus.set(questionId, 'saved');
                        updateSaveStatus(form, 'saved', 'تم حفظ إجابتك بنجاح');
                        console.log(`✓ Answer saved for question ${questionId}`);
                        
                        // Verify the answer was actually saved
                        verifyAnswer(questionId, index);
                        
                        if (callback) callback(true);
                    } else {
                        console.error(`Error saving answer: ${data.error || 'Unknown error'}`);
                        if (retryCount < maxRetries) {
                            retryCount++;
                            console.log(`Retry attempt ${retryCount} for question ${questionId}`);
                            setTimeout(attemptSave, 1000);
                        } else {
                            savedStatus.set(questionId, 'error');
                            updateSaveStatus(form, 'error', 'فشل في حفظ إجابتك. سيتم إعادة المحاولة تلقائيًا.');
                            
                            // Auto retry after 3 seconds
                            setTimeout(() => attemptSave(), 3000);
                            
                            if (callback) callback(false);
                        }
                    }
                })
                .catch(error => {
                    console.error(`Network error saving answer: ${error}`);
                    if (retryCount < maxRetries) {
                        retryCount++;
                        console.log(`Retry attempt ${retryCount} for question ${questionId}`);
                        setTimeout(attemptSave, 1000);
                    } else {
                        savedStatus.set(questionId, 'error');
                        updateSaveStatus(form, 'error', 'فشل في حفظ إجابتك. سيتم إعادة المحاولة تلقائيًا.');
                        
                        // Auto retry after 3 seconds
                        setTimeout(() => attemptSave(), 3000);
                        
                        if (callback) callback(false);
                    }
                });
            }
            
            attemptSave();
            
            // Update UI elements
            updateUnansweredList();
        }
        
        // Verify if an answer is saved in the database
        function verifyAnswer(questionId, index) {
            fetch(`{{ route('student.exams.check-answer') }}?exam_id={{ $exam->id }}&question_id=${questionId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`✓ Verified: Answer for question ${questionId} is saved in the database`);
                    savedStatus.set(questionId, 'saved');
                    
                    // Update UI to show it's saved
                    updateQuestionNavButton(index, true);
                    
                    // Update answer count in summary
                    updateUnansweredList();
                } else {
                    console.error(`✗ Error: Answer for question ${questionId} not found in database`);
                    savedStatus.set(questionId, 'error');
                    
                    // Retry saving if verification failed
                    const form = document.querySelector(`.answer-form[data-question-id="${questionId}"]`);
                    if (form) {
                        console.log(`Retrying save for question ${questionId}...`);
                        saveAnswer(form);
                    }
                }
            })
            .catch(error => {
                console.error(`Error verifying answer: ${error}`);
                // Don't change status on verification error
            });
        }
        
        // Update question navigation button appearance
        function updateQuestionNavButton(index, isAnswered) {
            document.querySelectorAll(`.question-nav-btn[data-target-index="${index}"]`).forEach(btn => {
                if (isAnswered) {
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-success');
                } else {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }
            });
        }
        
        // Auto-save on radio button change
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const form = this.closest('form');
                saveAnswer(form);
            });
        });
        
        // Auto-save on textarea change (debounced)
        let textareaTimeout;
        document.querySelectorAll('textarea[name="answer"]').forEach(textarea => {
            textarea.addEventListener('input', function() {
                clearTimeout(textareaTimeout);
                const form = this.closest('form');
                
                if (textarea.value.trim().length > 0) {
                    // Show saving indicator after typing stops
                    updateSaveStatus(form, 'saving', 'سيتم حفظ إجابتك تلقائياً...');
                    
                    textareaTimeout = setTimeout(() => {
                        saveAnswer(form);
                    }, 1000); // 1 second delay
                }
            });
        });
        
        // Update unanswered questions list
        function updateUnansweredList() {
            // Get all forms and check which ones have answers
            const forms = document.querySelectorAll('.answer-form');
            const answeredQuestions = [];
            let totalAnswered = 0;
            
            forms.forEach((form, index) => {
                const questionId = form.getAttribute('data-question-id');
                const answer = answersCache.get(questionId);
                
                // Check if this form has an answer
                if (answer && answer.trim() !== '') {
                    answeredQuestions.push(index);
                    totalAnswered++;
                    
                    // Update the navigation button to show as answered
                    updateQuestionNavButton(index, true);
                }
            });
            
            // Update the counts in the modal
            document.getElementById('answered-count').textContent = totalAnswered;
            const percent = (totalAnswered / {{ count($questions) }}) * 100;
            document.getElementById('answered-progress').style.width = `${percent}%`;
            
            // Update the list of unanswered questions
            let unansweredHTML = '';
            let unansweredCount = 0;
            
            for (let i = 0; i < forms.length; i++) {
                if (!answeredQuestions.includes(i)) {
                    unansweredHTML += `<div class="badge bg-danger m-1">السؤال ${i + 1}</div>`;
                    unansweredCount++;
                }
            }
            
            if (unansweredCount === 0) {
                unansweredHTML = '<p class="text-success mt-2"><i class="fas fa-check-circle me-1"></i> لقد أجبت على جميع الأسئلة</p>';
            }
            
            document.getElementById('unanswered-questions-list').innerHTML = unansweredHTML;
            
            return {
                totalAnswered,
                unansweredCount
            };
        }
        
        // Submit all answers before final submission
        async function submitAllAnswers() {
            if (isSubmitting) return;
            
            isSubmitting = true;
            const submissionLoader = document.getElementById('submission-loader');
            const submissionStatus = document.getElementById('submission-status');
            submissionLoader.classList.remove('d-none');
            
            // Disable buttons during submission
            document.getElementById('finalSubmitBtn').disabled = true;
            document.querySelector('button[data-bs-dismiss="modal"]').disabled = true;
            
            console.log('Starting final submission process...');
            submissionStatus.textContent = 'التحقق من حفظ جميع الإجابات...';
            
            // Get all forms with answers
            const forms = document.querySelectorAll('.answer-form');
            const pendingSaves = [];
            
            forms.forEach(form => {
                const questionId = form.getAttribute('data-question-id');
                const formData = new FormData(form);
                const answer = formData.get('answer');
                
                if (answer && answer.trim() !== '') {
                    const status = savedStatus.get(questionId);
                    
                    // Add to pending saves if not saved or in error state
                    if (status !== 'saved') {
                        pendingSaves.push({ form, questionId });
                    }
                }
            });
            
            // Save all pending answers
            if (pendingSaves.length > 0) {
                submissionStatus.textContent = `حفظ ${pendingSaves.length} إجابات متبقية...`;
                console.log(`Saving ${pendingSaves.length} pending answers...`);
                
                for (let i = 0; i < pendingSaves.length; i++) {
                    const { form, questionId } = pendingSaves[i];
                    submissionStatus.textContent = `حفظ الإجابة ${i+1} من ${pendingSaves.length}...`;
                    
                    await new Promise(resolve => {
                        saveAnswer(form, (success) => {
                            console.log(`Answer ${i+1}/${pendingSaves.length} for question ${questionId}: ${success ? 'saved' : 'failed'}`);
                            setTimeout(resolve, 500); // Short delay between saves
                        });
                    });
                }
            }
            
            // Verify all answers are saved
            let allSaved = true;
            
            forms.forEach(form => {
                const questionId = form.getAttribute('data-question-id');
                const formData = new FormData(form);
                const answer = formData.get('answer');
                
                if (answer && answer.trim() !== '') {
                    const status = savedStatus.get(questionId);
                    if (status !== 'saved') {
                        allSaved = false;
                        console.error(`Answer for question ${questionId} is not confirmed saved!`);
                    }
                }
            });
            
            if (!allSaved) {
                submissionStatus.textContent = 'تحذير: بعض الإجابات قد لا تكون محفوظة. جاري المحاولة مرة أخرى...';
                console.warn('Some answers may not be saved correctly. Final verification...');
                
                // One more check by verifying with the server
                const verification = [];
                
                for (const form of forms) {
                    const questionId = form.getAttribute('data-question-id');
                    const formData = new FormData(form);
                    const answer = formData.get('answer');
                    
                    if (answer && answer.trim() !== '') {
                        verification.push(
                            fetch(`{{ route('student.exams.check-answer') }}?exam_id={{ $exam->id }}&question_id=${questionId}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => ({ questionId, saved: data.success }))
                            .catch(() => ({ questionId, saved: false }))
                        );
                    }
                }
                
                const results = await Promise.all(verification);
                const notSaved = results.filter(r => !r.saved).map(r => r.questionId);
                
                if (notSaved.length > 0) {
                    submissionStatus.textContent = `إعادة حفظ ${notSaved.length} إجابات...`;
                    console.warn(`${notSaved.length} answers need to be saved again:`, notSaved);
                    
                    // Try one more time to save these answers
                    for (const qId of notSaved) {
                        const form = document.querySelector(`.answer-form[data-question-id="${qId}"]`);
                        if (form) {
                            await new Promise(resolve => {
                                saveAnswer(form, () => setTimeout(resolve, 800));
                            });
                        }
                    }
                }
            }
            
            // Final submission
            submissionStatus.textContent = 'جاري تسليم الاختبار...';
            console.log('All answers confirmed. Submitting exam...');
            
            // Add a short delay to ensure everything is processed
            setTimeout(() => {
                document.getElementById('submitExamForm').submit();
            }, 1000);
        }
        
        // Submit button handling
        document.getElementById('finalSubmitBtn').addEventListener('click', submitAllAnswers);
        
        // Initialize the page
        updateUnansweredList();
        
        // Pre-verify answers that are already loaded
        document.querySelectorAll('.answer-form').forEach(form => {
            const questionId = form.getAttribute('data-question-id');
            const index = parseInt(form.closest('.question-card').getAttribute('data-question-index'));
            
            // Check if we have form elements with values
            const formData = new FormData(form);
            const answer = formData.get('answer');
            
            if (answer && answer.trim() !== '') {
                // Add to cache
                answersCache.set(questionId, answer);
                
                // Verify it's in the database
                verifyAnswer(questionId, index);
            }
        });
        
        // When the modal is shown, update the unanswered list
        const submitExamModal = document.getElementById('submitExamModal');
        submitExamModal.addEventListener('show.bs.modal', function() {
            // Update the lists of answered and unanswered questions
            updateUnansweredList();
        });
    });
</script>
@endsection 