@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt"></i> تفاصيل الاختبار
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

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>معلومات أساسية</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 40%">عنوان الاختبار</th>
                                    <td>{{ $exam->title }}</td>
                                </tr>
                                <tr>
                                    <th>المقرر الدراسي</th>
                                    <td>{{ $exam->course->name }}</td>
                                </tr>
                                <tr>
                                    <th>المجموعة</th>
                                    <td>{{ $exam->group->name }}</td>
                                </tr>
                                <tr>
                                    <th>المدرس</th>
                                    <td>{{ $exam->teacher->name }}</td>
                                </tr>
                                <tr>
                                    <th>مدة الاختبار</th>
                                    <td>{{ $exam->duration }} دقيقة</td>
                                </tr>
                                <tr>
                                    <th>عدد الأسئلة</th>
                                    <td>{{ $exam->questions->count() }}</td>
                                </tr>
                                <tr>
                                    <th>إجمالي الدرجات</th>
                                    <td>{{ $exam->total_marks }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>حالة الاختبار</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 40%">الحالة</th>
                                    <td>
                                        @if($exam->status == 'pending')
                                            <span class="badge badge-warning">قيد الإعداد</span>
                                        @elseif($exam->status == 'active')
                                            <span class="badge badge-success">نشط</span>
                                        @elseif($exam->status == 'completed')
                                            <span class="badge badge-secondary">منتهي</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>حالة النشر</th>
                                    <td>
                                        @if($exam->is_published)
                                            <span class="badge badge-success">منشور</span>
                                        @else
                                            <span class="badge badge-secondary">غير منشور</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>حالة الفتح</th>
                                    <td>
                                        @if($exam->is_open)
                                            <span class="badge badge-success">مفتوح</span>
                                        @else
                                            <span class="badge badge-secondary">مغلق</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>نوع الأسئلة</th>
                                    <td>
                                        @switch($exam->question_type)
                                            @case('multiple_choice')
                                                اختيار من متعدد
                                                @break
                                            @case('true_false')
                                                صح وخطأ
                                                @break
                                            @case('open_ended')
                                                أسئلة مقالية
                                                @break
                                            @case('mixed')
                                                مختلط
                                                @break
                                            @default
                                                غير محدد
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>إجمالي المحاولات</th>
                                    <td>{{ $exam->total_attempts }}</td>
                                </tr>
                                <tr>
                                    <th>محاولات تم تقديمها</th>
                                    <td>{{ $exam->submitted_count }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3">قائمة الأسئلة</h5>
                    @if($exam->questions->isEmpty())
                        <div class="alert alert-info">
                            لا توجد أسئلة مضافة لهذا الاختبار.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>نص السؤال</th>
                                        <th>نوع السؤال</th>
                                        <th>الدرجة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exam->questions as $index => $question)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($question->question_text, 100) }}</td>
                                            <td>
                                                @switch($question->question_type)
                                                    @case('multiple_choice')
                                                        اختيار من متعدد
                                                        @break
                                                    @case('true_false')
                                                        صح وخطأ
                                                        @break
                                                    @case('open_ended')
                                                        سؤال مفتوح
                                                        @break
                                                    @default
                                                        غير محدد
                                                @endswitch
                                            </td>
                                            <td>{{ $question->marks }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> عودة إلى قائمة الاختبارات
                        </a>
                        <a href="{{ route('admin.exams.report.detail', $exam->id) }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> عرض تقرير تفصيلي
                        </a>
                        <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الاختبار؟')">
                                <i class="fas fa-trash"></i> حذف الاختبار
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 