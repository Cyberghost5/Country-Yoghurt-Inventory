<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('delivery_allocation_id')
                  ->nullable()
                  ->after('order_id')
                  ->constrained('delivery_allocations')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['delivery_allocation_id']);
            $table->dropColumn('delivery_allocation_id');
        });
    }
};
