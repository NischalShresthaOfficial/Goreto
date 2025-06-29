<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('lat_long');
            $table->string('place_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('lat_long')->unique()->after('longitude');
            $table->dropColumn('place_id');
        });
    }
};
