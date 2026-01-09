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
        Schema::create('roomassigns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');   
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');   
            $table->unsignedInteger('space_id')->nullable();
            $table->foreign('space_id')->references('id')->on('spaces')->onDelete('cascade');   
            $table->unsignedInteger('chair_id')->nullable();
            $table->foreign('chair_id')->references('id')->on('chairs')->onDelete('cascade');    
            $table->enum('status', ['assign', 'close']);
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
        Schema::dropIfExists('roomassigns');

    }
};
