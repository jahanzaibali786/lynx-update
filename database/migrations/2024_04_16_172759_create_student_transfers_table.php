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
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('student_id')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->integer('challan_id')->unsigned();
            $table->date('transfer_date');
            $table->string('transfer_type',33);
            $table->integer('branch_from');
            $table->integer('class_from');
            $table->integer('section_from')->nullable();
            $table->integer('branch_to');
            $table->integer('class_to');
            $table->integer('section_to')->nullable();
            $table->string('reason', 355);
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
        Schema::dropIfExists('student_transfers');
    }
};
