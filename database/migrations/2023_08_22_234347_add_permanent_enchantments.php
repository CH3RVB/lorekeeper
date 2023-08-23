<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPermanentEnchantments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gear_enchantments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('gear_id')->unsigned();
            $table->integer('enchantment_id')->unsigned();

            $table->integer('quantity');

            $table->foreign('gear_id')->references('id')->on('gears');
            $table->foreign('enchantment_id')->references('id')->on('enchantments');
        });

        Schema::create('weapon_enchantments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('weapon_id')->unsigned();
            $table->integer('enchantment_id')->unsigned();

            $table->integer('quantity');

            $table->foreign('weapon_id')->references('id')->on('weapons');
            $table->foreign('enchantment_id')->references('id')->on('enchantments');
        });

        Schema::table('enchantment_categories', function (Blueprint $table) {

            $table->integer('class_restriction')->unsigned()->nullable()->default(null);

            $table->foreign('class_restriction')->references('id')->on('character_classes');
        });

        Schema::table('user_enchantments_log', function (Blueprint $table) {
            $table->text('log', 2048)->change();
            $table->text('data', 2048)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
