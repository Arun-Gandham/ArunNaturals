<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('target_audience')->default('all')->after('is_active');
            $table->decimal('target_value', 10, 2)->nullable()->after('target_audience');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['target_audience', 'target_value']);
        });
    }
};

