<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('influencers')->cascadeOnDelete();
            $table->decimal('requested_usd', 12, 2);
            $table->decimal('service_fee_pct', 5, 2);
            $table->decimal('service_fee_usd', 12, 2);
            $table->decimal('net_payout_usd', 12, 2);
            // PENDING (awaiting admin) | APPROVED (queued for payment) | PAID | REJECTED (returns entries to CLEARED)
            $table->string('status', 20)->default('PENDING');
            // MOBILE_MONEY | BANK_TRANSFER
            $table->string('method', 30);
            $table->string('destination_label', 120)->nullable();
            $table->string('destination_account', 120);
            $table->text('influencer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['influencer_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};
