<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // التحقق من وجود جدول المجموعات، وإنشائه إذا لم يكن موجودًا
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        } else {
            // إضافة الأعمدة المفقودة في حالة عدم وجودها
            Schema::table('groups', function (Blueprint $table) {
                if (!Schema::hasColumn('groups', 'name')) {
                    $table->string('name')->after('id');
                }
                
                if (!Schema::hasColumn('groups', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                
                if (!Schema::hasColumn('groups', 'active')) {
                    $table->boolean('active')->default(true)->after('description');
                }
            });
        }
        
        // إضافة بعض البيانات النموذجية للاختبار
        if (DB::table('groups')->count() == 0) {
            DB::table('groups')->insert([
                [
                    'name' => 'مجموعة هندسة البرمجيات',
                    'description' => 'مجموعة متخصصة في هندسة البرمجيات والتطوير',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'مجموعة تطوير الويب',
                    'description' => 'مجموعة متخصصة في تطوير تطبيقات الويب',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا نقوم بحذف الجدول في حالة التراجع
    }
};
