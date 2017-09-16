<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutotradeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
          Schema::table('coins', function (Blueprint $table) {
             $table->Double('highest_price', 15, 7);
             $table->Double('buy_point', 15, 7);
             $table->SmallInteger('sale_completed_1');
             $table->SmallInteger('sale_completed_2');
             $table->SmallInteger('sale_trigger_1');
             $table->SmallInteger('sale_trigger_2');
             $table->SmallInteger('been_bought');
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
         Schema::table('coins', function (Blueprint $table) {
             $table->dropColumn('highest_price');
             $table->dropColumn('buy_point');
             $table->dropColumn('sale_completed_1');
             $table->dropColumn('sale_completed_2');
             $table->dropColumn('sale_trigger_1');
             $table->dropColumn('sale_trigger_2');
             $table->dropColumn('been_bought');
        });
    }
}
