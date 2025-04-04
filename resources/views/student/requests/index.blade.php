@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">الطلبات الإدارية</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-clipboard-list me-2"></i>الطلبات الإدارية
            </h1>
            <p class="text-muted">إدارة ومتابعة طلباتك المقدمة للإدارة</p>
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
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">إنشاء طلب جديد</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                        <i class="fas fa-plus-circle me-1"></i> طلب جديد
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-light border-0 h-100 text-center">
                                <div class="card-body py-4">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="fas fa-file-alt text-primary fa-2x"></i>
                                    </div>
                                    <h6 class="mb-2">طلب إفادة</h6>
                                    <p class="text-muted small mb-3">طلب إفادة رسمية من المؤسسة</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal" data-type="certificate">
                                        تقديم الطلب
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-light border-0 h-100 text-center">
                                <div class="card-body py-4">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="fas fa-calendar-alt text-warning fa-2x"></i>
                                    </div>
                                    <h6 class="mb-2">إذن غياب</h6>
                                    <p class="text-muted small mb-3">طلب إذن غياب لظروف خاصة</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal" data-type="absence">
                                        تقديم الطلب
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-light border-0 h-100 text-center">
                                <div class="card-body py-4">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="fas fa-exchange-alt text-success fa-2x"></i>
                                    </div>
                                    <h6 class="mb-2">تغيير مجموعة</h6>
                                    <p class="text-muted small mb-3">طلب تغيير المجموعة الدراسية</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal" data-type="group_change">
                                        تقديم الطلب
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-light border-0 h-100 text-center">
                                <div class="card-body py-4">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="fas fa-flag text-info fa-2x"></i>
                                    </div>
                                    <h6 class="mb-2">طلب آخر</h6>
                                    <p class="text-muted small mb-3">تقديم طلب غير مدرج في القائمة</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal" data-type="other">
                                        تقديم الطلب
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">طلباتي السابقة</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="all-requests">الكل</button>
                        <button type="button" class="btn btn-outline-warning" id="pending-requests">معلقة</button>
                        <button type="button" class="btn btn-outline-success" id="approved-requests">مقبولة</button>
                        <button type="button" class="btn btn-outline-danger" id="rejected-requests">مرفوضة</button>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($requests) && $requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>نوع الطلب</th>
                                        <th>التفاصيل</th>
                                        <th>تاريخ الطلب</th>
                                        <th>الحالة</th>
                                        <th>تعليق الإدارة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr class="request-row {{ $request->status }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $request->type_name }}</td>
                                            <td>{{ Str::limit($request->details, 30) }}</td>
                                            <td>{{ $request->request_date->format('Y-m-d') }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($request->status == 'pending') bg-warning
                                                    @elseif($request->status == 'approved') bg-success
                                                    @elseif($request->status == 'rejected') bg-danger
                                                    @endif">
                                                    @if($request->status == 'pending') قيد المعالجة
                                                    @elseif($request->status == 'approved') مقبول
                                                    @elseif($request->status == 'rejected') مرفوض
                                                    @endif
                                                </span>
                                            </td>
                                            <td>{{ $request->admin_comment ?? '-' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary view-request" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewRequestModal" 
                                                        data-id="{{ $request->id }}"
                                                        data-type="{{ $request->type_name }}"
                                                        data-details="{{ $request->details }}"
                                                        data-date="{{ $request->request_date->format('Y-m-d') }}"
                                                        data-status="{{ $request->status }}"
                                                        data-comment="{{ $request->admin_comment }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($request->status == 'pending')
                                                    <form action="{{ route('student.requests.destroy', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3342/3342137.png" class="mb-3" style="width: 80px; opacity: 0.6;" alt="لا توجد طلبات">
                            <p class="text-muted">لم تقم بتقديم أي طلبات بعد</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal - إنشاء طلب جديد -->
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-labelledby="createRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRequestModalLabel">إنشاء طلب جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('student.requests.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="request_type" class="form-label">نوع الطلب <span class="text-danger">*</span></label>
                        <select class="form-select" id="request_type" name="type" required>
                            <option value="" selected disabled>اختر نوع الطلب</option>
                            <option value="certificate">طلب إفادة</option>
                            <option value="absence">إذن غياب</option>
                            <option value="group_change">تغيير مجموعة</option>
                            <option value="other">طلب آخر</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="request_details" class="form-label">تفاصيل الطلب <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="request_details" name="details" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تقديم الطلب</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal - عرض تفاصيل الطلب -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRequestModalLabel">تفاصيل الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-4 fw-bold">نوع الطلب:</div>
                    <div class="col-8" id="modal-request-type"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">تاريخ الطلب:</div>
                    <div class="col-8" id="modal-request-date"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">حالة الطلب:</div>
                    <div class="col-8" id="modal-request-status"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">التفاصيل:</div>
                    <div class="col-8" id="modal-request-details"></div>
                </div>
                <div class="row admin-comment-row">
                    <div class="col-4 fw-bold">تعليق الإدارة:</div>
                    <div class="col-8" id="modal-admin-comment"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set request type in modal when clicking on card buttons
        const requestButtons = document.querySelectorAll('[data-type]');
        requestButtons.forEach(button => {
            button.addEventListener('click', function() {
                const requestType = this.getAttribute('data-type');
                document.getElementById('request_type').value = requestType;
            });
        });

        // Filter requests by status
        const allBtn = document.getElementById('all-requests');
        const pendingBtn = document.getElementById('pending-requests');
        const approvedBtn = document.getElementById('approved-requests');
        const rejectedBtn = document.getElementById('rejected-requests');
        const requestRows = document.querySelectorAll('.request-row');

        allBtn.addEventListener('click', function() {
            setActiveButton(allBtn);
            requestRows.forEach(row => {
                row.style.display = '';
            });
        });

        pendingBtn.addEventListener('click', function() {
            setActiveButton(pendingBtn);
            filterRows('pending');
        });

        approvedBtn.addEventListener('click', function() {
            setActiveButton(approvedBtn);
            filterRows('approved');
        });

        rejectedBtn.addEventListener('click', function() {
            setActiveButton(rejectedBtn);
            filterRows('rejected');
        });

        function setActiveButton(activeBtn) {
            [allBtn, pendingBtn, approvedBtn, rejectedBtn].forEach(btn => {
                btn.classList.remove('active');
            });
            activeBtn.classList.add('active');
        }

        function filterRows(status) {
            requestRows.forEach(row => {
                if (row.classList.contains(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Populate view request modal
        const viewButtons = document.querySelectorAll('.view-request');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                const details = this.getAttribute('data-details');
                const date = this.getAttribute('data-date');
                const status = this.getAttribute('data-status');
                const comment = this.getAttribute('data-comment');

                document.getElementById('modal-request-type').textContent = type;
                document.getElementById('modal-request-date').textContent = date;
                document.getElementById('modal-request-details').textContent = details;
                
                const statusEl = document.getElementById('modal-request-status');
                statusEl.textContent = '';
                const badge = document.createElement('span');
                badge.classList.add('badge');
                
                if (status === 'pending') {
                    badge.classList.add('bg-warning');
                    badge.textContent = 'قيد المعالجة';
                } else if (status === 'approved') {
                    badge.classList.add('bg-success');
                    badge.textContent = 'مقبول';
                } else if (status === 'rejected') {
                    badge.classList.add('bg-danger');
                    badge.textContent = 'مرفوض';
                }
                
                statusEl.appendChild(badge);
                
                const commentRow = document.querySelector('.admin-comment-row');
                const commentEl = document.getElementById('modal-admin-comment');
                
                if (comment && comment !== 'null') {
                    commentEl.textContent = comment;
                    commentRow.style.display = '';
                } else {
                    commentRow.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection 