<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('business_name', 150);
            $table->string('slug', 160)->unique();
            $table->string('support_email', 255);
            $table->string('support_phone', 40);
            $table->string('base_city', 120);
            $table->string('vehicle_types', 255)->nullable();
            $table->string('status', 20)->default('PENDING'); // PENDING | ACTIVE | REJECTED | SUSPENDED
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_providers');
    }
};
