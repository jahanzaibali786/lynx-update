<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challan_heads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('challan_id')->unsigned();
            $table->integer('head_id')->unsigned();
            $table->integer('price');
            $table->integer('concession');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challan_heads');
    }
};
