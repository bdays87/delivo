<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->decimal('affiliate_total_min_pct', 5, 2)->default(0);
            $table->decimal('affiliate_total_max_pct', 5, 2)->default(50);
            $table->decimal('influencer_payout_fee_pct', 5, 2)->default(2.50);
            $table->decimal('influencer_payout_fee_min_usd', 12, 2)->default(0.50);
        });
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_total_min_pct',
                'affiliate_total_max_pct',
                'influencer_payout_fee_pct',
                'influencer_payout_fee_min_usd',
            ]);
        });
    }
};
