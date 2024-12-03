<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('slots', function (Blueprint $table) {
            // Check if the foreign key and column exist before dropping them
            if (Schema::hasColumn('slots', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }

            // Add new foreign key referencing supervisors table
            if (!Schema::hasColumn('slots', 'supervisor_id')) {
                $table->foreignId('supervisor_id')->constrained('supervisors')->onDelete('cascade')->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('slots', function (Blueprint $table) {
            // Drop new foreign key if it exists
            if (Schema::hasColumn('slots', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }

            // Add old foreign key back
            if (!Schema::hasColumn('slots', 'supervisor_id')) {
                $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade')->after('id');
            }
        });
    }
};
