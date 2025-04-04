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
        Schema::create('admin_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // نوع الطلب: إجازة، تغيير موعد، إلخ
            $table->text('details'); // تفاصيل الطلب
            $table->timestamp('request_date'); // تاريخ تقديم الطلب
            $table->string('status')->default('pending'); // حالة الطلب: قيد الانتظار، مقبول، مرفوض
            $table->text('admin_comment')->nullable(); // تعليق المسؤول على الطلب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_requests');
    }
};
