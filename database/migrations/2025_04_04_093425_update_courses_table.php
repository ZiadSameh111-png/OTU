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
        if (!Schema::hasColumn('courses', 'teacher_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->foreignId('teacher_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
            });
        }

        // Create course_group pivot table if it doesn't exist
        if (!Schema::hasTable('course_group')) {
            Schema::create('course_group', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->foreignId('group_id')->constrained()->onDelete('cascade');
                $table->timestamps();
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
        Schema::dropIfExists('course_group');

        if (Schema::hasColumn('courses', 'teacher_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropForeign(['teacher_id']);
                $table->dropColumn('teacher_id');
            });
        }
    }
};
