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
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->string('session')->after('students_required'); // Adds the session column after students_required
            $table->string('department')->after('session'); // Adds the department column after session
            $table->string('tools')->after('department'); // Adds the tools column after department
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['session', 'department', 'tools']);
            //
        });
    }
};
