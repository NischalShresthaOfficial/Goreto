<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->foreignId('chat_id')->nullable()->constrained('chats')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropForeign(['chat_id']);
            $table->dropColumn('chat_id');
        });
    }
};
