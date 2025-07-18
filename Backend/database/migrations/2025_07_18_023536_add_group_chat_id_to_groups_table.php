<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedBigInteger('group_chat_id')->nullable()->after('created_by');
            $table->foreign('group_chat_id')
                ->references('id')
                ->on('chats')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['group_chat_id']);
            $table->dropColumn('group_chat_id');
        });
    }
};
