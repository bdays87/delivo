<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-line attribution + commission snapshot. Stage 4 reads these to
     * compute an influencer's payout balance.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('influencer_id')->nullable()->after('product_variant_id')
                ->constrained('influencers')->nullOnDelete();
            $table->decimal('buyer_discount_pct_snapshot', 5, 2)->default(0)->after('unit_price_usd_snapshot');
            $table->decimal('line_discount_usd_snapshot', 12, 2)->default(0)->after('buyer_discount_pct_snapshot');
            $table->decimal('influencer_commission_pct_snapshot', 5, 2)->default(0)->after('line_discount_usd_snapshot');
            $table->decimal('line_commission_usd_snapshot', 12, 2)->default(0)->after('influencer_commission_pct_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('influencer_id');
            $table->dropColumn([
                'buyer_discount_pct_snapshot',
                'line_discount_usd_snapshot',
                'influencer_commission_pct_snapshot',
                'line_commission_usd_snapshot',
            ]);
        });
    }
};
