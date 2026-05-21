<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('type', 20); // MOBILE_WALLET | BANK_TRANSFER

            $table->string('label', 100)->nullable();

            // Mobile-wallet branch
            $table->foreignId('mobile_wallet_id')->nullable()->constrained('mobile_wallets')->nullOnDelete();
            $table->string('mobile_wallet_msisdn', 20)->nullable();

            // Bank-transfer branch
            $table->string('bank_name', 120)->nullable();
            $table->string('bank_account_name', 150)->nullable();
            $table->string('bank_account_number', 64)->nullable();
            $table->string('bank_currency', 3)->nullable(); // USD | ZWG

            $table->boolean('is_primary')->default(false);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | ARCHIVED

            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payout_accounts');
    }
};
