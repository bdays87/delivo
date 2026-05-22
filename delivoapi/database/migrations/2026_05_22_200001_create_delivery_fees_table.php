<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distance-banded delivery fees. PricingService resolves the customer's
     * distance via Google Distance Matrix, then picks the band where
     * min_km <= distance <= max_km (or max_km IS NULL = "and up").
     */
    public function up(): void
    {
        Schema::create('delivery_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('min_km');
            $table->unsignedInteger('max_km')->nullable();
            $table->decimal('fee_usd', 12, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | ARCHIVED
            $table->timestamps();

            $table->unique('min_km');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_fees');
    }
};
