<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_provider_vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_provider_id')->constrained('delivery_providers')->cascadeOnDelete();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['delivery_provider_id', 'vehicle_type_id'], 'provider_vehicle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_provider_vehicle_types');
    }
};
