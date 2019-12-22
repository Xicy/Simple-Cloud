<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeSymbolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_symbols', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger("exchange_id")->index();
            $table->bigInteger("coin_id")->index();
            $table->bigInteger("pair_id")->index();
            $table->string("symbol");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_symbols');
    }
}
