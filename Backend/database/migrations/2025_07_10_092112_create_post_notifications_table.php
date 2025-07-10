<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('post_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_notifications');
    }
}
