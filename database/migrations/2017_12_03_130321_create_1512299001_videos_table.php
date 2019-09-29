<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create1512299001VideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(! Schema::hasTable('videos')) {
            Schema::create('videos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->unsignedInteger('user_id');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id', '94785_5c23d71b74f2b')->references('id')->on('users')->onDelete('cascade');

                $table->index(['deleted_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
