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
            // Drop the foreign key first
            $table->dropForeign(['room_id']);

            // Drop the room_id column after removing the foreign key
            $table->dropColumn('room_id');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Add back the room_id column and its foreign key on rollback
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('cascade');
        });
    }
};