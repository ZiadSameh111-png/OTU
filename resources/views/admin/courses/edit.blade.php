@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">تعديل المقرر</h2>
            <a href="{{ route('admin.courses') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right ml-1"></i> العودة للمقررات
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.courses.update', $course->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">اسم المقرر <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $course->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">رمز المقرر <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $course->code) }}" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">وصف المقرر</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="teacher_id" class="form-label">المدرس</label>
                    <select class="form-select @error('teacher_id') is-invalid @enderror" id="teacher_id" name="teacher_id">
                        <option value="">-- اختر المدرس --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id', $course->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label class="form-label">المجموعات</label>
                    <div class="card">
                        <div class="card-body">
                            @if(count($groups) > 0)
                                <div class="row">
                                    @foreach($groups as $group)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="groups[]" value="{{ $group->id }}" id="group_{{ $group->id }}" 
                                                {{ (is_array(old('groups', $course->groups->pluck('id')->toArray())) && in_array($group->id, old('groups', $course->groups->pluck('id')->toArray()))) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="group_{{ $group->id }}">
                                                    {{ $group->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">لا توجد مجموعات متاحة.</p>
                            @endif
                        </div>
                    </div>
                    @error('groups')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save ml-1"></i> حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 