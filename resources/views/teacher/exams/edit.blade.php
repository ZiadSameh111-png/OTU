@extends('layouts.app')

@section('content')
<script>
// دوال مساعدة لإضافة السؤال
function loadQuestionForm(type) {
    console.log('تم استدعاء دالة loadQuestionForm مع النوع:', type);
    
    const questionFormContent = document.getElementById('questionFormContent');
    const saveQuestionBtn = document.getElementById('saveQuestionBtn');
    
    if (!questionFormContent || !saveQuestionBtn) {
        console.error('لم يتم العثور على عناصر واجهة المستخدم');
        return;
    }
    
    if (type === 'multiple_choice') {
        // نموذج سؤال الاختيار المتعدد
        questionFormContent.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label for="question_text" class="form-label">نص السؤال <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">خيارات الإجابة <span class="text-danger">*</span></label>
                    <div class="mb-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="a" required>
                            </div>
                            <input type="text" class="form-control" name="options[a]" placeholder="الخيار أ" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="b">
                            </div>
                            <input type="text" class="form-control" name="options[b]" placeholder="الخيار ب" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="c">
                            </div>
                            <input type="text" class="form-control" name="options[c]" placeholder="الخيار ج" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="d">
                            </div>
                            <input type="text" class="form-control" name="options[d]" placeholder="الخيار د" required>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="marks" class="form-label">الدرجة <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="marks" name="marks" min="1" value="2" required>
                </div>
            </div>
        `;
        saveQuestionBtn.disabled = false;
    } else if (type === 'true_false') {
        // نموذج سؤال صح/خطأ
        questionFormContent.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label for="question_text" class="form-label">نص السؤال <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">الإجابة الصحيحة <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="correct_answer" id="true" value="true" required>
                        <label class="form-check-label" for="true">صح</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="correct_answer" id="false" value="false">
                        <label class="form-check-label" for="false">خطأ</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="marks" class="form-label">الدرجة <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="marks" name="marks" min="1" value="1" required>
                </div>
            </div>
        `;
        saveQuestionBtn.disabled = false;
    } else if (type === 'open_ended') {
        // نموذج سؤال مفتوح
        questionFormContent.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-12 mb-3">
                    <label for="question_text" class="form-label">نص السؤال <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                </div>
                
                <div class="col-md-6">
                    <label for="marks" class="form-label">الدرجة <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="marks" name="marks" min="1" value="5" required>
                </div>
            </div>
        `;
        saveQuestionBtn.disabled = false;
    } else {
        questionFormContent.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> يرجى اختيار نوع السؤال أولاً.
            </div>
        `;
        saveQuestionBtn.disabled = true;
    }
}

