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
        Schema::table('fee_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_payments', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            }
            
            // التأكد من وجود باقي الأعمدة المطلوبة
            if (!Schema::hasColumn('fee_payments', 'status')) {
                $table->string('status')->default('pending')->after('payment_date');
            }
            
            if (!Schema::hasColumn('fee_payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_method');
            }
            
            if (!Schema::hasColumn('fee_payments', 'description')) {
                $table->text('description')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('fee_payments', 'notes')) {
                $table->text('notes')->nullable()->after('description');
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
        Schema::table('fee_payments', function (Blueprint $table) {
            if (Schema::hasColumn('fee_payments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
