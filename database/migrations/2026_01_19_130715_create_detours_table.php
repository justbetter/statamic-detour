<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detours', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('from');
            $table->string('to');
            $table->string('code');
            $table->string('type');
            $table->string('sites')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detours');
    }
};
