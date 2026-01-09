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
        Schema::create('challan_sec_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roll_no');
            $table->unsignedBigInteger('challan_id');
            $table->decimal('amount');
            $table->date('date');
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->integer('owned_by');
            $table->integer('created_by');
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
        Schema::dropIfExists('challan_sec_adjustments');
    }
};
