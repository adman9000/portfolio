<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('code');
            $table->string('name');
        });

        Schema::create('users_coins', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->Integer('coin_id');
            $table->Double('amount_owned', 15, 7)->nullable();
            $table->Double('bought_at', 15, 7)->nullable();
            $table->Double('sold_at', 15, 7)->nullable();
            $table->Double('balance', 15, 7)->nullable();
            $table->Double('gbp_value', 15, 7)->nullable();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->Integer('coin_sold_id');
            $table->Integer('coin_bought_id');
            $table->Double('amount_sold', 15, 7);
            $table->Double('amount_bought', 15, 7);
            $table->Double('exhange_rate', 15, 7);
            $table->Double('fees', 15, 7);
        });

        Schema::create('coin_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->Integer('coin_id');
            $table->Double('current_price', 15, 7);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coins');
        Schema::dropIfExists('users_coins');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('coin_prices');
    }
}
