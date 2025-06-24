<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->text('messages');
            $table->timestampsTz('sent_at')->nullable();
            $table->timestampsTz('seen_at')->nullable();
            $table->string('sent_by')->nullable();
            $table->timestampsTz();

            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
