<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaymoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /************************************************************************************************
         * CHARACTER CLASSES / CHARACTER RELATED MIGRATIONS
         ************************************************************************************************/
        Schema::create('character_classes', function (Blueprint $table) { 
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->boolean('has_image')->default(0);
        });

        Schema::table('characters', function (Blueprint $table) { 
            $table->integer('class_id')->unsigned()->nullable()->default(null);

            $table->foreign('class_id')->references('id')->on('character_classes');
        });

        Schema::table('character_stats', function (Blueprint $table) { 
            $table->integer('count')->default(1)->change();
        });

        Schema::table('stats', function (Blueprint $table) { 
            $table->dropColumn('default');
            $table->integer('base')->default(1);
        });

        /************************************************************************************************
         * GEAR
         ************************************************************************************************/
        Schema::create('gear_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('sort')->unsigned()->default(0);
            $table->text('description')->nullable()->default(null);
            $table->boolean('has_image')->default(0);

            $table->integer('class_restriction')->unsigned()->nullable()->default(null);

            $table->foreign('class_restriction')->references('id')->on('character_classes');
        });

        Schema::create('gears', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('gear_category_id')->unsigned()->nullable()->default(null);

            $table->string('name');
            $table->string('description', 512)->nullable()->default(null);
            $table->boolean('has_image')->default(0);

            $table->boolean('allow_transfer')->default(1);

            $table->integer('parent_id')->unsigned()->nullable()->default(null);

            $table->foreign('gear_category_id')->references('id')->on('gear_categories');
        });

        Schema::create('user_gears', function (Blueprint $table) {
            $table->Increments('id');

            $table->integer('gear_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->integer('character_id')->unsigned()->nullable()->default(null);
            $table->timestamp('attached_at')->nullable()->default(null);

            $table->string('data', 1024)->nullable();

            // custom image
            $table->boolean('has_image')->default(0);

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable()->default(null);

            $table->foreign('gear_id')->references('id')->on('gears');
            $table->foreign('character_id')->references('id')->on('characters');
        });

        /************************************************************************************************
         * WEAPONS
         ************************************************************************************************/
        Schema::create('weapon_categories', function (Blueprint $table) {
            $table->increments('id');

            $table->boolean('has_image')->default(0);
            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->integer('sort')->unsigned()->default(0);

            $table->integer('class_restriction')->unsigned()->nullable()->default(null);
            
            $table->foreign('class_restriction')->references('id')->on('character_classes');
        });

        Schema::create('weapons', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('weapon_category_id')->unsigned()->nullable()->default(null);

            $table->string('name');
            $table->string('description', 512)->nullable()->default(null);
            $table->boolean('has_image')->default(0);

            $table->integer('parent_id')->unsigned()->nullable()->default(null);

            $table->boolean('allow_transfer')->default(1);

            $table->foreign('weapon_category_id')->references('id')->on('weapon_categories');
        });

        Schema::create('user_weapons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();

            $table->integer('weapon_id')->unsigned();
            
            $table->integer('character_id')->unsigned()->nullable()->default(null);
            $table->timestamp('attached_at')->nullable()->default(null);

            $table->string('data', 1024)->nullable();

            // custom image
            $table->boolean('has_image')->default(0);

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable()->default(null);

            $table->foreign('weapon_id')->references('id')->on('weapons');
            $table->foreign('character_id')->references('id')->on('characters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { 
        // GEAR
        Schema::dropIfExists('user_gears');
        Schema::dropIfExists('gears');
        Schema::dropIfExists('gear_categories');
        // WEAPONS
        Schema::dropIfExists('user_weapons');
        Schema::dropIfExists('weapons');
        Schema::dropIfExists('weapon_categories');
        // CHARACTER
        Schema::table('characters', function (Blueprint $table) { 
            $table->dropForeign('characters_class_id_foreign');
            $table->dropColumn('class_id');
        });
        Schema::dropIfExists('character_classes');
    }
}
