@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.messages') }}">صندوق الوارد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">عرض الرسالة</li>
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
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>{{ $message->subject }}
                    </h5>
                    <div>
                        <a href="{{ route('teacher.messages') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="message-info mb-4 border-bottom pb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong><i class="fas fa-user me-1 text-secondary"></i> المرسل:</strong> 
                                    {{ $message->sender->name }}
                                </p>
                                <p class="mb-1">
                                    <strong><i class="fas fa-user-circle me-1 text-secondary"></i> المستلم:</strong> 
                                    {{ $message->recipient->name }}
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="mb-1">
                                    <strong><i class="fas fa-calendar-alt me-1 text-secondary"></i> تاريخ الإرسال:</strong> 
                                    {{ $message->created_at->format('Y-m-d H:i') }}
                                </p>
                                <p class="mb-1">
                                    <strong><i class="fas fa-{{ $message->is_read ? 'envelope-open' : 'envelope' }} me-1 text-secondary"></i> الحالة:</strong> 
                                    <span class="badge bg-{{ $message->is_read ? 'success' : 'warning' }}">
                                        {{ $message->is_read ? 'مقروءة' : 'غير مقروءة' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="message-content mb-4">
                        <h6 class="mb-3"><i class="fas fa-align-left me-1 text-primary"></i> محتوى الرسالة:</h6>
                        <div class="p-4 bg-light rounded">
                            {{ $message->content }}
                        </div>
                    </div>

                    <div class="message-actions d-flex justify-content-between mt-4">
                        <div>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash-alt me-1"></i> حذف الرسالة
                            </button>
                        </div>
                        <div>
                            <button id="star-button" class="btn btn-outline-warning btn-sm {{ $message->is_starred ? 'active' : '' }}" data-message-id="{{ $message->id }}">
                                <i class="fas fa-star me-1"></i> {{ $message->is_starred ? 'إلغاء التمييز' : 'تمييز بنجمة' }}
                            </button>
                            
                            @if($message->sender_id != Auth::id())
                                <a href="{{ route('teacher.messages.create') }}" class="btn btn-primary btn-sm ms-2">
                                    <i class="fas fa-reply me-1"></i> رد
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Delete Confirmation -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                هل أنت متأكد من رغبتك في حذف هذه الرسالة؟ لا يمكن التراجع عن هذه العملية.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('teacher.messages.destroy', $message->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">تأكيد الحذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // معالجة نقر زر التمييز بنجمة
        const starButton = document.getElementById('star-button');
        if (starButton) {
            starButton.addEventListener('click', function() {
                const messageId = this.getAttribute('data-message-id');
                
                // إرسال طلب AJAX لتبديل حالة النجمة
                fetch(`/teacher/messages/toggle-star/${messageId}`, {
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
                        // تحديث حالة الزر
                        if (data.is_starred) {
                            starButton.classList.add('active');
                            starButton.innerHTML = '<i class="fas fa-star me-1"></i> إلغاء التمييز';
                        } else {
                            starButton.classList.remove('active');
                            starButton.innerHTML = '<i class="fas fa-star me-1"></i> تمييز بنجمة';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        }
    });
</script>
@endsection

@endsection 