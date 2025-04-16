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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->decimal('online_exam_grade', 8, 2)->nullable();
            $table->decimal('online_exam_total', 8, 2)->nullable();
            $table->decimal('paper_exam_grade', 8, 2)->nullable();
            $table->decimal('paper_exam_total', 8, 2)->nullable();
            $table->decimal('practical_grade', 8, 2)->nullable();
            $table->decimal('practical_total', 8, 2)->nullable();
            $table->decimal('total_grade', 8, 2)->nullable();
            $table->decimal('total_possible', 8, 2)->nullable();
            $table->boolean('is_final')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('comments')->nullable();
            $table->timestamps();
            
            // Garantizar que un estudiante solo tiene una entrada de calificación por curso
            $table->unique(['student_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