function resetForm() {
    console.log('إعادة تعيين النموذج');
    document.getElementById('addQuestionForm').reset();
    document.getElementById('question_type').value = '';
    
    const questionFormContent = document.getElementById('questionFormContent');
    const saveQuestionBtn = document.getElementById('saveQuestionBtn');
    
    if (questionFormContent) {
        questionFormContent.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> يرجى اختيار نوع السؤال أولاً.
            </div>
        `;
    }
    
    if (saveQuestionBtn) {
        saveQuestionBtn.disabled = true;
    }
}

// تنفيذ عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    console.log('تم تحميل الصفحة بنجاح');
    // إضافة مستمع أحداث لاختيار نوع السؤال
    const questionTypeSelect = document.getElementById('question_type');
    if (questionTypeSelect) {
        console.log('تم العثور على عنصر اختيار نوع السؤال');
        questionTypeSelect.addEventListener('change', function() {
            loadQuestionForm(this.value);
        });
    }
});
</script>

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
                                <button type="submit" class="btn btn-success btn-sm" {{ $exam->questions->count() == 0 ? 'disabled' : '' }}>
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
                                <br>
                                <span class="text-dark fw-bold mt-1">{{ $exam->description ?? '' }}</span>
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
            
            <!-- Add Question Card -->
            @if (!$exam->is_published)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 fw-bold">إضافة سؤال جديد</h5>
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
                    
                    <form id="addQuestionForm" action="{{ route('teacher.exams.questions.add', $exam->id) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label for="question_type" class="form-label">نوع السؤال <span class="text-danger">*</span></label>
                                <select class="form-select" id="question_type" name="question_type" required onchange="loadQuestionForm(this.value)">
                                    <option value="">اختر نوع السؤال...</option>
                                    <option value="multiple_choice">اختيار متعدد</option>
                                    <option value="true_false">صح وخطأ</option>
                                    <option value="open_ended">سؤال مفتوح</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="questionFormContent">
                            <!-- Question form content will be loaded dynamically based on selection -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> يرجى اختيار نوع السؤال أولاً.
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="resetQuestionForm" onclick="resetForm()">
                                <i class="fas fa-redo me-1"></i> إعادة تعيين
                            </button>
                            <button type="submit" class="btn btn-success" id="saveQuestionBtn" disabled>
                                <i class="fas fa-plus-circle me-1"></i> إضافة السؤال
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            
            <!-- Questions Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        الأسئلة ({{ $exam->questions->count() }})
                        @if ($exam->total_marks > 0)
                        <span class="badge bg-info ms-2">إجمالي الدرجات: {{ $exam->total_marks }}</span>
                        @endif
                    </h5>
                    @if (!$exam->is_published && $exam->questions->count() > 0)
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="questionsActions" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i> إجراءات
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="questionsActions">
                            <li>
                                <button class="dropdown-item text-primary" id="reorderQuestionsBtn">
                                    <i class="fas fa-sort me-1"></i> إعادة ترتيب الأسئلة
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger" id="clearQuestionsBtn" 
                                    onclick="return confirm('هل أنت متأكد من حذف جميع الأسئلة؟ هذا الإجراء لا يمكن التراجع عنه.')">
                                    <i class="fas fa-trash-alt me-1"></i> حذف جميع الأسئلة
                                </button>
                            </li>
                        </ul>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if ($exam->questions->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i> لا توجد أسئلة مضافة حتى الآن. 
                            @if (!$exam->is_published)
                            استخدم النموذج أعلاه لإضافة أسئلة جديدة.
                            @endif
                        </div>
                    @else
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
                                                            @foreach ($question->options as $key => $option)
                                                                <li class="list-group-item {{ $key == $question->correct_answer ? 'list-group-item-success' : '' }}">
                                                                    {{ $option }}
                                                                    @if ($key == $question->correct_answer)
                                                                        <span class="badge bg-success float-end">الإجابة الصحيحة</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @elseif ($question->question_type == 'true_false')
                                                    <div class="col-md-12 mb-3">
                                                        <h6 class="fw-bold">الإجابة الصحيحة:</h6>
                                                        <p>
                                                            @if ($question->correct_answer == 'true')
                                                                <span class="badge bg-success">صح</span>
                                                            @else
                                                                <span class="badge bg-danger">خطأ</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                @endif
                                                
                                                <div class="col-md-12 mb-3">
                                                    <h6 class="fw-bold">الدرجة:</h6>
                                                    <p>{{ $question->marks }} درجة</p>
                                                </div>
                                                
                                                @if (!$exam->is_published)
                                                    <div class="col-md-12">
                                                        <div class="d-flex mt-2">
                                                            <button type="button" class="btn btn-sm btn-info edit-question me-2" data-question-id="{{ $question->id }}">
                                                                <i class="fas fa-edit"></i> تعديل
                                                            </button>
                                                            <form action="{{ route('teacher.exams.remove-question', $question->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i> حذف
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Question Type Templates (Hidden) -->
            <div id="multipleChoiceTemplate" style="display: none;">
                <div class="row mb-3">
                    <div class="col-md-12 mb-3">
                        <label for="question_text" class="form-label">نص السؤال <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">خيارات الإجابة <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_answer" value="a" required>
                                </div>
                                <input type="text" class="form-control" name="options[a]" placeholder="الخيار أ" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_answer" value="b">
                                </div>
                                <input type="text" class="form-control" name="options[b]" placeholder="الخيار ب" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_answer" value="c">
                                </div>
                                <input type="text" class="form-control" name="options[c]" placeholder="الخيار ج" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_answer" value="d">
                                </div>
                                <input type="text" class="form-control" name="options[d]" placeholder="الخيار د" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="marks" class="form-label">الدرجة <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="marks" name="marks" min="1" value="2" required>
                    </div>
                </div>
            </div>
            
            <div id="trueFalseTemplate" style="display: none;">
                <div class="row mb-3">
                    <div class="col-md-12 mb-3">
                        <label for="question_text" class="form-label">نص السؤال <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">الإجابة الصحيحة <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer" id="true" value="true" required>
                            <label class="form-check-label" for="true">صح</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer" id="false" value="false">
                            <label class="form-check-label" for="false">خطأ</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="marks" class="form-label">الدرجة <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="marks" name="marks" min="1" value="1" required>
                    </div>
                </div>
            </div>
            
            <div id="openEndedTemplate" style="display: none;">
                <div class="row mb-3">
                    <div class="col-md-12 mb-3">
                        <label for="question_text" class="form-label">نص السؤال <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="marks" class="form-label">الدرجة <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="marks" name="marks" min="1" value="5" required>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">تعليمات</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">أضف أسئلة متنوعة لقياس مستويات الطلاب المختلفة.</li>
                        <li class="mb-2">قم بتوزيع الدرجات بشكل عادل حسب صعوبة السؤال.</li>
                        <li class="mb-2">تأكد من وضوح صياغة الأسئلة وعدم وجود أخطاء بها.</li>
                        <li class="mb-2">تأكد من تحديد الإجابات الصحيحة للأسئلة الموضوعية.</li>
                        <li class="mb-2">يجب نشر الاختبار لكي يتمكن الطلاب من رؤيته وحله.</li>
                        <li>لا يمكن تعديل الأسئلة بعد نشر الاختبار وبدء الطلاب في حله.</li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0 fw-bold">إحصائيات الاختبار</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            عدد الأسئلة
                            <span class="badge bg-info rounded-pill">{{ $exam->questions->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            إجمالي الدرجات
                            <span class="badge bg-primary rounded-pill">{{ $exam->total_marks }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            أسئلة اختيار متعدد
                            <span class="badge bg-success rounded-pill">{{ $exam->questions->where('question_type', 'multiple_choice')->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            أسئلة صح وخطأ
                            <span class="badge bg-warning rounded-pill">{{ $exam->questions->where('question_type', 'true_false')->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            أسئلة مقالية
                            <span class="badge bg-danger rounded-pill">{{ $exam->questions->where('question_type', 'open_ended')->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Question Modal -->
    <div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editQuestionModalLabel">تعديل السؤال</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editQuestionModalBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                        <p class="mt-2">جاري تحميل بيانات السؤال...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const questionType = document.getElementById('question_type');
        const questionFormContent = document.getElementById('questionFormContent');
        const saveQuestionBtn = document.getElementById('saveQuestionBtn');
        const resetQuestionForm = document.getElementById('resetQuestionForm');
        
        const multipleChoiceTemplate = document.getElementById('multipleChoiceTemplate').innerHTML;
        const trueFalseTemplate = document.getElementById('trueFalseTemplate').innerHTML;
        const openEndedTemplate = document.getElementById('openEndedTemplate').innerHTML;
        
        // تصحيح مشكلة زر إضافة السؤال
        console.log('تهيئة نموذج إضافة السؤال...');
        
        // Load question form based on selected type
        questionType.addEventListener('change', function() {
            const selectedType = this.value;
            console.log('تم اختيار نوع السؤال:', selectedType);
            
            if (selectedType === 'multiple_choice') {
                questionFormContent.innerHTML = multipleChoiceTemplate;
                saveQuestionBtn.disabled = false;
                console.log('تم تفعيل زر الإضافة - اختيار متعدد');
            } else if (selectedType === 'true_false') {
                questionFormContent.innerHTML = trueFalseTemplate;
                saveQuestionBtn.disabled = false;
                console.log('تم تفعيل زر الإضافة - صح وخطأ');
            } else if (selectedType === 'open_ended') {
                questionFormContent.innerHTML = openEndedTemplate;
                saveQuestionBtn.disabled = false;
                console.log('تم تفعيل زر الإضافة - سؤال مفتوح');
            } else {
                questionFormContent.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i> يرجى اختيار نوع السؤال أولاً.</div>';
                saveQuestionBtn.disabled = true;
                console.log('تم تعطيل زر الإضافة - لم يتم اختيار نوع');
            }
            
            // تأكيد إلغاء تعطيل الزر
            if (selectedType && ['multiple_choice', 'true_false', 'open_ended'].includes(selectedType)) {
                setTimeout(() => {
                    saveQuestionBtn.removeAttribute('disabled');
                    console.log('تم التأكد من تفعيل الزر');
                }, 100);
            }
        });
        
        // Reset question form
        resetQuestionForm.addEventListener('click', function() {
            document.getElementById('addQuestionForm').reset();
            questionType.value = '';
            questionFormContent.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i> يرجى اختيار نوع السؤال أولاً.</div>';
            saveQuestionBtn.disabled = true;
        });
        
        // Edit question functionality
        const editButtons = document.querySelectorAll('.edit-question');
        const editQuestionModal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
        const editQuestionModalBody = document.getElementById('editQuestionModalBody');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const questionId = this.getAttribute('data-question-id');
                
                // Show loading state
                editQuestionModal.show();
                
                // Fetch question data for editing
                fetch(`/teacher/exams/questions/${questionId}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Populate the modal with the question data
                            editQuestionModalBody.innerHTML = createEditForm(data.question);
                        } else {
                            editQuestionModalBody.innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i> ${data.message || 'حدث خطأ أثناء تحميل بيانات السؤال'}
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching question:', error);
                        editQuestionModalBody.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i> حدث خطأ أثناء الاتصال بالخادم
                            </div>
                        `;
                    });
            });
        });
        
        function createEditForm(question) {
            let formHtml = `
                <form action="/teacher/exams/questions/${question.id}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <label for="edit_question_text" class="form-label">نص السؤال <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_question_text" name="question_text" rows="3" required>${question.question_text}</textarea>
                        </div>
            `;
            
            if (question.question_type === 'multiple_choice') {
                formHtml += `
                    <div class="col-md-12 mb-3">
                        <label class="form-label">خيارات الإجابة <span class="text-danger">*</span></label>
                `;
                
                const options = question.options || {};
                const letters = ['a', 'b', 'c', 'd'];
                
                letters.forEach(letter => {
                    formHtml += `
                        <div class="mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_answer" value="${letter}" ${question.correct_answer === letter ? 'checked' : ''} required>
                                </div>
                                <input type="text" class="form-control" name="options[${letter}]" placeholder="الخيار ${letter}" value="${options[letter] || ''}" required>
                            </div>
                        </div>
                    `;
                });
                
                formHtml += `</div>`;
            } else if (question.question_type === 'true_false') {
                formHtml += `
                    <div class="col-md-12 mb-3">
                        <label class="form-label">الإجابة الصحيحة <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer" id="edit_true" value="true" ${question.correct_answer === 'true' ? 'checked' : ''} required>
                            <label class="form-check-label" for="edit_true">صح</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer" id="edit_false" value="false" ${question.correct_answer === 'false' ? 'checked' : ''}>
                            <label class="form-check-label" for="edit_false">خطأ</label>
                        </div>
                    </div>
                `;
            }
            
            formHtml += `
                    <div class="col-md-6">
                        <label for="edit_marks" class="form-label">الدرجة <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_marks" name="marks" min="1" value="${question.marks}" required>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
            `;
            
            return formHtml;
        }
    });
</script>
@endsection 