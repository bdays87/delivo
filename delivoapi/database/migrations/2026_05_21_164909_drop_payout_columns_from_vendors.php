<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payout details now live in `vendor_payout_accounts` (one vendor can
     * have many — e.g. a USD bank account and a ZWG bank account, plus
     * mobile wallets). The single-account columns on `vendors` are dropped.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mobile_wallet_id');
            $table->dropColumn([
                'payout_method',
                'mobile_wallet_msisdn',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_currency',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('payout_method', 20)->nullable()->after('commission_pct_override');
            $table->foreignId('mobile_wallet_id')->nullable()->after('payout_method')->constrained('mobile_wallets')->nullOnDelete();
            $table->string('mobile_wallet_msisdn', 20)->nullable()->after('mobile_wallet_id');
            $table->string('bank_name', 120)->nullable()->after('mobile_wallet_msisdn');
            $table->string('bank_account_name', 150)->nullable()->after('bank_name');
            $table->string('bank_account_number', 64)->nullable()->after('bank_account_name');
            $table->string('bank_currency', 3)->nullable()->after('bank_account_number');
        });
    }
};
