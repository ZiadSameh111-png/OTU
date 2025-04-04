@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4 fw-bold text-primary">
                <i class="fas fa-edit me-2"></i>تعديل الاختبار
            </h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Exam Information Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">معلومات الاختبار</h5>
                    @if (!$exam->is_published)
                        <div>
                            <form action="{{ route('teacher.exams.publish', $exam->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-globe me-1"></i> نشر الاختبار
                                </button>
                            </form>
                        </div>
                    @else
                        <div>
                            <form action="{{ route('teacher.exams.unpublish', $exam->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-eye-slash me-1"></i> إلغاء النشر
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">عنوان الاختبار:</h6>
                            <p>{{ $exam->title }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">المقرر الدراسي:</h6>
                            <p>{{ $exam->course->name ?? 'غير محدد' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">المجموعة:</h6>
                            <p>{{ $exam->group->name ?? 'غير محدد' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">نوع الأسئلة:</h6>
                            <p>
                                @switch($exam->question_type)
                                    @case('multiple_choice')
                                        اختيار من متعدد
                                        @break
                                    @case('true_false')
                                        صح وخطأ
                                        @break
                                    @case('open_ended')
                                        أسئلة مقالية
                                        @break
                                    @case('mixed')
                                        مختلط
                                        @break
                                    @default
                                        غير محدد
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">تاريخ البداية:</h6>
                            <p>{{ $exam->start_time ? $exam->start_time->format('Y-m-d H:i') : 'غير محدد' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">تاريخ النهاية:</h6>
                            <p>{{ $exam->end_time ? $exam->end_time->format('Y-m-d H:i') : 'غير محدد' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">مدة الاختبار:</h6>
                            <p>{{ $exam->duration }} دقيقة</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">الحالة:</h6>
                            <p>
                                @if ($exam->is_published)
                                    <span class="badge bg-success">منشور</span>
                                @else
                                    <span class="badge bg-secondary">غير منشور</span>
                                @endif
                                
                                @switch($exam->status)
                                    @case('pending')
                                        <span class="badge bg-warning">قيد الإعداد</span>
                                        @break
                                    @case('active')
                                        <span class="badge bg-primary">نشط</span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-info">مكتمل</span>
                                        @break
                                    @case('expired')
                                        <span class="badge bg-danger">منتهي</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">غير محدد</span>
                                @endswitch
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('teacher.exams.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                        </a>
                        
                        @if (!$exam->is_published)
                            <form action="{{ route('teacher.exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الاختبار؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i> حذف الاختبار
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Questions Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">الأسئلة ({{ $exam->questions->count() }})</h5>
                </div>
                <div class="card-body">
                    @if ($exam->questions->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i> لا توجد أسئلة مضافة حتى الآن. استخدم النموذج أدناه لإضافة أسئلة جديدة.
                        </div>
                    @else
                        <div class="mb-4">
                            <p class="mb-1">إجمالي الدرجات: <strong>{{ $exam->total_marks }}</strong></p>
                        </div>
                        
                        <div class="accordion" id="questionsAccordion">
                            @foreach ($exam->questions as $index => $question)
                                <div class="accordion-item mb-3 border">
                                    <h2 class="accordion-header" id="heading{{ $question->id }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $question->id }}" aria-expanded="false" aria-controls="collapse{{ $question->id }}">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <div>
                                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                    {{ Str::limit($question->question_text, 100) }}
                                                </div>
                                                <div>
                                                    <span class="badge bg-info">{{ $question->marks }} درجة</span>
                                                    <span class="badge bg-secondary ms-1">
                                                        @switch($question->question_type)
                                                            @case('multiple_choice')
                                                                اختيار متعدد
                                                                @break
                                                            @case('true_false')
                                                                صح/خطأ
                                                                @break
                                                            @case('open_ended')
                                                                مقالي
                                                                @break
                                                            @default
                                                                غير محدد
                                                        @endswitch
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $question->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $question->id }}" data-bs-parent="#questionsAccordion">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <h6 class="fw-bold">نص السؤال:</h6>
                                                    <p>{{ $question->question_text }}</p>
                                                </div>
                                                
                                                @if ($question->question_type == 'multiple_choice' && is_array($question->options))
                                                    <div class="col-md-12 mb-3">
                                                        <h6 class="fw-bold">خيارات الإجابة:</h6>
                                                        <ul class="list-group">
                                                            @foreach ($question->options as $option)
                                                                <li class="list-group-item {{ $option == $question->correct_answer ? 'list-group-item-success' : '' }}">
                                                                    {{ $option }}
                                                                    @if ($option == $question->correct_answer)
                                                                        <span class="badge bg-success float-end">الإجابة الصحيحة</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @elseif ($question->question_type == 'true_false')
                                                    <div class="col-md-12 mb-3">
                                                        <h6 class="fw-bold">الإجابة الصحيحة:</h6>
                                                        <p>{{ $question->correct_answer == 'true' ? 'صح' : 'خطأ' }}</p>
                                                    </div>
                                                @endif
                                                
                                                <div class="col-md-12">
                                                    <div class="d-flex justify-content-end">
                                                        <form action="{{ route('teacher.exams.update-question', $question->id) }}" method="POST" class="me-2">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="button" class="btn btn-sm btn-primary edit-question-btn" data-question-id="{{ $question->id }}">
                                                                <i class="fas fa-edit"></i> تعديل
                                                            </button>
                                                        </form>
                                                        
                                                        <form action="{{ route('teacher.exams.remove-question', $question->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i> حذف
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Add Question Card -->
            @if (!$exam->is_published)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 text-dark fw-bold">إضافة سؤال جديد</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('teacher.exams.questions.add', $exam->id) }}" method="POST" id="addQuestionForm">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="question_text" class="form-label">نص السؤال <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="question_text" name="question_text" rows="3" required>{{ old('question_text') }}</textarea>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="question_type" class="form-label">نوع السؤال <span class="text-danger">*</span></label>
                                    <select class="form-select" id="question_type" name="question_type" required>
                                        @if ($exam->question_type == 'multiple_choice' || $exam->question_type == 'mixed')
                                            <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>اختيار من متعدد</option>
                                        @endif
                                        
                                        @if ($exam->question_type == 'true_false' || $exam->question_type == 'mixed')
                                            <option value="true_false" {{ old('question_type') == 'true_false' ? 'selected' : '' }}>صح وخطأ</option>
                                        @endif
                                        
                                        @if ($exam->question_type == 'open_ended' || $exam->question_type == 'mixed')
                                            <option value="open_ended" {{ old('question_type') == 'open_ended' ? 'selected' : '' }}>مقالي</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="marks" class="form-label">الدرجة <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="marks" name="marks" min="1" max="100" value="{{ old('marks', 1) }}" required>
                                </div>
                            </div>
                            
                            <!-- Multiple Choice Options -->
                            <div id="multiple_choice_options" class="mb-3 {{ old('question_type') == 'multiple_choice' ? '' : 'd-none' }}">
                                <label class="form-label">خيارات الإجابة <span class="text-danger">*</span></label>
                                
                                <div id="options_container">
                                    @if (old('options'))
                                        @foreach (old('options') as $key => $option)
                                            <div class="input-group mb-2">
                                                <div class="input-group-text">
                                                    <input class="form-check-input option-radio" type="radio" name="correct_answer" value="{{ $option }}" {{ old('correct_answer') == $option ? 'checked' : '' }} required>
                                                </div>
                                                <input type="text" class="form-control option-input" name="options[]" value="{{ $option }}" placeholder="أدخل الخيار" required>
                                                <button type="button" class="btn btn-outline-danger remove-option">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group mb-2">
                                            <div class="input-group-text">
                                                <input class="form-check-input option-radio" type="radio" name="correct_answer" value="" checked required>
                                            </div>
                                            <input type="text" class="form-control option-input" name="options[]" placeholder="أدخل الخيار" required>
                                            <button type="button" class="btn btn-outline-danger remove-option">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="input-group mb-2">
                                            <div class="input-group-text">
                                                <input class="form-check-input option-radio" type="radio" name="correct_answer" value="" required>
                                            </div>
                                            <input type="text" class="form-control option-input" name="options[]" placeholder="أدخل الخيار" required>
                                            <button type="button" class="btn btn-outline-danger remove-option">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add_option">
                                    <i class="fas fa-plus"></i> إضافة خيار
                                </button>
                            </div>
                            
                            <!-- True/False Options -->
                            <div id="true_false_options" class="mb-3 {{ old('question_type') == 'true_false' ? '' : 'd-none' }}">
                                <label class="form-label">الإجابة الصحيحة <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <div class="form-check me-4">
                                        <input class="form-check-input" type="radio" name="correct_answer" id="true" value="true" {{ old('correct_answer') == 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="true">صح</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="correct_answer" id="false" value="false" {{ old('correct_answer') == 'false' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="false">خطأ</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary" id="resetFormBtn">
                                    <i class="fas fa-redo me-1"></i> إعادة تعيين
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> إضافة السؤال
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">إحصائيات الاختبار</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            عدد الأسئلة
                            <span class="badge bg-primary rounded-pill">{{ $exam->questions->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            مجموع الدرجات
                            <span class="badge bg-success rounded-pill">{{ $exam->total_marks }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            أسئلة اختيار متعدد
                            <span class="badge bg-info rounded-pill">{{ $exam->questions->where('question_type', 'multiple_choice')->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            أسئلة صح وخطأ
                            <span class="badge bg-warning rounded-pill">{{ $exam->questions->where('question_type', 'true_false')->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            أسئلة مقالية
                            <span class="badge bg-secondary rounded-pill">{{ $exam->questions->where('question_type', 'open_ended')->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">الإجراءات</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i> قائمة الاختبارات
                        </a>
                        
                        @if ($exam->is_published)
                            <button class="btn btn-outline-primary" disabled>
                                <i class="fas fa-eye me-1"></i> معاينة الاختبار
                            </button>
                            
                            <a href="{{ route('teacher.exams.grading') }}?exam_id={{ $exam->id }}" class="btn btn-outline-success">
                                <i class="fas fa-pen me-1"></i> تصحيح الإجابات
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">تعليمات</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">قم بإضافة الأسئلة باستخدام النموذج أدناه.</li>
                        <li class="mb-2">يمكنك تعديل أو حذف الأسئلة قبل نشر الاختبار.</li>
                        <li class="mb-2">تأكد من تخصيص الدرجة المناسبة لكل سؤال.</li>
                        <li class="mb-2">يمكنك نشر الاختبار عندما تكون جاهزاً.</li>
                        <li>بعد النشر، لن تتمكن من تعديل أو حذف الأسئلة.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle question type change
        const questionTypeSelect = document.getElementById('question_type');
        const multipleChoiceOptions = document.getElementById('multiple_choice_options');
        const trueFalseOptions = document.getElementById('true_false_options');
        
        if (questionTypeSelect) {
            questionTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                
                // Hide all option containers
                multipleChoiceOptions.classList.add('d-none');
                trueFalseOptions.classList.add('d-none');
                
                // Show appropriate container based on selected type
                if (selectedType === 'multiple_choice') {
                    multipleChoiceOptions.classList.remove('d-none');
                } else if (selectedType === 'true_false') {
                    trueFalseOptions.classList.remove('d-none');
                }
            });
        }
        
        // Handle adding options for multiple choice questions
        const addOptionBtn = document.getElementById('add_option');
        const optionsContainer = document.getElementById('options_container');
        
        if (addOptionBtn && optionsContainer) {
            addOptionBtn.addEventListener('click', function() {
                // Check if we have less than 4 options
                const optionInputs = optionsContainer.querySelectorAll('.option-input');
                if (optionInputs.length < 4) {
                    const newOption = document.createElement('div');
                    newOption.className = 'input-group mb-2';
                    newOption.innerHTML = `
                        <div class="input-group-text">
                            <input class="form-check-input option-radio" type="radio" name="correct_answer" value="" required>
                        </div>
                        <input type="text" class="form-control option-input" name="options[]" placeholder="أدخل الخيار" required>
                        <button type="button" class="btn btn-outline-danger remove-option">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    optionsContainer.appendChild(newOption);
                    
                    // Update event listeners for the new elements
                    updateOptionEventListeners();
                } else {
                    alert('يمكنك إضافة حد أقصى 4 خيارات للسؤال');
                }
            });
            
            // Initial setup for existing options
            updateOptionEventListeners();
        }
        
        // Reset form button
        const resetFormBtn = document.getElementById('resetFormBtn');
        const addQuestionForm = document.getElementById('addQuestionForm');
        
        if (resetFormBtn && addQuestionForm) {
            resetFormBtn.addEventListener('click', function() {
                addQuestionForm.reset();
                
                // Reset option inputs
                const optionInputs = optionsContainer.querySelectorAll('.option-input');
                optionInputs.forEach((input, index) => {
                    input.value = '';
                    
                    // Check the first radio button
                    const radio = input.closest('.input-group').querySelector('.option-radio');
                    radio.checked = index === 0;
                    radio.value = '';
                });
                
                // Reset question type display
                const event = new Event('change');
                questionTypeSelect.dispatchEvent(event);
            });
        }
        
        // Function to update event listeners for option inputs and remove buttons
        function updateOptionEventListeners() {
            // Update radio values when option text changes
            const optionInputs = document.querySelectorAll('.option-input');
            optionInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const radio = this.closest('.input-group').querySelector('.option-radio');
                    radio.value = this.value;
                });
            });
            
            // Handle remove option buttons
            const removeOptionBtns = document.querySelectorAll('.remove-option');
            removeOptionBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const optionCount = optionsContainer.querySelectorAll('.input-group').length;
                    if (optionCount > 2) {
                        this.closest('.input-group').remove();
                    } else {
                        alert('يجب أن يكون هناك خياران على الأقل');
                    }
                });
            });
        }
    });
</script>
@endsection 