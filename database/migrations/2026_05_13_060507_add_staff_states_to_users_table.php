<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('staff_states')->nullable()->after('state');
        });

        // Seed staff_states from existing state column for current staff
        DB::table('users')->where('role', 'staff')->whereNotNull('state')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'staff_states' => json_encode([$user->state]),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('staff_states');
        });
    }
};
