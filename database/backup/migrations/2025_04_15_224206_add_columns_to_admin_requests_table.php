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
        Schema::table('admin_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_requests', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('admin_requests', 'type')) {
                $table->string('type')->default('other');
            }
            
            if (!Schema::hasColumn('admin_requests', 'details')) {
                $table->text('details')->nullable();
            }
            
            if (!Schema::hasColumn('admin_requests', 'priority')) {
                $table->string('priority')->default('normal');
            }
            
            if (!Schema::hasColumn('admin_requests', 'request_date')) {
                $table->timestamp('request_date')->default(now());
            }
            
            if (!Schema::hasColumn('admin_requests', 'status')) {
                $table->string('status')->default('pending');
            }
            
            if (!Schema::hasColumn('admin_requests', 'admin_comment')) {
                $table->text('admin_comment')->nullable();
            }
            
            if (!Schema::hasColumn('admin_requests', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('admin_requests', 'attachment')) {
                $table->string('attachment')->nullable();
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
        Schema::table('admin_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['admin_id']);
            
            $table->dropColumn([
                'user_id',
                'type',
                'details',
                'priority',
                'request_date',
                'status',
                'admin_comment',
                'admin_id',
                'attachment'
            ]);
        });
    }
};
