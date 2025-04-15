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
        Schema::create('grade_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('user_id');
            $table->float('old_midterm_grade')->nullable();
            $table->float('old_assignment_grade')->nullable();
            $table->float('old_final_grade')->nullable();
            $table->float('new_midterm_grade')->nullable();
            $table->float('new_assignment_grade')->nullable();
            $table->float('new_final_grade')->nullable();
            $table->text('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grade_edit_logs');
    }
};
