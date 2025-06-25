<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['active', 'blocked']);
            $table->integer('likes')->default(0);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
