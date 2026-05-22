<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_provider_coverage_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_provider_id')->constrained('delivery_providers')->cascadeOnDelete();
            $table->foreignId('delivery_zone_id')->constrained('delivery_zones')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['delivery_provider_id', 'delivery_zone_id'], 'provider_zone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_provider_coverage_areas');
    }
};
