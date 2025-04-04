@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-pen"></i> تصحيح الاختبارات
                    </h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">تصفية الاختبارات</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('teacher.exams.grading') }}">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="course_id">المقرر</label>
                                        <select class="form-control" id="course_id" name="course_id">
                                            <option value="">جميع المقررات</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                                    {{ $course->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="status">حالة التصحيح</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>بانتظار التصحيح</option>
                                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد التصحيح</option>
                                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>تم التصحيح</option>
                                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>الكل</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 align-self-end mb-3">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-filter"></i> تصفية
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">بانتظار التصحيح</h5>
                                    <p class="card-text display-4">{{ $stats['pending'] }}</p>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('teacher.exams.grading', ['status' => 'pending']) }}" class="btn btn-light btn-sm w-100">عرض</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">قيد التصحيح</h5>
                                    <p class="card-text display-4">{{ $stats['in_progress'] }}</p>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('teacher.exams.grading', ['status' => 'in_progress']) }}" class="btn btn-light btn-sm w-100">عرض</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">تم التصحيح</h5>
                                    <p class="card-text display-4">{{ $stats['completed'] }}</p>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('teacher.exams.grading', ['status' => 'completed']) }}" class="btn btn-light btn-sm w-100">عرض</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attempts Table -->
                    @if(count($attempts) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>الطالب</th>
                                        <th>عنوان الاختبار</th>
                                        <th>المقرر</th>
                                        <th>تاريخ التقديم</th>
                                        <th>عدد الأسئلة</th>
                                        <th>الأسئلة المصححة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attempts as $attempt)
                                        <tr>
                                            <td>{{ $attempt->student->name }}</td>
                                            <td>{{ $attempt->exam->title }}</td>
                                            <td>{{ $attempt->exam->course->name }}</td>
                                            <td>{{ $attempt->submit_time ? $attempt->submit_time->format('Y-m-d h:i A') : 'غير محدد' }}</td>
                                            <td>{{ $attempt->exam->questions->count() }}</td>
                                            <td>{{ $attempt->gradedQuestionsCount() }} / {{ $attempt->openEndedQuestionsCount() }}</td>
                                            <td>
                                                @if($attempt->is_graded)
                                                    <span class="badge badge-success">تم التصحيح</span>
                                                @elseif($attempt->hasOpenEndedQuestions() && $attempt->gradedQuestionsCount() > 0)
                                                    <span class="badge badge-info">قيد التصحيح</span>
                                                @elseif($attempt->needsGrading())
                                                    <span class="badge badge-warning">بانتظار التصحيح</span>
                                                @else
                                                    <span class="badge badge-secondary">لا يحتاج للتصحيح</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('teacher.exams.grade', $attempt->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-pen"></i> تصحيح
                                                </a>
                                                <a href="{{ route('teacher.exams.attempt.view', $attempt->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $attempts->appends(request()->except('page'))->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-lg mb-3"></i>
                            <p>لا توجد اختبارات تنتظر التصحيح حالياً.</p>
                            <a href="{{ route('teacher.exams.index') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-list"></i> عودة إلى قائمة الاختبارات
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tips Section -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">نصائح للتصحيح</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-lightbulb text-warning"></i> التصحيح التلقائي</h5>
                                    <p class="card-text">الأسئلة متعددة الخيارات وأسئلة الصح والخطأ يتم تصحيحها تلقائياً. فقط الأسئلة المفتوحة تحتاج إلى تصحيح يدوي.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-comments text-info"></i> التعليقات</h5>
                                    <p class="card-text">يمكنك إضافة تعليقات توضيحية لكل سؤال لمساعدة الطلاب على فهم التقييم.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-exclamation-triangle text-danger"></i> تنبيه هام</h5>
                                    <p class="card-text">بمجرد الانتهاء من تصحيح جميع أسئلة الاختبار، يمكن للطالب رؤية النتيجة النهائية والملاحظات. يرجى التأكد من مراجعة التصحيح قبل حفظه.</p>
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
