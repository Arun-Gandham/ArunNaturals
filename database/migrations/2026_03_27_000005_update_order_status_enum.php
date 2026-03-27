<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Extend the ENUM values for order status to support richer tracking
        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM(
                'draft',
                'placed',
                'preparing_for_dispatch',
                'ready_for_pickup',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'cancelled'
            ) NOT NULL DEFAULT 'placed'
        ");
    }

    public function down(): void
    {
        // Revert back to the original minimal ENUM set
        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM(
                'draft',
                'placed',
                'cancelled'
            ) NOT NULL DEFAULT 'placed'
        ");
    }
};

