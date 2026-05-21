<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->string('support_email');
            $table->string('support_phone', 20);

            // Optional / aspirational KYC fields — most ZW startups won't have these.
            $table->string('tin', 64)->nullable();
            $table->string('registration_no', 64)->nullable();

            // Payout details (admin disburses out-of-band; these are display info only).
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number', 64)->nullable();
            $table->string('ecocash_msisdn', 20)->nullable();

            // Optional per-vendor commission override; null falls back to platform default.
            $table->decimal('commission_pct_override', 5, 2)->nullable();

            $table->string('status', 20)->default('PENDING'); // PENDING | ACTIVE | REJECTED | SUSPENDED
            $table->text('rejection_reason')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('suspended_at')->nullable();

            $table->unique('owner_user_id'); // one vendor per user in v1
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
