<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // PayoutMethod = 'MOBILE_WALLET' | 'BANK_TRANSFER'. Null until the vendor configures payouts.
            $table->string('payout_method', 20)->nullable()->after('commission_pct_override');

            $table->foreignId('mobile_wallet_id')
                ->nullable()
                ->after('payout_method')
                ->constrained('mobile_wallets')
                ->nullOnDelete();

            $table->string('mobile_wallet_msisdn', 20)->nullable()->after('mobile_wallet_id');

            // 3-letter currency code (USD | ZWG) — only meaningful when payout_method = BANK_TRANSFER.
            $table->string('bank_currency', 3)->nullable()->after('bank_account_number');

            $table->dropColumn('ecocash_msisdn');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('ecocash_msisdn', 20)->nullable()->after('bank_account_number');

            $table->dropColumn('bank_currency');
            $table->dropConstrainedForeignId('mobile_wallet_id');
            $table->dropColumn(['payout_method', 'mobile_wallet_msisdn']);
        });
    }
};
