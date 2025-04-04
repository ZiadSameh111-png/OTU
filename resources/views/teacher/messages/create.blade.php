@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.messages') }}">صندوق الوارد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">إرسال رسالة جديدة</li>
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

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2 text-primary"></i>إرسال رسالة جديدة</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.messages.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="recipient_id" class="form-label">المستلم</label>
                            <select name="recipient_id" id="recipient_id" class="form-select @error('recipient_id') is-invalid @enderror" required>
                                <option value="">-- اختر المستلم --</option>
                                <optgroup label="الطلاب">
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('recipient_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name }} (طالب)
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('recipient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">عنوان الرسالة</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">محتوى الرسالة</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('teacher.messages') }}" class="btn btn-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> إرسال الرسالة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 