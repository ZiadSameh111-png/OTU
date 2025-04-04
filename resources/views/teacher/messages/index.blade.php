@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">صندوق الوارد</li>
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
                        <i class="fas fa-envelope-open-text me-2 text-primary"></i>صندوق الوارد
                        @if($unreadCount > 0)
                            <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
                        @endif
                    </h5>
                    
                    <div class="d-flex">
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-1"></i> تصفية
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item {{ request()->is('teacher/messages') && !request('is_read') ? 'active' : '' }}" href="{{ route('teacher.messages') }}">جميع الرسائل</a></li>
                                <li><a class="dropdown-item {{ request('is_read') == '0' ? 'active' : '' }}" href="{{ route('teacher.messages', ['is_read' => 0]) }}">غير مقروءة</a></li>
                                <li><a class="dropdown-item {{ request('is_read') == '1' ? 'active' : '' }}" href="{{ route('teacher.messages', ['is_read' => 1]) }}">مقروءة</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($messages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" width="5%">الحالة</th>
                                        <th scope="col" width="40%">عنوان الرسالة</th>
                                        <th scope="col" width="20%">المرسل</th>
                                        <th scope="col" width="20%">تاريخ الإرسال</th>
                                        <th scope="col" width="15%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                        <tr class="{{ $message->is_read ? '' : 'table-light fw-bold' }}">
                                            <td>
                                                @if($message->is_read)
                                                    <i class="fas fa-envelope-open text-muted"></i>
                                                @else
                                                    <i class="fas fa-envelope text-primary"></i>
                                                @endif
                                            </td>
                                            <td>{{ $message->subject }}</td>
                                            <td>{{ $message->sender->name }}</td>
                                            <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal{{ $message->id }}" data-message-id="{{ $message->id }}">
                                                    <i class="fas fa-eye me-1"></i> قراءة
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal for Reading Message -->
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
                            <img src="https://cdn-icons-png.flaticon.com/512/3917/3917779.png" alt="لا توجد رسائل" style="width: 120px; opacity: 0.6;">
                            <p class="mt-3 text-muted">لا توجد رسائل في صندوق الوارد الخاص بك</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // تحديث حالة الرسالة ليصبح مقروءًا عند فتحها
    document.addEventListener('DOMContentLoaded', function() {
        const modals = document.querySelectorAll('[data-bs-toggle="modal"]');
        
        modals.forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-message-id');
                
                // إرسال طلب AJAX لتحديث حالة الرسالة
                fetch(`/teacher/messages/${messageId}/read`, {
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
                        // تحديث الواجهة عند النجاح
                        const row = this.closest('tr');
                        row.classList.remove('table-light', 'fw-bold');
                        const icon = row.querySelector('td:first-child i');
                        icon.className = 'fas fa-envelope-open text-muted';
                        
                        // تحديث عدد الرسائل غير المقروءة في الشريط الجانبي (إذا كان موجودًا)
                        const badge = document.querySelector('.nav-link .badge');
                        if (badge && parseInt(badge.textContent) > 0) {
                            const count = parseInt(badge.textContent) - 1;
                            if (count > 0) {
                                badge.textContent = count;
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
</script>
@endsection

@endsection