<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('milestones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id')->nullable()->default(null)->unsigned();
            $table->integer('subcategory_id')->nullable()->default(null)->unsigned();
            $table->integer('milestone')->unsigned();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_visible')->default(1);
            $table->boolean('has_image')->default(0);
            $table->string('summary', 300)->nullable()->default(null);

            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);

            $table->string('milestone_type')->default('Item');
        });

        Schema::create('user_milestones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('milestone_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('user_milestones');
    }
};
