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
            $table->string('priority')->default('normal')->after('details');
            $table->string('attachment')->nullable()->after('status');
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
            $table->dropColumn('priority');
            $table->dropColumn('attachment');
        });
    }
};
