@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمين</a></li>
                    <li class="breadcrumb-item active" aria-current="page">عرض المستخدم</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="position-relative mb-4">
                        <div class="user-avatar-lg bg-primary text-white mx-auto d-flex align-items-center justify-content-center">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        @if($user->roles->isNotEmpty())
                            <div class="position-absolute bottom-0 start-50 translate-middle">
                                <span class="badge bg-primary py-2 px-3 rounded-pill">
                                    <i class="fas fa-user-tag me-1"></i> {{ $user->roles->first()->name }}
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted">
                        <i class="fas fa-envelope me-1"></i> {{ $user->email }}
                    </p>
                    
                    <hr class="my-4">
                    
                    <div class="row text-start mb-3">
                        <div class="col-6 mb-3">
                            <div class="text-muted small">تاريخ الإنشاء</div>
                            <div>
                                <i class="far fa-calendar-alt text-primary me-1"></i>
                                {{ $user->created_at->format('Y-m-d') }}
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-muted small">وقت الإنشاء</div>
                            <div>
                                <i class="far fa-clock text-primary me-1"></i>
                                {{ $user->created_at->format('H:i:s') }}
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">تاريخ آخر تحديث</div>
                            <div>
                                <i class="far fa-calendar-check text-primary me-1"></i>
                                {{ $user->updated_at->format('Y-m-d') }}
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">وقت آخر تحديث</div>
                            <div>
                                <i class="far fa-clock text-primary me-1"></i>
                                {{ $user->updated_at->format('H:i:s') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                            <i class="fas fa-user-edit me-1"></i> تعديل المستخدم
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8 col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex align-items-center">
                    <div class="avatar-sm bg-primary-soft text-primary rounded-circle me-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="mb-0">الصلاحيات</h5>
                </div>
                <div class="card-body p-4">
                    @if($user->roles->isNotEmpty() && $user->roles->first()->permissions->isNotEmpty())
                        <div class="row">
                            @foreach($user->roles->first()->permissions as $permission)
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 rounded bg-light">
                                        <div class="avatar-xs bg-primary-soft text-primary rounded-circle me-3">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div>{{ $permission->name }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="https://cdn-icons-png.flaticon.com/512/1680/1680012.png" alt="No Permissions" style="width: 120px; opacity: 0.6;">
                            <p class="text-muted mt-3">لا توجد صلاحيات معينة لهذا المستخدم</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex align-items-center">
                    <div class="avatar-sm bg-primary-soft text-primary rounded-circle me-3">
                        <i class="fas fa-history"></i>
                    </div>
                    <h5 class="mb-0">نشاط المستخدم</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item p-3">
                            <div class="d-flex w-100 align-items-center">
                                <div class="timeline-point bg-primary me-3"></div>
                                <div>
                                    <h6 class="mb-1">تم إنشاء الحساب</h6>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> {{ $user->created_at->format('Y-m-d H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item p-3">
                            <div class="d-flex w-100 align-items-center">
                                <div class="timeline-point bg-primary me-3"></div>
                                <div>
                                    <h6 class="mb-1">تم تحديث بيانات الحساب</h6>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> {{ $user->updated_at->format('Y-m-d H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item p-3">
                            <div class="d-flex w-100 align-items-center">
                                <div class="timeline-point bg-primary-soft me-3"></div>
                                <div>
                                    <h6 class="mb-1">تم تعيين الدور 
                                        @if($user->roles->isNotEmpty())
                                            <span class="badge bg-primary-soft text-primary">{{ $user->roles->first()->name }}</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> {{ $user->created_at->format('Y-m-d H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top p-3">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-1"></i> العودة
                        </a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> حذف المستخدم
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-avatar-lg {
    width: 100px;
    height: 100px;
    font-size: 32px;
    border-radius: 50%;
    margin-bottom: 20px;
}

.avatar-xs {
    width: 30px;
    height: 30px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-point {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    position: relative;
}

.timeline-point:before {
    content: '';
    position: absolute;
    left: 5px;
    top: 12px;
    height: 38px;
    width: 2px;
    background-color: rgba(0, 225, 180, 0.2);
}

.list-group-item:last-child .timeline-point:before {
    display: none;
}
</style>
@endsection 