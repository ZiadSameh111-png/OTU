@extends('layouts.app')

@section('title', 'إضافة موقع جديد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">إضافة موقع جديد</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.locations.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">اسم الموقع</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">خط العرض</label>
                                    <input type="number" step="0.0000001" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" required>
                                    @error('latitude')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">خط الطول</label>
                                    <input type="number" step="0.0000001" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" required>
                                    @error('longitude')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="range_meters">النطاق (متر)</label>
                            <input type="number" name="range_meters" id="range_meters" class="form-control @error('range_meters') is-invalid @enderror" value="{{ old('range_meters', 100) }}" required min="10">
                            @error('range_meters')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">تحديد الموقع على الخريطة</h3>
            </div>
            <div class="card-body">
                <div id="map" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- تضمين Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<!-- تضمين Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    let map;
    let marker;
    let circle;

    document.addEventListener('DOMContentLoaded', function() {
        // الإحداثيات الافتراضية (الرياض، المملكة العربية السعودية)
        const defaultLat = 24.7136;
        const defaultLng = 46.6753;
        
        // تهيئة الخريطة
        map = L.map('map').setView([defaultLat, defaultLng], 15);
        
        // إضافة طبقة الخريطة من OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // إضافة علامة يمكن سحبها
        marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);
        
        // تحديث الإحداثيات عند تحريك العلامة
        marker.on('dragend', function() {
            const position = marker.getLatLng();
            document.getElementById("latitude").value = position.lat.toFixed(7);
            document.getElementById("longitude").value = position.lng.toFixed(7);
            updateCircle();
        });
        
        // إضافة دائرة لتمثيل النطاق
        const radius = parseInt(document.getElementById('range_meters').value);
        circle = L.circle([defaultLat, defaultLng], {
            radius: radius,
            color: '#3388ff',
            fillColor: '#3388ff',
            fillOpacity: 0.2
        }).addTo(map);
        
        // السماح بالنقر على الخريطة لتحريك العلامة
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById("latitude").value = e.latlng.lat.toFixed(7);
            document.getElementById("longitude").value = e.latlng.lng.toFixed(7);
            updateCircle();
        });
        
        // تحديث الخريطة عند تغيير الإحداثيات يدويًا
        document.getElementById('latitude').addEventListener('change', updateMarkerPosition);
        document.getElementById('longitude').addEventListener('change', updateMarkerPosition);
        document.getElementById('range_meters').addEventListener('change', updateCircle);
    });

    function updateMarkerPosition() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);
        
        if (!isNaN(lat) && !isNaN(lng)) {
            const newPosition = L.latLng(lat, lng);
            marker.setLatLng(newPosition);
            map.setView(newPosition);
            updateCircle();
        }
    }
    
    function updateCircle() {
        const position = marker.getLatLng();
        const radius = parseInt(document.getElementById('range_meters').value);
        
        if (!isNaN(radius) && radius >= 10) {
            circle.setLatLng(position);
            circle.setRadius(radius);
        }
    }
</script>
@endpush 