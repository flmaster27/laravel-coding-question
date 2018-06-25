<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSheepTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheep', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('pen_id')->nullable(false);
            $table->softDeletes();

            $table->index('pen_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sheep');
    }
}
