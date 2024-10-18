<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRewardMaker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('object_rewards', function (Blueprint $table) {
            $table->renameColumn('earner_type', 'recipient_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('object_rewards', function (Blueprint $table) {
            $table->renameColumn('recipient_type', 'earner_type');
        });
    }
}
