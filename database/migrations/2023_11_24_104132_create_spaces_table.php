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
        Schema::create('spaces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('capacity');
            $table->string('price');
            $table->string('description');
            $table->unsignedInteger('type_id')->nullable();
            $table->foreign('type_id')->references('id')->on('space_types')->onDelete('cascade');            
            $table->enum('meeting', ['no', 'yes']);
            $table->enum('window', ['no', 'yes']);
            $table->unsignedBigInteger('owned_by');
            $table->unsignedBigInteger('created_by');
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
        Schema::dropIfExists('spaces');
    }
};
