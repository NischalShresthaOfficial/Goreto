<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_weather_conditions', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->decimal('temperature');
            $table->decimal('humidity');
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_weather_conditions');
    }
};
