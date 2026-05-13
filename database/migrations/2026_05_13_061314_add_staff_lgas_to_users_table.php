<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('staff_lgas')->nullable()->after('staff_states');
        });

        // Seed staff_lgas from existing lga column for current staff
        DB::table('users')->where('role', 'staff')->whereNotNull('lga')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'staff_lgas' => json_encode([$user->lga]),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('staff_lgas');
        });
    }
};
