@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمين</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تعديل المستخدم</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary-soft text-primary rounded-circle me-3">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h5 class="mb-0">تعديل المستخدم: {{ $user->name }}</h5>
                    </div>
                    <div class="user-avatar rounded-circle bg-primary d-flex align-items-center justify-content-center">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="name" class="form-label">الاسم <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control border-0 bg-light @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="أدخل اسم المستخدم">
                                    </div>
                                    @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control border-0 bg-light @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="أدخل البريد الإلكتروني">
                                    </div>
                                    @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="password" class="form-label">كلمة المرور <small class="text-muted">(اتركها فارغة إذا كنت لا تريد تغييرها)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control border-0 bg-light @error('password') is-invalid @enderror" id="password" name="password" placeholder="كلمة مرور جديدة">
                                    </div>
                                    @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control border-0 bg-light" id="password_confirmation" name="password_confirmation" placeholder="تأكيد كلمة المرور الجديدة">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="role_id" class="form-label">الدور <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-user-tag"></i></span>
                                <select class="form-select border-0 bg-light @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                    <option value="" disabled>اختر دور المستخدم</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" 
                                        {{ old('role_id', $user->roles->first() ? $user->roles->first()->id : '') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('role_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted mb-2">تم الإنشاء في</label>
                                    <div class="bg-light p-3 rounded">
                                        <i class="far fa-calendar-alt text-primary me-2"></i>
                                        {{ $user->created_at->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted mb-2">آخر تحديث</label>
                                    <div class="bg-light p-3 rounded">
                                        <i class="far fa-calendar-check text-primary me-2"></i>
                                        {{ $user->updated_at->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-right me-1"></i> العودة
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i> حفظ التغييرات
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-avatar {
    width: 48px;
    height: 48px;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
}
</style>
@endsection 