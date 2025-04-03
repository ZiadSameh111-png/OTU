@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">إدارة المستخدمين</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">لوحة التحكم</a></li>
                                <li class="breadcrumb-item active">المستخدمين</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>
                        إضافة مستخدم جديد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الدور</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary-soft rounded-circle me-2">
                                                    <span class="avatar-title text-primary">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->roles->first())
                                                <span class="badge bg-primary">
                                                    {{ $user->roles->first()->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    بدون دور
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('users.edit', $user->id) }}" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('users.destroy', $user->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" 
                                                 alt="No users" 
                                                 style="width: 120px; opacity: 0.5">
                                            <p class="text-muted mt-3">لا يوجد مستخدمين حالياً</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-title {
    font-size: 14px;
    font-weight: 600;
}

.table > :not(caption) > * > * {
    padding: 1rem;
    border-color: var(--border-color);
}

.pagination {
    margin-bottom: 0;
}

.page-link {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.page-link:hover {
    background-color: rgba(0, 225, 180, 0.1);
    border-color: var(--border-color);
    color: var(--accent-color);
}

.page-item.active .page-link {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
    color: #000;
}

.page-item.disabled .page-link {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-secondary);
}
</style>
@endsection 