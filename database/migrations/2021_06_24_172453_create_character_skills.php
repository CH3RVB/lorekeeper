<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterSkills extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('skill_category_id')->unsigned()->nullable()->default(null);
            $table->integer('prerequisite_id')->unsigned()->nullable()->default(null); // Prerequisite id, from this table. Automatically set to parent_id unless specifically set to somethign else.
            $table->integer('parent_id')->unsigned()->nullable()->default(null);
            $table->integer('parent_level')->unsigned()->nullable()->default(null);
            $table->boolean('has_image')->default(0);
        });

        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('has_image')->default(0);
        });

        Schema::create('character_skills', function (Blueprint $table) {
            $table->id();
            $table->integer('character_id');
            $table->integer('skill_id');
            $table->integer('level');
        });
    
        Schema::create('prompt_skills', function (Blueprint $table) {
            $table->integer('prompt_id');
            $table->integer('skill_id');
            $table->integer('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_categories');
        Schema::dropIfExists('character_skills');
        Schema::dropIfExists('prompt_skills');
    }
}
