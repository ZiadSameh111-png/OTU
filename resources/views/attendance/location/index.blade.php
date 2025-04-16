@extends('layouts.app')

@section('title', 'تسجيل الحضور المكاني')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1 fw-bold">
                <i class="fas fa-map-marker-alt text-primary"></i> تسجيل الحضور المكاني
            </h1>
            <p class="text-muted">يمكنك تسجيل حضورك من خلال تحديد موقعك الحالي ومطابقته مع الموقع المطلوب</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-times-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- تسجيل الحضور -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-check me-2 text-primary"></i> تسجيل الحضور
                    </h5>
                </div>
                <div class="card-body">
                    @if($locations->count() > 0)
                        <form id="attendanceForm" action="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.store') : route('teacher.location-attendance.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            
                            <div class="form-group mb-4">
                                <label for="location_id" class="form-label">اختر الموقع</label>
                                <select name="location_id" id="location_id" class="form-select" required>
                                    <option value="">-- اختر الموقع --</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" data-lat="{{ $location->latitude }}" data-lng="{{ $location->longitude }}" data-range="{{ $location->range_meters }}">
                                            {{ $location->name }} (النطاق: {{ $location->range_meters }} متر)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="text-center mb-4">
                                <button type="button" id="getLocationBtn" class="btn btn-primary">
                                    <i class="fas fa-map-marker-alt me-2"></i> تحديد موقعي الحالي
                                </button>
                            </div>
                            
                            <div id="locationDetails" class="d-none">
                                <div class="alert alert-info mb-4">
                                    <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i> معلومات الموقع</h6>
                                    <p class="mb-0">موقعك الحالي: <span id="currentLocation">-</span></p>
                                    <p class="mb-0">المسافة بين موقعك والموقع المطلوب: <span id="distanceInfo">-</span></p>
                                    <p class="mb-0">الحالة: <span id="statusInfo">-</span></p>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" id="submitBtn" class="btn btn-success">
                                        <i class="fas fa-check-circle me-2"></i> تسجيل الحضور
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> لا توجد مواقع مسجلة في النظام.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- الخريطة -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-map me-2 text-primary"></i> الخريطة
                    </h5>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 400px; border-radius: 10px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- حضور اليوم -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2 text-primary"></i> سجل حضور اليوم
                    </h5>
                    <a href="{{ auth()->user()->hasRole('Student') ? route('student.location-attendance.history') : route('teacher.location-attendance.history') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calendar-alt me-1"></i> عرض السجل الكامل
                    </a>
                </div>
                <div class="card-body">
                    @if($todayAttendance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الوقت</th>
                                        <th>الموقع</th>
                                        <th>المسافة</th>
                                        <th>الحالة</th>
                                        <th>ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayAttendance as $attendance)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($attendance->attendance_time)->format('h:i A') }}</td>
                                            <td>{{ $attendance->locationSetting->name }}</td>
                                            <td>{{ $attendance->distance_meters }} متر</td>
                                            <td>
                                                @if($attendance->is_within_range)
                                                    <span class="badge bg-success">ضمن النطاق</span>
                                                @else
                                                    <span class="badge bg-warning">خارج النطاق</span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance->notes }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1054/1054870.png" alt="لم يتم تسجيل حضور" width="80" class="mb-3 opacity-50">
                            <p class="text-muted">لم تقم بتسجيل الحضور اليوم</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map;
    let userMarker;
    let locationMarker;
    let locationCircle;
    let selectedLocation;
    
    // تهيئة الخريطة
    document.addEventListener('DOMContentLoaded', function() {
        // إنشاء الخريطة
        map = L.map('map').setView([24.7136, 46.6753], 13);
        
        // إضافة طبقة OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // تهيئة عناصر الواجهة
        initializeFormHandlers();
    });
    
    function initializeFormHandlers() {
        const locationSelect = document.getElementById('location_id');
        const getLocationBtn = document.getElementById('getLocationBtn');
        const locationDetails = document.getElementById('locationDetails');
        const currentLocation = document.getElementById('currentLocation');
        const distanceInfo = document.getElementById('distanceInfo');
        const statusInfo = document.getElementById('statusInfo');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        
        // عند تغيير الموقع المحدد
        locationSelect.addEventListener('change', function() {
            if (this.value) {
                const option = this.options[this.selectedIndex];
                const lat = parseFloat(option.dataset.lat);
                const lng = parseFloat(option.dataset.lng);
                const range = parseInt(option.dataset.range);
                
                selectedLocation = {
                    id: this.value,
                    name: option.text,
                    lat: lat,
                    lng: lng,
                    range: range
                };
                
                // إضافة علامة الموقع على الخريطة
                if (locationMarker) {
                    locationMarker.remove();
                }
                if (locationCircle) {
                    locationCircle.remove();
                }
                
                locationMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        html: '<i class="fas fa-university fa-2x text-primary"></i>',
                        iconSize: [20, 20],
                        className: 'location-marker'
                    })
                }).addTo(map);
                
                locationCircle = L.circle([lat, lng], {
                    radius: range,
                    color: '#3388ff',
                    fillColor: '#3388ff',
                    fillOpacity: 0.2
                }).addTo(map);
                
                map.setView([lat, lng], 15);
                
                // إذا كان موقع المستخدم موجودًا، فقم بحساب المسافة
                if (userMarker) {
                    updateLocationInfo();
                }
            }
        });
        
        // عند النقر على زر تحديد الموقع الحالي
        getLocationBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('متصفحك لا يدعم تحديد الموقع الجغرافي');
                return;
            }
            
            getLocationBtn.disabled = true;
            getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري تحديد الموقع...';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // نجاح
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    latitudeInput.value = lat;
                    longitudeInput.value = lng;
                    
                    // إضافة علامة موقع المستخدم
                    if (userMarker) {
                        userMarker.remove();
                    }
                    
                    userMarker = L.marker([lat, lng], {
                        icon: L.divIcon({
                            html: '<i class="fas fa-user fa-2x text-danger"></i>',
                            iconSize: [20, 20],
                            className: 'user-marker'
                        })
                    }).addTo(map);
                    
                    // تحديث معلومات الموقع
                    currentLocation.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    
                    // إذا كان هناك موقع محدد، قم بحساب المسافة
                    if (selectedLocation) {
                        updateLocationInfo();
                    }
                    
                    // إظهار تفاصيل الموقع
                    locationDetails.classList.remove('d-none');
                    
                    // إعادة تمكين الزر
                    getLocationBtn.disabled = false;
                    getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i> تحديد موقعي الحالي';
                    
                    // تكبير الخريطة لتشمل كلا العلامتين
                    if (locationMarker) {
                        const bounds = L.latLngBounds([
                            [lat, lng],
                            [selectedLocation.lat, selectedLocation.lng]
                        ]);
                        map.fitBounds(bounds, { padding: [50, 50] });
                    } else {
                        map.setView([lat, lng], 15);
                    }
                },
                function(error) {
                    // فشل
                    let errorMessage;
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'تم رفض طلب تحديد الموقع. يرجى السماح للموقع بالوصول إلى موقعك الجغرافي.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'معلومات الموقع غير متاحة.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'انتهت مهلة طلب تحديد الموقع.';
                            break;
                        case error.UNKNOWN_ERROR:
                            errorMessage = 'حدث خطأ غير معروف أثناء تحديد الموقع.';
                            break;
                    }
                    
                    alert(errorMessage);
                    
                    // إعادة تمكين الزر
                    getLocationBtn.disabled = false;
                    getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i> تحديد موقعي الحالي';
                },
                {
                    enableHighAccuracy: true, // دقة عالية
                    timeout: 10000, // 10 ثوانٍ
                    maximumAge: 0 // عدم استخدام الموقع المخزن
                }
            );
        });
    }
    
    // حساب المسافة بين نقطتين باستخدام صيغة هافرساين
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // نصف قطر الأرض بالمتر
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    // تحديث معلومات الموقع
    function updateLocationInfo() {
        if (!userMarker || !selectedLocation) return;
        
        const userLat = parseFloat(document.getElementById('latitude').value);
        const userLng = parseFloat(document.getElementById('longitude').value);
        
        // حساب المسافة
        const distance = calculateDistance(
            userLat,
            userLng,
            selectedLocation.lat,
            selectedLocation.lng
        );
        
        // تحديث معلومات المسافة
        distanceInfo.textContent = `${Math.round(distance)} متر`;
        
        // تحديث الحالة
        const isWithinRange = distance <= selectedLocation.range;
        statusInfo.innerHTML = isWithinRange
            ? '<span class="badge bg-success">ضمن النطاق المسموح به</span>'
            : '<span class="badge bg-danger">خارج النطاق المسموح به</span>';
            
        // إضافة خط يربط بين الموقعين
        if (window.connectingLine) {
            window.connectingLine.remove();
        }
        
        window.connectingLine = L.polyline([
            [userLat, userLng],
            [selectedLocation.lat, selectedLocation.lng]
        ], {
            color: isWithinRange ? 'green' : 'red',
            dashArray: '5, 10'
        }).addTo(map);
    }
</script>

<style>
    .location-marker, .user-marker {
        background: none;
        border: none;
    }
    
    .text-success {
        color: #198754 !important;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
</style>
@endpush 