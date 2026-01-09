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
        Schema::create('student_readmissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('branch_id')->unsigned();
            $table->integer('student_id')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->integer('challan_id')->unsigned();
            $table->date('readmission_date');
            $table->string('remarks', 355)->nullable();
            $table->string('status', 20)->default('draft');
            $table->integer('session_id')->nullable()->unsigned();
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
        Schema::dropIfExists('student_readmissions');
    }
};
