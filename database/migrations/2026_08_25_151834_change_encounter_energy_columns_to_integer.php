<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEncounterEnergyColumnsToInteger extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->integer('encounter_energy')->default(0)->change();
        });
        Schema::table('characters', function (Blueprint $table) {
            $table->integer('encounter_energy')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('encounter_energy')->default(0)->change();
        });
        Schema::table('characters', function (Blueprint $table) {
            $table->string('encounter_energy')->default(0)->change();
        });
    }
}
