<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Cmc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
      
       Schema::drop('coin_prices');


        Schema::table('coins', function (Blueprint $table) {
            $table->text('prices')->nullable(); 
            $table->bigInteger('current_supply')->nullable();
            $table->bigInteger('max_supply')->nullable();
            $table->dropColumn('exchange');
            $table->dropColumn('highest_price');
            $table->dropColumn('buy_point');
            $table->dropColumn('sale_completed_1');
            $table->dropColumn('sale_completed_2');
            $table->dropColumn('sale_trigger_1');
            $table->dropColumn('sale_trigger_2');
            $table->dropColumn('amount_owned');
            $table->dropColumn('been_bought');
        });


         Schema::create('cmc_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('coin_id')->unsigned()->index();
            $table->Double('btc_price', 16, 10)->nullable();
            $table->Double('usd_price', 16, 10)->nullable();
            $table->Double('gbp_price', 16, 10)->nullable();
            $table->bigInteger('current_supply')->nullable();
        });

         Schema::create('exchanges_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('coin_id')->unsigned()->index();
            $table->integer('exchange_id')->unsigned()->index();
            $table->integer('exchange_coin_id')->unsigned()->index();
            $table->Double('btc_price', 16, 10)->nullable();
            $table->Double('usd_price', 16, 10)->nullable();
            $table->Double('gbp_price', 16, 10)->nullable();
        });

         Schema::table('cmc_prices', function (Blueprint $table) {
            $table->foreign('coin_id')->references('id')->on('coins');
        });

         Schema::table('exchanges_prices', function (Blueprint $table) {
            $table->foreign('coin_id')->references('id')->on('coins');
            $table->foreign('exchange_id')->references('id')->on('exchanges');
            //$table->foreign('exchange_coin_id')->references('id')->on('coin_exchange');
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
    }
}
