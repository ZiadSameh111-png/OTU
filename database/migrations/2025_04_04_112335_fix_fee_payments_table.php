<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // نحفظ البيانات الموجودة في جدول مؤقت (إذا كان هناك بيانات)
        if (Schema::hasTable('fee_payments')) {
            DB::statement('CREATE TABLE fee_payments_backup LIKE fee_payments');
            DB::statement('INSERT INTO fee_payments_backup SELECT * FROM fee_payments');
            
            // نسقط الجدول القديم
            Schema::dropIfExists('fee_payments');
        }
        
        // نعيد إنشاء الجدول بالهيكل الصحيح
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->timestamp('payment_date');
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('fee_payments');
        
        // إذا كان هناك نسخة احتياطية، نعيدها
        if (Schema::hasTable('fee_payments_backup')) {
            DB::statement('CREATE TABLE fee_payments LIKE fee_payments_backup');
            DB::statement('INSERT INTO fee_payments SELECT * FROM fee_payments_backup');
            Schema::dropIfExists('fee_payments_backup');
        }
    }
};
