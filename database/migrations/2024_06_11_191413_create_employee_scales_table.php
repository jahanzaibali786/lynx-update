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
        Schema::create('employee_scales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('scale_no')->default(0);
            // $table->integer('initial_basic')->default(0);
            // $table->integer('house_rent')->default(0);
            // $table->integer('medical')->default(0);
            // $table->integer('special_allowance')->default(0);
            // $table->integer('gross_pay')->default(0);
            $table->integer('adhoc')->default(0);
            $table->date('effect_from');
            $table->integer('department_id')->default(0);
            $table->string('status')->default(1);
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
        Schema::dropIfExists('employee_scales');
    }
};
