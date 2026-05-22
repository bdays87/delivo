<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each coverage area now owns a central dispatch hub — vendors operating
     * in that city drop their orders at the hub, and Delivo's couriers
     * deliver from the hub to the customer. Distance is computed from the
     * hub's coordinates.
     */
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->string('hub_name', 150)->nullable()->after('city');
            $table->string('hub_address', 255)->nullable()->after('hub_name');
            $table->decimal('hub_latitude', 10, 6)->nullable()->after('hub_address');
            $table->decimal('hub_longitude', 10, 6)->nullable()->after('hub_latitude');
            $table->dropColumn('fee_usd'); // band lookup in delivery_fees replaces this.
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn(['hub_name', 'hub_address', 'hub_latitude', 'hub_longitude']);
            $table->decimal('fee_usd', 12, 2)->default(0);
        });
    }
};
