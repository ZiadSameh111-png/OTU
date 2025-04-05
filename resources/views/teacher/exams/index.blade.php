@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-file-alt"></i> إدارة الاختبارات
                    </h4>
                    <a href="{{ route('teacher.exams.create') }}" class="btn btn-light">
                        <i class="fas fa-plus"></i> إنشاء اختبار جديد
                    </a>
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

                    @if(count($exams) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>عنوان الاختبار</th>
                                        <th>المقرر</th>
                                        <th>المجموعة</th>
                                        <th>المدة (دقيقة)</th>
                                        <th>الحالة</th>
                                        <th>عدد الأسئلة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exams as $exam)
                                        <tr>
                                            <td>{{ $exam->title }}</td>
                                            <td>{{ $exam->course->name }}</td>
                                            <td>{{ $exam->group->name }}</td>
                                            <td>{{ $exam->duration }}</td>
                                            <td>
                                                @if($exam->status === 'pending')
                                                    <span class="badge badge-info">قادم</span>
                                                @elseif($exam->status === 'active')
                                                    <span class="badge badge-success">نشط</span>
                                                @elseif($exam->status === 'completed')
                                                    <span class="badge badge-secondary">منتهي</span>
                                                @endif
                                                
                                                @if($exam->is_published)
                                                    <span class="badge badge-primary">منشور</span>
                                                @else
                                                    <span class="badge badge-warning">غير منشور</span>
                                                @endif
                                            </td>
                                            <td>{{ $exam->questions->count() }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('teacher.exams.edit', $exam->id) }}" class="btn btn-sm btn-info mr-1">
                                                        <i class="fas fa-edit"></i> تعديل
                                                    </a>
                                                    
                                                    @if($exam->is_published)
                                                        <form action="{{ route('teacher.exams.unpublish', $exam->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من إلغاء نشر هذا الاختبار؟')">
                                                                <i class="fas fa-ban"></i> إلغاء النشر
                                                            </button>
                                                        </form>
                                                        
                                                        @if($exam->is_open)
                                                            <form action="{{ route('teacher.exams.close', $exam->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PUT')
                                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('هل أنت متأكد من إغلاق هذا الاختبار؟')">
                                                                    <i class="fas fa-lock"></i> إغلاق الاختبار
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form action="{{ route('teacher.exams.open', $exam->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PUT')
                                                                <button type="submit" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-unlock"></i> فتح الاختبار
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        <form action="{{ route('teacher.exams.publish', $exam->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check"></i> نشر
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    <form action="{{ route('teacher.exams.destroy', $exam->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الاختبار نهائياً؟ لا يمكن التراجع عن هذه العملية!')">
                                                            <i class="fas fa-trash"></i> حذف
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-lg mb-3"></i>
                            <p>لم تقم بإنشاء أي اختبارات بعد.</p>
                            <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus"></i> إنشاء اختبار جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie"></i> إحصائيات الاختبارات
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center mb-3">
                                    <div class="h5">إجمالي الاختبارات</div>
                                    <div class="display-4">{{ count($exams) }}</div>
                                </div>
                                <div class="col-6 text-center mb-3">
                                    <div class="h5">الاختبارات النشطة</div>
                                    <div class="display-4">{{ $exams->where('status', 'active')->count() }}</div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="h5">الاختبارات القادمة</div>
                                    <div class="display-4">{{ $exams->where('status', 'pending')->count() }}</div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="h5">الاختبارات المنتهية</div>
                                    <div class="display-4">{{ $exams->where('status', 'completed')->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mt-3 mt-md-0">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-tasks"></i> روابط سريعة
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <a href="{{ route('teacher.exams.create') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-plus-circle text-primary"></i> إنشاء اختبار جديد
                                </a>
                                <a href="{{ route('teacher.exams.grading') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-pen text-success"></i> تصحيح الاختبارات
                                </a>
                                <a href="{{ route('teacher.dashboard') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-book text-info"></i> إدارة المقررات
                                </a>
                                <a href="{{ route('teacher.dashboard') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-home text-secondary"></i> العودة للوحة التحكم
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
