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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('assignment_grade')->default(40); // Default 40% for assignment grade
            $table->integer('final_grade')->default(60); // Default 60% for final grade
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Create pivot table for course_group relationship
        Schema::create('course_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // A course can only be assigned to a group once
            $table->unique(['course_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_group');
        Schema::dropIfExists('courses');
    }
}; 