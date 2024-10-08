<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiringItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->timestamp('expiry_date')->nullable()->default(null);
            $table->unsignedInteger('expiry_number')->nullable()->default(null);
            $table->string('expiry_interval')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
            $table->dropColumn('expiry_number');
            $table->dropColumn('expiry_interval');
        });
    }
}
