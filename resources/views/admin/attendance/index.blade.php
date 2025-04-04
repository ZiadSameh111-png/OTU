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
                            <label for="teacher_id" class="form-label">اسم الدكتور <span class="text-danger">*</span></label>
                            <select class="form-select @error('teacher_id') is-invalid @enderror" id="teacher_id" name="teacher_id" required>
                                <option value="" selected disabled>-- اختر الدكتور --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id')
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
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>سجل الحضور
                    </h5>
                    
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('admin.attendance') }}" class="d-flex gap-2">
                            <input type="date" class="form-control form-control-sm" name="date" value="{{ request('date', now()->format('Y-m-d')) }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-filter me-1"></i> تصفية حسب التاريخ
                            </button>
                        </form>
                        
                        <a href="{{ route('admin.attendance') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-sync-alt me-1"></i> عرض الكل
                        </a>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">اسم الدكتور</th>
                                        <th scope="col">تاريخ الحضور</th>
                                        <th scope="col">الحالة</th>
                                        <th scope="col">الملاحظات</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $attendance)
                                        <tr>
                                            <td>{{ $attendance->teacher->name }}</td>
                                            <td>{{ $attendance->attendance_date->format('Y-m-d') }}</td>
                                            <td>
                                                @if($attendance->status == 'present')
                                                    <span class="badge bg-success">حاضر</span>
                                                @elseif($attendance->status == 'absent')
                                                    <span class="badge bg-danger">غائب</span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance->notes ?: '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.attendance.edit', $attendance) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
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
@endsection 