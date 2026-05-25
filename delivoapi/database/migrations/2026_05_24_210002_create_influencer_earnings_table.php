<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('influencer_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('influencers')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->decimal('amount_usd', 12, 2);
            // PENDING (order paid) | CLEARED (order delivered) | PAID (paid out to influencer)
            $table->string('status', 20)->default('PENDING');
            $table->foreignId('payout_request_id')->nullable()->constrained('payout_requests')->nullOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['influencer_id', 'status']);
            $table->unique('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencer_earnings');
    }
};
