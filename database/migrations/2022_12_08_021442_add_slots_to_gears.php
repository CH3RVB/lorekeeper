<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlotsToGears extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gears', function (Blueprint $table) {
            $table->integer('slots')->unsigned()->nullable()->default(null);
        });

        Schema::table('weapons', function (Blueprint $table) {
            $table->integer('slots')->unsigned()->nullable()->default(null);
        });

        Schema::table('user_gears', function (Blueprint $table) {
            $table->boolean('slots')->default(false);
        });
        Schema::table('user_weapons', function (Blueprint $table) {
            $table->boolean('slots')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gears', function (Blueprint $table) {
            $table->dropColumn('slots');
        });
        Schema::table('weapons', function (Blueprint $table) {
            $table->dropColumn('slots');
        });
        Schema::table('user_gears', function (Blueprint $table) {
            $table->dropColumn('slots');
        });
        Schema::table('user_weapons', function (Blueprint $table) {
            $table->dropColumn('slots');
        });
    }
}
