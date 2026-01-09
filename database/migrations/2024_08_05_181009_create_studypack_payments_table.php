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
        Schema::create('studypack_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('challan_id');
            $table->date('date');
            $table->decimal('amount',15,2)->default('0.00');
            $table->integer('account_id');
            $table->integer('payment_method');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->integer('voucher_id')->nullable();
            $table->string('add_receipt')->nullable();
            $table->integer('owned_by')->nullable();
            $table->integer('created_by')->nullable();
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
        Schema::dropIfExists('studypack_payments');
    }
};
