<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnchantTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enchantments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->integer('cost')->unsigned()->nullable()->default(null);
            $table->integer('currency_id')->unsigned()->nullable()->default(null);
            $table->integer('enchantment_category_id')->unsigned()->nullable()->default(null);
            $table->string('description', 512)->nullable()->default(null);
            $table->string('parsed_description', 512)->nullable()->default(null);
            $table->boolean('has_image')->default(0);
            $table->boolean('allow_transfer')->default(1);

            $table->integer('parent_id')->unsigned()->nullable()->default(null);

            //$table->foreign('enchantment_category_id')->references('id')->on('enchantment_categories');
        });

        Schema::create('enchantment_stats', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('stat_id')->unsigned();
            $table->integer('enchantment_id')->unsigned(); 
            $table->integer('count')->unsigned()->nullable()->default(null); //stat boost amount

            //$table->foreign('enchantment_id')->references('id')->on('enchantments');
           // $table->foreign('stat_id')->references('id')->on('stats');
           // these arent working yet
        });

        Schema::create('user_enchantments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('gear_stack_id')->nullable()->default(null);
            $table->integer('weapon_stack_id')->nullable()->default(null);
            $table->integer('enchantment_id')->unsigned();
            $table->string('data', 1024)->nullable();
            $table->timestamp('attached_at')->nullable()->default(null);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable()->default(null);

            //$table->foreign('gear_stack_id')->references('id')->on('user_gears');
            //$table->foreign('enchantment_id')->references('id')->on('enchantments');
            //$table->foreign('weapon_stack_id')->references('id')->on('user_weapons');
        });

        Schema::create('enchantment_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('sort')->unsigned()->default(0);
            $table->text('description')->nullable()->default(null);
            $table->boolean('has_image')->default(0);
        });

        Schema::create('user_enchantments_log', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('enchantment_id')->unsigned();
            $table->integer('quantity')->unsigned()->default(1);

            $table->integer('sender_id')->unsigned()->nullable();
            $table->integer('recipient_id')->unsigned()->nullable();
            $table->string('log'); 
            $table->string('log_type'); 
            $table->string('data', 1024)->nullable();

            $table->timestamps();

            $table->foreign('enchantment_id')->references('id')->on('enchantments');
            $table->integer('stack_id')->unsigned()->nullable();
            $table->foreign('stack_id')->references('id')->on('user_enchantments');

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
        Schema::dropIfExists('enchantments');
        Schema::dropIfExists('enchantment_stats');
        Schema::dropIfExists('user_enchantments');
        Schema::dropIfExists('enchantment_categories');
        Schema::dropIfExists('user_enchantments_log');
        Schema::dropIfExists('gear_enchantments');
    }
}
