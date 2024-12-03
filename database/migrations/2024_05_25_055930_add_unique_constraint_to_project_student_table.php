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
        Schema::table('project_student', function (Blueprint $table) {
            $table->unique('student_id'); // Ensure each student can only be assigned to one project
        });
    }

    public function down()
    {
        Schema::table('project_student', function (Blueprint $table) {
            $table->dropUnique(['student_id']); // Drop the unique constraint if rolled back
        });
    }
    
};
