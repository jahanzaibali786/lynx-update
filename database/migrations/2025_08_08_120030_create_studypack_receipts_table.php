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
        Schema::create('studypack_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->date('recipt_date');
            $table->integer('challan_id');
            $table->integer('student_id');
            $table->float('recipt_amount')->default(0);
            $table->float('challan_amount')->default(0);
            $table->float('late_amount')->default(0);
            $table->float('arrears')->default(0);
            $table->integer('bank_id');
            $table->integer('account_id');
            $table->integer('voucher_id')->nullable();
            $table->string('referance')->nullable();
            $table->string('receive_type',25)->nullable();
            $table->integer('received_by');
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
        Schema::dropIfExists('studypack_receipts');
    }
};
