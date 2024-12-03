<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Add new columns or modify existing ones here
            if (!Schema::hasColumn('messages', 'chatroom_id')) {
                $table->foreignId('chatroom_id')->nullable()->constrained('chat_rooms')->onDelete('cascade')->after('room_id'); // Add 'chatroom_id' column
            }

            if (!Schema::hasColumn('messages', 'content')) {
                $table->text('content')->nullable()->after('message'); // Add 'content' column if needed
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Drop the added columns on rollback
            if (Schema::hasColumn('messages', 'chatroom_id')) {
                $table->dropForeign(['chatroom_id']);
                $table->dropColumn('chatroom_id');
            }

            if (Schema::hasColumn('messages', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
};