<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCoinTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
          Schema::table('users_coins', function (Blueprint $table) {
            $table->Integer('user_id');
        });
            Schema::table('transactions', function (Blueprint $table) {
            $table->Integer('user_id');
        });
        Schema::rename("users_coins", "coin_user");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::rename("coin_user", "users_coins");
        Schema::table('users_coins', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
            Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
