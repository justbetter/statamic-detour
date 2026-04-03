<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detours', function (Blueprint $table) {
            $table->string('query_string_handling')->nullable()->after('sites');
            $table->string('query_string_strip_keys')->nullable()->after('query_string_handling');
        });
    }

    public function down(): void
    {
        Schema::table('detours', function (Blueprint $table) {
            $table->dropColumn(['query_string_handling', 'query_string_strip_keys']);
        });
    }
};
