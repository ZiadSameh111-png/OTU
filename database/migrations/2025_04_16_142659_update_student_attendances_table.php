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
        Schema::table('student_attendances', function (Blueprint $table) {
            // تغيير اسم العمود من date إلى attendance_date
            if (Schema::hasColumn('student_attendances', 'date')) {
                $table->renameColumn('date', 'attendance_date');
            } else if (!Schema::hasColumn('student_attendances', 'attendance_date')) {
                // إذا لم يكن العمود موجودًا على الإطلاق
                $table->date('attendance_date')->after('teacher_id');
            }
            
            // إضافة عمود جديد للمستخدم الذي سجل الحضور
            if (!Schema::hasColumn('student_attendances', 'recorded_by')) {
                $table->unsignedBigInteger('recorded_by')->nullable()->after('notes');
                $table->foreign('recorded_by')->references('id')->on('users');
            }
            
            // اضافة وقت الحضور
            if (!Schema::hasColumn('student_attendances', 'attendance_time')) {
                $table->time('attendance_time')->nullable()->after('attendance_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            // إعادة اسم العمود من attendance_date إلى date إذا كان موجودًا
            if (Schema::hasColumn('student_attendances', 'attendance_date')) {
                $table->renameColumn('attendance_date', 'date');
            }
            
            // حذف عمود المستخدم الذي سجل الحضور
            if (Schema::hasColumn('student_attendances', 'recorded_by')) {
                $table->dropForeign(['recorded_by']);
                $table->dropColumn('recorded_by');
            }
            
            // حذف عمود وقت الحضور
            if (Schema::hasColumn('student_attendances', 'attendance_time')) {
                $table->dropColumn('attendance_time');
            }
        });
    }
};
