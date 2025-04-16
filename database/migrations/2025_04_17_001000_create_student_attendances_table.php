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
        if (!Schema::hasTable('student_attendances')) {
            Schema::create('student_attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedule_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->date('attendance_date');
                $table->time('attendance_time')->nullable();
                $table->enum('status', ['present', 'absent'])->default('present');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->timestamps();
                
                $table->foreign('student_id')->references('id')->on('users');
                $table->foreign('teacher_id')->references('id')->on('users');
                $table->foreign('recorded_by')->references('id')->on('users');
                $table->foreign('schedule_id')->references('id')->on('schedules');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
}; 