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
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-envelope me-2"></i>الرسائل الداخلية
            </h1>
            <p class="text-muted">إدارة الرسائل مع المعلمين والإدارة</p>
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

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="d-grid gap-2 mb-4">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
                    <i class="fas fa-pen me-2"></i>إنشاء رسالة جديدة
                </button>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">الصناديق</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('student.messages', ['folder' => 'inbox']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request('folder', 'inbox') == 'inbox' ? 'active' : '' }}">
                            <div>
                                <i class="fas fa-inbox me-2"></i>
                                الوارد
                            </div>
                            @if(isset($unreadCount) && $unreadCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('student.messages', ['folder' => 'sent']) }}" class="list-group-item list-group-item-action {{ request('folder') == 'sent' ? 'active' : '' }}">
                            <i class="fas fa-paper-plane me-2"></i>
                            المرسلة
                        </a>
                        <a href="{{ route('student.messages', ['folder' => 'starred']) }}" class="list-group-item list-group-item-action {{ request('folder') == 'starred' ? 'active' : '' }}">
                            <i class="fas fa-star me-2"></i>
                            المميزة بنجمة
                        </a>
                        <a href="{{ route('student.messages', ['folder' => 'trash']) }}" class="list-group-item list-group-item-action {{ request('folder') == 'trash' ? 'active' : '' }}">
                            <i class="fas fa-trash me-2"></i>
                            المحذوفة
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">التصنيفات</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($categories ?? [] as $category)
                            <a href="{{ route('student.messages', ['category' => $category->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request('category') == $category->id ? 'active' : '' }}">
                                <div>
                                    <i class="fas fa-tag me-2" style="color: {{ $category->color }};"></i>
                                    {{ $category->name }}
                                </div>
                                <span class="badge bg-secondary rounded-pill">{{ $category->messages_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        @if(request('folder', 'inbox') == 'inbox')
                            الرسائل الواردة
                        @elseif(request('folder') == 'sent')
                            الرسائل المرسلة
                        @elseif(request('folder') == 'starred')
                            الرسائل المميزة بنجمة
                        @elseif(request('folder') == 'trash')
                            الرسائل المحذوفة
                        @elseif(request('category'))
                            رسائل التصنيف
                        @else
                            الرسائل
                        @endif
                    </h5>
                    <div class="input-group" style="width: 250px;">
                        <input type="text" class="form-control" placeholder="بحث..." id="message-search">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(isset($messages) && $messages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="select-all">
                                            </div>
                                        </th>
                                        <th style="width: 40px;"></th>
                                        <th>المرسل</th>
                                        <th>الموضوع</th>
                                        <th style="width: 140px;">التاريخ</th>
                                        <th style="width: 120px;">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                        <tr class="{{ !$message->is_read && request('folder', 'inbox') == 'inbox' ? 'table-active' : '' }}">
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input message-checkbox" type="checkbox" value="{{ $message->id }}">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if(!$message->is_read && request('folder', 'inbox') == 'inbox')
                                                    <span class="badge bg-danger rounded-pill"></span>
                                                @endif
                                                @if($message->is_starred)
                                                    <i class="fas fa-star text-warning"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('student.messages.show', $message->id) }}" class="text-decoration-none text-dark">
                                                    {{ $message->sender->name ?? 'غير معروف' }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('student.messages.show', $message->id) }}" class="text-decoration-none text-dark">
                                                    <div class="d-flex align-items-center">
                                                        @if($message->category)
                                                            <span class="badge me-2" style="background-color: {{ $message->category->color }};">{{ $message->category->name }}</span>
                                                        @endif
                                                        {{ Str::limit($message->subject, 50) }}
                                                    </div>
                                                    <small class="text-muted">{{ Str::limit($message->content, 60) }}</small>
                                                </a>
                                            </td>
                                            <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('student.messages.show', $message->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-star" data-id="{{ $message->id }}">
                                                        <i class="fas {{ $message->is_starred ? 'fa-star text-warning' : 'fa-star' }}"></i>
                                                    </button>
                                                    <form action="{{ route('student.messages.destroy', $message->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary mark-read" disabled>
                                    <i class="fas fa-envelope-open me-1"></i> تعليم كمقروءة
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary mark-star" disabled>
                                    <i class="fas fa-star me-1"></i> تعليم بنجمة
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-selected" disabled>
                                    <i class="fas fa-trash me-1"></i> حذف المحدد
                                </button>
                            </div>
                            
                            <div>
                                {{ $messages->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3917/3917032.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد رسائل">
                            <p class="text-muted">لا توجد رسائل في هذا الصندوق</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal - إنشاء رسالة جديدة -->
<div class="modal fade" id="composeModal" tabindex="-1" aria-labelledby="composeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="composeModalLabel">إنشاء رسالة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('student.messages.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="recipient_type" class="form-label">إرسال إلى <span class="text-danger">*</span></label>
                        <select class="form-select" id="recipient_type" name="recipient_type" required>
                            <option value="" selected disabled>اختر نوع المستلم</option>
                            <option value="teacher">معلم</option>
                            <option value="admin">إدارة</option>
                        </select>
                    </div>
                    
                    <div class="mb-3 recipient-teacher" style="display: none;">
                        <label for="teacher_id" class="form-label">المعلم <span class="text-danger">*</span></label>
                        <select class="form-select" id="teacher_id" name="teacher_id">
                            <option value="" selected disabled>اختر المعلم</option>
                            @foreach($teachers ?? [] as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3 recipient-admin" style="display: none;">
                        <label for="admin_id" class="form-label">القسم الإداري <span class="text-danger">*</span></label>
                        <select class="form-select" id="admin_id" name="admin_id">
                            <option value="" selected disabled>اختر القسم</option>
                            @foreach($adminDepartments ?? [] as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">الموضوع <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">محتوى الرسالة <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">التصنيف (اختياري)</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">بدون تصنيف</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إرسال الرسالة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle recipient fields based on selection
        const recipientType = document.getElementById('recipient_type');
        const teacherField = document.querySelector('.recipient-teacher');
        const adminField = document.querySelector('.recipient-admin');
        
        recipientType.addEventListener('change', function() {
            if (this.value === 'teacher') {
                teacherField.style.display = 'block';
                adminField.style.display = 'none';
            } else if (this.value === 'admin') {
                teacherField.style.display = 'none';
                adminField.style.display = 'block';
            } else {
                teacherField.style.display = 'none';
                adminField.style.display = 'none';
            }
        });
        
        // Select all messages checkbox
        const selectAll = document.getElementById('select-all');
        const messageCheckboxes = document.querySelectorAll('.message-checkbox');
        const actionButtons = document.querySelectorAll('.mark-read, .mark-star, .delete-selected');
        
        selectAll.addEventListener('change', function() {
            messageCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            
            toggleActionButtons();
        });
        
        messageCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleActionButtons();
            });
        });
        
        function toggleActionButtons() {
            const hasChecked = Array.from(messageCheckboxes).some(checkbox => checkbox.checked);
            
            actionButtons.forEach(button => {
                button.disabled = !hasChecked;
            });
        }
        
        // Star toggle functionality
        const starButtons = document.querySelectorAll('.toggle-star');
        
        starButtons.forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                const starIcon = this.querySelector('i');
                
                fetch(`{{ route('student.messages.toggle-star', '') }}/${messageId}`.replace('student.messages.toggle-star', 'student.messages.toggle-star'), {
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
        
        // Bulk actions
        document.querySelector('.mark-read').addEventListener('click', function() {
            const selectedIds = getSelectedMessageIds();
            
            if (selectedIds.length > 0) {
                fetch('{{ route('student.messages.mark-read') }}'.replace('student.messages.mark-read', 'student.messages.mark-read'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message_ids: selectedIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            }
        });
        
        document.querySelector('.mark-star').addEventListener('click', function() {
            const selectedIds = getSelectedMessageIds();
            
            if (selectedIds.length > 0) {
                fetch('{{ route('student.messages.mark-star') }}'.replace('student.messages.mark-star', 'student.messages.mark-star'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message_ids: selectedIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            }
        });
        
        document.querySelector('.delete-selected').addEventListener('click', function() {
            const selectedIds = getSelectedMessageIds();
            
            if (selectedIds.length > 0 && confirm('هل أنت متأكد من حذف الرسائل المحددة؟')) {
                fetch('{{ route('student.messages.batch-delete') }}'.replace('student.messages.batch-delete', 'student.messages.batch-delete'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message_ids: selectedIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            }
        });
        
        function getSelectedMessageIds() {
            return Array.from(messageCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);
        }
        
        // Search functionality
        const searchInput = document.getElementById('message-search');
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                
                if (searchTerm) {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('search', searchTerm);
                    window.location.href = currentUrl.toString();
                }
            }
        });
    });
</script>
@endpush
@endsection 