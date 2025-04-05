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
            // إزالة حقول التاريخ
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
            
            // تأكد من وجود حقل is_open
            if (!Schema::hasColumn('exams', 'is_open')) {
                $table->boolean('is_open')->default(false)->after('is_published');
            }
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
            // إعادة حقول التاريخ
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
        });
    }
};
