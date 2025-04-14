@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0 text-primary fw-bold">
                <i class="fas fa-bell me-2"></i>الإشعارات
            </h1>
            <p class="text-muted">عرض جميع الإشعارات والتنبيهات الخاصة بك</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-tabs" id="notificationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">
                        <i class="fas fa-list me-1"></i> الإشعارات
                        @if($unread_count > 0)
                            <span class="badge bg-danger">{{ $unread_count }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="trash-tab" data-bs-toggle="tab" href="#trash" role="tab" aria-controls="trash" aria-selected="false">
                        <i class="fas fa-trash-alt me-1"></i> المحذوفة
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="notificationsTabContent">
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>قائمة الإشعارات
                    </h5>
                    <div>
                        @if($unread_count > 0)
                        <a href="{{ route('notifications.markAllAsRead') }}" class="btn btn-sm btn-secondary ms-2">
                            <i class="fas fa-check-double me-1"></i> تحديد الكل كمقروء
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($notifications) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>العنوان</th>
                                        <th>الوصف</th>
                                        <th>المرسل</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                        <tr class="{{ is_null($notification->read_at) ? 'table-light' : '' }}">
                                            <td><strong>{{ $notification->title }}</strong></td>
                                            <td>{{ Str::limit($notification->description, 50) }}</td>
                                            <td>
                                                @if($notification->sender)
                                                    {{ $notification->sender->name }}
                                                @else
                                                    الإدارة
                                                @endif
                                            </td>
                                            <td>{{ $notification->created_at->format('Y-m-d h:i A') }}</td>
                                            <td>
                                                @if(is_null($notification->read_at))
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-envelope me-1"></i> غير مقروء
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-envelope-open me-1"></i> مقروء
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info view-notification" 
                                                    data-id="{{ $notification->id }}"
                                                    data-title="{{ $notification->title }}"
                                                    data-description="{{ $notification->description }}"
                                                    data-sender="{{ $notification->sender ? $notification->sender->name : 'الإدارة' }}"
                                                    data-date="{{ $notification->created_at->format('Y-m-d h:i A') }}"
                                                    data-read="{{ !is_null($notification->read_at) }}"
                                                    data-read-url="{{ route('notifications.markAsRead', $notification->id) }}">
                                                    <i class="fas fa-eye"></i> عرض
                                                </button>
                                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-notification-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash-alt"></i> حذف
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center pt-3 pb-3">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center p-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2098/2098565.png" alt="لا توجد إشعارات" style="width: 120px; opacity: 0.5;">
                            <p class="mt-4 text-muted">لا توجد إشعارات جديدة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="trash" role="tabpanel" aria-labelledby="trash-tab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-trash-alt me-2 text-secondary"></i>الإشعارات المحذوفة
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>العنوان</th>
                                    <th>الوصف</th>
                                    <th>تاريخ الحذف</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="trashed-notifications">
                                <!-- سيتم تحميل الإشعارات المحذوفة عبر AJAX -->
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <p class="my-3 text-muted">لا توجد إشعارات محذوفة</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal عرض الإشعار -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" aria-labelledby="viewNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold">الوصف:</label>
                    <p id="notificationDescription" class="border p-2 rounded"></p>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">المرسل:</label>
                        <p id="notificationSender" class="border p-2 rounded"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">تاريخ الإرسال:</label>
                        <p id="notificationDate" class="border p-2 rounded"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <a href="#" id="markAsReadBtn" class="btn btn-success d-none">
                    <i class="fas fa-check"></i> تحديد كمقروء
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // عرض الإشعار
        const viewButtons = document.querySelectorAll('.view-notification');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const description = this.getAttribute('data-description');
                const sender = this.getAttribute('data-sender');
                const date = this.getAttribute('data-date');
                const isRead = this.getAttribute('data-read') === "1";
                const readUrl = this.getAttribute('data-read-url');
                
                document.getElementById('notificationTitle').textContent = title;
                document.getElementById('notificationDescription').textContent = description;
                document.getElementById('notificationSender').textContent = sender;
                document.getElementById('notificationDate').textContent = date;
                
                const markAsReadBtn = document.getElementById('markAsReadBtn');
                
                if (!isRead) {
                    markAsReadBtn.classList.remove('d-none');
                    markAsReadBtn.href = readUrl;
                } else {
                    markAsReadBtn.classList.add('d-none');
                }
                
                const modal = new bootstrap.Modal(document.getElementById('viewNotificationModal'));
                modal.show();
                
                // إذا كان الإشعار غير مقروء، قم بتحديثه كمقروء بعد فتح التفاصيل
                if (!isRead) {
                    fetch(readUrl)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // تحديث الواجهة لتعكس حالة "مقروء"
                                this.closest('tr').querySelector('.badge').innerHTML = '<i class="fas fa-envelope-open me-1"></i> مقروء';
                                this.closest('tr').querySelector('.badge').classList.remove('bg-danger');
                                this.closest('tr').querySelector('.badge').classList.add('bg-success');
                                // تحديث عدد الإشعارات غير المقروءة
                                const unreadCount = parseInt(document.querySelector('.badge.bg-danger').textContent) - 1;
                                if (unreadCount > 0) {
                                    document.querySelector('.badge.bg-danger').textContent = unreadCount;
                                } else {
                                    document.querySelector('.badge.bg-danger').remove();
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        });
        
        // تأكيد حذف الإشعار
        const deleteForms = document.querySelectorAll('.delete-notification-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('هل أنت متأكد من حذف هذا الإشعار؟')) {
                    this.submit();
                }
            });
        });

        // تحميل الإشعارات المحذوفة
        function loadTrashedNotifications() {
            fetch('{{ route('notifications.trash') }}')
                .then(response => response.json())
                .then(data => {
                    const trashedNotificationsContainer = document.getElementById('trashed-notifications');
                    
                    if (data.length === 0) {
                        trashedNotificationsContainer.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center">
                                    <p class="my-3 text-muted">لا توجد إشعارات محذوفة</p>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    let html = '';
                    data.forEach(notification => {
                        const deletedDate = new Date(notification.deleted_at).toLocaleString('ar-SA');
                        html += `
                            <tr>
                                <td><strong>${notification.title}</strong></td>
                                <td>${notification.description.substring(0, 50)}${notification.description.length > 50 ? '...' : ''}</td>
                                <td>${deletedDate}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success restore-notification" data-id="${notification.id}">
                                        <i class="fas fa-trash-restore"></i> استعادة
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger force-delete-notification" data-id="${notification.id}">
                                        <i class="fas fa-trash-alt"></i> حذف نهائي
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    trashedNotificationsContainer.innerHTML = html;
                    
                    // إضافة مستمعي أحداث لأزرار الاستعادة والحذف النهائي
                    document.querySelectorAll('.restore-notification').forEach(button => {
                        button.addEventListener('click', handleRestore);
                    });
                    
                    document.querySelectorAll('.force-delete-notification').forEach(button => {
                        button.addEventListener('click', handleForceDelete);
                    });
                })
                .catch(error => {
                    console.error('Error loading trashed notifications:', error);
                    document.getElementById('trashed-notifications').innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center">
                                <p class="my-3 text-danger">حدث خطأ أثناء تحميل الإشعارات المحذوفة</p>
                            </td>
                        </tr>
                    `;
                });
        }
        
        // استعادة إشعار محذوف
        function handleRestore() {
            const id = this.getAttribute('data-id');
            if (confirm('هل أنت متأكد من استعادة هذا الإشعار؟')) {
                fetch(`/notifications/${id}/restore`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadTrashedNotifications(); // إعادة تحميل الإشعارات المحذوفة
                        location.reload(); // إعادة تحميل الصفحة لتحديث قائمة الإشعارات النشطة
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error restoring notification:', error);
                    alert('حدث خطأ أثناء استعادة الإشعار');
                });
            }
        }
        
        // حذف إشعار نهائيًا
        function handleForceDelete() {
            const id = this.getAttribute('data-id');
            if (confirm('هل أنت متأكد من حذف هذا الإشعار نهائيًا؟ لا يمكن التراجع عن هذه العملية.')) {
                fetch(`/notifications/${id}/force-delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadTrashedNotifications(); // إعادة تحميل الإشعارات المحذوفة
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error force deleting notification:', error);
                    alert('حدث خطأ أثناء حذف الإشعار نهائيًا');
                });
            }
        }
        
        // تحميل الإشعارات المحذوفة عند النقر على تبويب "المحذوفة"
        document.getElementById('trash-tab').addEventListener('click', loadTrashedNotifications);
    });
</script>
@endsection 