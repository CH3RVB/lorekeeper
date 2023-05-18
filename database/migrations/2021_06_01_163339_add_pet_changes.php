<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPetChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('pet_categories', function (Blueprint $table) { 
            $table->boolean('allow_attach')->default(1);
        });

        Schema::table('user_pets', function (Blueprint $table) { 
            $table->timestamp('attached_at')->nullable()->default(null);
            $table->integer('variant_id')->nullable()->default(null);
        });

        Schema::create('pet_variants', function (Blueprint $table) { 
            $table->id();
            $table->integer('pet_id');
            $table->string('variant_name');
            $table->boolean('has_image')->default(0); 
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
        Schema::table('pet_categories', function (Blueprint $table) { 
            $table->dropColumn('allow_attach');
        });

        Schema::table('user_pets', function (Blueprint $table) { 
            $table->dropColumn('attached_at');
            $table->dropColumn('variant_id');
        });

        Schema::dropIfExists('pet_variants');
    }
}
