<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nullable so existing vendors don't blow up.
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('city', 120)->nullable()->after('support_phone');
        });

        // 2. Backfill existing rows. Default to Harare per BossBen's call.
        DB::table('vendors')->whereNull('city')->update(['city' => 'Harare']);

        // 3. Lock to NOT NULL so future vendors must supply it.
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('city', 120)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }
};
