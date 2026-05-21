<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE | ARCHIVED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_wallets');
    }
};
