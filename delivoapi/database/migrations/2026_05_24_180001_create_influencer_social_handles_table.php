<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('influencer_social_handles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('influencers')->cascadeOnDelete();
            $table->string('platform', 30); // INSTAGRAM | TIKTOK | X | YOUTUBE | FACEBOOK | OTHER
            $table->string('handle', 120);
            $table->string('url', 255)->nullable();
            $table->unsignedInteger('followers')->nullable();
            $table->string('status', 20)->default('PENDING'); // PENDING | APPROVED | REJECTED
            $table->timestamps();

            $table->unique(['influencer_id', 'platform', 'handle'], 'influencer_handle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencer_social_handles');
    }
};
