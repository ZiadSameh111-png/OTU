@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>إضافة مستخدم جديد
                    </h5>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger animate__animated animate__shakeX">
                            <ul class="mb-0 list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.store') }}" class="animate__animated animate__fadeIn">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-2"></i>الاسم
                            </label>
                            <input id="name" type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>البريد الإلكتروني
                            </label>
                            <input id="email" type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>كلمة المرور
                            </label>
                            <div class="input-group">
                                <input id="password" type="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    name="password" 
                                    required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-2"></i>تأكيد كلمة المرور
                            </label>
                            <div class="input-group">
                                <input id="password_confirmation" type="password" 
                                    class="form-control" 
                                    name="password_confirmation" 
                                    required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="role_id" class="form-label">
                                <i class="fas fa-user-tag me-2"></i>الدور
                            </label>
                            <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">اختر الدور</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4" id="group-container" style="{{ old('role_id') && $roles->find(old('role_id'))->name == 'Student' ? '' : 'display: none;' }}">
                            <label for="group_id" class="form-label">
                                <i class="fas fa-users me-2"></i>المجموعة
                            </label>
                            <select id="group_id" name="group_id" class="form-select @error('group_id') is-invalid @enderror">
                                <option value="">اختر المجموعة</option>
                                @foreach(\App\Models\Group::where('active', true)->get() as $group)
                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">يتم تحديد المجموعة للطلاب فقط</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-2"></i>رجوع
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>حفظ المستخدم
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
        // Show/hide group selection based on role
        const roleSelect = document.getElementById('role_id');
        const groupContainer = document.getElementById('group-container');
        
        roleSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isStudent = selectedOption.text === 'Student';
            groupContainer.style.display = isStudent ? 'block' : 'none';
        });

        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Toggle Password Confirmation Visibility
        const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
        const passwordConfirmation = document.getElementById('password_confirmation');
        
        togglePasswordConfirmation.addEventListener('click', function() {
            const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmation.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
</script>
@endpush
@endsection 