@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">إدارة الحضور</li>
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
        <!-- نموذج تسجيل حضور جديد -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2 text-primary"></i>تسجيل حضور جديد
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.attendance.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="user_type" class="form-label">نوع المستخدم <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_type') is-invalid @enderror" id="user_type" name="user_type" required>
                                <option value="teacher" {{ old('user_type') == 'teacher' ? 'selected' : '' }}>معلم</option>
                                <option value="student" {{ old('user_type') == 'student' ? 'selected' : '' }}>طالب</option>
                            </select>
                            @error('user_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 teacher-select">
                            <label for="user_id" class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                <option value="" selected disabled>-- اختر المستخدم --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" data-type="teacher" {{ old('user_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                @endforeach
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" data-type="student" style="display: none;" {{ old('user_id') == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="attendance_date" class="form-label">تاريخ الحضور <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" id="attendance_date" name="attendance_date" value="{{ old('attendance_date', now()->format('Y-m-d')) }}" required>
                            @error('attendance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>حاضر</option>
                                <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>غائب</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="يمكنك كتابة أي ملاحظات إضافية هنا...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> تسجيل الحضور
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- جدول سجل الحضور -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>سجل الحضور الشامل (المعلمين والطلاب)
                    </h5>
                </div>
                
                <!-- فلاتر البحث -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.attendance') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">تاريخ الحضور</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ $date ?? now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> تصفية
                            </button>
                            <a href="{{ route('admin.attendance') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-1"></i> إعادة ضبط
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="card-body p-0">
                    @if($attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">اسم المستخدم</th>
                                        <th scope="col">النوع</th>
                                        <th scope="col">تاريخ الحضور</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col">المصدر</th>
                                        <th scope="col">الملاحظات</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $attendance)
                                        <tr>
                                            <td>{{ $attendance['user']->name }}</td>
                                            <td>
                                                @if($attendance['type'] == 'teacher')
                                                    <span class="badge bg-primary">معلم</span>
                                                @else
                                                    <span class="badge bg-info">طالب</span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance['attendance_date']->format('Y-m-d') }}</td>
                                            <td>
                                                @if($attendance['status'] == 'present')
                                                    <span class="badge bg-success">حاضر</span>
                                                @elseif($attendance['status'] == 'absent')
                                                    <span class="badge bg-danger">غائب</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($attendance['source']) && $attendance['source'] == 'location')
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-map-marker-alt me-1"></i> تسجيل مكاني
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-user-edit me-1"></i> تسجيل يدوي
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance['notes'] ?: '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if(!isset($attendance['source']) || $attendance['source'] != 'location')
                                                        <a href="{{ route('admin.attendance.edit', $attendance['id']) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @else
                                                        <button class="btn btn-sm btn-secondary" disabled title="لا يمكن تعديل سجلات الحضور المكاني">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4 mb-4">
                            {{ $attendances->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" alt="لا توجد بيانات" style="width: 120px; opacity: 0.6;">
                            <p class="mt-3 text-muted">لا توجد سجلات حضور في الوقت الحالي</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userTypeSelect = document.getElementById('user_type');
        const userIdSelect = document.getElementById('user_id');
        const userOptions = userIdSelect.querySelectorAll('option[data-type]');
        
        // تحديث قائمة المستخدمين عند تغيير نوع المستخدم
        userTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            userOptions.forEach(option => {
                if (option.getAttribute('data-type') === selectedType) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
            
            userIdSelect.value = '';
        });
        
        // تشغيل الدالة عند تحميل الصفحة
        userTypeSelect.dispatchEvent(new Event('change'));
    });
</script>
@endpush

@endsection 