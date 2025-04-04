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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration')->comment('Duration in minutes');
            $table->enum('status', ['pending', 'active', 'completed'])->default('pending');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'open_ended', 'mixed'])->default('mixed');
            $table->integer('total_marks')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            
            // Create a unique constraint to prevent duplicate exams for a course and group
            $table->unique(['course_id', 'group_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exams');
    }
};
