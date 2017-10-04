<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schemes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string("title");
            $table->integer("buy_drop_percent");
            $table->float("buy_amount", 15, 7);
            $table->integer("sell_1_gain_percent");
            $table->integer("sell_1_drop_percent");
            $table->integer("sell_1_sell_percent");
            $table->integer("sell_2_gain_percent");
            $table->integer("sell_2_drop_percent");
            $table->integer("sell_2_sell_percent");
            $table->integer("price_increase_percent");
        });

          Schema::create('coin_scheme', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer("coin_id");
            $table->integer("scheme_id");
            $table->float("set_price", 15, 7);
            $table->float("amount_held", 15, 7);
            $table->boolean("been_bought");
            $table->boolean("sale_1_completed");
            $table->boolean("sale_2_completed");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schemes');
        Schema::dropIfExists('coin_scheme');
    }
}
