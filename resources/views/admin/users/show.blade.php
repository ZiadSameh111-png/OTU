@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>بيانات المستخدم</h5>
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 20px;">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </div>

                <div class="card-body animate__animated animate__fadeIn">
                    @if(session('success'))
                    <div class="alert alert-success animate__animated animate__fadeIn mb-4">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="p-4 bg-light rounded">
                                <div class="mb-3">
                                    <span class="text-muted d-block mb-1"><i class="fas fa-id-card me-2"></i>الاسم</span>
                                    <h4 class="mb-0">{{ $user->name }}</h4>
                                </div>

                                <div class="mb-3">
                                    <span class="text-muted d-block mb-1"><i class="fas fa-envelope me-2"></i>البريد الإلكتروني</span>
                                    <h5 class="mb-0">{{ $user->email }}</h5>
                                </div>

                                <div class="mb-3">
                                    <span class="text-muted d-block mb-1"><i class="fas fa-user-tag me-2"></i>الدور</span>
                                    @foreach($user->roles as $role)
                                    <span class="badge bg-primary p-2">
                                        <i class="fas fa-shield-alt me-1"></i>{{ $role->name }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light">
                                <small class="text-muted d-block mb-1"><i class="fas fa-calendar-plus me-2"></i>تاريخ الإنشاء</small>
                                <p class="mb-0">{{ $user->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light">
                                <small class="text-muted d-block mb-1"><i class="fas fa-clock me-2"></i>آخر تحديث</small>
                                <p class="mb-0">{{ $user->updated_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                        </a>
                        <div>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>تعديل
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذا المستخدم؟')">
                                    <i class="fas fa-trash-alt me-1"></i>حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 