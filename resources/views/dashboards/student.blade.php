@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>لوحة تحكم الطالب
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                مرحباً {{ $user->name }}! مرحباً بك في نظام الجامعة الذكي.
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-4">الروابط السريعة</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="feature-card">
                                        <div class="feature-icon">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <div class="feature-content">
                                            <h3>المقررات الدراسية</h3>
                                            <p>عرض المقررات المسجلة</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-card">
                                        <div class="feature-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="feature-content">
                                            <h3>الجدول الدراسي</h3>
                                            <p>عرض جدول المحاضرات</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-card">
                                        <div class="feature-icon">
                                            <i class="fas fa-chart-bar"></i>
                                        </div>
                                        <div class="feature-content">
                                            <h3>النتائج</h3>
                                            <p>عرض نتائج الاختبارات</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 