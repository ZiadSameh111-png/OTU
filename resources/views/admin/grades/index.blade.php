@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1 text-primary fw-bold">
                <i class="fas fa-chart-bar me-2"></i>تقارير الدرجات
            </h1>
            <p class="text-muted fs-5">مراقبة ومتابعة الدرجات لجميع المقررات والمجموعات الدراسية</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2 text-primary"></i>حالة تسليم الدرجات
                    </h5>
                    <div class="filters">
                        <select class="form-select form-select-sm" id="group-filter">
                            <option value="all" selected>جميع المجموعات</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($courses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">م</th>
                                        <th width="15%">كود المقرر</th>
                                        <th width="25%">اسم المقرر</th>
                                        <th width="20%">أستاذ المقرر</th>
                                        <th width="15%">عدد الطلاب</th>
                                        <th width="15%">حالة التسليم</th>
                                        <th width="5%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courses as $i => $course)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $course->code ?? 'بدون كود' }}</td>
                                            <td>{{ $course->name }}</td>
                                            <td>{{ $course->teacher->name ?? 'غير محدد' }}</td>
                                            <td>{{ $courseStats[$course->id]['total'] }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        @php
                                                            $percentage = $courseStats[$course->id]['percentage'];
                                                            $progressClass = 'bg-danger';
                                                            if($percentage >= 70) $progressClass = 'bg-success';
                                                            elseif($percentage >= 30) $progressClass = 'bg-warning';
                                                        @endphp
                                                        <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                                            style="width: {{ $percentage }}%;" 
                                                            aria-valuenow="{{ $percentage }}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100"></div>
                                                    </div>
                                                    <span>{{ $percentage }}%</span>
                                                </div>
                                                <small class="text-muted d-block">
                                                    {{ $courseStats[$course->id]['submitted'] }} من {{ $courseStats[$course->id]['total'] }}
                                                </small>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.grades.view', $course->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2866/2866906.png" alt="لا توجد مقررات" style="width: 120px; opacity: 0.5;">
                            <p class="mt-4 text-muted">لا توجد مقررات دراسية مسجلة في النظام</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-award me-2 text-primary"></i>ملخص إحصائيات الدرجات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-3 text-center h-100">
                                <div class="display-6 mb-2 text-primary">{{ $courses->count() }}</div>
                                <div class="text-muted">إجمالي المقررات</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-3 text-center h-100">
                                <div class="display-6 mb-2 text-primary">{{ $groups->count() }}</div>
                                <div class="text-muted">إجمالي المجموعات</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-3 text-center h-100">
                                @php
                                    $totalStudents = 0;
                                    foreach($groups as $group) {
                                        $totalStudents += $group->students->count();
                                    }
                                @endphp
                                <div class="display-6 mb-2 text-primary">{{ $totalStudents }}</div>
                                <div class="text-muted">إجمالي الطلاب</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-3 text-center h-100">
                                @php
                                    $submittedCount = 0;
                                    $totalCount = 0;
                                    foreach($courseStats as $stat) {
                                        $submittedCount += $stat['submitted'];
                                        $totalCount += $stat['total'];
                                    }
                                    $overallPercentage = $totalCount > 0 ? round(($submittedCount / $totalCount) * 100) : 0;
                                @endphp
                                <div class="display-6 mb-2 text-primary">{{ $overallPercentage }}%</div>
                                <div class="text-muted">نسبة إكمال الدرجات</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2 text-primary"></i>مراحل تسليم الدرجات
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $notStarted = 0;
                        $inProgress = 0;
                        $completed = 0;

                        foreach ($courseStats as $stat) {
                            if ($stat['percentage'] == 0) {
                                $notStarted++;
                            } elseif ($stat['percentage'] < 100) {
                                $inProgress++;
                            } else {
                                $completed++;
                            }
                        }
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">لم يتم البدء</span>
                            <span class="badge bg-secondary">{{ $notStarted }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-secondary" role="progressbar" 
                                style="width: {{ $courses->count() > 0 ? ($notStarted / $courses->count() * 100) : 0 }}%;" 
                                aria-valuenow="{{ $notStarted }}" 
                                aria-valuemin="0" 
                                aria-valuemax="{{ $courses->count() }}"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">قيد التنفيذ</span>
                            <span class="badge bg-warning text-dark">{{ $inProgress }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                style="width: {{ $courses->count() > 0 ? ($inProgress / $courses->count() * 100) : 0 }}%;" 
                                aria-valuenow="{{ $inProgress }}" 
                                aria-valuemin="0" 
                                aria-valuemax="{{ $courses->count() }}"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold">مكتملة</span>
                            <span class="badge bg-success">{{ $completed }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                style="width: {{ $courses->count() > 0 ? ($completed / $courses->count() * 100) : 0 }}%;" 
                                aria-valuenow="{{ $completed }}" 
                                aria-valuemin="0" 
                                aria-valuemax="{{ $courses->count() }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // Group filter logic
        $('#group-filter').on('change', function() {
            const groupId = $(this).val();
            if (groupId === 'all') {
                $('table tbody tr').show();
            } else {
                // This is a simplified version - in a real app you'd need to reload data or use a more sophisticated approach
                // For demo purposes only
                $('table tbody tr').hide();
                $('table tbody tr').each(function() {
                    if ($(this).data('group-id') == groupId) {
                        $(this).show();
                    }
                });
            }
        });
    });
</script>
@endsection

@endsection 