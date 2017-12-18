<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::create('wallet_values', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('coin_id')->unsigned()->index();
            $table->integer('wallet_id')->unsigned()->index();
            $table->Double('balance', 20, 10)->nullable();
            $table->Double('btc_price', 16, 10)->nullable();
            $table->Double('usd_price', 20, 10)->nullable();
            $table->Double('gbp_price', 20, 10)->nullable();
            $table->Double('btc_value', 16, 10)->nullable();
            $table->Double('usd_value', 16, 2)->nullable();
            $table->Double('gbp_value', 16, 2)->nullable();
        });

         Schema::table('wallet_values', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('coin_id')->references('id')->on('coins');
            $table->foreign('wallet_id')->references('id')->on('wallets');
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
        Schema::dropIfExists('wallet_values');
    }
}
