<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantIdToPetstock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('showcase_stock', function (Blueprint $table) {
            $table->integer('variant_id')->unsigned()->nullable()->default(null);
            $table->string('pet_name')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('petstock', function (Blueprint $table) {
            //
        });
    }
}
