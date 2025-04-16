@extends('layouts.app')

@section('title', 'إدارة إعدادات المواقع')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">إعدادات المواقع</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة موقع جديد
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>خط العرض</th>
                                    <th>خط الطول</th>
                                    <th>النطاق (متر)</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($locations as $location)
                                    <tr>
                                        <td>{{ $location->id }}</td>
                                        <td>{{ $location->name }}</td>
                                        <td>{{ $location->latitude }}</td>
                                        <td>{{ $location->longitude }}</td>
                                        <td>{{ $location->range_meters }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الموقع؟')">
                                                        <i class="fas fa-trash"></i> حذف
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if(count($locations) == 0)
                                    <tr>
                                        <td colspan="6" class="text-center">لا توجد مواقع مسجلة</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 