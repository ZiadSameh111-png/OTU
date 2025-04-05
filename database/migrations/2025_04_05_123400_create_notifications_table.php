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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('receiver_type')->nullable()->comment('user, group, role');
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade');
            $table->string('role')->nullable()->comment('admin, teacher, student');
            $table->timestamp('read_at')->nullable();
            $table->string('notification_type')->default('general')->comment('general, academic, announcement, exam');
            $table->string('url')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('notifications');
    }
};
