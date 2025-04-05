@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0">درجاتي</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="filters">
                                <form action="{{ route('student.grades.index') }}" method="GET" class="form-inline justify-content-end">
                                    <div class="form-group mb-2">
                                        <label for="status" class="ml-2">فلترة حسب:</label>
                                        <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                                            <option value="">الكل</option>
                                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>الدرجات المكتملة</option>
                                            <option value="not_submitted" {{ request('status') == 'not_submitted' ? 'selected' : '' }}>الدرجات غير المكتملة</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
        </div>
    </div>

                    @if($grades->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle ml-1"></i>
                            لا توجد درجات لعرضها حاليًا.
        </div>
                    @else
                <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>المقرر</th>
                                        <th>رمز المقرر</th>
                                        <th>أستاذ المقرر</th>
                                        <th>مجموع الدرجات</th>
                                        <th>التقدير</th>
                                        <th>الحالة</th>
                                        <th>التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                                    @foreach($grades as $grade)
                                        @php
                                            // Calculate total grades
                                            $totalGrade = $grade->midterm_grade + $grade->assignment_grade + $grade->final_grade;
                                            $totalPossible = $grade->course->midterm_grade + $grade->course->assignment_grade + $grade->course->final_grade;
                                            $percentage = $totalPossible > 0 ? ($totalGrade / $totalPossible) * 100 : 0;

                                            // Get letter grade
                                            $letterGrade = $grade->getLetterGradeAttribute();
                                            
                                            // Determine badge class based on letter grade
                                            $badgeClass = 'badge-secondary';
                                            if ($grade->submitted) {
                                                if (in_array($letterGrade, ['A+', 'A'])) {
                                                    $badgeClass = 'badge-success';
                                                } elseif (in_array($letterGrade, ['B+', 'B'])) {
                                                    $badgeClass = 'badge-primary';
                                                } elseif (in_array($letterGrade, ['C+', 'C'])) {
                                                    $badgeClass = 'badge-info';
                                                } elseif (in_array($letterGrade, ['D+', 'D'])) {
                                                    $badgeClass = 'badge-warning';
                                                } elseif ($letterGrade == 'F') {
                                                    $badgeClass = 'badge-danger';
                                                }
                                    }
                                @endphp
                                <tr>
                                            <td>{{ $grade->course->name }}</td>
                                            <td>{{ $grade->course->code }}</td>
                                            <td>{{ $grade->course->teacher ? $grade->course->teacher->name : 'غير محدد' }}</td>
                                            <td>
                                                @if($grade->submitted)
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 ml-2" style="height: 8px;">
                                                            <div class="progress-bar bg-{{ $percentage >= 60 ? ($percentage >= 80 ? 'success' : 'info') : 'danger' }}" 
                                                                role="progressbar" 
                                                                style="width: {{ $percentage }}%;" 
                                                                aria-valuenow="{{ $percentage }}" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <span>{{ $totalGrade }}/{{ $totalPossible }}</span>
                                                    </div>
                                        @else
                                                    <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                                @if($grade->submitted)
                                                    <span class="badge {{ $badgeClass }}">{{ $letterGrade }}</span>
                                        @else
                                                    <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                                @if($grade->submitted)
                                                    <span class="badge badge-success">تم تقديم الدرجات</span>
                                        @else
                                                    <span class="badge badge-warning">لم يتم التقديم بعد</span>
                                        @endif
                                    </td>
                                    <td>
                                                <a href="{{ route('student.grades.details', $grade->course->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye ml-1"></i> التفاصيل
                                                </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">الإحصائيات</h5>
            </div>
                                    <div class="card-body">
                                        @php
                                            $submittedCount = $grades->where('submitted', true)->count();
                                            $pendingCount = $grades->where('submitted', false)->count();
                                            $totalCount = $grades->count();
                                            
                                            $passedCount = 0;
                                            $totalGradePoints = 0;
                                            $totalCreditHours = 0;
                                            
                                            foreach ($grades as $grade) {
                                                if ($grade->submitted) {
                                                    $totalGrade = $grade->midterm_grade + $grade->assignment_grade + $grade->final_grade;
                                                    $totalPossible = $grade->course->midterm_grade + $grade->course->assignment_grade + $grade->course->final_grade;
                                                    $percentage = $totalPossible > 0 ? ($totalGrade / $totalPossible) * 100 : 0;
                                                    
                                                    if ($percentage >= 60) {
                                                        $passedCount++;
                                                    }
                                                    
                                                    // Calculate GPA points
                                                    $gradePoint = 0;
                                                    if ($percentage >= 95) $gradePoint = 4.0;
                                                    elseif ($percentage >= 90) $gradePoint = 3.75;
                                                    elseif ($percentage >= 85) $gradePoint = 3.5;
                                                    elseif ($percentage >= 80) $gradePoint = 3.0;
                                                    elseif ($percentage >= 75) $gradePoint = 2.5;
                                                    elseif ($percentage >= 70) $gradePoint = 2.0;
                                                    elseif ($percentage >= 65) $gradePoint = 1.5;
                                                    elseif ($percentage >= 60) $gradePoint = 1.0;
                                                    
                                                    $creditHours = $grade->course->credit_hours ?? 3;
                                                    $totalGradePoints += ($gradePoint * $creditHours);
                                                    $totalCreditHours += $creditHours;
                                                }
                                            }
                                            
                                            $successRate = $submittedCount > 0 ? round(($passedCount / $submittedCount) * 100) : 0;
                                            $gpa = $totalCreditHours > 0 ? round($totalGradePoints / $totalCreditHours, 2) : 0;
                                @endphp
                                        
                                        <p><strong>إجمالي المقررات:</strong> {{ $totalCount }}</p>
                                        <p><strong>المقررات المكتملة:</strong> {{ $submittedCount }} ({{ $totalCount > 0 ? round(($submittedCount / $totalCount) * 100) : 0 }}%)</p>
                                        <p><strong>المقررات المتبقية:</strong> {{ $pendingCount }}</p>
                                        <p><strong>نسبة النجاح:</strong> {{ $successRate }}%</p>
                                        <p><strong>المعدل التراكمي التقريبي:</strong> {{ $gpa }} من 4.0</p>
                            </div>
                        </div>
                    </div>
                            
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">تفسير التقديرات</h5>
                                    </div>
                            <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><span class="badge badge-success">A+</span> - ممتاز مرتفع (95% فأعلى)</li>
                                                    <li><span class="badge badge-success">A</span> - ممتاز (90% - 94.9%)</li>
                                                    <li><span class="badge badge-primary">B+</span> - جيد جداً مرتفع (85% - 89.9%)</li>
                                                    <li><span class="badge badge-primary">B</span> - جيد جداً (80% - 84.9%)</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><span class="badge badge-info">C+</span> - جيد مرتفع (75% - 79.9%)</li>
                                                    <li><span class="badge badge-info">C</span> - جيد (70% - 74.9%)</li>
                                                    <li><span class="badge badge-warning">D+</span> - مقبول مرتفع (65% - 69.9%)</li>
                                                    <li><span class="badge badge-warning">D</span> - مقبول (60% - 64.9%)</li>
                                                    <li><span class="badge badge-danger">F</span> - راسب (أقل من 60%)</li>
                                                </ul>
                                            </div>
                                </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                </div>
            </div>
        </div>
</div>
@endsection 