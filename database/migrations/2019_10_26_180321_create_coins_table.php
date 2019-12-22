<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("name");
            $table->string('symbol');
            $table->string('gateway');
            $table->string('market')->nullable();
            $table->string('key')->unique();
            $table->string('rpc_url')->nullable();
            $table->unsignedBigInteger('height')->default(0);
            $table->unsignedInteger('block_time')->default(60);
            $table->boolean('enable')->default(true);
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
    }
}
