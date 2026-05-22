<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Strict coverage replaces the soft default-fee fallback: orders to
     * uncovered cities are blocked rather than charged a default. Drop the
     * column so it can't be set inadvertently.
     */
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn('default_delivery_fee_usd');
        });
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->decimal('default_delivery_fee_usd', 12, 2)->default(10.00);
        });
    }
};
