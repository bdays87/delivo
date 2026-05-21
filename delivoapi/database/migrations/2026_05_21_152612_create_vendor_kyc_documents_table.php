<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('type', 50); // NATIONAL_ID for v1; CR14/COI/etc. later
            $table->string('original_filename');
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->unsignedInteger('size_bytes')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('status', 20)->default('PENDING'); // PENDING | APPROVED | REJECTED
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_kyc_documents');
    }
};
