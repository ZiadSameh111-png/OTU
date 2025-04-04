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
        // حذف كل الجداول المتعلقة أولاً في حالة وجودها
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('roles');
        
        // 1. إنشاء جدول الأدوار
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        
        // 2. إنشاء جدول المجموعات
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        
        // 3. إنشاء جدول المقررات
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // 4. إضافة حقل group_id إلى جدول المستخدمين
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'group_id')) {
                $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');
            }
        });
        
        // 5. إنشاء جدول الربط بين المستخدمين والأدوار
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });
        
        // 6. إنشاء جدول الجداول الزمنية
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->string('day');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('role_user');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
        Schema::dropIfExists('courses');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('roles');
    }
};
