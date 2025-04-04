<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teacher_attendances', function (Blueprint $table) {
            // Check if teacher_id doesn't exist but user_id does
            if (!Schema::hasColumn('teacher_attendances', 'teacher_id') && 
                Schema::hasColumn('teacher_attendances', 'user_id')) {
                // Rename the user_id column to teacher_id
                $table->renameColumn('user_id', 'teacher_id');
            } 
            // If neither column exists, add teacher_id
            else if (!Schema::hasColumn('teacher_attendances', 'teacher_id') && 
                     !Schema::hasColumn('teacher_attendances', 'user_id')) {
                $table->foreignId('teacher_id')->constrained('users');
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
        Schema::table('teacher_attendances', function (Blueprint $table) {
            // Only rename if teacher_id exists and user_id doesn't
            if (Schema::hasColumn('teacher_attendances', 'teacher_id') && 
                !Schema::hasColumn('teacher_attendances', 'user_id')) {
                $table->renameColumn('teacher_id', 'user_id');
            }
            // If both exist (unlikely), drop teacher_id
            else if (Schema::hasColumn('teacher_attendances', 'teacher_id') && 
                     Schema::hasColumn('teacher_attendances', 'user_id')) {
                $table->dropColumn('teacher_id');
            }
        });
    }
};
