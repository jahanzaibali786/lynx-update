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
        Schema::create('student_withdrawals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('student_id')->unsigned();
            $table->integer('challan_id')->nullable()->unsigned();
            $table->integer('branch_id');
            $table->integer('class_id')->unsigned();
            $table->date('apply_date');
            $table->date('withdraw_date');
            $table->integer('document_no');
            $table->date('document_date');
            $table->string('remark', 355);
            $table->string('reason', 155);
            $table->string('status', 20)->default('draft');
            $table->integer('owned_by')->nullable()->unsigned();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('session_id')->nullable()->unsigned();
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
        Schema::dropIfExists('student_withdrawals');
    }
};
