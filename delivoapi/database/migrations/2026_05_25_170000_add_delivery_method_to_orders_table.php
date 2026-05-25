<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // HOME_DELIVERY (default — rider delivers) or SELF_PICKUP (customer
            // picks up from the vendor directly, no shipping fee).
            $table->string('delivery_method', 20)->default('HOME_DELIVERY')->after('mobile_wallet_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_method');
        });
    }
};
