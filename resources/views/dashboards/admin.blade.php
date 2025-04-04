@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Welcome & Stats Section -->
    <div class="row mb-4">
        <div class="col-lg-8 col-md-7">
            <div class="card h-100">
                <div class="card-body d-md-flex d-block">
                    <div class="flex-grow-1 position-relative">
                        <div class="position-relative">
                            <span class="badge bg-primary-soft text-primary position-absolute end-0 top-0">مرحباً</span>
                            <h3 class="mb-3 mt-2">مرحباً بك في نظام الجامعة الذكي</h3>
                        </div>
                        <p class="text-muted mb-4">
                            يوفر لك النظام كل ما تحتاجه لإدارة العملية التعليمية بكفاءة عالية
                            وسهولة في الوصول للمعلومات
                        </p>
                        
                        <div class="row mt-4 mb-2">
                            <div class="col-md-4 col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary-soft rounded-circle me-3">
                                        <span class="avatar-title text-primary">
                                            <i class="fas fa-users"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0">{{ $totalUsers }}</h5>
                                        <span class="text-muted fs-sm">إجمالي المستخدمين</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary-soft rounded-circle me-3">
                                        <span class="avatar-title text-primary">
                                            <i class="fas fa-user-graduate"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0">{{ $totalStudents }}</h5>
                                        <span class="text-muted fs-sm">الطلاب</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary-soft rounded-circle me-3">
                                        <span class="avatar-title text-primary">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0">{{ $totalTeachers }}</h5>
                                        <span class="text-muted fs-sm">المدرسين</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary-soft rounded-circle me-3">
                                        <span class="avatar-title text-primary">
                                            <i class="fas fa-user-friends"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0">{{ $totalGroups }}</h5>
                                        <span class="text-muted fs-sm">المجموعات</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6 class="mb-2">نسبة التطور العام للنظام</h6>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 75%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">اكتمال النظام</small>
                                <small class="text-primary fw-bold">75%</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-center ms-md-4 mt-4 mt-md-0 d-none d-md-block">
                        <div class="position-relative">
                            <img src="https://cdn-icons-png.flaticon.com/512/2471/2471543.png" alt="Education" style="width: 160px; filter: drop-shadow(0 10px 10px rgba(0, 225, 180, 0.2));">
                            <div class="position-absolute" style="bottom: 0; right: 0; transform: translate(30%, 30%);">
                                <div class="bg-primary text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 20px; box-shadow: 0 5px 15px rgba(0, 225, 180, 0.5);">
                                    <span>+{{ $totalUsers }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">يونيو 2024</h5>
                    <div class="dropdown">
                        <button class="btn btn-link p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar-alt me-2"></i> عرض التقويم</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-plus me-2"></i> إضافة مناسبة</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-bold text-muted fs-sm">الأحد</div>
                            <div class="fw-bold text-muted fs-sm">السبت</div>
                        </div>
                        <div class="calendar-grid">
                            @foreach(range(1, 30) as $day)
                                <div class="calendar-day {{ $day == 4 ? 'active' : '' }} {{ in_array($day, [13, 19]) ? 'highlight' : '' }}">
                                    {{ $day }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4">
                        <h6 class="mb-3">المناسبات القادمة</h6>
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-3">
                                <div class="bg-primary-soft rounded text-center p-2" style="width: 45px;">
                                    <div class="fs-sm text-muted">يونيو</div>
                                    <div class="fw-bold">19</div>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0">اجتماع مجلس الكلية</h6>
                                <small class="text-muted">10:00 صباحاً - 12:00 ظهراً</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Cards -->
    <div class="row mb-4">
        <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-graduation-cap me-2 text-primary"></i> تقدم المقررات
            </h5>
            <a href="#" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> إضافة مقرر
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3 position-relative">
                    <span class="badge bg-primary position-absolute end-0 top-0 m-3">68%</span>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">تطوير الويب</h5>
                            <span class="d-block text-muted small">
                                <i class="fas fa-user me-1"></i> أحمد محمد
                            </span>
                        </div>
                        <div class="bg-primary-soft p-3 rounded">
                            <i class="fas fa-code text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 68%"></div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <h6 class="mb-0">8</h6>
                            <small class="text-muted d-block">دروس</small>
                        </div>
                        <div class="col-4 border-end">
                            <h6 class="mb-0">24</h6>
                            <small class="text-muted d-block">طالب</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">12</h6>
                            <small class="text-muted d-block">ساعة</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer p-3 border-top bg-primary-soft">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="#" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i> عرض
                        </a>
                        <div>
                            <span class="text-muted small me-2">آخر تحديث:</span>
                            <span class="text-primary small">اليوم</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3 position-relative">
                    <span class="badge bg-primary position-absolute end-0 top-0 m-3">45%</span>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">تصميم واجهات المستخدم</h5>
                            <span class="d-block text-muted small">
                                <i class="fas fa-user me-1"></i> سارة أحمد
                            </span>
                        </div>
                        <div class="bg-primary-soft p-3 rounded">
                            <i class="fas fa-palette text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 45%"></div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <h6 class="mb-0">6</h6>
                            <small class="text-muted d-block">دروس</small>
                        </div>
                        <div class="col-4 border-end">
                            <h6 class="mb-0">18</h6>
                            <small class="text-muted d-block">طالب</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">10</h6>
                            <small class="text-muted d-block">ساعة</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer p-3 border-top bg-primary-soft">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="#" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i> عرض
                        </a>
                        <div>
                            <span class="text-muted small me-2">آخر تحديث:</span>
                            <span class="text-primary small">أمس</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3 position-relative">
                    <span class="badge bg-primary position-absolute end-0 top-0 m-3">75%</span>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">البرمجة المتقدمة</h5>
                            <span class="d-block text-muted small">
                                <i class="fas fa-user me-1"></i> محمد علي
                            </span>
                        </div>
                        <div class="bg-primary-soft p-3 rounded">
                            <i class="fas fa-laptop-code text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 75%"></div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <h6 class="mb-0">10</h6>
                            <small class="text-muted d-block">دروس</small>
                        </div>
                        <div class="col-4 border-end">
                            <h6 class="mb-0">30</h6>
                            <small class="text-muted d-block">طالب</small>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">16</h6>
                            <small class="text-muted d-block">ساعة</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer p-3 border-top bg-primary-soft">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="#" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i> عرض
                        </a>
                        <div>
                            <span class="text-muted small me-2">آخر تحديث:</span>
                            <span class="text-primary small">منذ يومين</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups and Schedules Navigation -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h5 class="mb-0">
                <i class="fas fa-user-friends me-2 text-primary"></i> إدارة النظام
            </h5>
        </div>
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">إدارة المجموعات</h5>
                            <span class="d-block text-muted small">
                                <i class="fas fa-info-circle me-1"></i> عرض وإدارة كل مجموعات النظام
                            </span>
                        </div>
                        <div class="bg-primary-soft p-3 rounded">
                            <i class="fas fa-user-friends text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="row text-center mb-3">
                        <div class="col-6 border-end">
                            <h6 class="mb-0">{{ $totalGroups }}</h6>
                            <small class="text-muted d-block">إجمالي المجموعات</small>
                        </div>
                        <div class="col-6">
                            <h6 class="mb-0">{{ $activeGroups }}</h6>
                            <small class="text-muted d-block">مجموعات نشطة</small>
                        </div>
                    </div>
                    <a href="{{ route('groups.index') }}" class="btn btn-primary w-100">
                        <i class="fas fa-cog me-1"></i> إدارة المجموعات
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body p-3 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">إدارة الجداول الدراسية</h5>
                            <span class="d-block text-muted small">
                                <i class="fas fa-info-circle me-1"></i> عرض وتنظيم جداول المجموعات المختلفة
                            </span>
                        </div>
                        <div class="bg-primary-soft p-3 rounded">
                            <i class="fas fa-calendar-alt text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-2 mb-4">
                        <p class="text-muted mb-0">يمكنك إدارة الجداول الدراسية وتحديد المقررات والأوقات للمجموعات المختلفة</p>
                    </div>
                    <a href="{{ route('schedules.index') }}" class="btn btn-primary w-100">
                        <i class="fas fa-cog me-1"></i> إدارة الجداول الدراسية
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics & Activities -->
    <div class="row">
        <div class="col-lg-8 col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">نشاط النظام</h5>
                        <p class="text-muted mb-0 fs-sm">إحصائيات النشاط على مدار الأشهر الماضية</p>
                    </div>
                    <div>
                        <select class="form-select form-select-sm">
                            <option>هذا الشهر</option>
                            <option>هذا الأسبوع</option>
                            <option>هذا العام</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" class="activity-chart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">المهام القادمة</h5>
                    <a href="#" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item p-3">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 d-flex align-items-center">
                                        <i class="fas fa-circle me-2 text-primary" style="font-size: 8px;"></i>
                                        تحليل البيانات
                                    </h6>
                                    <small class="text-muted d-flex align-items-center mt-1">
                                        <i class="far fa-calendar-alt me-1"></i> اليوم
                                    </small>
                                </div>
                                <span class="badge bg-primary-soft text-primary">25%</span>
                            </div>
                            <div class="d-flex align-items-center mt-3">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: 25%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item p-3">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 d-flex align-items-center">
                                        <i class="fas fa-circle me-2 text-primary" style="font-size: 8px;"></i>
                                        تطوير الواجهة
                                    </h6>
                                    <small class="text-muted d-flex align-items-center mt-1">
                                        <i class="far fa-calendar-alt me-1"></i> غداً
                                    </small>
                                </div>
                                <span class="badge bg-primary-soft text-primary">60%</span>
                            </div>
                            <div class="d-flex align-items-center mt-3">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item p-3">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 d-flex align-items-center">
                                        <i class="fas fa-circle me-2 text-primary" style="font-size: 8px;"></i>
                                        إنشاء المكونات
                                    </h6>
                                    <small class="text-muted d-flex align-items-center mt-1">
                                        <i class="far fa-calendar-alt me-1"></i> الأحد
                                    </small>
                                </div>
                                <span class="badge bg-primary-soft text-primary">45%</span>
                            </div>
                            <div class="d-flex align-items-center mt-3">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: 45%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top p-3">
                    <a href="#" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-tasks me-1"></i> عرض جميع المهام
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Activity Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('activityChart').getContext('2d');
        
        // Create gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(0, 225, 180, 0.2)');
        gradient.addColorStop(1, 'rgba(0, 225, 180, 0.01)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'نشاط المستخدمين',
                    data: [65, 40, 65, 50, 65, 40],
                    borderColor: '#00e1b4',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: gradient,
                    pointBackgroundColor: '#00e1b4',
                    pointBorderColor: '#00e1b4',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#00e1b4',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBorderWidth: 3,
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e2029',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        bodyFont: {
                            family: 'Cairo'
                        },
                        titleFont: {
                            family: 'Cairo',
                            weight: 'bold'
                        },
                        padding: 12,
                        boxPadding: 8,
                        usePointStyle: true,
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return 'النشاط: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#8a8d91',
                            font: {
                                family: 'Cairo'
                            },
                            padding: 10
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#8a8d91',
                            font: {
                                family: 'Cairo'
                            },
                            padding: 10
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                elements: {
                    line: {
                        borderJoinStyle: 'round'
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection 