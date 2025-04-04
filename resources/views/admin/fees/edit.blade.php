@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.fees') }}">إدارة الرسوم</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تعديل الرسوم</li>
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
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2 text-primary"></i>تعديل الرسوم
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.fees.update', $fee) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="user_id" class="form-label">الطالب <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                <option value="" selected disabled>-- اختر الطالب --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('user_id', $fee->user_id) == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="total_amount" class="form-label">المبلغ المستحق <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('total_amount') is-invalid @enderror" id="total_amount" name="total_amount" step="0.01" min="0" value="{{ old('total_amount', $fee->total_amount) }}" required>
                                <span class="input-group-text">ريال</span>
                            </div>
                            @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="due_date" class="form-label">تاريخ الاستحقاق</label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', $fee->due_date ? $fee->due_date->format('Y-m-d') : '') }}">
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_year" class="form-label">العام الدراسي</label>
                            <input type="text" class="form-control @error('academic_year') is-invalid @enderror" id="academic_year" name="academic_year" value="{{ old('academic_year', $fee->academic_year) }}">
                            @error('academic_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="fee_type" class="form-label">نوع الرسوم</label>
                            <select class="form-select @error('fee_type') is-invalid @enderror" id="fee_type" name="fee_type">
                                <option value="tuition" {{ old('fee_type', $fee->fee_type) == 'tuition' ? 'selected' : '' }}>رسوم دراسية</option>
                                <option value="registration" {{ old('fee_type', $fee->fee_type) == 'registration' ? 'selected' : '' }}>رسوم تسجيل</option>
                                <option value="books" {{ old('fee_type', $fee->fee_type) == 'books' ? 'selected' : '' }}>رسوم كتب</option>
                                <option value="transportation" {{ old('fee_type', $fee->fee_type) == 'transportation' ? 'selected' : '' }}>رسوم نقل</option>
                                <option value="other" {{ old('fee_type', $fee->fee_type) == 'other' ? 'selected' : '' }}>رسوم أخرى</option>
                            </select>
                            @error('fee_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف الرسوم</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $fee->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <strong>المبلغ المدفوع:</strong> {{ number_format($fee->paid_amount, 2) }} ريال
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-{{ $fee->remaining_amount > 0 ? 'warning' : 'success' }}">
                                        <strong>المبلغ المتبقي:</strong> {{ number_format($fee->remaining_amount, 2) }} ريال
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.fees') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-1"></i> الرجوع
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 