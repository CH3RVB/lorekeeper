<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowcaseTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('showcases', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(1);
            $table->string('name');
            $table->boolean('has_image')->default(0);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);

            $table->integer('sort')->unsigned()->default(0);

            $table->boolean('is_active')->default(1);
        });
        Schema::create('showcase_stock', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('showcase_id')->unsigned()->index();
            $table->integer('item_id')->unsigned();
            $table->integer('section_id')->unsigned()->nullable();
            $table->integer('subsection_id')->unsigned()->nullable();
            $table->string('data', 1024)->nullable(); // includes information like staff notes, etc.
            $table->integer('quantity')->default(0);
            $table->string('stock_type')->default('Item');
            $table->boolean('is_visible')->default(0);
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
