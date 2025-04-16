<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('location_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_setting_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('attendance_time');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('distance_meters')->nullable();
            $table->boolean('is_within_range')->default(false);
            $table->string('status')->default('present');
            $table->string('notes')->nullable();
            $table->string('device_info')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            // لضمان عدم تكرار تسجيل الحضور لنفس المستخدم في نفس اليوم ونفس الموقع
            $table->unique(['user_id', 'location_setting_id', 'attendance_date'], 'loc_attendance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_attendances');
    }
};
