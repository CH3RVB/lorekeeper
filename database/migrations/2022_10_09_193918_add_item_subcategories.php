<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemSubcategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_subcategories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            
            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->integer('sort')->unsigned()->default(0);
            $table->boolean('has_image')->default(0);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->integer('item_subcategory_id')->unsigned()->nullable()->default(null);
            $table->foreign('item_subcategory_id')->references('id')->on('item_subcategories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_subcategories');
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('item_subcategory_id');
        });
    }
}
