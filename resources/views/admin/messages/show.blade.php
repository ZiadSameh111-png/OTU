@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="mb-1 fw-bold text-primary">
                <i class="fas fa-envelope me-2"></i>عرض الرسالة
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.messages') }}">الرسائل</a></li>
                    <li class="breadcrumb-item active" aria-current="page">عرض الرسالة</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">تفاصيل الرسالة</h5>
                        <span class="badge bg-secondary">{{ \Carbon\Carbon::parse($message->created_at)->format('Y-m-d H:i A') }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">المرسل</h6>
                                <p class="mb-0 text-muted">{{ $sender->name ?? 'النظام' }}</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="bg-info text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">المستلم</h6>
                                <p class="mb-0 text-muted">{{ $message->receiver_name }}</p>
                            </div>
                        </div>
                        
                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="fw-bold">عنوان الرسالة</h6>
                            <p class="fs-5">{{ $message->subject }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold">محتوى الرسالة</h6>
                            <div class="p-4 bg-light rounded mt-2">
                                {{ $message->content }}
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.messages') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-right me-2"></i>العودة للرسائل
                            </a>
                            
                            <div>
                                @if($canReply)
                                <a href="{{ route('admin.messages.reply', $message->id) }}" class="btn btn-primary me-2">
                                    <i class="fas fa-reply me-2"></i>الرد
                                </a>
                                @endif
                                
                                <a href="{{ route('admin.messages.create') }}" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i>رسالة جديدة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 