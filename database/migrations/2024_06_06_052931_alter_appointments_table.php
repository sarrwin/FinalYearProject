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
        Schema::table('appointments', function (Blueprint $table) {
            // Check if the foreign keys and columns exist before dropping them
            if (Schema::hasColumn('appointments', 'student_id')) {
                $table->dropForeign(['student_id']);
                $table->dropColumn('student_id');
            }

            if (Schema::hasColumn('appointments', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }

            // Add new foreign keys referencing students and supervisors tables
            if (!Schema::hasColumn('appointments', 'student_id')) {
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->after('id');
            }

            if (!Schema::hasColumn('appointments', 'supervisor_id')) {
                $table->foreignId('supervisor_id')->constrained('supervisors')->onDelete('cascade')->after('student_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Drop new foreign keys if they exist
            if (Schema::hasColumn('appointments', 'student_id')) {
                $table->dropForeign(['student_id']);
                $table->dropColumn('student_id');
            }

            if (Schema::hasColumn('appointments', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            }

            // Add old foreign keys back
            if (!Schema::hasColumn('appointments', 'student_id')) {
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade')->after('id');
            }

            if (!Schema::hasColumn('appointments', 'supervisor_id')) {
                $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade')->after('student_id');
            }
        });
    }
};
