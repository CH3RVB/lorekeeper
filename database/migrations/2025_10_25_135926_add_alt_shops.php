<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAltShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('shop_type')->nullable()->default(null);
            $table->longtext('alt_data')->nullable()->default(null);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->longtext('alt_data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('shop_type');
            $table->dropColumn('alt_data');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('alt_data');
        });
    }
}
