<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Route shape: INTRA_CITY fleets only serve within a single city (use
     * coverage_areas to pick which). INTER_CITY fleets define routes
     * (origin → waypoints → destination) and can optionally also offer
     * intra-city service in specific cities (still via coverage_areas).
     */
    public function up(): void
    {
        Schema::table('delivery_providers', function (Blueprint $table) {
            $table->string('route_type', 16)->default('INTRA_CITY')->after('base_city'); // INTRA_CITY | INTER_CITY
            $table->boolean('offers_intra_city')->default(false)->after('route_type');
            $table->dropColumn('vehicle_types'); // replaced by vehicle_types relation
        });
    }

    public function down(): void
    {
        Schema::table('delivery_providers', function (Blueprint $table) {
            $table->dropColumn(['route_type', 'offers_intra_city']);
            $table->string('vehicle_types', 255)->nullable();
        });
    }
};
