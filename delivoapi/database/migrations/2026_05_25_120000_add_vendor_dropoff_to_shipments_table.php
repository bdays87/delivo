<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_delivery_shipments', function (Blueprint $table) {
            $table->timestamp('dropoff_deadline')->nullable()->after('shipment_status');
            $table->timestamp('dropped_off_at')->nullable()->after('dropoff_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('order_delivery_shipments', function (Blueprint $table) {
            $table->dropColumn(['dropoff_deadline', 'dropped_off_at']);
        });
    }
};
