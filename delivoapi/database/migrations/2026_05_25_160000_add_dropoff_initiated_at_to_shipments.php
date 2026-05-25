<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_delivery_shipments', function (Blueprint $table) {
            // Vendor stamps this when they select a hub and head out with the
            // parcel. Admin/hub staff later sets dropped_off_at on receipt.
            $table->timestamp('dropoff_initiated_at')->nullable()->after('dropoff_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('order_delivery_shipments', function (Blueprint $table) {
            $table->dropColumn('dropoff_initiated_at');
        });
    }
};
