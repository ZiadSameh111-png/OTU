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
        Schema::table('fees', function (Blueprint $table) {
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('fee_type')->default('tuition');
            $table->string('status')->default('unpaid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'paid_amount',
                'remaining_amount',
                'due_date',
                'description',
                'academic_year',
                'fee_type',
                'status'
            ]);
        });
    }
};
