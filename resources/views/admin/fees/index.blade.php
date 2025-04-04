@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">إدارة الرسوم</li>
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
        <!-- نموذج تسجيل دفعة جديدة -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-hand-holding-usd me-2 text-primary"></i>تسجيل دفعة جديدة
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.fees.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="student_id" class="form-label">الطالب <span class="text-danger">*</span></label>
                            <select class="form-select @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required>
                                <option value="" selected disabled>-- اختر الطالب --</option>
                                @if(isset($students) && count($students) > 0)
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="total_amount" class="form-label">المبلغ المستحق <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('total_amount') is-invalid @enderror" id="total_amount" name="total_amount" step="0.01" min="0" value="{{ old('total_amount') }}" required>
                                <span class="input-group-text">ريال</span>
                            </div>
                            @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">المبلغ المدفوع</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('paid_amount') is-invalid @enderror" id="paid_amount" name="paid_amount" step="0.01" min="0" value="{{ old('paid_amount', 0) }}">
                                <span class="input-group-text">ريال</span>
                            </div>
                            @error('paid_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="due_date" class="form-label">تاريخ الاستحقاق</label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+1 month'))) }}">
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_year" class="form-label">العام الدراسي</label>
                            <input type="text" class="form-control @error('academic_year') is-invalid @enderror" id="academic_year" name="academic_year" value="{{ old('academic_year', date('Y').'/'.((int)date('Y')+1)) }}">
                            @error('academic_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="fee_type" class="form-label">نوع الرسوم</label>
                            <select class="form-select @error('fee_type') is-invalid @enderror" id="fee_type" name="fee_type">
                                <option value="tuition" {{ old('fee_type') == 'tuition' ? 'selected' : '' }}>رسوم دراسية</option>
                                <option value="registration" {{ old('fee_type') == 'registration' ? 'selected' : '' }}>رسوم تسجيل</option>
                                <option value="books" {{ old('fee_type') == 'books' ? 'selected' : '' }}>رسوم كتب</option>
                                <option value="transportation" {{ old('fee_type') == 'transportation' ? 'selected' : '' }}>رسوم نقل</option>
                                <option value="other" {{ old('fee_type') == 'other' ? 'selected' : '' }}>رسوم أخرى</option>
                            </select>
                            @error('fee_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف الرسوم</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> تسجيل الرسوم
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- جدول الرسوم -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>سجل الرسوم
                    </h5>
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> تصفية
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item {{ request()->is('admin/fees') && !request('filter') ? 'active' : '' }}" href="{{ route('admin.fees') }}">جميع الطلاب</a></li>
                            <li><a class="dropdown-item {{ request('filter') == 'remaining' ? 'active' : '' }}" href="{{ route('admin.fees', ['filter' => 'remaining']) }}">لديهم مبالغ متبقية</a></li>
                            <li><a class="dropdown-item {{ request('filter') == 'paid' ? 'active' : '' }}" href="{{ route('admin.fees', ['filter' => 'paid']) }}">تم دفع الرسوم بالكامل</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($fees->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col">اسم الطالب</th>
                                        <th scope="col">المبلغ المستحق</th>
                                        <th scope="col">المبلغ المدفوع</th>
                                        <th scope="col">المبلغ المتبقي</th>
                                        <th scope="col">تاريخ آخر دفعة</th>
                                        <th scope="col">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fees as $fee)
                                        <tr>
                                            <td>{{ $fee->student->name }}</td>
                                            <td>{{ number_format($fee->total_amount, 2) }} ريال</td>
                                            <td>{{ number_format($fee->paid_amount, 2) }} ريال</td>
                                            <td>
                                                <span class="{{ $fee->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($fee->remaining_amount, 2) }} ريال
                                                </span>
                                            </td>
                                            <td>{{ $fee->last_payment_date ? $fee->last_payment_date->format('Y-m-d') : '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.fees.payments', $fee) }}" class="btn btn-sm btn-primary" title="عرض الدفعات">
                                                        <i class="fas fa-list"></i>
                                                    </a>
                                                    <a href="{{ route('admin.fees.edit', $fee) }}" class="btn btn-sm btn-secondary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($fee->remaining_amount > 0)
                                                        <a href="{{ route('admin.fees.payments.create', $fee) }}" class="btn btn-sm btn-success" title="تسجيل دفعة">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4 mb-4">
                            {{ $fees->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" alt="لا توجد بيانات" style="width: 120px; opacity: 0.6;">
                            <p class="mt-3 text-muted">لا توجد سجلات رسوم في الوقت الحالي</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 