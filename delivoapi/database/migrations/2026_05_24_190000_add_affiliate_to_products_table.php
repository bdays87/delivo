<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Affiliate commission split. Vendor declares the total commission as
     * two components: a discount applied to the buyer when the affiliate
     * code is used, and the cut paid to the influencer who promoted it.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('affiliate_influencer_pct', 5, 2)->default(0)->after('weight_kg');
            $table->decimal('affiliate_buyer_discount_pct', 5, 2)->default(0)->after('affiliate_influencer_pct');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['affiliate_influencer_pct', 'affiliate_buyer_discount_pct']);
        });
    }
};
