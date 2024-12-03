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
        Schema::table('supervisors', function (Blueprint $table) {
            $table->renameColumn('user_id', 'supervisor_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('user_id', 'student_id');
        });
    }

    public function down()
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->renameColumn('supervisor_id', 'user_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('student_id', 'user_id');
        });
    }
};
