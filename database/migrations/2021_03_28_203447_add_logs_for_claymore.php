<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogsForClaymore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('user_gears_log', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('gear_id')->unsigned();
            $table->integer('quantity')->unsigned()->default(1);

            $table->integer('sender_id')->unsigned()->nullable();
            $table->integer('recipient_id')->unsigned()->nullable();
            $table->string('log'); 
            $table->string('log_type'); 
            $table->string('data', 1024)->nullable();

            $table->timestamps();

            $table->foreign('gear_id')->references('id')->on('gears');
            $table->integer('stack_id')->unsigned()->nullable();
            $table->foreign('stack_id')->references('id')->on('user_gears');

            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('recipient_id')->references('id')->on('users');
        });

        Schema::create('user_weapons_log', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('weapon_id')->unsigned();
            $table->integer('quantity')->unsigned()->default(1);

            $table->integer('sender_id')->unsigned()->nullable();
            $table->integer('recipient_id')->unsigned()->nullable();
            $table->string('log'); 
            $table->string('log_type'); 
            $table->string('data', 1024)->nullable();

            $table->timestamps();

            $table->foreign('weapon_id')->references('id')->on('weapons');
            $table->integer('stack_id')->unsigned()->nullable();
            $table->foreign('stack_id')->references('id')->on('user_weapons');

            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('recipient_id')->references('id')->on('users');
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
        Schema::dropifExists('user_gears_log');
        Schema::dropifExists('user_weapons_log');
    }
}
