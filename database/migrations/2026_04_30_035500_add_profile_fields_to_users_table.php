<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('state', 100)->nullable()->after('phone');
            $table->string('lga', 120)->nullable()->after('state');
            $table->string('shop_name')->nullable()->after('lga');
            $table->string('address')->nullable()->after('shop_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'state', 'lga', 'shop_name', 'address']);
        });
    }
};