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
            $table->boolean('booked')->nullable()->default(false)->change(); // Make the booked column nullable and default to false
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->boolean('booked')->default(false)->change(); // Revert back to non-nullable and default to false if necessary
        });
    }
};
