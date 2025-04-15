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
        Schema::create('student_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->datetime('start_time')->nullable();
            $table->datetime('submit_time')->nullable();
            $table->integer('total_marks_obtained')->nullable();
            $table->integer('total_possible_marks')->nullable();
            $table->enum('status', ['started', 'in_progress', 'submitted', 'graded'])->default('started');
            $table->boolean('is_graded')->default(false);
            $table->datetime('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Each student can only have one attempt per exam
            $table->unique(['student_id', 'exam_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_exam_attempts');
    }
};
