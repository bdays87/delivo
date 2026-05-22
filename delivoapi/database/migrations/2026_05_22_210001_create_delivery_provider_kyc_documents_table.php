<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_provider_kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_provider_id')->constrained('delivery_providers')->cascadeOnDelete();
            $table->string('type', 60); // NATIONAL_ID | VEHICLE_REGISTRATION | DRIVERS_LICENSE
            $table->string('original_filename', 255);
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->string('status', 20)->default('PENDING'); // PENDING | APPROVED | REJECTED
            $table->timestamps();

            $table->index(['delivery_provider_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_provider_kyc_documents');
    }
};
