<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_images', function (Blueprint $table) {
            $table->enum('status', ['verified', 'unverified'])
                  ->default('unverified');
        });
    }

    public function down(): void
    {
        Schema::table('location_images', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
