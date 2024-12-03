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
            $table->string('meeting_details')->nullable()->after('end_time');
        });
    }

    public function down()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('meeting_details');
        });
    }
};
