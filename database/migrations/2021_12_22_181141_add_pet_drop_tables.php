<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPetDropTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('pet_drop_data', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('pet_id')->unsigned();

            // Will hold defined parameters and item data.
            $table->text('parameters')->nullable()->default(null);
            $table->text('data')->nullable()->default(null);

            $table->boolean('is_active')->default(1);
        });

        Schema::create('pet_drops', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            // Specific drop data being used, as well as associated character
            $table->integer('drop_id')->unsigned();
            $table->integer('user_pet_id')->unsigned();

            // Specific parameters associated with the individual character
            $table->text('parameters')->nullable();

            // Number of opportunities to collect the drops. Not equivalent to quantity.
            $table->integer('drops_available')->unsigned()->default(0);

            // Timestamp at which next drop becomes available
            $table->timestamp('next_day')->nullable()->default(null);

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
        Schema::dropIfExists('pet_drop_data');
        Schema::dropIfExists('pet_drops');
    }
}
