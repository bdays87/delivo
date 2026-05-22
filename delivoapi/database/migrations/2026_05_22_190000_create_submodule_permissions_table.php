<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submodule_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submodule_id')->constrained('submodules')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['submodule_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submodule_permissions');
    }
};
