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
                        <i class="fas fa-clipboard-list me-2 text-primary"></i>الطلبات الإدارية
                    </h5>
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> تصفية
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item {{ request()->is('admin/requests') && !request('status') ? 'active' : '' }}" href="{{ route('admin.requests') }}">جميع الطلبات</a></li>
                            <li><a class="dropdown-item {{ request('status') == 'pending' ? 'active' : '' }}" href="{{ route('admin.requests', ['status' => 'pending']) }}">قيد المعالجة</a></li>
                            <li><a class="dropdown-item {{ request('status') == 'approved' ? 'active' : '' }}" href="{{ route('admin.requests', ['status' => 'approved']) }}">مقبولة</a></li>
                            <li><a class="dropdown-item {{ request('status') == 'rejected' ? 'active' : '' }}" href="{{ route('admin.requests', ['status' => 'rejected']) }}">مرفوضة</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">اسم الطالب</th>
                                        <th scope="col">نوع الطلب</th>
                                        <th scope="col">التفاصيل</th>
                                        <th scope="col">تاريخ التقديم</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr>
                                            <td>{{ $request->student->name }}</td>
                                            <td>{{ $request->type_name }}</td>
                                            <td>
                                                {{ Str::limit($request->details, 50) }}
                                                @if(strlen($request->details) > 50)
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $request->id }}">
                                                        <i class="fas fa-eye ms-1"></i>عرض المزيد
                                                    </a>
                                                @endif
                                            </td>
                                            <td>{{ $request->request_date->format('Y-m-d') }}</td>
                                            <td>
                                                @if($request->status == 'pending')
                                                    <span class="badge bg-warning">قيد المعالجة</span>
                                                @elseif($request->status == 'approved')
                                                    <span class="badge bg-success">مقبول</span>
                                                @elseif($request->status == 'rejected')
                                                    <span class="badge bg-danger">مرفوض</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#responseModal{{ $request->id }}">
                                                    <i class="fas fa-reply me-1"></i> الرد
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal for Viewing Full Details -->
                                        <div class="modal fade" id="detailsModal{{ $request->id }}" tabindex="-1" aria-labelledby="detailsModalLabel{{ $request->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detailsModalLabel{{ $request->id }}">تفاصيل الطلب</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>{{ $request->details }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal for Responding to Request -->
                                        <div class="modal fade" id="responseModal{{ $request->id }}" tabindex="-1" aria-labelledby="responseModalLabel{{ $request->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="responseModalLabel{{ $request->id }}">الرد على الطلب</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.requests.update', $request) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="status" class="form-label">الحالة</label>
                                                                <select class="form-select" id="status" name="status" required>
                                                                    <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>قيد المعالجة</option>
                                                                    <option value="approved" {{ $request->status == 'approved' ? 'selected' : '' }}>مقبول</option>
                                                                    <option value="rejected" {{ $request->status == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="admin_comment" class="form-label">الرد</label>
                                                                <textarea class="form-control" id="admin_comment" name="admin_comment" rows="3" placeholder="أدخل ردك على الطلب هنا...">{{ $request->admin_comment }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                            <button type="submit" class="btn btn-primary">تحديث الحالة</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4 mb-4">
                            {{ $requests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1380/1380641.png" alt="لا توجد طلبات" style="width: 120px; opacity: 0.6;">
                            <p class="mt-3 text-muted">لا توجد طلبات إدارية في النظام</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 