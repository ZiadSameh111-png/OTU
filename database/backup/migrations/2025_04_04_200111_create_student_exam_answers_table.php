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
        Schema::create('student_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('exam_questions')->onDelete('cascade');
            $table->text('answer')->nullable();
            $table->integer('marks_obtained')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->text('feedback')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->timestamps();
            
            // Each student can only answer a question once
            $table->unique(['student_id', 'exam_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_exam_answers');
    }
};
