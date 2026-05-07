<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_allocation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_allocation_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_allocation_items');
    }
};
