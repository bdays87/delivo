<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            // Service charge: percentage of subtotal, with a USD floor.
            $table->decimal('service_charge_pct', 5, 2)->default(2.50);
            $table->decimal('service_charge_min_usd', 12, 2)->default(0.50);
            // Fallback delivery fee for cities not in the zones table.
            $table->decimal('default_delivery_fee_usd', 12, 2)->default(10.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
