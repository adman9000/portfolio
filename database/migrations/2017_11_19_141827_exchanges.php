<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Exchanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('exchanges', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('slug');
            $table->string('title');
        });

        Schema::create('exchanges_coins', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('coin_id');
            $table->integer('exchange_id');
            $table->string('code');
        });

        Schema::create('users_exchanges', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id');
            $table->integer('exchange_id');
            $table->string('api_key');
            $table->string('api_secret');
        });

        Schema::table('coin_prices', function (Blueprint $table) {
            $table->integer('exchange_coin_id');
        });

        Schema::table('coin_user', function (Blueprint $table) {
            $table->integer('exchange_coin_id');
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
        Schema::drop('exchanges');
        Schema::drop('exchanges_coins');
        Schema::drop('users_exchanges');
    }
}
