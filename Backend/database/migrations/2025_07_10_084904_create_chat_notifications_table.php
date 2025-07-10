<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('chat_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content');

            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');

            $table->boolean('is_read')->default(false);

            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_notifications');
    }
}
