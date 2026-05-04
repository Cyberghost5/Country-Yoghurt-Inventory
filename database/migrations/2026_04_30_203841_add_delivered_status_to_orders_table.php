<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','approved','rejected','delivered') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Move any 'delivered' rows back to 'approved' before shrinking the enum
        DB::statement("UPDATE orders SET status = 'approved' WHERE status = 'delivered'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    }
};
