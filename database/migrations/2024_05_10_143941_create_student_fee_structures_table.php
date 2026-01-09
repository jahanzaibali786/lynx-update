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
        Schema::create('student_fee_structures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reg_id')->unsigned();
            $table->integer('student_id')->unsigned();
            $table->integer('head_id')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->integer('branch_id')->unsigned();
            $table->integer('amount');
            $table->integer('discount');
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
        Schema::dropIfExists('student_fee_structures');
    }
};
