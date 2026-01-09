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
        Schema::create('class_wise_fees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('session_id')->nullable();
            $table->integer('class_id')->unsigned();
            $table->integer('head_id')->unsigned();
            $table->integer('amount');
            $table->integer('discount')->nullable();
            $table->integer('owned_by')->nullable()->unsigned();
            $table->integer('created_by')->nullable()->unsigned();
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
        Schema::dropIfExists('class_wise_fees');
    }
};
