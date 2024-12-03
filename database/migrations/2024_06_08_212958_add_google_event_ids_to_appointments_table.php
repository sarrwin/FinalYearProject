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
            $table->string('student_google_event_id')->nullable()->after('google_event_id');
            $table->string('supervisor_google_event_id')->nullable()->after('student_google_event_id');
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('student_google_event_id');
            $table->dropColumn('supervisor_google_event_id');
        });
    }
};
