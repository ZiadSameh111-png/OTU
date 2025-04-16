@extends('layouts.app')

@section('title', 'إدارة الدرجات')

@section('styles')
<style>
    .filter-card {
        border-radius: 10px;
        overflow: hidden;
    }
    .stats-card {
        border-radius: 10px;
        transition: all 0.3s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .stats-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">إدارة الدرجات</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">إدارة الدرجات</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.grades.export') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-file-export me-1"></i> تصدير البيانات
            </a>
            <a href="{{ route('admin.grades.reports') }}" class="btn btn-primary">
                <i class="fas fa-chart-line me-1"></i> التقارير
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm filter-card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.grades.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="course_id" class="form-label">المقرر</label>
                    <select name="course_id" id="course_id" class="form-select">
                        <option value="">جميع المقررات</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="group_id" class="form-label">المجموعة</label>
                    <select name="group_id" id="group_id" class="form-select">
                        <option value="">جميع المجموعات</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="semester" class="form-label">الفصل الدراسي</label>
                    <select name="semester" id="semester" class="form-select">
                        <option value="">جميع الفصول</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester }}" {{ request('semester') == $semester ? 'selected' : '' }}>
                                {{ $semester }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">الحالة</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="complete" {{ request('status') == 'complete' ? 'selected' : '' }}>مكتملة</option>
                        <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>غير مكتملة</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">بحث</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="اسم الطالب أو الرقم...">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                    <a href="{{ route('admin.grades.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i> إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm stats-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-graduation-cap fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">إجمالي الطلاب</p>
                        <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm stats-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">معدل النجاح</p>
                        <h3 class="mb-0">{{ $stats['pass_rate'] }}%</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm stats-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-calculator fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">متوسط الدرجات</p>
                        <h3 class="mb-0">{{ $stats['average_score'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm stats-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">الطلاب المتعثرين</p>
                        <h3 class="mb-0">{{ $stats['at_risk_students'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">قائمة الدرجات</h5>
            <span class="badge bg-primary">{{ $grades->total() }} سجل</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الطالب</th>
                            <th>المقرر</th>
                            <th>المجموعة</th>
                            <th>أعمال المقرر</th>
                            <th>منتصف الفصل</th>
                            <th>النهائي</th>
                            <th>العملي</th>
                            <th>المجموع</th>
                            <th>التقدير</th>
                            <th>الحالة</th>
                            <th>خيارات</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($grades as $grade)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 fw-bold">{{ $grade->student->name }}</p>
                                        <small class="text-muted">{{ $grade->student->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <p class="mb-0">{{ $grade->course->name }}</p>
                                    <small class="text-muted">{{ $grade->course->code }}</small>
                                </div>
                            </td>
                            <td>{{ $grade->student->group->name ?? 'غير محدد' }}</td>
                            <td>{{ $grade->coursework ?? '-' }}/{{ $grade->course->coursework_weight }}</td>
                            <td>{{ $grade->midterm ?? '-' }}/{{ $grade->course->midterm_weight }}</td>
                            <td>{{ $grade->final ?? '-' }}/{{ $grade->course->final_weight }}</td>
                            <td>{{ $grade->practical ?? '-' }}/{{ $grade->course->practical_weight }}</td>
                            <td>
                                <span class="fw-bold">{{ $grade->total_score ?? '-' }}/100</span>
                            </td>
                            <td>
                                <span class="badge {{ getGradeBadgeColor($grade->grade) }}">{{ $grade->grade ?? '-' }}</span>
                            </td>
                            <td>
                                @if($grade->is_complete)
                                    <span class="badge bg-success">مكتملة</span>
                                @else
                                    <span class="badge bg-warning text-dark">غير مكتملة</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        إجراءات
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.grades.edit', $grade->id) }}">
                                                <i class="fas fa-edit me-1"></i> تحرير
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.grades.show', $grade->id) }}">
                                                <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.grades.destroy', $grade->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-1"></i> حذف
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">لا توجد سجلات للعرض</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-end">
                {{ $grades->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('هل أنت متأكد من رغبتك في حذف هذا السجل؟')) {
                this.submit();
            }
        });
    });
});

// Helper function for grade badges (used in Blade)
function getGradeBadgeColor(grade) {
    if (!grade) return 'bg-secondary';
    
    switch(grade) {
        case 'A+':
        case 'A':
        case 'A-':
            return 'bg-success';
        case 'B+':
        case 'B':
        case 'B-':
            return 'bg-info';
        case 'C+':
        case 'C':
        case 'C-':
            return 'bg-warning';
        case 'D+':
        case 'D':
            return 'bg-warning text-dark';
        case 'F':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
</script>
@endsection

@php
// Helper functions
function getGradeBadgeColor($grade) {
    if (!$grade) return 'bg-secondary';
    
    switch($grade) {
        case 'A+':
        case 'A':
        case 'A-':
            return 'bg-success';
        case 'B+':
        case 'B':
        case 'B-':
            return 'bg-info';
        case 'C+':
        case 'C':
        case 'C-':
            return 'bg-warning';
        case 'D+':
        case 'D':
            return 'bg-warning text-dark';
        case 'F':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
@endphp 