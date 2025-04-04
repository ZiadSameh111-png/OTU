@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">تعديل الجدول الدراسي</h2>
            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-1"></i> العودة إلى قائمة الجداول
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

            <form action="{{ route('schedules.update', $schedule->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label for="group_id" class="form-label fw-bold">المجموعة</label>
                            <select id="group_id" name="group_id" class="form-select @error('group_id') is-invalid @enderror" required>
                                <option value="">اختر المجموعة...</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ $schedule->group_id == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label for="course_id" class="form-label fw-bold">المقرر الدراسي</label>
                            <select id="course_id" name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                <option value="">اختر المقرر...</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ $schedule->course_id == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }} ({{ $course->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="form-group">
                            <label for="day" class="form-label fw-bold">اليوم</label>
                            <select id="day" name="day" class="form-select @error('day') is-invalid @enderror" required>
                                <option value="">اختر اليوم...</option>
                                @foreach($days as $day)
                                    <option value="{{ $day }}" {{ $schedule->day == $day ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                            @error('day')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="form-group">
                            <label for="start_time" class="form-label fw-bold">وقت البداية</label>
                            <input type="time" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', date('H:i', strtotime($schedule->start_time))) }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="form-group">
                            <label for="end_time" class="form-label fw-bold">وقت النهاية</label>
                            <input type="time" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time', date('H:i', strtotime($schedule->end_time))) }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-2 mb-4">
                        <div class="form-group">
                            <label for="room" class="form-label fw-bold">القاعة</label>
                            <input type="text" id="room" name="room" class="form-control @error('room') is-invalid @enderror" 
                                   value="{{ old('room', $schedule->room) }}" placeholder="اختياري">
                            @error('room')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
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