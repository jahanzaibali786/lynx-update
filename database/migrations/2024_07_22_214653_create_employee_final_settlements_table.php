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
        Schema::create('employee_final_settlements', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->integer('emp_id');
            $table->integer('designation_id');
            $table->date('doj');
            $table->string('tenure');
            $table->integer('payscale_no');
            $table->float('basic_sal');
            $table->integer('working_days');
            $table->integer('created_by')->nullable();
            $table->integer('owned_by')->nullable();
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
        Schema::dropIfExists('employee_final_settlements');
    }
};
