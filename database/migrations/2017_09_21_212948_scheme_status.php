<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchemeStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
          Schema::table('schemes', function (Blueprint $table) {

            $table->boolean("enabled");
            $table->dateTime("date_start")->nullable();

          });
           Schema::table('coin_scheme', function (Blueprint $table) {
            $table->float("amount_held", 15, 7)->nullable()->change();
            $table->boolean("been_bought")->default(false)->change();
            $table->boolean("sale_1_completed")->default(false)->change();
            $table->boolean("sale_2_completed")->default(false)->change();
            $table->boolean("sale_1_triggered")->default(false);
            $table->boolean("sale_2_triggered")->default(false);
            $table->float("highest_price", 15, 7)->nullable();
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
        Schema::table('schemes', function (Blueprint $table) {
            $table->dropColumn("enabled");
            $table->dropColumn("date_start");
        });
    }
}
