<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete(); // denorm for vendor payouts
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            // Snapshots — survive product/variant edits.
            $table->string('product_name_snapshot', 200);
            $table->string('color_snapshot', 60)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price_usd_snapshot', 12, 2);
            $table->decimal('line_total_usd_snapshot', 12, 2);

            $table->timestamps();

            $table->index('order_id');
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
