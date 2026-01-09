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
        Schema::create('emp_final_settlement_heads', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('final_settlement_id');
            $table->integer('scale_no');
            $table->integer('head_id');
            $table->float('head_value');
            $table->float('earned_value');
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
        Schema::dropIfExists('emp_final_settlement_heads');
    }
};
