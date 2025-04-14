@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">الرسائل الداخلية</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- نموذج إرسال رسالة جديدة -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-paper-plane me-2 text-primary"></i>إرسال رسالة جديدة
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.messages.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="recipient_type" class="form-label">نوع المستلم <span class="text-danger">*</span></label>
                            <select class="form-select @error('recipient_type') is-invalid @enderror" id="recipient_type" name="recipient_type" required>
                                <option value="" selected disabled>-- اختر نوع المستلم --</option>
                                <option value="student" {{ old('recipient_type') == 'student' ? 'selected' : '' }}>طالب</option>
                                <option value="teacher" {{ old('recipient_type') == 'teacher' ? 'selected' : '' }}>دكتور</option>
                                <option value="group" {{ old('recipient_type') == 'group' ? 'selected' : '' }}>مجموعة</option>
                                <option value="all_students" {{ old('recipient_type') == 'all_students' ? 'selected' : '' }}>جميع الطلاب</option>
                                <option value="all_teachers" {{ old('recipient_type') == 'all_teachers' ? 'selected' : '' }}>جميع الدكاترة</option>
                            </select>
                            @error('recipient_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- حقل اختيار الطالب (يظهر فقط عند اختيار "طالب") -->
                        <div class="mb-3 recipient-field" id="student-field" style="display: none;">
                            <label for="student_id" class="form-label">اختر الطالب <span class="text-danger">*</span></label>
                            <select class="form-select @error('recipient_id') is-invalid @enderror" id="student_id" name="student_id">
                                <option value="" selected disabled>-- اختر الطالب --</option>
                                @if(isset($students) && count($students) > 0)
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('recipient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- حقل اختيار الدكتور (يظهر فقط عند اختيار "دكتور") -->
                        <div class="mb-3 recipient-field" id="teacher-field" style="display: none;">
                            <label for="teacher_id" class="form-label">اختر الدكتور <span class="text-danger">*</span></label>
                            <select class="form-select @error('recipient_id') is-invalid @enderror" id="teacher_id" name="teacher_id">
                                <option value="" selected disabled>-- اختر الدكتور --</option>
                                @if(isset($teachers) && count($teachers) > 0)
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        
                        <!-- حقل اختيار المجموعة (يظهر فقط عند اختيار "مجموعة") -->
                        <div class="mb-3 recipient-field" id="group-field" style="display: none;">
                            <label for="group_id" class="form-label">اختر المجموعة <span class="text-danger">*</span></label>
                            <select class="form-select @error('group_id') is-invalid @enderror" id="group_id" name="group_id">
                                <option value="" selected disabled>-- اختر المجموعة --</option>
                                @if(isset($groups) && count($groups) > 0)
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">عنوان الرسالة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" placeholder="أدخل عنوان الرسالة" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">محتوى الرسالة <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="5" placeholder="اكتب محتوى الرسالة هنا..." required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> إرسال
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- جدول الرسائل المرسلة -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>الرسائل المرسلة
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    @if($messages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">المستلم</th>
                                        <th scope="col">نوع المستلم</th>
                                        <th scope="col">عنوان الرسالة</th>
                                        <th scope="col">تاريخ الإرسال</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                        <tr>
                                            <td>
                                                @if($message->recipient_type == 'group')
                                                    {{ $message->group->name ?? 'مجموعة غير معروفة' }}
                                                @else
                                                    {{ $message->recipient->name ?? 'مستلم غير معروف' }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($message->recipient_type == 'group')
                                                    <span class="badge bg-info">مجموعة</span>
                                                @elseif($message->recipient_type == 'student')
                                                    <span class="badge bg-success">طالب</span>
                                                @elseif($message->recipient_type == 'teacher')
                                                    <span class="badge bg-primary">دكتور</span>
                                                @endif
                                            </td>
                                            <td>{{ $message->subject }}</td>
                                            <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal{{ $message->id }}">
                                                    <i class="fas fa-eye me-1"></i> عرض
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal for Viewing Message -->
                                        <div class="modal fade" id="viewModal{{ $message->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $message->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewModalLabel{{ $message->id }}">{{ $message->subject }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <h6>المرسل:</h6>
                                                            <p>{{ $message->sender->name }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6>المستلم:</h6>
                                                            <p>
                                                                @if($message->recipient_type == 'group')
                                                                    {{ $message->group->name ?? 'مجموعة غير معروفة' }} (مجموعة)
                                                                @else
                                                                    {{ $message->recipient->name ?? 'مستلم غير معروف' }} 
                                                                    @if($message->recipient_type == 'student')
                                                                        (طالب)
                                                                    @elseif($message->recipient_type == 'teacher')
                                                                        (دكتور)
                                                                    @endif
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6>تاريخ الإرسال:</h6>
                                                            <p>{{ $message->created_at->format('Y-m-d H:i') }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6>المحتوى:</h6>
                                                            <div class="p-3 bg-light rounded">
                                                                {{ $message->content }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4 mb-4">
                            {{ $messages->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1556/1556426.png" alt="لا توجد رسائل" style="width: 120px; opacity: 0.6;">
                            <p class="mt-3 text-muted">لم تقم بإرسال أي رسائل بعد</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // Hide all recipient fields initially
        $('#student-field, #teacher-field, #group-field').hide();
        
        // Show the appropriate recipient field based on the selected recipient type
        $('#recipient_type').change(function() {
            $('#student-field, #teacher-field, #group-field').hide();
            
            const selectedType = $(this).val();
            
            if (selectedType === 'student') {
                $('#student-field').show();
            } else if (selectedType === 'teacher') {
                $('#teacher-field').show();
            } else if (selectedType === 'group') {
                $('#group-field').show();
            }
            // For all_students and all_teachers, no recipient selection is needed
        });
        
        // If there's a pre-selected value (e.g., from validation errors), show the appropriate field
        const selectedType = $('#recipient_type').val();
        if (selectedType === 'student') {
            $('#student-field').show();
        } else if (selectedType === 'teacher') {
            $('#teacher-field').show();
        } else if (selectedType === 'group') {
            $('#group-field').show();
        }

        // Star toggle functionality
        const starButtons = document.querySelectorAll('.toggle-star');
        
        starButtons.forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                const starIcon = this.querySelector('i');
                
                fetch(`/admin/messages/toggle-star/${messageId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.is_starred) {
                            starIcon.classList.add('text-warning');
                        } else {
                            starIcon.classList.remove('text-warning');
                        }
                    }
                });
            });
        });
        
        // Mark as read functionality
        const markReadButtons = document.querySelectorAll('.mark-read');
        
        markReadButtons.forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                
                fetch('/admin/messages/mark-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message_ids: [messageId] })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            });
        });
    });
</script>
@endsection

@endsection 