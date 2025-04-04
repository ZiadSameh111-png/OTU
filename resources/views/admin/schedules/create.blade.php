@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">إضافة جدول دراسي جديد</h2>
                <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-1"></i> العودة إلى الجداول
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('schedules.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course_id">المقرر الدراسي</label>
                                    <select name="course_id" id="course_id" class="form-control @error('course_id') is-invalid @enderror" required>
                                        <option value="">اختر المقرر...</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->name }} ({{ $course->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group_id">المجموعة</label>
                                    <select name="group_id" id="group_id" class="form-control @error('group_id') is-invalid @enderror" required>
                                        <option value="">اختر المجموعة...</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('group_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="day">اليوم</label>
                                    <select name="day" id="day" class="form-control @error('day') is-invalid @enderror" required>
                                        <option value="">اختر اليوم...</option>
                                        @foreach($days as $day)
                                            <option value="{{ $day }}" {{ old('day') == $day ? 'selected' : '' }}>
                                                {{ $day }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('day')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_time">وقت البداية</label>
                                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" class="form-control @error('start_time') is-invalid @enderror" required>
                                    @error('start_time')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_time">وقت النهاية</label>
                                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" class="form-control @error('end_time') is-invalid @enderror" required>
                                    @error('end_time')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="room">القاعة (اختياري)</label>
                                    <input type="text" name="room" id="room" value="{{ old('room') }}" class="form-control @error('room') is-invalid @enderror" placeholder="أدخل اسم أو رقم القاعة">
                                    @error('room')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> إنشاء الجدول
                                </button>
                                <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                                    إلغاء
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 