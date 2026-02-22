<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserCharaSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //after some deliberation i'm still going to keep these models and tables together to reduce redundancy and also allow even more functions on default admin sales
        Schema::table('sales', function (Blueprint $table) {
            //admin flag
            $table->boolean('is_admin')->default(true);
            $table->tinyInteger('limit_setting')->default(0); //0: no limit | 1: can enter for only 1 | 2: can enter for all but only win 1

            $table->boolean('is_rolled')->default(false);
        });

        Schema::table('sales_characters', function (Blueprint $table) {
            //having json to store everything is nice but by god we need more columns to handle what i'm doing here more easily. pray.

            //sometimes an auction is hosted on the behalf of another player, in which case, let's desinate the recipient (will grant to them as well if set to)
            $table->integer('recipient_id')->unsigned()->nullable()->default(null);

            $table->integer('winner_id')->unsigned()->nullable()->default(null);

            $table->longText('data')->nullable()->default(null)->change();

            $table->boolean('is_rolled')->default(false);
        });

        Schema::table('user_settings', function (Blueprint $table) {
            //not sure if this will get much use, but a flag for if the user can partake in sales (both user and admin)
            $table->boolean('is_sale_banned')->default(false);
            $table->string('paypal_email')->nullable()->default(null);
            $table->tinyInteger('get_user_sale_pings')->default(0);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_user_sales_unread')->default(0);
        });

        //bids for sales, similar to comments in a way
        Schema::create('sales_bids', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('sales_id');
            $table->integer('sales_character_id')->unsigned()->nullable()->default(null);
            //email for paypal, not front-facing
            $table->string('paypal_email')->nullable()->default(null);
            $table->boolean('is_featured')->default(false);
            //so many options...
            //pending: awaiting user input
            //accepted was accepted by the seller
            //paid is mostly for paypal
            //complete is 100% finished
            $table->enum('status', ['Pending', 'Accepted', 'Rejected', 'Paid', 'Complete','Requested','Approval'])->default('Pending');
            // Add a staff ID so we know who processed the trade, if applicable
            $table->integer('staff_id')->unsigned()->nullable()->default(null);

            $table->string('data')->nullable()->default(null);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('characters', function (Blueprint $table) {
            // Fill this with a trade ID so we can tell if the character is busy
            $table->integer('sales_id')->unsigned()->nullable()->default(null);
        });

        Schema::table('character_transfers', function (Blueprint $table) {
            // Fill this with a trade ID so we can tell if the character is busy
            $table->integer('sales_bid_id')->unsigned()->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('is_admin');
            $table->dropColumn('limit_setting');
            $table->dropColumn('is_rolled');
        });

        Schema::dropIfExists('sales_bids');

        Schema::table('sales_characters', function (Blueprint $table) {
            $table->dropColumn('recipient_id');
            $table->dropColumn('winner_id');
            $table->dropColumn('is_rolled');
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('is_sale_banned');
            $table->dropColumn('paypal_email');
            $table->dropColumn('get_user_sale_pings');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_user_sales_unread');
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('sales_id');
        });

        Schema::table('character_transfers', function (Blueprint $table) {
            $table->dropColumn('sales_bid_id');
        });
    }
}
