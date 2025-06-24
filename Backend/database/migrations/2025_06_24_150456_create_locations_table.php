<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude');
            $table->decimal('longitude');
            $table->string('plus_code')->nullable();
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
