<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropUnique(['state']);
            $table->dropColumn('state');
            $table->foreignId('staff_id')->nullable()->unique()->after('id')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropUnique(['staff_id']);
            $table->dropColumn('staff_id');
            $table->string('state')->default('');
            $table->unique('state');
        });
    }
};
