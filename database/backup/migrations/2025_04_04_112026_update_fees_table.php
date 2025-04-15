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
        if (!Schema::hasTable('fees')) {
            Schema::create('fees', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->text('description');
                $table->decimal('amount', 10, 2);
                $table->date('due_date')->nullable();
                $table->string('status')->default('active');
                $table->string('category')->nullable();
                $table->string('semester')->nullable();
                $table->string('academic_year')->nullable();
                $table->boolean('applies_to_all')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('fees', function (Blueprint $table) {
                if (!Schema::hasColumn('fees', 'name')) {
                    $table->string('name')->nullable()->after('id');
                }
                
                if (!Schema::hasColumn('fees', 'description')) {
                    $table->text('description')->after('name');
                }
                
                if (!Schema::hasColumn('fees', 'amount')) {
                    $table->decimal('amount', 10, 2)->after('description');
                }
                
                if (!Schema::hasColumn('fees', 'due_date')) {
                    $table->date('due_date')->nullable()->after('amount');
                }
                
                if (!Schema::hasColumn('fees', 'status')) {
                    $table->string('status')->default('active')->after('due_date');
                }
                
                if (!Schema::hasColumn('fees', 'category')) {
                    $table->string('category')->nullable()->after('status');
                }
                
                if (!Schema::hasColumn('fees', 'semester')) {
                    $table->string('semester')->nullable()->after('category');
                }
                
                if (!Schema::hasColumn('fees', 'academic_year')) {
                    $table->string('academic_year')->nullable()->after('semester');
                }
                
                if (!Schema::hasColumn('fees', 'applies_to_all')) {
                    $table->boolean('applies_to_all')->default(false)->after('academic_year');
                }
                
                if (!Schema::hasColumn('fees', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('applies_to_all')->constrained('users')->nullOnDelete();
                }
                
                // تعديل الأعمدة القديمة أو إزالتها إذا كانت موجودة
                if (Schema::hasColumn('fees', 'student_id')) {
                    $table->dropForeign(['student_id']);
                    $table->dropColumn('student_id');
                }
                
                if (Schema::hasColumn('fees', 'total_amount')) {
                    $table->dropColumn('total_amount');
                }
                
                if (Schema::hasColumn('fees', 'paid_amount')) {
                    $table->dropColumn('paid_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // لا نقوم بأي إجراء للتراجع عن التغييرات لتجنب فقدان البيانات
    }
};
