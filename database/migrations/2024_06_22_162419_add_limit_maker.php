<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLimitMaker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('object_limits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('object_id');
            $table->string('object_type');
            $table->integer('limit_id');
            $table->string('limit_type')->default('Item');
            $table->integer('quantity')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('object_limits');
    }
}
