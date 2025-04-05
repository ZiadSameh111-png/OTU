@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0 text-primary fw-bold">
                <i class="fas fa-bell me-2"></i>إنشاء إشعار جديد
            </h1>
            <p class="text-muted">إنشاء وإرسال إشعار جديد للمستخدمين</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">
                <i class="fas fa-plus-circle me-2 text-primary"></i>إشعار جديد
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('notifications.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">عنوان الإشعار <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" 
                                value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">محتوى الإشعار <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" 
                                name="description" rows="5" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="url" class="form-label">رابط الإشعار (اختياري)</label>
                            <input type="url" class="form-control @error('url') is-invalid @enderror" id="url" name="url" 
                                value="{{ old('url') }}" placeholder="https://example.com">
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">أدخل الرابط الذي سيتم توجيه المستخدم إليه عند النقر على الإشعار</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="notification_type" class="form-label">نوع الإشعار <span class="text-danger">*</span></label>
                            <select class="form-select @error('notification_type') is-invalid @enderror" id="notification_type" 
                                name="notification_type" required>
                                <option value="">اختر نوع الإشعار</option>
                                <option value="general" {{ old('notification_type') == 'general' ? 'selected' : '' }}>عام</option>
                                <option value="academic" {{ old('notification_type') == 'academic' ? 'selected' : '' }}>أكاديمي</option>
                                <option value="announcement" {{ old('notification_type') == 'announcement' ? 'selected' : '' }}>إعلان</option>
                                <option value="exam" {{ old('notification_type') == 'exam' ? 'selected' : '' }}>اختبار</option>
                            </select>
                            @error('notification_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="receiver_type" class="form-label">نوع المستلم <span class="text-danger">*</span></label>
                            <select class="form-select @error('receiver_type') is-invalid @enderror" id="receiver_type" 
                                name="receiver_type" required>
                                <option value="">اختر نوع المستلم</option>
                                <option value="user" {{ old('receiver_type') == 'user' ? 'selected' : '' }}>مستخدم محدد</option>
                                <option value="group" {{ old('receiver_type') == 'group' ? 'selected' : '' }}>مجموعة</option>
                                <option value="role" {{ old('receiver_type') == 'role' ? 'selected' : '' }}>دور محدد</option>
                            </select>
                            @error('receiver_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="user-receiver-options d-none receiver-options" id="user-options">
                            <div class="form-group mb-3">
                                <label for="receiver_id" class="form-label">المستخدم المستلم <span class="text-danger">*</span></label>
                                <select class="form-select @error('receiver_id') is-invalid @enderror" id="receiver_id" name="receiver_id">
                                    <option value="">اختر المستخدم</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('receiver_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('receiver_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="group-receiver-options d-none receiver-options" id="group-options">
                            <div class="form-group mb-3">
                                <label for="group_id" class="form-label">المجموعة المستلمة <span class="text-danger">*</span></label>
                                <select class="form-select @error('group_id') is-invalid @enderror" id="group_id" name="group_id">
                                    <option value="">اختر المجموعة</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="role-receiver-options d-none receiver-options" id="role-options">
                            <div class="form-group mb-3">
                                <label for="role" class="form-label">الدور المستلم <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                                    <option value="">اختر الدور</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>المسؤولون</option>
                                    <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>المدرسون</option>
                                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>الطلاب</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> إرسال الإشعار
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const receiverTypeSelect = document.getElementById('receiver_type');
        const receiverOptions = document.querySelectorAll('.receiver-options');
        
        // Function to show appropriate options based on receiver type
        function updateReceiverOptions() {
            // Hide all options first
            receiverOptions.forEach(option => {
                option.classList.add('d-none');
            });
            
            // Show the selected option
            const selectedValue = receiverTypeSelect.value;
            if (selectedValue) {
                document.getElementById(`${selectedValue}-options`).classList.remove('d-none');
            }
        }
        
        // Set initial state
        updateReceiverOptions();
        
        // Update on change
        receiverTypeSelect.addEventListener('change', updateReceiverOptions);
    });
</script>
@endpush
@endsection 