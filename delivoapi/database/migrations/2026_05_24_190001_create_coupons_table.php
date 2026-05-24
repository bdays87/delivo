<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unified coupons table. Two flavours:
     *   - Influencer codes: influencer_id non-null, product_id set, splits
     *     between buyer_discount_pct (applied at checkout) and
     *     influencer_commission_pct (paid to influencer when order pays out).
     *   - Vendor coupons (future slice 14): influencer_id null, vendor-wide
     *     or per-product promo codes.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('influencer_id')->nullable()->constrained('influencers')->nullOnDelete();
            $table->decimal('buyer_discount_pct', 5, 2)->default(0);
            $table->decimal('influencer_commission_pct', 5, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | ARCHIVED
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index(['influencer_id', 'status']);
            $table->unique(['influencer_id', 'product_id'], 'one_code_per_influencer_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
