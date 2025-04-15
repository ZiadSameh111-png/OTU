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
        // Add columns directly with DB::statement to avoid potential issues
        try {
            DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS type VARCHAR(255) DEFAULT "other" AFTER id');
            DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS details TEXT NULL AFTER type');
            DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS priority VARCHAR(255) DEFAULT "normal" AFTER details');
            DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER priority');
            DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS status VARCHAR(255) DEFAULT "pending" AFTER request_date');
            DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS admin_comment TEXT NULL AFTER status');
            DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS attachment VARCHAR(255) NULL AFTER admin_comment');
            
            // Check if foreign keys exist
            $keyExists = DB::select("SHOW KEYS FROM admin_requests WHERE Key_name = 'admin_requests_user_id_foreign'");
            if (empty($keyExists)) {
                // Add user_id column if it doesn't exist
                DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS user_id BIGINT UNSIGNED AFTER id');
                DB::statement('ALTER TABLE admin_requests ADD CONSTRAINT admin_requests_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
            }
            
            $keyExists = DB::select("SHOW KEYS FROM admin_requests WHERE Key_name = 'admin_requests_admin_id_foreign'");
            if (empty($keyExists)) {
                // Add admin_id column if it doesn't exist
                DB::statement('ALTER TABLE admin_requests ADD COLUMN IF NOT EXISTS admin_id BIGINT UNSIGNED NULL AFTER user_id');
                DB::statement('ALTER TABLE admin_requests ADD CONSTRAINT admin_requests_admin_id_foreign FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL');
            }
        } catch (\Exception $e) {
            // Log any errors but continue with the migration
            \Log::error('Error adding columns to admin_requests: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No down migration as we're fixing the table
    }
};
