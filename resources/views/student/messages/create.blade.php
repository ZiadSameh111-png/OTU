@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.messages') }}">الرسائل الداخلية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">إنشاء رسالة جديدة</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold text-primary mb-0">
                <i class="fas fa-paper-plane me-2"></i>إنشاء رسالة جديدة
            </h1>
            <p class="text-muted">إرسال رسالة إلى المعلمين أو الإدارة</p>
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">الصناديق</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('student.messages', ['folder' => 'inbox']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-inbox me-2"></i>
                                الوارد
                            </div>
                            @if(isset($unreadCount) && $unreadCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('student.messages', ['folder' => 'sent']) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-paper-plane me-2"></i>
                            المرسلة
                        </a>
                        <a href="{{ route('student.messages', ['folder' => 'starred']) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-star me-2"></i>
                            المميزة بنجمة
                        </a>
                        <a href="{{ route('student.messages', ['folder' => 'trash']) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-trash me-2"></i>
                            المحذوفة
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2 text-primary"></i>إنشاء رسالة جديدة</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('student.messages.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="recipient_type" class="form-label">إرسال إلى <span class="text-danger">*</span></label>
                            <select class="form-select @error('recipient_type') is-invalid @enderror" id="recipient_type" name="recipient_type" required>
                                <option value="" selected disabled>اختر نوع المستلم</option>
                                <option value="teacher" {{ old('recipient_type') == 'teacher' ? 'selected' : '' }}>معلم</option>
                                <option value="admin" {{ old('recipient_type') == 'admin' ? 'selected' : '' }}>إدارة</option>
                            </select>
                            @error('recipient_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 recipient-teacher" style="display: {{ old('recipient_type') == 'teacher' ? 'block' : 'none' }};">
                            <label for="teacher_id" class="form-label">المعلم <span class="text-danger">*</span></label>
                            <select class="form-select @error('teacher_id') is-invalid @enderror" id="teacher_id" name="teacher_id">
                                <option value="" selected disabled>اختر المعلم</option>
                                @foreach($teachers ?? [] as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 recipient-admin" style="display: {{ old('recipient_type') == 'admin' ? 'block' : 'none' }};">
                            <label for="admin_id" class="form-label">المسؤول الإداري <span class="text-danger">*</span></label>
                            <select class="form-select @error('admin_id') is-invalid @enderror" id="admin_id" name="admin_id">
                                <option value="" selected disabled>اختر المسؤول</option>
                                @foreach($admins ?? [] as $admin)
                                    <option value="{{ $admin->id }}" {{ old('admin_id') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                                @endforeach
                            </select>
                            @error('admin_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">موضوع الرسالة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">محتوى الرسالة <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('student.messages') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-1"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> إرسال الرسالة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
    });
</script>
@endpush

@endsection 