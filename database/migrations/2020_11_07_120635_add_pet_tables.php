<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPetTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pet_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->boolean('has_image')->default(0);
            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->integer('sort')->unsigned()->default(0);
        });

        Schema::create('pets', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('pet_category_id')->unsigned()->nullable()->default(null);
            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->boolean('has_image')->default(0);

            $table->foreign('pet_category_id')->references('id')->on('pet_categories');
            $table->boolean('allow_transfer')->default(1);
        });

        Schema::create('user_pets', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('pet_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->integer('count')->unsigned()->default(1);

            $table->string('data', 1024)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pet_id')->references('id')->on('pets');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('user_pets_log', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('pet_id')->unsigned();
            $table->integer('quantity')->unsigned()->default(1);

            $table->integer('sender_id')->unsigned()->nullable();
            $table->integer('recipient_id')->unsigned()->nullable();
            $table->string('log'); 
            $table->string('log_type'); 
            $table->string('data', 1024)->nullable();

            $table->timestamps();

            $table->foreign('pet_id')->references('id')->on('pets');
            $table->integer('stack_id')->unsigned()->nullable();
            $table->foreign('stack_id')->references('id')->on('user_pets');

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
        Schema::dropIfExists('user_pets_log');
        Schema::dropIfExists('user_pets');
        Schema::dropIfExists('pets');
        Schema::dropIfExists('pet_categories');
    }
}