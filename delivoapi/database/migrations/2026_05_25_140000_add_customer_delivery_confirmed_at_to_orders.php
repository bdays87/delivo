<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Records when the customer entered the delivery code. Distinct
            // from delivered_at, which only the admin sets when closing out
            // the order.
            $table->timestamp('customer_delivery_confirmed_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_delivery_confirmed_at');
        });
    }
};
