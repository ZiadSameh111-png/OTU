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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->decimal('midterm_grade', 5, 2)->nullable();
            $table->decimal('assignment_grade', 5, 2)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->decimal('score', 5, 2)->nullable(); // Total score (0-100)
            $table->decimal('gpa', 3, 2)->nullable(); // GPA value (0.00-4.00)
            $table->string('grade', 2)->nullable(); // Letter grade (A+, A, A-, etc.)
            $table->boolean('submitted')->default(false);
            $table->timestamp('submission_date')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->text('comments')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['student_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grades');
    }
};
