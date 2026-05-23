<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inter-city routes a provider runs. Waypoints capture the cities the
     * route passes through (e.g. Harare → Bulawayo via Kadoma, Kwekwe,
     * Gweru). The matcher accepts any pickup→drop pair that appears in
     * the ordered city list, allowing partial-leg deliveries.
     */
    public function up(): void
    {
        Schema::create('delivery_provider_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_provider_id')->constrained('delivery_providers')->cascadeOnDelete();
            $table->string('origin_city', 120);
            $table->string('destination_city', 120);
            $table->json('waypoints')->nullable(); // ordered array of intermediate city names
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | ARCHIVED
            $table->timestamps();

            $table->index('delivery_provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_provider_routes');
    }
};
