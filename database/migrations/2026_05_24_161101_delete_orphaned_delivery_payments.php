<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Delete payment records whose delivery_allocation_id no longer exists
     * (i.e. the parent delivery was hard-deleted without cascading to payments).
     */
    public function up(): void
    {
        DB::table('payments')
            ->whereNotNull('delivery_allocation_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('delivery_allocations')
                      ->whereColumn('delivery_allocations.id', 'payments.delivery_allocation_id');
            })
            ->delete();
    }

    /**
     * Orphaned records cannot be restored — down() is intentionally a no-op.
     */
    public function down(): void
    {
        //
    }
};
