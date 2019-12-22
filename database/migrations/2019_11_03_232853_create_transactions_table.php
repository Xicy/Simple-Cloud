<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->enum('status', ["completed", "pending", "canceled"])->index();
            $table->decimal('amount', 32, 16);
            $table->string('tx_id')->nullable();
            $table->unsignedBigInteger('wallet_id')->index();
            $table->enum('type', ["withdraw", "deposit"])->index();
            $table->json('data')->nullable();
            $table->unsignedInteger('txout')->virtualAs("(`data` -> '$.txout')")->nullable();
            $table->timestamps();

            $table->unique(["wallet_id", "txout", "tx_id"]);
            //$table->foreign('wallet_id')->references('id')->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
