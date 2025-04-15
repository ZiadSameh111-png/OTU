<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            // إضافة حقل is_open
            $table->boolean('is_open')->default(false)->after('is_published');
            
            // جعل حقول التاريخ اختيارية
            $table->dateTime('start_time')->nullable()->change();
            $table->dateTime('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exams', function (Blueprint $table) {
            // حذف حقل is_open
            $table->dropColumn('is_open');
            
            // إعادة حقول التاريخ إلى الوضع الأصلي (غير اختيارية)
            $table->dateTime('start_time')->nullable(false)->change();
            $table->dateTime('end_time')->nullable(false)->change();
        });
    }
};
