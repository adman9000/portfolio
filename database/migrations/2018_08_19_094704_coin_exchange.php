<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoinExchange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
          Schema::create('coin_exchange', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer("coin_id");
            $table->integer("exchange_id");
            $table->string('code')->nullable();;
            $table->string('market_code')->nullable();;
            $table->Double('btc_price', 15, 7)->nullable();;
            $table->Double('usd_price', 15, 7)->nullable();;
            $table->Double('gbp_price', 15, 7)->nullable();;
        });

          Schema::table('coin_exchange', function (Blueprint $table) {
            $table->foreign('coin_id')->references('id')->on('coins');
            //$table->foreign('exchange_id')->references('id')->on('exchanges');
        });
           Schema::table('exchanges_prices', function (Blueprint $table) {
            $table->foreign('exchange_coin_id')->references('id')->on('coin_exchange');
        });

           Schema::table('coin_user', function(Blueprint $table) {
            $table->Double('available', 20,10)->nullable();
            $table->Double('locked', 20,10)->nullable();
            $table->integer("user_exchange_id");

           })

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists("coin_exchange");
    }
}
