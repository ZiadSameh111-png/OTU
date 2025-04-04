@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">تعديل المجموعة: {{ $group->name }}</h2>
            <a href="{{ route('groups.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-1"></i> العودة إلى قائمة المجموعات
            </a>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('groups.update', $group->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label for="name" class="form-label fw-bold">اسم المجموعة</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $group->name) }}" required autocomplete="off">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label for="active" class="form-label fw-bold">الحالة</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $group->active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">نشط</label>
                            </div>
                            @error('active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-4">
                        <div class="form-group">
                            <label for="description" class="form-label fw-bold">وصف المجموعة</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" 
                                     rows="4">{{ old('description', $group->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('groups.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 