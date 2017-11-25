<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('transactions', function (Blueprint $table) {
            $table->Text('status');
            $table->Text('uuid');
            $table->Integer('scheme_id');
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
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('uuid');
            $table->dropColumn('scheme_id');
        });
    }
}
