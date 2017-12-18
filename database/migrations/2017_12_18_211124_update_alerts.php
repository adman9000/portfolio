<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('alerts', function (Blueprint $table) {

            $table->Double('gbp_current_value', 16, 2)->nullable();
            $table->Double('gbp_current_price', 16, 2)->nullable();
            $table->Double('gbp_min_price', 16, 2)->nullable();
            $table->Double('gbp_max_price', 16, 2)->nullable();
            $table->Double('balance', 20, 10)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn('gbp_current_value');
            $table->dropColumn('gbp_current_price');
            $table->dropColumn('gbp_min_price');
            $table->dropColumn('gbp_max_price');
            $table->dropColumn('balance');
        });
    }
}
