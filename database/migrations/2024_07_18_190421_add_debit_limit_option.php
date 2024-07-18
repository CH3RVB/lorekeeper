<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDebitLimitOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //making a limit setting thing for other functs
        Schema::create('limit_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('object_id');
            $table->string('object_type');

            $table->boolean('debit_limits')->default(0);
            $table->boolean('use_characters')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('limit_settings');
    }
}
