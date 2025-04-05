@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4 fw-bold text-primary">
                <i class="fas fa-plus-circle me-2"></i>إنشاء اختبار جديد
            </h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">معلومات الاختبار</h5>
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

                    <form action="{{ route('teacher.exams.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="title" class="form-label">عنوان الاختبار <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="course_id" class="form-label">المقرر الدراسي <span class="text-danger">*</span></label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <option value="">اختر المقرر...</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="group_id" class="form-label">المجموعة <span class="text-danger">*</span></label>
                                <select class="form-select" id="group_id" name="group_id" required>
                                    <option value="">اختر المجموعة...</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="duration" class="form-label">مدة الاختبار (بالدقائق) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="duration" name="duration" min="1" max="240" value="{{ old('duration', 60) }}" required>
                                <div class="form-text">مدة الاختبار بالدقائق (من 1 إلى 240 دقيقة)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="question_type" class="form-label">نوع الأسئلة <span class="text-danger">*</span></label>
                                <select class="form-select" id="question_type" name="question_type" required>
                                    <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>اختيار من متعدد</option>
                                    <option value="true_false" {{ old('question_type') == 'true_false' ? 'selected' : '' }}>صح وخطأ</option>
                                    <option value="open_ended" {{ old('question_type') == 'open_ended' ? 'selected' : '' }}>أسئلة مقالية</option>
                                    <option value="mixed" {{ old('question_type') == 'mixed' ? 'selected' : '' }}>مختلط</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('teacher.exams.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> إنشاء الاختبار
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">تعليمات</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">قم بإدخال عنوان وصفي للاختبار.</li>
                        <li class="mb-2">حدد المقرر الدراسي والمجموعة المستهدفة.</li>
                        <li class="mb-2">حدد المدة الزمنية للاختبار بالدقائق.</li>
                        <li class="mb-2">اختر نوع الأسئلة التي ستستخدمها في الاختبار.</li>
                        <li>بعد إنشاء الاختبار، ستتمكن من إضافة الأسئلة وفتح الاختبار للطلاب.</li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark fw-bold">أنواع الأسئلة</h5>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>اختيار من متعدد</dt>
                        <dd class="mb-3">أسئلة مع خيارات متعددة وإجابة صحيحة واحدة.</dd>
                        
                        <dt>صح وخطأ</dt>
                        <dd class="mb-3">أسئلة بسيطة مع خياري صح أو خطأ فقط.</dd>
                        
                        <dt>أسئلة مقالية</dt>
                        <dd class="mb-3">أسئلة تتطلب إجابات نصية مفتوحة من الطلاب.</dd>
                        
                        <dt>مختلط</dt>
                        <dd>مزيج من الأنواع المختلفة للأسئلة في نفس الاختبار.</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter groups based on selected course
        const courseSelect = document.getElementById('course_id');
        const groupSelect = document.getElementById('group_id');
        const allGroups = @json($groups);
        
        courseSelect.addEventListener('change', function() {
            const courseId = this.value;
            
            // Clear the group select
            groupSelect.innerHTML = '<option value="">اختر المجموعة...</option>';
            
            if (courseId) {
                // Get groups for the selected course via AJAX
                fetch(`/api/courses/${courseId}/groups`)
                    .then(response => response.json())
                    .then(groups => {
                        groups.forEach(group => {
                            const option = document.createElement('option');
                            option.value = group.id;
                            option.textContent = group.name;
                            groupSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching groups:', error));
            }
        });
    });
</script>
@endsection 