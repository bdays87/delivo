<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('applied_coupon_id')->nullable()->after('mobile_wallet_id')
                ->constrained('coupons')->nullOnDelete();
            $table->string('applied_coupon_code', 40)->nullable()->after('applied_coupon_id');
            $table->decimal('total_buyer_discount_usd', 12, 2)->default(0)->after('subtotal_usd');
            $table->decimal('total_influencer_commission_usd', 12, 2)->default(0)->after('total_buyer_discount_usd');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('applied_coupon_id');
            $table->dropColumn([
                'applied_coupon_code',
                'total_buyer_discount_usd',
                'total_influencer_commission_usd',
            ]);
        });
    }
};
