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
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('branch_from_id');
            $table->integer('branch_to_id');
            $table->integer('department_from_id');
            $table->integer('department_to_id');
            $table->integer('designation_from_id');
            $table->integer('designation_to_id');
            $table->integer('status')->default(0);
            $table->date('transfer_date');
            $table->string('transfer_reason');
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
        Schema::dropIfExists('employee_transfers');
    }
};
