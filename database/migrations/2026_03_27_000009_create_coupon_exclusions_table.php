<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupon_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->timestamps();

            $table->unique(['coupon_id', 'customer_phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_exclusions');
    }
};

